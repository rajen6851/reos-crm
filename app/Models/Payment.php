<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id', 'booking_id', 'payment_schedule_id', 'receipt_number', 'amount', 'payment_date', 'payment_method', 'transaction_reference', 'bank_name', 'status', 'recorded_by_user_id', 'cleared_at', 'notes'
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'cleared_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
