<?php

namespace App\Services;

use App\Events\BrokerLeadStatusChanged;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LeadService
{
    public function __construct(
        protected BrokerLeadStatusService $statusService
    ) {}

    public function updateStatus(Lead $lead, string $newStatus, ?string $notes, User $user, bool $isReopen = false): Lead
    {
        return DB::transaction(function () use ($lead, $newStatus, $notes, $user, $isReopen) {
            $oldStatus = $lead->status;

            // Validate status transition rules
            $this->statusService->validateTransition($oldStatus, $newStatus, $isReopen);

            $lead->status = $newStatus;
            if ($notes) {
                $lead->notes = ($lead->notes ? $lead->notes . "\n" : '') . "[" . now()->format('Y-m-d H:i') . "] {$user->name}: {$notes}";
            }
            $lead->save();

            // Log activity
            LeadActivity::create([
                'company_id' => $lead->company_id,
                'lead_id' => $lead->id,
                'user_id' => $user->id,
                'activity_type' => 'status_change',
                'description' => "Status changed from {$oldStatus} to {$newStatus}.",
                'metadata' => ['old_status' => $oldStatus, 'new_status' => $newStatus],
            ]);

            // Sync broker visible status
            $brokerLead = $this->statusService->syncBrokerVisibleStatus($lead);

            if ($brokerLead && ($newStatus === 'converted' || $brokerLead->broker_visible_status === 'Booked')) {
                $commissionService = app(BrokerCommissionService::class);
                $commissionService->ensureCommissionForBrokerLead($brokerLead);
            }

            // Dispatch event
            event(new BrokerLeadStatusChanged(
                $lead,
                $oldStatus,
                $newStatus,
                $brokerLead?->broker_visible_status ?? 'Updated'
            ));

            return $lead;
        });
    }
}
