<?php

namespace App\Services\LeadSources\Adapters;

use App\Models\LeadSource;

class HousingAdapter extends AbstractLeadSourceAdapter
{
    public function getType(): string
    {
        return 'housing';
    }

    public function getName(): string
    {
        return 'Housing.com Portal';
    }

    public function parseWebhookPayload(array $payload, LeadSource $source): array
    {
        $name = $payload['name'] ?? $payload['user_name'] ?? 'Housing.com Lead';
        $parts = explode(' ', trim($name), 2);

        $phone = $payload['phone'] ?? $payload['mobile'] ?? '';
        $email = $payload['email'] ?? '';
        $project = $payload['project_name'] ?? null;
        $housingId = $payload['lead_id'] ?? $payload['id'] ?? null;

        return [
            'first_name' => $parts[0] ?? 'Housing',
            'last_name' => $parts[1] ?? 'Lead',
            'phone' => $this->sanitizePhone($phone),
            'email' => $email,
            'project_name' => $project,
            'campaign_name' => 'Housing.com Listing',
            'source_lead_id' => (string) $housingId,
            'notes' => "Inquiry from Housing.com",
            'raw_payload' => $payload,
        ];
    }
}
