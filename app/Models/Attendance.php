<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'work_location',
        'status',
        'notes',
        'selfie_path',
        'latitude',
        'longitude',
        'address',
        'checkout_latitude',
        'checkout_longitude',
        'checkout_address',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
