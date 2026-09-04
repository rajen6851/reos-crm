<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'booking_code',
        'lead_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'project_id',
        'unit_id',
        'sales_user_id',
        'broker_id',
        'cost_sheet_id',
        'booking_amount',
        'total_unit_cost',
        'booking_date',
        'status',
        'approval_status',
        'approved_by_user_id',
        'approved_at',
        'rejection_reason',
        'cancellation_reason',
    ];

    protected $casts = [
        'booking_date' => 'datetime',
        'approved_at' => 'datetime',
        'booking_amount' => 'decimal:2',
        'total_unit_cost' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function salesUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_user_id');
    }

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    public function costSheet(): BelongsTo
    {
        return $this->belongsTo(CostSheet::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function agreement(): HasOne
    {
        return $this->hasOne(Agreement::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentSchedules(): HasMany
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    public function coApplicants(): HasMany
    {
        return $this->hasMany(CoApplicant::class);
    }
}
