<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Broker extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'user_id',
        'agency_name',
        'broker_code',
        'phone',
        'email',
        'commission_rate',
        'status',
        'payout_bank_details',
    ];

    protected $casts = [
        'payout_bank_details' => 'array',
        'commission_rate' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function brokerLeads(): HasMany
    {
        return $this->hasMany(BrokerLead::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(BrokerCommission::class);
    }
}
