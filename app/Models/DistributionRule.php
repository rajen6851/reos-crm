<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributionRule extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function members()
    {
        return $this->hasMany(DistributionRuleMember::class);
    }

    public function states()
    {
        return $this->hasOne(DistributionState::class);
    }

    public function memberStates()
    {
        return $this->hasMany(DistributionMemberState::class);
    }

    public function logs()
    {
        return $this->hasMany(DistributionLog::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function source()
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }
}
