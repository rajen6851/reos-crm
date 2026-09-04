<?php

namespace App\Http\Resources;

use App\Services\BrokerPrivacyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrokerLeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $privacyService = app(BrokerPrivacyService::class);
        return $privacyService->sanitizeLead($this->resource);
    }
}
