<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributionMemberState extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function rule()
    {
        return $this->belongsTo(DistributionRule::class, 'distribution_rule_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
