<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectFloor extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'building_id',
        'floor_number',
        'name',
        'total_units',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(ProjectBuilding::class, 'building_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class, 'floor_id');
    }
}
