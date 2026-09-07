<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaasApprovalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requested_by_user_id',
        'action_type',
        'target_type',
        'target_id',
        'target_name',
        'payload',
        'reason',
        'status',
        'reviewed_by_user_id',
        'reviewer_notes',
        'executed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'executed_at' => 'datetime',
        ];
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function getActionBadgeAttribute(): string
    {
        return match ($this->action_type) {
            'delete_company' => 'bg-rose-100 text-rose-800 border-rose-300',
            'destroy_plan' => 'bg-amber-100 text-amber-800 border-amber-300',
            'update_company_status' => 'bg-sky-100 text-sky-800 border-sky-300',
            'delete_subadmin' => 'bg-purple-100 text-purple-800 border-purple-300',
            default => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action_type) {
            'delete_company' => 'Delete Builder Company',
            'destroy_plan' => 'Delete SaaS Subscription Plan',
            'update_company_status' => 'Update Company Subscription Status',
            'delete_subadmin' => 'Delete SaaS Sub-Admin',
            default => str_replace('_', ' ', ucfirst($this->action_type)),
        };
    }
}
