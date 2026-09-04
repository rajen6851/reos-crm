<?php

namespace App\Services;

use App\Models\BrokerLead;
use App\Models\Lead;
use InvalidArgumentException;

class BrokerLeadStatusService
{
    /**
     * Internal CRM Status => Broker Visible Status Mapping
     */
    protected array $statusMap = [
        'new' => 'Submitted',
        'under_review' => 'Under Review',
        'assigned' => 'Assigned',
        'contacted' => 'Contacted',
        'follow_up' => 'Follow-up',
        'site_visit' => 'Site Visit Scheduled',
        'site_visit_scheduled' => 'Site Visit Scheduled',
        'site_visit_completed' => 'Site Visit Completed',
        'interested' => 'Interested',
        'negotiation' => 'Negotiation',
        'booking_initiated' => 'Booking Initiated',
        'converted' => 'Booked',
        'booked' => 'Booked',
        'lost' => 'Lost',
    ];

    /**
     * Allowed status transitions for internal CRM
     */
    protected array $allowedTransitions = [
        'new' => ['under_review', 'assigned', 'contacted', 'follow_up', 'site_visit', 'interested', 'negotiation', 'converted', 'booked', 'lost'],
        'under_review' => ['assigned', 'contacted', 'follow_up', 'site_visit', 'interested', 'negotiation', 'converted', 'booked', 'lost'],
        'assigned' => ['contacted', 'follow_up', 'site_visit', 'interested', 'negotiation', 'converted', 'booked', 'lost'],
        'contacted' => ['follow_up', 'site_visit', 'interested', 'negotiation', 'converted', 'booked', 'lost'],
        'follow_up' => ['site_visit', 'interested', 'negotiation', 'converted', 'booked', 'lost'],
        'site_visit' => ['site_visit_completed', 'interested', 'follow_up', 'negotiation', 'converted', 'booked', 'lost'],
        'site_visit_completed' => ['interested', 'negotiation', 'booking_initiated', 'converted', 'booked', 'lost'],
        'interested' => ['negotiation', 'booking_initiated', 'converted', 'booked', 'lost'],
        'negotiation' => ['booking_initiated', 'converted', 'booked', 'lost'],
        'booking_initiated' => ['converted', 'booked', 'lost'],
        'converted' => [], // Terminal status
        'booked' => [], // Terminal status
        'lost' => ['follow_up'], // Allowed only via explicit manager reopen request
    ];

    public function mapInternalToBrokerStatus(string $internalStatus): string
    {
        $normalized = strtolower(trim(str_replace(' ', '_', $internalStatus)));
        return $this->statusMap[$normalized] ?? ucfirst(str_replace('_', ' ', $internalStatus));
    }

    public function validateTransition(string $currentStatus, string $newStatus, bool $isReopen = false): void
    {
        $current = strtolower(trim($currentStatus));
        $new = strtolower(trim($newStatus));

        if ($current === $new) {
            return;
        }

        if ($current === 'lost' && ($new === 'converted' || $new === 'booked')) {
            throw new InvalidArgumentException("Cannot transition lead directly from Lost to Booked.");
        }

        $allowed = $this->allowedTransitions[$current] ?? [];

        if (!$isReopen && !empty($allowed) && !in_array($new, $allowed, true)) {
            throw new InvalidArgumentException("Invalid status transition from {$currentStatus} to {$newStatus}.");
        }
    }

    public function syncBrokerVisibleStatus(Lead $lead, ?string $customMessage = null): ?BrokerLead
    {
        $brokerLead = $lead->brokerLead;

        if (!$brokerLead) {
            return null;
        }

        $brokerStatus = ($lead->assigned_to_user_id && in_array($lead->status, ['new', 'contacted']))
            ? 'Assigned'
            : $this->mapInternalToBrokerStatus($lead->status);

        $brokerLead->update([
            'broker_visible_status' => $brokerStatus,
            'broker_visible_message' => $customMessage ?? "Lead status updated to {$brokerStatus}",
        ]);

        return $brokerLead;
    }
}
