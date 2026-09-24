<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalarySlip extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'user_id',
        'month',
        'working_days',
        'present_days',
        'leave_days',
        'basic_salary',
        'allowances',
        'commission_earned',
        'deductions',
        'net_salary',
        'status',
        'deletion_requested_by',
        'deletion_reason',
    ];

    public function deletionRequester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deletion_requested_by');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
