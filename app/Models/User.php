<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'company_id',
        'role_id',
        'name',
        'email',
        'phone',
        'branch',
        'department',
        'designation',
        'password',
        'avatar_path',
        'is_active',
        'is_super_admin',
        'fcm_token',
        'device_type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_super_admin' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to_user_id');
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to_user_id');
    }

    public function hasRole(string $slug): bool
    {
        if ($this->is_super_admin && in_array($slug, ['founder', 'super_admin', 'admin'])) {
            return true;
        }
        return $this->role?->slug === $slug;
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->is_super_admin || $this->isCompanyAdmin()) {
            return true;
        }

        if (!$this->role) {
            return false;
        }

        // Check assigned permissions via relation
        if ($this->role->relationLoaded('permissions')) {
            if ($this->role->permissions->contains('slug', $permissionSlug)) {
                return true;
            }
        } else {
            if ($this->role->permissions()->where('slug', $permissionSlug)->exists()) {
                return true;
            }
        }

        // Default role-based permission fallback mapping
        return match ($permissionSlug) {
            'manage-leads' => in_array($this->role->slug, ['manager', 'sales_executive']),
            'assign-leads' => in_array($this->role->slug, ['manager', 'admin', 'director', 'founder']),
            'manage-projects' => in_array($this->role->slug, ['manager', 'admin', 'director', 'founder']),
            'approve-bookings' => in_array($this->role->slug, ['manager', 'admin', 'director', 'founder']),
            'approve-agreement-skips' => in_array($this->role->slug, ['director', 'founder', 'admin']),
            'manage-commissions' => in_array($this->role->slug, ['manager', 'admin', 'director', 'founder']),
            'process-payouts' => in_array($this->role->slug, ['admin', 'director', 'founder']),
            'manage-users' => in_array($this->role->slug, ['admin', 'director', 'founder', 'manager']),
            'broker-access' => $this->role->slug === 'broker',
            default => false,
        };
    }

    public function isSaaSFounder(): bool
    {
        return (bool) $this->is_super_admin;
    }

    public function isCompanyAdmin(): bool
    {
        return in_array($this->role?->slug, ['admin', 'director', 'founder']) && !$this->is_super_admin;
    }

    public function isDirector(): bool
    {
        return $this->role?->slug === 'director';
    }

    public function isDirectorOrFounder(): bool
    {
        return $this->is_super_admin || in_array($this->role?->slug, ['director', 'founder']);
    }

    public function isManager(): bool
    {
        return in_array($this->role?->slug, ['manager', 'sales_manager']);
    }

    public function isSales(): bool
    {
        return in_array($this->role?->slug, ['sales_executive', 'executive']);
    }

    public function isBroker(): bool
    {
        return $this->role?->slug === 'broker';
    }
}
