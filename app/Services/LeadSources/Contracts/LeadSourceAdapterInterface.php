<?php

namespace App\Services\LeadSources\Contracts;

use App\Models\LeadSource;

interface LeadSourceAdapterInterface
{
    /**
     * Get unique identifier slug of source type (e.g. meta, google, 99acres, magicbricks, housing, website, custom_api)
     */
    public function getType(): string;

    /**
     * Human readable display name of the adapter
     */
    public function getName(): string;

    /**
     * Validate credentials payload passed from user UI
     */
    public function validateCredentials(array $credentials): bool;

    /**
     * Parse raw incoming webhook/API payload into standard REOS lead format:
     * [
     *   'first_name' => string,
     *   'last_name' => ?string,
     *   'phone' => string,
     *   'email' => ?string,
     *   'interested_project_id' => ?int,
     *   'project_name' => ?string,
     *   'campaign_name' => ?string,
     *   'source_lead_id' => ?string,
     *   'notes' => ?string,
     *   'raw_payload' => array
     * ]
     */
    public function parseWebhookPayload(array $payload, LeadSource $source): array;

    /**
     * Test connection/credentials against provider API
     */
    public function testConnection(LeadSource $source): array;
}
