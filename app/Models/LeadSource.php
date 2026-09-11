<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LeadSource extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'type',
        'webhook_token',
        'status',
        'credentials',
        'settings',
        'last_synced_at',
        'error_log',
        'is_active',
    ];

    protected $casts = [
        'credentials' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name . '-' . Str::random(5));
            }
            if (empty($model->webhook_token)) {
                $model->webhook_token = Str::random(32);
            }
        });
    }

    public function getWebhookUrlAttribute(): string
    {
        if (empty($this->webhook_token)) {
            return '';
        }
        return url("/api/webhooks/lead-sources/{$this->type}/{$this->webhook_token}");
    }
}
