<?php

namespace App\Events;

use App\Models\BrokerPayout;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BrokerPayoutProcessed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public BrokerPayout $payout,
        public Collection $commissions
    ) {}
}
