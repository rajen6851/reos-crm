<?php

namespace App\Services;

use App\Models\Lead;

class DuplicateLeadService
{
    /**
     * Search for duplicate leads by phone, alternate phone, or email within company.
     */
    public function findDuplicate(int $companyId, string $phone, ?string $email = null): ?Lead
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleanPhone) > 10) {
            $cleanPhone = substr($cleanPhone, -10); // Match last 10 digits
        }

        return Lead::where('company_id', $companyId)
            ->where(function ($q) use ($phone, $cleanPhone, $email) {
                $q->where('phone', $phone)
                  ->orWhere('phone', 'like', "%{$cleanPhone}%")
                  ->orWhere('alternate_phone', $phone)
                  ->orWhere('alternate_phone', 'like', "%{$cleanPhone}%");
                if ($email) {
                    $q->orWhere('email', $email);
                }
            })
            ->first();
    }

    /**
     * Alias for findDuplicate
     */
    public function checkDuplicate(int $companyId, string $phone, ?string $email = null): ?Lead
    {
        return $this->findDuplicate($companyId, $phone, $email);
    }
}
