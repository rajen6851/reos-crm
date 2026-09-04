<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoApplicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'booking_id',
        'full_name',
        'relationship',
        'phone',
        'email',
        'pan_number',
        'aadhar_number',
        'address',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
