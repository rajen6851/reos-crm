<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectBuilding extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'project_id',
        'name',
        'code',
        'total_floors',
        'total_units',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function floors(): HasMany
    {
        return $this->hasMany(ProjectFloor::class, 'building_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class, 'building_id');
    }
}
