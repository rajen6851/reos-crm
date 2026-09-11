<?php

namespace App\Services\LeadSources\Adapters;

use App\Models\LeadSource;

class MagicBricksAdapter extends AbstractLeadSourceAdapter
{
    public function getType(): string
    {
        return 'magicbricks';
    }

    public function getName(): string
    {
        return 'MagicBricks Property Portal';
    }

    public function parseWebhookPayload(array $payload, LeadSource $source): array
    {
        $name = $payload['name'] ?? $payload['buyer_name'] ?? 'MagicBricks Prospect';
        $parts = explode(' ', trim($name), 2);

        $phone = $payload['mobile'] ?? $payload['phone'] ?? '';
        $email = $payload['email'] ?? '';
        $project = $payload['project_name'] ?? $payload['property_name'] ?? null;
        $mbId = $payload['id'] ?? $payload['mb_id'] ?? null;

        return [
            'first_name' => $parts[0] ?? 'MagicBricks',
            'last_name' => $parts[1] ?? 'Prospect',
            'phone' => $this->sanitizePhone($phone),
            'email' => $email,
            'project_name' => $project,
            'campaign_name' => 'MagicBricks Campaign',
            'source_lead_id' => (string) $mbId,
            'notes' => "Lead received from MagicBricks Portal",
            'raw_payload' => $payload,
        ];
    }
}
