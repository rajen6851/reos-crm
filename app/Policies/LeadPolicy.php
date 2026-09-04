<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Lead $lead): bool
    {
        if ($user->is_super_admin) {
            return true;
        }
        return $user->company_id === $lead->company_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Lead $lead): bool
    {
        if ($user->is_super_admin) {
            return true;
        }
        return $user->company_id === $lead->company_id;
    }
}
