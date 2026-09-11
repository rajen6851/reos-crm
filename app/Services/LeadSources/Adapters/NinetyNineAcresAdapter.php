<?php

namespace App\Services\LeadSources\Adapters;

use App\Models\LeadSource;

class NinetyNineAcresAdapter extends AbstractLeadSourceAdapter
{
    public function getType(): string
    {
        return '99acres';
    }

    public function getName(): string
    {
        return '99acres Property Portal';
    }

    public function parseWebhookPayload(array $payload, LeadSource $source): array
    {
        $name = $payload['name'] ?? $payload['buyer_name'] ?? $payload['contact_name'] ?? '99acres Prospect';
        $parts = explode(' ', trim($name), 2);

        $phone = $payload['phone'] ?? $payload['mobile'] ?? $payload['contact_number'] ?? '';
        $email = $payload['email'] ?? '';
        $project = $payload['project'] ?? $payload['property_name'] ?? null;
        $leadId = $payload['query_id'] ?? $payload['lead_id'] ?? null;

        return [
            'first_name' => $parts[0] ?? '99acres',
            'last_name' => $parts[1] ?? 'Prospect',
            'phone' => $this->sanitizePhone($phone),
            'email' => $email,
            'project_name' => $project,
            'campaign_name' => '99acres Listing',
            'source_lead_id' => (string) $leadId,
            'notes' => "Lead from 99acres property query" . ($project ? " for {$project}" : ''),
            'raw_payload' => $payload,
        ];
    }
}
