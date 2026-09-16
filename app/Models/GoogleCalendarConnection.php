<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleCalendarConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'google_email',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'calendar_id',
        'status',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
