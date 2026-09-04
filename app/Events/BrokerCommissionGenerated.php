<?php

namespace App\Events;

use App\Models\BrokerCommission;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BrokerCommissionGenerated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public BrokerCommission $commission
    ) {}
}
