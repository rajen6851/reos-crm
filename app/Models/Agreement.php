<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Agreement extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id', 'booking_id', 'agreement_number', 'draft_file_path', 'signed_file_path', 'status', 'skip_requested_by_user_id', 'skip_approved_by_user_id', 'skip_reason', 'executed_at'
    ];

    protected $casts = [
        'executed_at' => 'datetime',
    ];

    public function booking(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
