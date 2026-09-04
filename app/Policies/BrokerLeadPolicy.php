<?php

namespace App\Policies;

use App\Models\Broker;
use App\Models\BrokerLead;
use App\Models\User;

class BrokerLeadPolicy
{
    /**
     * Determine if user can view broker lead
     */
    public function view(User $user, BrokerLead $brokerLead): bool
    {
        if ($user->company_id !== $brokerLead->company_id) {
            return false;
        }

        // If user is a broker, verify broker ownership
        $broker = Broker::where('company_id', $user->company_id)
            ->where('user_id', $user->id)
            ->first();

        if ($broker) {
            return $brokerLead->broker_id === $broker->id;
        }

        // Internal staff (Admin, Manager, Sales) can view
        return true;
    }

    /**
     * Determine if user can submit a broker lead
     */
    public function submit(User $user): bool
    {
        return $user->company_id !== null;
    }

    /**
     * Determine if broker can view financial commission
     */
    public function viewCommission(User $user, BrokerLead $brokerLead): bool
    {
        return $this->view($user, $brokerLead);
    }

    /**
     * Determine if broker can view payout status
     */
    public function viewPayout(User $user, BrokerLead $brokerLead): bool
    {
        return $this->view($user, $brokerLead);
    }
}
