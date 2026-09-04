<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrokerPayoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payout_code' => $this->payout_code,
            'amount_paid' => $this->amount_paid,
            'payout_date' => $this->payout_date->format('Y-m-d H:i:s'),
            'payment_method' => $this->payment_method,
            'transaction_reference' => $this->transaction_reference,
            'status' => ucfirst($this->status),
            'commissions' => BrokerCommissionResource::collection($this->whenLoaded('commissions')),
        ];
    }
}
