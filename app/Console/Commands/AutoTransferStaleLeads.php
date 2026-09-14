<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Lead;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\LeadAssignmentService;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class AutoTransferStaleLeads extends Command
{
    /**
     * php artisan leads:auto-transfer
     * php artisan leads:auto-transfer --dry-run        (preview without changes)
     * php artisan leads:auto-transfer --hours=24       (custom stale window)
     */
    protected $signature = 'leads:auto-transfer
                            {--dry-run : Preview which leads would be transferred without making changes}
                            {--hours=48 : Hours without activity before a lead is considered stale}';

    protected $description = 'Auto-transfer stale leads (no response) to next available sales executive.';

    public function handle(LeadAssignmentService $assignmentService, NotificationService $notifier): int
    {
        $isDryRun   = $this->option('dry-run');
        $staleHours = (int) $this->option('hours');
        $cutoffTime = now()->subHours($staleHours);

        $this->info($isDryRun
            ? "🔍 [DRY RUN] Scanning for leads with no activity in last {$staleHours}h..."
            : "🤖 Auto-Transfer: Scanning leads with no activity in last {$staleHours}h..."
        );

        // Find stale leads: assigned, active status, no activity within cutoff, within max transfers
        $staleLeads = Lead::withoutGlobalScopes()
            ->whereNotNull('assigned_to_user_id')
            ->whereIn('status', ['new', 'contacted'])
            ->where(function ($q) use ($cutoffTime) {
                $q->whereNull('last_activity_at')
                  ->orWhere('last_activity_at', '<', $cutoffTime);
            })
            ->where(function ($q) use ($cutoffTime) {
                // Newly assigned leads get full stale window from creation
                $q->where('created_at', '<', $cutoffTime);
            })
            ->where('transfer_count', '<', 3) // Max 3 auto-transfers per lead
            ->with(['assignedTo', 'company'])
            ->get();

        if ($staleLeads->isEmpty()) {
            $this->info("✅ No stale leads found. All leads are being worked on!");
            return 0;
        }

        $this->info("Found {$staleLeads->count()} stale lead(s).");

        $transferred = 0;
        $skipped     = 0;
        $alerted     = 0;

        foreach ($staleLeads as $lead) {
            $company = $lead->company;

            if (!$company) {
                $this->warn("  ⚠ Lead {$lead->lead_code} has no company — skipping.");
                $skipped++;
                continue;
            }

            // Find next available executive in the same company (round-robin, exclude current assignee)
            $executives = User::where('company_id', $company->id)
                ->where('is_active', true)
                ->where('id', '!=', $lead->assigned_to_user_id)
                ->whereHas('role', function ($q) {
                    $q->whereIn('slug', ['sales_executive', 'executive', 'sales']);
                })
                ->withCount([
                    'assignedLeads as active_leads_count' => function ($q) {
                        $q->whereIn('status', ['new', 'contacted', 'site_visit', 'negotiation']);
                    }
                ])
                ->orderBy('active_leads_count') // Round-robin: assign to exec with fewest leads
                ->get();

            if ($executives->isEmpty()) {
                // No executive available — alert manager
                $this->warn("  ⚠ Lead {$lead->lead_code} [{$company->name}]: No other executive available. Alerting manager...");

                if (!$isDryRun) {
                    $managers = User::where('company_id', $company->id)
                        ->whereHas('role', function ($q) {
                            $q->whereIn('slug', ['admin', 'company_admin', 'manager', 'sales_manager', 'director', 'founder']);
                        })
                        ->get();

                    foreach ($managers as $manager) {
                        $notifier->notify(
                            $manager,
                            'stale_lead_alert',
                            "🚨 Stale Lead Alert: {$lead->first_name} {$lead->last_name} ({$lead->lead_code})",
                            "Lead '{$lead->first_name} {$lead->last_name}' ({$lead->lead_code}) has had no activity for {$staleHours}+ hours and no other executive is available for auto-transfer. Please assign manually.",
                            url("/leads/{$lead->id}")
                        );
                    }

                    // Mark eligible timestamp so we don't re-alert every hour
                    $lead->update(['transfer_eligible_at' => now()]);
                }

                $alerted++;
                continue;
            }

            $nextExec         = $executives->first();
            $previousAssignee = $lead->assignedTo;
            $nextTransferNo   = $lead->transfer_count + 1;

            $reason = "No activity for {$staleHours}+ hours (Auto-Transfer #{$nextTransferNo})";

            if ($isDryRun) {
                $this->line("  [DRY RUN] Would transfer {$lead->lead_code} ({$lead->first_name} {$lead->last_name}) from {$previousAssignee?->name} → {$nextExec->name}");
                $transferred++;
                continue;
            }

            // Perform the transfer
            $assignmentService->transferLead(
                $lead,
                $nextExec,
                $nextExec, // System-triggered: use next exec as actor (no system user)
                $reason,
                "Auto-transferred after {$staleHours}h of inactivity. Previous assignee: " . ($previousAssignee?->name ?? 'N/A'),
                'auto'
            );

            // Notify new executive
            $notifier->notify(
                $nextExec,
                'lead_auto_transferred',
                "🤖 Lead Auto-Assigned: {$lead->first_name} {$lead->last_name} ({$lead->lead_code})",
                "Hello {$nextExec->name}, Lead '{$lead->first_name} {$lead->last_name}' ({$lead->phone}) has been automatically transferred to you as the previous executive had no activity for {$staleHours}+ hours. Please contact the customer immediately!",
                url("/leads/{$lead->id}")
            );

            // Notify previous executive
            if ($previousAssignee) {
                $notifier->notify(
                    $previousAssignee,
                    'lead_auto_removed',
                    "⚠️ Lead Auto-Transferred Away: {$lead->first_name} {$lead->last_name}",
                    "Hello {$previousAssignee->name}, Lead '{$lead->first_name} {$lead->last_name}' ({$lead->lead_code}) was automatically transferred to {$nextExec->name} due to {$staleHours}+ hours of inactivity.",
                    url("/leads/{$lead->id}")
                );
            }

            AuditLogService::log(
                'lead_auto_transferred',
                "Auto-transferred stale Lead {$lead->lead_code} from " . ($previousAssignee?->name ?? 'N/A') . " to {$nextExec->name}. Reason: {$reason}",
                $lead,
                ['from' => $previousAssignee?->name],
                ['to' => $nextExec->name, 'stale_hours' => $staleHours]
            );

            $this->info("  ✅ {$lead->lead_code} → {$nextExec->name} (was: {$previousAssignee?->name})");
            $transferred++;
        }

        $this->newLine();
        $this->info("Summary: {$transferred} transferred | {$skipped} skipped | {$alerted} manager alerts sent.");

        return 0;
    }
}
