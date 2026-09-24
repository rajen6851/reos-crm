<?php

namespace App\Services;

use App\Models\Company;

class SubscriptionLimitService
{
    /**
     * Check if a company can add a specific number of new users.
     *
     * @param Company $company
     * @param int $newUsersCount Number of users being added (default 1)
     * @return bool
     */
    public function canAddMoreUsers(Company $company, int $newUsersCount = 1): bool
    {
        // If there's no subscription plan assigned, we can optionally block or allow.
        // Assuming we allow unassigned to be on an implicit 'trial' or we just enforce if a plan exists.
        // But for strict SaaS, we must enforce the plan limits.
        if (!$company->subscriptionPlan) {
            // No plan assigned? Treat as default limit of 10 or block?
            // Let's assume a default hard limit of 10 if no plan is found to prevent abuse.
            $limit = 10;
        } else {
            $limit = $company->subscriptionPlan->max_users;
        }

        // Count all internal staff users belonging to this company.
        // Exclude brokers from this count as they are usually external partners.
        $currentUsersCount = $company->users()
            ->whereHas('role', function ($q) {
                $q->where('slug', '!=', 'broker');
            })
            ->count();

        return ($currentUsersCount + $newUsersCount) <= $limit;
    }

    /**
     * Check if a company can create a specific number of new projects.
     *
     * @param Company $company
     * @param int $newProjectsCount Number of projects being added (default 1)
     * @return bool
     */
    public function canAddMoreProjects(Company $company, int $newProjectsCount = 1): bool
    {
        if (!$company->subscriptionPlan) {
            $limit = 2; // Default strict limit for no plan
        } else {
            // If plan has no max_projects limit (e.g. 0 or null), assume unlimited
            if (!$company->subscriptionPlan->max_projects) {
                return true;
            }
            $limit = $company->subscriptionPlan->max_projects;
        }

        $currentProjectsCount = $company->projects()->count();

        return ($currentProjectsCount + $newProjectsCount) <= $limit;
    }
}
