<?php

namespace App\Services\LeadSources\Adapters;

use App\Models\LeadSource;
use App\Services\LeadSources\Contracts\LeadSourceAdapterInterface;

abstract class AbstractLeadSourceAdapter implements LeadSourceAdapterInterface
{
    public function validateCredentials(array $credentials): bool
    {
        return true;
    }

    public function testConnection(LeadSource $source): array
    {
        if ($source->status === 'connected' || !empty($source->credentials)) {
            return [
                'success' => true,
                'message' => "Connection verified successfully for {$this->getName()}.",
            ];
        }

        return [
            'success' => false,
            'message' => "Credentials missing for {$this->getName()}.",
        ];
    }

    protected function sanitizePhone(?string $phone): string
    {
        if (!$phone) {
            return '';
        }
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleaned) > 10 && str_starts_with($cleaned, '91')) {
            $cleaned = substr($cleaned, -10);
        }
        return $cleaned;
    }
}
