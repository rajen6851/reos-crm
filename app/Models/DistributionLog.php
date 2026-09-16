<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributionLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function rule()
    {
        return $this->belongsTo(DistributionRule::class, 'distribution_rule_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
