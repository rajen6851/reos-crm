<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class CostSheet extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id', 'project_id', 'unit_id', 'base_cost', 'plc_cost', 'parking_cost', 'statutory_charges', 'other_charges', 'total_cost', 'payment_plan_type', 'valid_until', 'created_by_user_id'
    ];

    protected $casts = [
        'valid_until' => 'datetime',
        'base_cost' => 'decimal:2',
        'plc_cost' => 'decimal:2',
        'parking_cost' => 'decimal:2',
        'statutory_charges' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];
}
