<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'code',
        'slug',
        'logo_path',
        'email',
        'phone',
        'address',
        'tax_number',
        'status',
        'subscription_plan_id',
        'subscription_expires_at',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'subscription_expires_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)->orderBy('created_at', 'desc');
    }

    public function brokers(): HasMany
    {
        return $this->hasMany(Broker::class);
    }

    /**
     * Subscription status check
     */
    public function isSubscriptionActive(): bool
    {
        if ($this->status === 'suspended' || $this->status === 'pending_subscription') {
            return false;
        }

        if (!$this->subscription_expires_at) {
            return true;
        }

        return $this->subscription_expires_at->isFuture();
    }

    /**
     * Remaining days in subscription
     */
    public function daysUntilSubscriptionExpires(): ?int
    {
        if (!$this->subscription_expires_at) {
            return null;
        }

        $diff = (int) now()->diffInDays($this->subscription_expires_at, false);
        return max(0, $diff);
    }

    /**
     * Feature entitlement check
     */
    public function hasFeature(string $feature): bool
    {
        $plan = $this->subscriptionPlan;
        if (!$plan) {
            return false;
        }

        $features = $plan->features ?? [];
        return in_array($feature, $features, true) || in_array(strtolower($feature), array_map('strtolower', $features), true);
    }

    /**
     * User limit check
     */
    public function canAddUser(): bool
    {
        $plan = $this->subscriptionPlan;
        if (!$plan || !$plan->max_users) {
            return true;
        }

        return $this->users()->count() < $plan->max_users;
    }

    /**
     * Project limit check
     */
    public function canAddProject(): bool
    {
        $plan = $this->subscriptionPlan;
        if (!$plan || !$plan->max_projects) {
            return true;
        }

        return $this->projects()->count() < $plan->max_projects;
    }

    /**
     * Monthly lead creation limit check
     */
    public function canAddLeadMonthly(): bool
    {
        $plan = $this->subscriptionPlan;
        if (!$plan || !$plan->max_leads_per_month) {
            return true;
        }

        $leadsThisMonth = $this->leads()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        return $leadsThisMonth < $plan->max_leads_per_month;
    }

    /**
     * Comprehensive usage summary
     */
    public function usageSummary(): array
    {
        $plan = $this->subscriptionPlan;

        $currentUsers = $this->users()->count();
        $currentProjects = $this->projects()->count();
        $currentMonthlyLeads = $this->leads()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        return [
            'company_id' => $this->id,
            'company_name' => $this->name,
            'company_status' => $this->status,
            'is_subscription_active' => $this->isSubscriptionActive(),
            'subscription_expires_at' => $this->subscription_expires_at?->format('Y-m-d H:i:s'),
            'days_remaining' => $this->daysUntilSubscriptionExpires(),
            'plan' => $plan ? [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'price' => $plan->price,
                'billing_cycle' => $plan->billing_cycle,
                'max_users' => $plan->max_users,
                'max_projects' => $plan->max_projects,
                'max_leads_per_month' => $plan->max_leads_per_month,
                'features' => $plan->features,
            ] : null,
            'usage' => [
                'users' => [
                    'current' => $currentUsers,
                    'limit' => $plan?->max_users,
                    'can_add' => $this->canAddUser(),
                ],
                'projects' => [
                    'current' => $currentProjects,
                    'limit' => $plan?->max_projects,
                    'can_add' => $this->canAddProject(),
                ],
                'monthly_leads' => [
                    'current' => $currentMonthlyLeads,
                    'limit' => $plan?->max_leads_per_month,
                    'can_add' => $this->canAddLeadMonthly(),
                ],
            ]
        ];
    }
}
