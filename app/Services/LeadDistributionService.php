<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadAssignment;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LeadDistributionService
{
    /**
     * 2-Tier Round-Robin Auto Lead Distribution
     * Level 1: Manager Pool (Round-Robin)
     * Level 2: Sales Executive (Round-Robin)
     */
    public function distributeNewLead(Lead $lead, ?NotificationService $notificationService = null): Lead
    {
        if (!$lead->company_id) {
            return $lead;
        }

        // ==========================================
        // TIER 1: Auto Assign to Manager (Round-Robin)
        // ==========================================
        $selectedManager = null;

        if (!$lead->assigned_to_manager_id) {
            $managers = User::where('company_id', $lead->company_id)
                ->where('is_active', true)
                ->whereHas('role', function ($q) {
                    $q->whereIn('slug', ['manager', 'sales_manager']);
                })
                ->get();

            if ($managers->isNotEmpty()) {
                // Find Manager with minimum assigned leads (Round-Robin Balance)
                $selectedManager = $managers->sortBy(function ($manager) {
                    return Lead::where('company_id', $manager->company_id)
                        ->where('assigned_to_manager_id', $manager->id)
                        ->count();
                })->first();

                if ($selectedManager) {
                    $lead->assigned_to_manager_id = $selectedManager->id;
                    Log::info("[LEAD DISTRIBUTION - L1 MANAGER] Lead #{$lead->id} ('{$lead->name}') auto-assigned to Manager #{$selectedManager->id} ({$selectedManager->name})");

                    if ($notificationService) {
                        $notificationService->notify(
                            $selectedManager,
                            'lead_assigned_to_manager',
                            "📩 New Lead Assigned to Your Pool",
                            "New customer lead '{$lead->name}' ({$lead->phone}) has been assigned to your Manager Team Pool.",
                            route('leads.show', $lead->id)
                        );
                    }
                }
            }
        } else {
            $selectedManager = User::find($lead->assigned_to_manager_id);
        }

        // ==========================================
        // TIER 2: Auto Assign to Sales Executive (Round-Robin)
        // ==========================================
        if (!$lead->assigned_to_user_id) {
            $executivesQuery = User::where('company_id', $lead->company_id)
                ->where('is_active', true)
                ->whereHas('role', function ($q) {
                    $q->whereIn('slug', ['sales_executive', 'executive', 'sales']);
                });

            // If a manager is assigned, prioritize Executives reporting to that Manager
            if ($selectedManager) {
                $teamExecutives = (clone $executivesQuery)
                    ->where('reporting_manager_id', $selectedManager->id)
                    ->get();

                $executives = $teamExecutives->isNotEmpty() ? $teamExecutives : $executivesQuery->get();
            } else {
                $executives = $executivesQuery->get();
            }

            if ($executives->isNotEmpty()) {
                // Find Executive with minimum assigned leads (Round-Robin Balance)
                $selectedExecutive = $executives->sortBy(function ($executive) {
                    return Lead::where('company_id', $executive->company_id)
                        ->where('assigned_to_user_id', $executive->id)
                        ->count();
                })->first();

                if ($selectedExecutive) {
                    $lead->assigned_to_user_id = $selectedExecutive->id;
                    Log::info("[LEAD DISTRIBUTION - L2 EXECUTIVE] Lead #{$lead->id} ('{$lead->name}') auto-assigned to Executive #{$selectedExecutive->id} ({$selectedExecutive->name})");


                    // Create assignment history record
                    LeadAssignment::create([
                        'company_id' => $lead->company_id,
                        'lead_id' => $lead->id,
                        'assigned_by_user_id' => $selectedManager ? $selectedManager->id : $selectedExecutive->id,
                        'assigned_to_user_id' => $selectedExecutive->id,
                        'assignment_type' => 'initial',
                        'assignment_reason' => 'Auto Round-Robin Distribution',
                        'assigned_at' => now(),
                    ]);

                    // Audit log activity
                    LeadActivity::create([
                        'company_id' => $lead->company_id,
                        'lead_id' => $lead->id,
                        'user_id' => $selectedManager ? $selectedManager->id : $selectedExecutive->id,
                        'activity_type' => 'assigned',
                        'description' => "Lead auto-assigned (Round Robin) to Sales Executive {$selectedExecutive->name}",
                        'metadata' => [
                            'assigned_to' => $selectedExecutive->id,
                            'reason' => 'Auto Round-Robin Distribution',
                        ],
                    ]);

                    if ($notificationService) {
                        $notificationService->notify(
                            $selectedExecutive,
                            'lead_assigned',
                            "🎯 New Lead Auto-Assigned",
                            "New customer lead '{$lead->name}' ({$lead->phone}) has been auto-assigned to you via Round-Robin.",
                            route('leads.show', $lead->id)
                        );
                    }
                }
            }
        }

        $lead->save();

        return $lead;
    }
}

