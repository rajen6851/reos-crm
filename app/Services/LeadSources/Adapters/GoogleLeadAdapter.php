<?php

namespace App\Services\LeadSources\Adapters;

use App\Models\LeadSource;

class GoogleLeadAdapter extends AbstractLeadSourceAdapter
{
    public function getType(): string
    {
        return 'google';
    }

    public function getName(): string
    {
        return 'Google Ads Lead Forms';
    }

    public function parseWebhookPayload(array $payload, LeadSource $source): array
    {
        // Google Ads webhook payload uses user_column_data array
        $columns = $payload['user_column_data'] ?? [];
        $firstName = 'Google';
        $lastName = 'Lead';
        $phone = '';
        $email = '';
        $campaign = $payload['campaign_id'] ?? 'Google Search/Display Ads';
        $googleLeadId = $payload['lead_id'] ?? null;

        foreach ($columns as $column) {
            $columnId = strtolower($column['column_id'] ?? $column['column_name'] ?? '');
            $val = $column['string_value'] ?? '';

            if (str_contains($columnId, 'first_name') || str_contains($columnId, 'full_name')) {
                $parts = explode(' ', trim($val), 2);
                $firstName = $parts[0] ?? 'Google';
                if (isset($parts[1])) {
                    $lastName = $parts[1];
                }
            } elseif (str_contains($columnId, 'last_name')) {
                $lastName = $val;
            } elseif (str_contains($columnId, 'phone')) {
                $phone = $val;
            } elseif (str_contains($columnId, 'email')) {
                $email = $val;
            }
        }

        // Direct key fallbacks
        if (empty($phone) && !empty($payload['phone_number'])) {
            $phone = $payload['phone_number'];
        }
        if (empty($email) && !empty($payload['email'])) {
            $email = $payload['email'];
        }

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $this->sanitizePhone($phone),
            'email' => $email,
            'campaign_name' => (string) $campaign,
            'source_lead_id' => (string) $googleLeadId,
            'notes' => "Google Lead Form submission",
            'raw_payload' => $payload,
        ];
    }
}
