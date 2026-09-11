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

        // Check if payload contains nested changes from Meta Page webhook format
        if (isset($payload['entry'][0]['changes'][0]['value'])) {
            $changeValue = $payload['entry'][0]['changes'][0]['value'];
            if (is_array($changeValue)) {
                $leadGenId = $changeValue['leadgen_id'] ?? $leadGenId;
                $payload = array_merge($payload, $changeValue);
            }
        }

        // Direct key fallback if payload is flat JSON
        if (empty($phone) && !empty($payload['phone'])) {
            $phone = $payload['phone'];
        }
        if (empty($phone) && !empty($payload['phone_number'])) {
            $phone = $payload['phone_number'];
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

        // Graph API Lookup for real Meta leadgen_id
        $accessToken = $source->credentials['access_token'] ?? null;
        if (!empty($leadGenId) && empty($phone) && !empty($accessToken)) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(5)->get("https://graph.facebook.com/v20.0/{$leadGenId}", [
                    'access_token' => $accessToken,
                ]);

                if ($response->successful()) {
                    $graphData = $response->json();
                    $fieldData = $graphData['field_data'] ?? [];
                    foreach ($fieldData as $field) {
                        $name = strtolower($field['name'] ?? '');
                        $val = is_array($field['values'] ?? null) ? ($field['values'][0] ?? '') : ($field['value'] ?? '');

                        if (in_array($name, ['full_name', 'name', 'first_name'])) {
                            $parts = explode(' ', trim($val), 2);
                            $firstName = $parts[0] ?? $firstName;
                            $lastName = $parts[1] ?? $lastName;
                        } elseif ($name === 'last_name') {
                            $lastName = $val;
                        } elseif (str_contains($name, 'phone')) {
                            $phone = $val;
                        } elseif (str_contains($name, 'email')) {
                            $email = $val;
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("[META GRAPH API LOOKUP WARNING] " . $e->getMessage());
            }
        }

        // Fallback for Meta test webhooks so test lead is always saved
        if (empty($phone)) {
            $uniqueSeed = (string) ($leadGenId ?? rand(100000, 999999));
            $phone = '98' . sprintf('%08d', abs(crc32($uniqueSeed)) % 100000000);
            $firstName = ($firstName === 'Meta' || empty($firstName)) ? 'Meta Test' : $firstName;
            $lastName = ($lastName === 'Lead' || empty($lastName)) ? 'Prospect' : $lastName;
        }

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $this->sanitizePhone($phone),
            'email' => $email,
            'campaign_name' => $campaign,
            'source_lead_id' => (string) $leadGenId,
            'notes' => "Meta Lead Ads campaign: {$campaign}" . ($leadGenId ? " (ID: {$leadGenId})" : ''),
            'raw_payload' => $payload,
        ];
    }
}

