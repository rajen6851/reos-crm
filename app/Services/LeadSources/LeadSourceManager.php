<?php

namespace App\Services\LeadSources;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadSource;
use App\Models\Project;
use App\Services\LeadDistributionService;
use App\Services\NotificationService;
use App\Services\LeadSources\Adapters\GoogleLeadAdapter;
use App\Services\LeadSources\Adapters\HousingAdapter;
use App\Services\LeadSources\Adapters\MagicBricksAdapter;
use App\Services\LeadSources\Adapters\MetaLeadAdapter;
use App\Services\LeadSources\Adapters\NinetyNineAcresAdapter;
use App\Services\LeadSources\Adapters\WebsiteLeadAdapter;
use App\Services\LeadSources\Contracts\LeadSourceAdapterInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LeadSourceManager
{
    /**
     * @var array<string, LeadSourceAdapterInterface>
     */
    protected array $adapters = [];

    public function __construct(
        protected LeadDistributionService $distributionService,
        protected ?NotificationService $notificationService = null
    ) {
        $this->registerAdapter(new MetaLeadAdapter());
        $this->registerAdapter(new GoogleLeadAdapter());
        $this->registerAdapter(new NinetyNineAcresAdapter());
        $this->registerAdapter(new MagicBricksAdapter());
        $this->registerAdapter(new HousingAdapter());
        $this->registerAdapter(new WebsiteLeadAdapter());
    }

    public function registerAdapter(LeadSourceAdapterInterface $adapter): void
    {
        $this->adapters[$adapter->getType()] = $adapter;
    }

    public function getAdapter(string $type): LeadSourceAdapterInterface
    {
        return $this->adapters[$type] ?? new WebsiteLeadAdapter();
    }

    public function getSupportedTypes(): array
    {
        return [
            'meta' => [
                'type' => 'meta',
                'name' => 'Meta Ads (Facebook & Instagram)',
                'icon' => 'fab fa-facebook-square',
                'color' => 'indigo',
                'description' => 'Connect Facebook & Instagram Lead Ads for real-time lead capture.',
                'fields' => [
                    'page_id' => 'Page ID',
                    'access_token' => 'System User Access Token',
                ],
            ],
            'google' => [
                'type' => 'google',
                'name' => 'Google Ads Lead Forms',
                'icon' => 'fab fa-google',
                'color' => 'rose',
                'description' => 'Capture leads directly from Google Search & Display Lead Form extensions.',
                'fields' => [
                    'google_secret_key' => 'Google Webhook Secret Key',
                ],
            ],
            '99acres' => [
                'type' => '99acres',
                'name' => '99acres Property Portal',
                'icon' => 'fas fa-building',
                'color' => 'blue',
                'description' => 'Real-time property buyer inquiries from 99acres.',
                'fields' => [
                    'api_key' => '99acres API Key / Key Secret',
                    'username' => '99acres Account ID / Username',
                ],
            ],
            'magicbricks' => [
                'type' => 'magicbricks',
                'name' => 'MagicBricks Portal',
                'icon' => 'fas fa-home',
                'color' => 'amber',
                'description' => 'Receive buyer queries directly from MagicBricks account.',
                'fields' => [
                    'api_key' => 'MagicBricks Lead API Key',
                ],
            ],
            'housing' => [
                'type' => 'housing',
                'name' => 'Housing.com Portal',
                'icon' => 'fas fa-city',
                'color' => 'emerald',
                'description' => 'Ingest customer leads from Housing.com property listings.',
                'fields' => [
                    'auth_token' => 'Housing.com Auth Token',
                ],
            ],
            'website' => [
                'type' => 'website',
                'name' => 'Website Lead Form / Custom Webhook',
                'icon' => 'fas fa-globe',
                'color' => 'cyan',
                'description' => 'Connect custom website forms, landing pages, or generic JSON webhooks.',
                'fields' => [
                    'secret_key' => 'Optional Verification Token',
                ],
            ],
        ];
    }

    public function processIncomingLead(LeadSource $source, array $payload): array
    {
        try {
            $adapter = $this->getAdapter($source->type);
            $parsed = $adapter->parseWebhookPayload($payload, $source);

            if (empty($parsed['phone'])) {
                Log::warning("[LEAD INGESTION FAILED] Missing phone number for source #{$source->id} ({$source->name})", $payload);
                $source->update([
                    'error_log' => 'Last payload rejected: Phone number missing in webhook payload.',
                ]);
                return [
                    'success' => false,
                    'message' => 'Phone number missing in webhook payload.',
                ];
            }

            // Resolve Project ID
            $projectId = null;
            if (!empty($parsed['project_name'])) {
                $project = Project::where('company_id', $source->company_id)
                    ->where('name', 'LIKE', '%' . $parsed['project_name'] . '%')
                    ->first();
                if ($project) {
                    $projectId = $project->id;
                }
            }

            if (!$projectId) {
                $projectId = $source->settings['default_project_id'] ?? null;
            }

            if (!$projectId) {
                $firstProject = Project::where('company_id', $source->company_id)->first();
                $projectId = $firstProject?->id;
            }

            // Deduplication Check
            $existingLead = Lead::where('company_id', $source->company_id)
                ->where('phone', $parsed['phone'])
                ->first();

            if ($existingLead) {
                Log::info("[LEAD INGESTION] Duplicate lead detected for phone {$parsed['phone']} (Existing Lead #{$existingLead->id})");
                
                // Update existing lead's last activity
                $existingLead->update([
                    'last_activity_at' => now(),
                ]);

                // Add activity note instead of creating a new lead
                LeadActivity::create([
                    'company_id' => $source->company_id,
                    'lead_id' => $existingLead->id,
                    'user_id' => null,
                    'activity_type' => 'updated',
                    'description' => "Duplicate form submission received via {$source->name}." . ($parsed['campaign_name'] ? " Campaign: {$parsed['campaign_name']}" : ""),
                    'metadata' => [
                        'source_type' => $source->type,
                        'source_id' => $source->id,
                        'source_lead_id' => $parsed['source_lead_id'] ?? null,
                        'is_duplicate_submission' => true
                    ],
                ]);

                $source->update([
                    'last_synced_at' => now(),
                    'error_log' => null,
                    'status' => 'connected',
                ]);

                return [
                    'success' => true,
                    'lead' => $existingLead,
                    'is_duplicate' => true,
                    'message' => 'Duplicate lead updated successfully.'
                ];
            }

            // Create NEW Lead Record (only if not duplicate)
            $leadCode = 'LD-' . strtoupper($source->type) . '-' . strtoupper(Str::random(6));

            $lead = Lead::create([
                'company_id' => $source->company_id,
                'source_id' => $source->id,
                'lead_code' => $leadCode,
                'first_name' => $parsed['first_name'] ?: 'Prospect',
                'last_name' => $parsed['last_name'] ?: '',
                'phone' => $parsed['phone'],
                'email' => $parsed['email'] ?: null,
                'interested_project_id' => $projectId,
                'status' => 'new',
                'is_duplicate' => false,
                'duplicate_of_lead_id' => null,
                'notes' => $parsed['notes'] ?? "Lead received via {$source->name}",
            ]);

            // Audit Activity Log
            LeadActivity::create([
                'company_id' => $source->company_id,
                'lead_id' => $lead->id,
                'user_id' => null,
                'activity_type' => 'created',
                'description' => "Lead ingested via integration: {$source->name}",
                'metadata' => [
                    'source_type' => $source->type,
                    'source_id' => $source->id,
                    'source_lead_id' => $parsed['source_lead_id'] ?? null,
                    'campaign_name' => $parsed['campaign_name'] ?? null,
                ],
            ]);

            // Trigger 2-Tier Round-Robin Auto-Distribution
            $this->distributionService->distributeNewLead($lead, $this->notificationService);

            // Update LeadSource status
            $source->update([
                'last_synced_at' => now(),
                'error_log' => null,
                'status' => 'connected',
            ]);

            Log::info("[LEAD INGESTION SUCCESS] Ingested Lead #{$lead->id} ({$lead->lead_code}) from Source #{$source->id} ({$source->name})");

            return [
                'success' => true,
                'lead' => $lead,
                'is_duplicate' => false,
            ];
        } catch (\Throwable $e) {
            Log::error("[LEAD INGESTION ERROR] Exception: {$e->getMessage()}", [
                'source_id' => $source->id,
                'payload' => $payload,
                'trace' => $e->getTraceAsString(),
            ]);

            $source->update([
                'error_log' => $e->getMessage(),
                'status' => 'error',
            ]);

            return [
                'success' => false,
                'message' => 'Internal error processing webhook payload: ' . $e->getMessage(),
            ];
        }
    }
}
