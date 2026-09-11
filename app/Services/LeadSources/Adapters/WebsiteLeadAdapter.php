<?php

namespace App\Services\LeadSources\Adapters;

use App\Models\LeadSource;

class WebsiteLeadAdapter extends AbstractLeadSourceAdapter
{
    public function getType(): string
    {
        return 'website';
    }

    public function getName(): string
    {
        return 'Website Lead Form / Custom Webhook';
    }

    public function parseWebhookPayload(array $payload, LeadSource $source): array
    {
        $name = $payload['name'] ?? $payload['full_name'] ?? trim(($payload['first_name'] ?? '') . ' ' . ($payload['last_name'] ?? ''));
        if (empty($name)) {
            $name = 'Website Visitor';
        }
        $parts = explode(' ', trim($name), 2);

        $phone = $payload['phone'] ?? $payload['mobile'] ?? $payload['contact_number'] ?? '';
        $email = $payload['email'] ?? '';
        $project = $payload['project'] ?? $payload['project_name'] ?? null;
        $notes = $payload['notes'] ?? $payload['message'] ?? 'Lead submitted via Website Webhook';

        return [
            'first_name' => $parts[0] ?? 'Website',
            'last_name' => $parts[1] ?? 'Visitor',
            'phone' => $this->sanitizePhone($phone),
            'email' => $email,
            'project_name' => $project,
            'campaign_name' => $payload['campaign'] ?? 'Website Direct Inquiry',
            'source_lead_id' => (string) ($payload['lead_id'] ?? $payload['id'] ?? null),
            'notes' => $notes,
            'raw_payload' => $payload,
        ];
    }
}
