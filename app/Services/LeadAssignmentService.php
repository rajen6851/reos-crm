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
                'company_id' => $lead->company_id,
                'lead_id' => $lead->id,
                'assigned_by_user_id' => $assignedBy->id,
                'assigned_to_user_id' => $assignedTo->id,
                'assignment_type' => $assignmentType,
                'previous_assignee_id' => $previousAssigneeId,
                'assignment_reason' => $reason,
                'assigned_at' => now(),
            ]);

            // Update main Lead (keep within allowed enum: new, contacted, follow_up, site_visit, interested, negotiation, converted, lost)
            $oldStatus = $lead->status;
            $newStatus = ($oldStatus === 'new') ? 'contacted' : $oldStatus;

            $lead->update([
                'assigned_to_user_id' => $assignedTo->id,
                'status' => $newStatus,
            ]);

            // Sync broker visible status
            $this->statusService->syncBrokerVisibleStatus(
                $lead,
                "Lead assigned to executive {$assignedTo->name}"
            );

            // Audit log
            LeadActivity::create([
                'company_id' => $lead->company_id,
                'lead_id' => $lead->id,
                'user_id' => $assignedBy->id,
                'activity_type' => 'assigned',
                'description' => "Lead {$assignmentType} to {$assignedTo->name} by {$assignedBy->name}",
                'metadata' => [
                    'assigned_to' => $assignedTo->id,
                    'previous_assignee' => $previousAssigneeId,
                    'reason' => $reason,
                ],
            ]);

            // Event
            event(new BrokerLeadAssigned($lead, $assignedTo, $assignedBy));

            return $assignment;
        });
    }
}
