<?php

namespace App\Events;

use App\Models\Broker;
use App\Models\BrokerLead;
use App\Models\Lead;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BrokerLeadSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Lead $lead,
        public BrokerLead $brokerLead,
        public Broker $broker
    ) {}
}
