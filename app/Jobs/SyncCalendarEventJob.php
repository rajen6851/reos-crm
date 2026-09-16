<?php

namespace App\Jobs;

use App\Models\SiteVisit;
use App\Services\GoogleCalendarService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncCalendarEventJob implements ShouldQueue
{
    use Queueable;

    protected SiteVisit $siteVisit;

    /**
     * Create a new job instance.
     */
    public function __construct(SiteVisit $siteVisit)
    {
        $this->siteVisit = $siteVisit;
    }

    /**
     * Execute the job.
     */
    public function handle(GoogleCalendarService $calendarService): void
    {
        $calendarService->syncSiteVisit($this->siteVisit);
    }
}
