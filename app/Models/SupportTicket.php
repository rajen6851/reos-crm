<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'ticket_number',
        'ticket_code',
        'subject',
        'category',
        'priority',
        'status',
        'assigned_to_user_id',
        'description',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = 'TCK-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));
            }
            if (empty($ticket->ticket_code)) {
                $ticket->ticket_code = $ticket->ticket_number;
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function replies()
    {
        return $this->hasMany(SupportTicketReply::class, 'support_ticket_id')->orderBy('created_at', 'asc');
    }
}
