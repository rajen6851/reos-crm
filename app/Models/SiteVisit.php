<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteVisit extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id', 'lead_id', 'project_id', 'unit_id', 'assigned_to_user_id', 'scheduled_at', 'visited_at', 'status', 'outcome', 'feedback_notes', 'pickup_location'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'visited_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }
}
