<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class KycDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'documentable_type',
        'documentable_id',
        'document_type',
        'document_number',
        'file_path',
        'expiry_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }
}
