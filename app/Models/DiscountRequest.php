<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class DiscountRequest extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id', 'requested_by_user_id', 'plan_id', 'discount_percent', 'reason', 'status', 'approved_by_user_id'
    ];
}
