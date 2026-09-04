<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BrokerPayout extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'broker_id',
        'payout_code',
        'amount_paid',
        'payout_date',
        'payment_method',
        'transaction_reference',
        'remarks',
        'status',
        'processed_by_user_id',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'payout_date' => 'datetime',
    ];

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    public function commissions(): BelongsToMany
    {
        return $this->belongsToMany(BrokerCommission::class, 'broker_payout_commissions');
    }
}
