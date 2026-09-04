<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class LeadSource extends Model
{
    use BelongsToTenant;

    protected $fillable = ['company_id', 'name', 'slug', 'is_active'];
}
