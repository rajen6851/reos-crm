<?php

namespace App\Events;

use App\Models\BrokerCommission;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BrokerCommissionApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public BrokerCommission $commission,
        public User $approvedBy
    ) {}
}
