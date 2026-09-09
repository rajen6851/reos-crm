<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LeadDistributionService
{
    /**
     * Round-Robin & Manager Pool Two-Tier Lead Auto-Distribution
     */
    public function distributeNewLead(Lead $lead, ?NotificationService $notificationService = null): Lead
    {
        if (!$lead->company_id) {
            return $lead;
        }

        // 1. Fetch active Managers for the company
        $managers = User::where('company_id', $lead->company_id)
            ->whereHas('role', function ($q) {
                $q->whereIn('slug', ['manager', 'sales_manager']);
            })
            ->get();

        if ($managers->isNotEmpty()) {
            // Find manager with minimum assigned leads count (Round-Robin Balance)
            $selectedManager = $managers->sortBy(function ($manager) {
                return Lead::where('company_id', $manager->company_id)
                    ->where('assigned_to_manager_id', $manager->id)
                    ->count();
            })->first();

            if ($selectedManager) {
                $lead->assigned_to_manager_id = $selectedManager->id;
                $lead->save();

                Log::info("[LEAD DISTRIBUTION] Lead #{$lead->id} ('{$lead->name}') auto-assigned to Manager #{$selectedManager->id} ({$selectedManager->name})");

                // Dispatch notification to assigned Manager
                if ($notificationService) {
                    $notificationService->notify(
                        $selectedManager,
                        'lead_assigned_to_manager',
                        "📩 New Lead Assigned to Your Pool",
                        "New customer lead '{$lead->name}' ({$lead->phone}) has been assigned to your Manager Team Pool. Click to assign to a Sales Executive.",
                        route('leads.show', $lead->id)
                    );
                }
            }
        }

        return $lead;
    }
}
