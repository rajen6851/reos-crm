<?php

namespace App\Services;

use App\Events\BrokerLeadSubmitted;
use App\Models\Broker;
use App\Models\BrokerLead;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BrokerLeadService
{
    public function __construct(
        protected DuplicateLeadService $duplicateLeadService,
        protected BrokerLeadStatusService $statusService
    ) {}

    public function submitBrokerLead(User $user, array $data): array
    {
        return DB::transaction(function () use ($user, $data) {
            // Derive authenticated broker profile
            $broker = Broker::where('company_id', $user->company_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$broker) {
                // If user is a broker, auto-derive or require active broker record
                $broker = Broker::firstOrCreate(
                    [
                        'company_id' => $user->company_id,
                        'user_id' => $user->id,
                    ],
                    [
                        'agency_name' => $user->name . ' Agency',
                        'broker_code' => 'BRK-' . strtoupper(Str::random(6)),
                        'phone' => $user->phone ?? '0000000000',
                        'email' => $user->email,
                        'commission_rate' => 2.00,
                        'status' => 'active',
                    ]
                );
            }

            $phone = $data['phone'];
            $email = $data['email'] ?? null;

            // Perform duplicate check strictly within company_id
            $existingLead = $this->duplicateLeadService->checkDuplicate($user->company_id, $phone, $email);
            $isDuplicate = $existingLead !== null;

            // Generate Lead Code
            $leadCode = 'LD-' . strtoupper(Str::random(8));

            // Create main Lead record
            $lead = Lead::create([
                'company_id' => $user->company_id,
                'lead_code' => $leadCode,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? null,
                'email' => $email,
                'phone' => $phone,
                'alternate_phone' => $data['alternate_phone'] ?? null,
                'broker_id' => $broker->id,
                'interested_project_id' => $data['project_id'],
                'interested_unit_type' => $data['unit_type'] ?? null,
                'budget_min' => $data['budget_min'] ?? null,
                'budget_max' => $data['budget_max'] ?? null,
                'status' => 'new',
                'is_duplicate' => $isDuplicate,
                'duplicate_of_lead_id' => $existingLead?->id,
                'notes' => $data['requirement_notes'] ?? null,
            ]);

            // Create BrokerLead authoritative visibility record
            $brokerLead = BrokerLead::create([
                'company_id' => $user->company_id,
                'broker_id' => $broker->id,
                'lead_id' => $lead->id,
                'project_id' => $data['project_id'],
                'unit_id' => $data['unit_id'] ?? null,
                'submitted_at' => now(),
                'broker_visible_status' => 'Submitted',
                'broker_visible_message' => 'Lead successfully submitted and waiting for manager review.',
                'property_type' => $data['property_type'] ?? null,
                'unit_type' => $data['unit_type'] ?? null,
                'budget_min' => $data['budget_min'] ?? null,
                'budget_max' => $data['budget_max'] ?? null,
                'preferred_location' => $data['preferred_location'] ?? null,
                'requirement_notes' => $data['requirement_notes'] ?? null,
                'city' => $data['city'] ?? null,
                'customer_type' => $data['customer_type'] ?? 'individual',
            ]);

            // Audit log
            LeadActivity::create([
                'company_id' => $user->company_id,
                'lead_id' => $lead->id,
                'user_id' => $user->id,
                'activity_type' => 'broker_lead_submitted',
                'description' => "Lead submitted by Broker agency {$broker->agency_name}",
                'metadata' => [
                    'broker_id' => $broker->id,
                    'is_duplicate' => $isDuplicate,
                    'project_id' => $data['project_id'],
                ],
            ]);

            // Event dispatching
            event(new BrokerLeadSubmitted($lead, $brokerLead, $broker));

            return [
                'lead' => $lead,
                'broker_lead' => $brokerLead,
                'is_duplicate' => $isDuplicate,
            ];
        });
    }
}
