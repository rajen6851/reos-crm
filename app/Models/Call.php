<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Call extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'lead_id',
        'user_id',
        'call_type',
        'call_outcome',
        'notes',
        'call_duration_seconds',
        'called_at',
        'next_followup_at',
    ];

    protected $casts = [
        'called_at' => 'datetime',
        'next_followup_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
