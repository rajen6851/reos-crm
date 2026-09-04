<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BrokerCommission;
use App\Models\BrokerLead;
use App\Models\Lead;
use App\Models\LeadActivity;

class BrokerPrivacyService
{
    /**
     * Sanitize lead details for broker consumption
     */
    public function sanitizeLead(Lead $lead): array
    {
        $brokerLead = $lead->brokerLead;

        return [
            'id' => $lead->id,
            'lead_code' => $lead->lead_code,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'customer_name' => trim("{$lead->first_name} {$lead->last_name}"),
            'phone' => $lead->phone,
            'email' => $lead->email,
            'project' => $lead->project ? [
                'id' => $lead->project->id,
                'name' => $lead->project->name,
                'code' => $lead->project->code,
                'city' => $lead->project->city,
            ] : null,
            'property_type' => $brokerLead?->property_type ?? $lead->interested_unit_type,
            'unit_type' => $brokerLead?->unit_type ?? $lead->interested_unit_type,
            'budget_min' => $lead->budget_min,
            'budget_max' => $lead->budget_max,
            'broker_visible_status' => $brokerLead?->broker_visible_status ?? 'Submitted',
            'broker_visible_message' => $brokerLead?->broker_visible_message ?? 'Lead submitted',
            'submitted_at' => $brokerLead?->submitted_at ?? $lead->created_at,
            'last_updated_at' => $lead->updated_at,
        ];
    }

    /**
     * Sanitize lead timeline activities for broker consumption
     */
    public function sanitizeTimeline(Lead $lead): array
    {
        // Only return client-safe activity types
        $safeActivityTypes = [
            'broker_lead_submitted',
            'assigned',
            'status_change',
            'site_visit_scheduled',
            'site_visit_completed',
            'booking_initiated',
            'booking_confirmed',
        ];

        return $lead->activities()
            ->get()
            ->filter(fn (LeadActivity $activity) => in_array($activity->activity_type, $safeActivityTypes, true))
            ->map(function (LeadActivity $activity) {
                return [
                    'id' => $activity->id,
                    'activity_type' => $activity->activity_type,
                    'title' => $this->formatPublicTitle($activity->activity_type, $activity->description),
                    'timestamp' => $activity->created_at->format('d M Y - h:i A'),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Sanitize booking details for broker
     */
    public function sanitizeBooking(Booking $booking): array
    {
        return [
            'id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'customer_name' => $booking->customer_name,
            'project_name' => $booking->project?->name,
            'unit_number' => $booking->unit?->unit_number,
            'booking_date' => $booking->booking_date->format('Y-m-d'),
            'status' => $booking->approval_status === 'approved' ? 'Booked' : 'Booking Initiated',
            'total_amount' => $booking->total_unit_cost,
        ];
    }

    protected function formatPublicTitle(string $type, string $defaultDesc): string
    {
        return match ($type) {
            'broker_lead_submitted' => 'Lead Submitted',
            'assigned' => 'Lead Assigned to Sales Team',
            'status_change' => 'Lead Pipeline Status Updated',
            'site_visit_scheduled' => 'Site Visit Scheduled',
            'site_visit_completed' => 'Site Visit Completed',
            'booking_initiated' => 'Booking Initiated',
            'booking_confirmed' => 'Booking Confirmed',
            default => 'Status Update',
        };
    }
}
