<?php

namespace App\Http\Controllers;

use App\Models\LeadActivity;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = LeadActivity::with(['lead', 'user'])
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }
}
