<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'location_address',
        'city',
        'state',
        'pincode',
        'latitude',
        'longitude',
        'rera_number',
        'amenities',
        'project_type',
        'status',
        'visibility',
        'banner_image',
        'documents',
    ];

    protected $casts = [
        'amenities' => 'array',
        'documents' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(ProjectBuilding::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function availableUnitsCount(): int
    {
        return $this->units()->where('status', 'available')->count();
    }
}
