<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = AuditLog::with('user')->latest();

        if ($request->filled('event')) {
            $query->where('event', 'like', "%{$request->event}%");
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%");
            });
        }

        $auditLogs = $query->paginate(15);

        $teamUsers = User::where('company_id', $user->company_id)->get();

        return view('admin.audit_logs', compact('auditLogs', 'teamUsers'));
    }
}
