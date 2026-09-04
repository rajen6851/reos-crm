<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        // Lead Conversion Funnel Stats
        $totalLeads = Lead::count();
        $newLeads = Lead::where('status', 'new')->count();
        $contactedLeads = Lead::where('status', 'contacted')->count();
        $siteVisits = Lead::where('status', 'site_visit')->count();
        $negotiations = Lead::where('status', 'negotiation')->count();
        $convertedLeads = Lead::where('status', 'converted')->count();
        $lostLeads = Lead::where('status', 'lost')->count();

        // Revenue & Inventory Metrics
        $totalBookings = Booking::count();
        $totalRevenue = Booking::sum('booking_amount');
        $totalUnits = Unit::count();
        $bookedUnits = Unit::where('status', 'booked')->count();
        $availableUnits = Unit::where('status', 'available')->count();

        // Sales Executive Performance
        $salesPerformance = User::where('company_id', $companyId)
            ->whereHas('role', function ($q) {
                $q->where('slug', 'sales_executive');
            })
            ->withCount([
                'leads as total_leads',
                'leads as converted_leads' => function ($q) {
                    $q->where('status', 'converted');
                },
                'leads as site_visit_leads' => function ($q) {
                    $q->where('status', 'site_visit');
                }
            ])
            ->get();

        return view('reports.index', compact(
            'totalLeads', 'newLeads', 'contactedLeads', 'siteVisits', 'negotiations',
            'convertedLeads', 'lostLeads', 'totalBookings', 'totalRevenue',
            'totalUnits', 'bookedUnits', 'availableUnits', 'salesPerformance'
        ));
    }
}
