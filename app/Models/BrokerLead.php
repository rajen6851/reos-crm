<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrokerLead extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'broker_id',
        'lead_id',
        'project_id',
        'unit_id',
        'submitted_at',
        'broker_visible_status',
        'broker_visible_message',
        'property_type',
        'unit_type',
        'budget_min',
        'budget_max',
        'preferred_location',
        'requirement_notes',
        'city',
        'customer_type',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
    ];

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
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
}
