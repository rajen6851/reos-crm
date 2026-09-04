<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unit extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'project_id',
        'building_id',
        'floor_id',
        'unit_number',
        'unit_type',
        'carpet_area',
        'builtup_area',
        'super_builtup_area',
        'facing',
        'base_price',
        'final_price',
        'status',
        'holding_expires_at',
        'hold_by_user_id',
    ];

    protected $casts = [
        'holding_expires_at' => 'datetime',
        'base_price' => 'decimal:2',
        'final_price' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(ProjectBuilding::class, 'building_id');
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(ProjectFloor::class, 'floor_id');
    }
}
