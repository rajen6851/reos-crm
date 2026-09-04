<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lead extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'lead_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'alternate_phone',
        'source_id',
        'broker_id',
        'assigned_to_user_id',
        'interested_project_id',
        'interested_unit_type',
        'budget_min',
        'budget_max',
        'status',
        'lost_reason',
        'is_duplicate',
        'duplicate_of_lead_id',
        'notes',
    ];

    protected $casts = [
        'is_duplicate' => 'boolean',
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
    ];

    public function getNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    public function brokerLead(): HasOne
    {
        return $this->hasOne(BrokerLead::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(LeadAssignment::class)->orderBy('created_at', 'desc');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'interested_project_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->orderBy('created_at', 'desc');
    }

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class)->orderBy('called_at', 'desc');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class)->orderBy('scheduled_at', 'desc');
    }

    public function siteVisits(): HasMany
    {
        return $this->hasMany(SiteVisit::class)->orderBy('scheduled_at', 'desc');
    }
}
