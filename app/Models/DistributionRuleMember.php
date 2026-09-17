<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributionRuleMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'distribution_rule_id',
        'user_id',
        'parent_member_id',
        'allocation_value',
    ];

    public function rule()
    {
        return $this->belongsTo(DistributionRule::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function executives()
    {
        return $this->hasMany(DistributionRuleMember::class, 'parent_member_id');
    }
}
