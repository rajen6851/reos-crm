<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Lead;
use App\Models\SiteVisit;
use Illuminate\Http\Request;

class ReportApiController extends Controller
{
    public function summary(Request $request)
    {
        abort_unless($request->user()->isManager() || $request->user()->isCompanyAdmin(), 403);
        $companyId = $request->user()->company_id;
        $leads = Lead::where('company_id', $companyId);
        $bookings = Booking::where('company_id', $companyId);

        return response()->json([
            'status' => 'success',
            'data' => [
                'leads' => [
                    'total' => (clone $leads)->count(),
                    'new' => (clone $leads)->where('status', 'new')->count(),
                    'site_visits' => (clone $leads)->where('status', 'site_visit')->count(),
                    'converted' => (clone $leads)->whereIn('status', ['converted', 'booked'])->count(),
                    'lost' => (clone $leads)->where('status', 'lost')->count(),
                ],
                'bookings' => [
                    'total' => (clone $bookings)->count(),
                    'pending' => (clone $bookings)->where('approval_status', 'pending')->count(),
                    'approved' => (clone $bookings)->where('approval_status', 'approved')->count(),
                    'booking_amount' => (clone $bookings)->sum('booking_amount'),
                ],
                'site_visits' => SiteVisit::where('company_id', $companyId)->count(),
            ],
        ]);
    }
}
