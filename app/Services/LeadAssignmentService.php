<?php

namespace App\Services;

use App\Events\BrokerLeadAssigned;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LeadAssignmentService
{
    public function __construct(
        protected BrokerLeadStatusService $statusService
    ) {}

    public function assignLead(Lead $lead, User $assignedTo, User $assignedBy, ?string $reason = null): LeadAssignment
    {
        return DB::transaction(function () use ($lead, $assignedTo, $assignedBy, $reason) {
            $previousAssigneeId = $lead->assigned_to_user_id;
            $assignmentType = $previousAssigneeId ? 'reassignment' : 'initial';

            // Create assignment record (preserves full history)
            $assignment = LeadAssignment::create([
                'company_id'          => $lead->company_id,
                'lead_id'             => $lead->id,
                'assigned_by_user_id' => $assignedBy->id,
                'assigned_to_user_id' => $assignedTo->id,
                'assignment_type'     => $assignmentType,
                'previous_assignee_id'=> $previousAssigneeId,
                'assignment_reason'   => $reason,
                'assigned_at'         => now(),
            ]);

            $oldStatus = $lead->status;
            $newStatus = ($oldStatus === 'new') ? 'contacted' : $oldStatus;

            $updatePayload = [
                'assigned_to_user_id' => $assignedTo->id,
                'status'              => $newStatus,
                'last_activity_at'    => now(),
                'transfer_eligible_at'=> null, // reset after fresh assignment
            ];

            if ($assignedBy->isManager()) {
                $updatePayload['assigned_to_manager_id'] = $assignedBy->id;
            }

            $lead->update($updatePayload);

            // Sync broker visible status
            $this->statusService->syncBrokerVisibleStatus(
                $lead,
                "Lead assigned to executive {$assignedTo->name}"
            );

            // Audit log
            LeadActivity::create([
                'company_id'    => $lead->company_id,
                'lead_id'       => $lead->id,
                'user_id'       => $assignedBy->id,
                'activity_type' => 'assigned',
                'description'   => "Lead {$assignmentType} to {$assignedTo->name} by {$assignedBy->name}",
                'metadata'      => [
                    'assigned_to'      => $assignedTo->id,
                    'previous_assignee'=> $previousAssigneeId,
                    'reason'           => $reason,
                ],
            ]);

            // Event
            event(new BrokerLeadAssigned($lead, $assignedTo, $assignedBy));

            return $assignment;
        });
    }

    /**
     * Transfer a lead to a new executive with full reason tracking.
     * Used for both manual transfers (Manager/Admin) and auto-transfers (Cron).
     */
    public function transferLead(
        Lead $lead,
        User $newAssignee,
        User $transferredBy,
        string $transferReason,
        ?string $transferNote = null,
        string $type = 'manual'
    ): LeadAssignment {
        return DB::transaction(function () use ($lead, $newAssignee, $transferredBy, $transferReason, $transferNote, $type) {
            $previousAssigneeId = $lead->assigned_to_user_id;

            // Record the transfer in assignments history
            $assignment = LeadAssignment::create([
                'company_id'          => $lead->company_id,
                'lead_id'             => $lead->id,
                'assigned_by_user_id' => $transferredBy->id,
                'assigned_to_user_id' => $newAssignee->id,
                'assignment_type'     => 'transfer_' . $type, // transfer_manual | transfer_auto
                'previous_assignee_id'=> $previousAssigneeId,
                'assignment_reason'   => $transferReason,
                'transfer_note'       => $transferNote,
                'assigned_at'         => now(),
            ]);

            // Update the lead
            $lead->update([
                'assigned_to_user_id'  => $newAssignee->id,
                'transfer_count'       => $lead->transfer_count + 1,
                'last_activity_at'     => now(),
                'transfer_eligible_at' => null, // reset after transfer
            ]);

            // Sync broker visible status
            $this->statusService->syncBrokerVisibleStatus(
                $lead,
                "Lead transferred to {$newAssignee->name}"
            );

            // Activity log entry
            $typeLabel = $type === 'auto' ? '🤖 Auto-Transfer' : '🔄 Manual Transfer';
            LeadActivity::create([
                'company_id'    => $lead->company_id,
                'lead_id'       => $lead->id,
                'user_id'       => $transferredBy->id,
                'activity_type' => 'transferred',
                'description'   => "{$typeLabel}: Lead transferred from " .
                    ($previousAssigneeId ? User::withoutGlobalScopes()->find($previousAssigneeId)?->name ?? 'Previous Exec' : 'Unassigned') .
                    " to {$newAssignee->name}. Reason: {$transferReason}",
                'metadata'      => [
                    'from_user_id'  => $previousAssigneeId,
                    'to_user_id'    => $newAssignee->id,
                    'reason'        => $transferReason,
                    'note'          => $transferNote,
                    'transfer_type' => $type,
                ],
            ]);

            event(new BrokerLeadAssigned($lead, $newAssignee, $transferredBy));

            return $assignment;
        });
    }

    /**
     * Mark a lead's last activity timestamp (call/followup logged).
     * Called whenever a call or followup is created.
     */
    public function updateLastActivity(Lead $lead): void
    {
        $lead->update([
            'last_activity_at'     => now(),
            'transfer_eligible_at' => null, // reset eligibility on new activity
        ]);
    }
}
