<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrokerCommissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_code' => $this->booking?->booking_code,
            'customer_name' => $this->booking?->customer_name,
            'commission_type' => $this->commission_type,
            'rate_value' => $this->rate_value,
            'total_commission_amount' => $this->total_commission_amount,
            'status' => ucfirst(str_replace('_', ' ', $this->status)),
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
