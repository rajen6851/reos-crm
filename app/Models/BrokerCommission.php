<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrokerCommission extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'broker_id',
        'booking_id',
        'lead_id',
        'commission_type',
        'rate_value',
        'total_commission_amount',
        'status',
        'approved_by_user_id',
        'approved_at',
    ];

    protected $casts = [
        'rate_value' => 'decimal:2',
        'total_commission_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
