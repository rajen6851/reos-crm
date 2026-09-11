<?php

namespace App\Services\LeadSources\Adapters;

use App\Models\LeadSource;

class MetaLeadAdapter extends AbstractLeadSourceAdapter
{
    public function getType(): string
    {
        return 'meta';
    }

    public function getName(): string
    {
        return 'Meta Ads (Facebook / Instagram)';
    }

    public function validateCredentials(array $credentials): bool
    {
        return !empty($credentials['page_id']) || !empty($credentials['access_token']);
    }

    public function parseWebhookPayload(array $payload, LeadSource $source): array
    {
        // Meta webhook payload parsing
        // Can be direct form field values or Meta Graph API webhook format
        $fields = $payload['field_data'] ?? $payload['entry'][0]['changes'][0]['value'] ?? $payload;

        $firstName = 'Meta';
        $lastName = 'Lead';
        $phone = '';
        $email = '';
        $campaign = $payload['campaign_name'] ?? 'Meta Lead Ads';
        $leadGenId = $payload['leadgen_id'] ?? $payload['id'] ?? null;

        if (is_array($fields)) {
            foreach ($fields as $field) {
                if (!is_array($field)) {
                    continue;
                }
                $name = strtolower($field['name'] ?? '');
                $val = is_array($field['values'] ?? null) ? ($field['values'][0] ?? '') : ($field['value'] ?? '');

                if (in_array($name, ['full_name', 'name', 'first_name'])) {
                    $parts = explode(' ', trim($val), 2);
                    $firstName = $parts[0] ?? 'Meta';
                    $lastName = $parts[1] ?? 'Lead';
                } elseif ($name === 'last_name') {
                    $lastName = $val;
                } elseif (str_contains($name, 'phone')) {
                    $phone = $val;
                } elseif (str_contains($name, 'email')) {
                    $email = $val;
                }
            }
        }

        // Direct key fallback if payload is flat JSON
        if (empty($phone) && !empty($payload['phone'])) {
            $phone = $payload['phone'];
        }
        if (empty($email) && !empty($payload['email'])) {
            $email = $payload['email'];
        }
        if (!empty($payload['first_name'])) {
            $firstName = $payload['first_name'];
        }
        if (!empty($payload['last_name'])) {
            $lastName = $payload['last_name'];
        }

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $this->sanitizePhone($phone),
            'email' => $email,
            'campaign_name' => $campaign,
            'source_lead_id' => (string) $leadGenId,
            'notes' => "Meta Lead Ads campaign: {$campaign}",
            'raw_payload' => $payload,
        ];
    }
}
