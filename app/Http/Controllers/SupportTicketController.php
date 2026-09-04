<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = $user->isBroker() 
            ? SupportTicket::withoutGlobalScopes()->with(['company', 'user', 'assignedTo'])
            : SupportTicket::with(['user', 'assignedTo']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // If user is sales executive or broker, show only their tickets
        if ($user->isSales() || $user->isBroker()) {
            $query->where('user_id', $user->id);
        }

        $tickets = $query->orderBy('updated_at', 'desc')->paginate(15);

        $agents = User::where('company_id', $user->company_id)
            ->whereDoesntHave('role', function ($q) {
                $q->where('slug', 'broker');
            })
            ->get();

        $companies = \App\Models\Company::withoutGlobalScopes()->where('status', 'active')->get();

        // Metrics
        $openCount = ($user->isBroker() ? SupportTicket::withoutGlobalScopes()->where('user_id', $user->id) : SupportTicket::query())->where('status', 'open')->count();
        $inProgressCount = ($user->isBroker() ? SupportTicket::withoutGlobalScopes()->where('user_id', $user->id) : SupportTicket::query())->where('status', 'in_progress')->count();
        $resolvedCount = ($user->isBroker() ? SupportTicket::withoutGlobalScopes()->where('user_id', $user->id) : SupportTicket::query())->where('status', 'resolved')->count();
        $closedCount = ($user->isBroker() ? SupportTicket::withoutGlobalScopes()->where('user_id', $user->id) : SupportTicket::query())->where('status', 'closed')->count();

        return view('support_tickets.index', compact(
            'tickets', 
            'agents', 
            'companies',
            'openCount', 
            'inProgressCount', 
            'resolvedCount', 
            'closedCount'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'company_id' => $user->isBroker() ? 'required|exists:companies,id' : 'nullable|exists:companies,id',
            'subject' => 'required|string|max:255',
            'category' => 'required|in:Technical,Billing,Inventory,Lead/CRM,General',
            'priority' => 'required|in:low,medium,high,urgent',
            'description' => 'required|string',
            'assigned_to_user_id' => 'nullable|exists:users,id',
        ]);

        $targetCompanyId = ($user->isBroker() && $request->filled('company_id'))
            ? $request->company_id
            : ($user->company_id ?? $request->company_id ?? 1);

        $ticket = SupportTicket::withoutGlobalScopes()->create([
            'company_id' => $targetCompanyId,
            'user_id' => $user->id,
            'subject' => $request->subject,
            'category' => $request->category,
            'priority' => $request->priority,
            'description' => $request->description,
            'assigned_to_user_id' => $request->assigned_to_user_id ?? null,
            'status' => 'open',
        ]);

        // Email Notification to Company Support Admins / Managers
        $admins = User::where('company_id', $targetCompanyId)
            ->whereHas('role', function ($q) {
                $q->whereIn('slug', ['admin', 'company_admin', 'founder', 'director', 'manager', 'sales_manager']);
            })->get();

        foreach ($admins as $admin) {
            app(\App\Services\NotificationService::class)->notify(
                $admin,
                'support_ticket',
                "🎧 New Support Ticket #{$ticket->ticket_number}: {$ticket->subject}",
                "A new support ticket #{$ticket->ticket_number} ({$ticket->category}) was submitted by {$user->name} with {$ticket->priority} priority.",
                url("/support-tickets/{$ticket->id}")
            );
        }

        return redirect()->route('support-tickets.show', $ticket->id)
            ->with('success', 'Support ticket #' . $ticket->ticket_number . ' submitted successfully to target company!');
    }

    public function show($id)
    {
        $ticket = SupportTicket::withoutGlobalScopes()->with(['company', 'user', 'assignedTo', 'replies.user'])->findOrFail($id);
        $agents = User::where('company_id', $ticket->company_id)
            ->whereDoesntHave('role', function ($q) {
                $q->where('slug', 'broker');
            })
            ->get();

        return view('support_tickets.show', compact('ticket', 'agents'));
    }

    public function reply(Request $request, $id)
    {
        $ticket = SupportTicket::withoutGlobalScopes()->with('user')->findOrFail($id);

        $request->validate([
            'message' => 'required|string',
        ]);

        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->user()->id,
            'message' => $request->message,
            'is_internal_note' => $request->has('is_internal_note') ? true : false,
        ]);

        // Auto update status if resolved/in_progress based on user role
        if ($ticket->status === 'open' && (auth()->user()->isManager() || auth()->user()->isCompanyAdmin())) {
            $ticket->update(['status' => 'in_progress']);
        }

        // Email Notification to Ticket Creator if reply is not by creator and not internal
        if (auth()->id() !== $ticket->user_id && !$request->has('is_internal_note')) {
            app(\App\Services\NotificationService::class)->notify(
                $ticket->user,
                'support_ticket_reply',
                "💬 New Reply on Ticket #{$ticket->ticket_number}",
                "Hello {$ticket->user->name}, a new reply was posted on your support ticket #{$ticket->ticket_number} by " . auth()->user()->name . ".",
                url("/support-tickets/{$ticket->id}")
            );
        }

        $ticket->touch();

        return redirect()->back()->with('success', 'Reply posted successfully!');
    }

    public function updateStatus(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);

        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'assigned_to_user_id' => 'nullable|exists:users,id',
        ]);

        $ticket->update([
            'status' => $request->status,
            'assigned_to_user_id' => $request->assigned_to_user_id ?? $ticket->assigned_to_user_id,
        ]);

        return redirect()->back()->with('success', 'Support ticket status updated to ' . strtoupper(str_replace('_', ' ', $request->status)) . '!');
    }

    public function destroy($id)
    {
        if (!auth()->user()->isCompanyAdmin() && auth()->user()->role?->slug !== 'founder') {
            return back()->with('error', 'Only Admins can delete support tickets.');
        }

        $ticket = SupportTicket::findOrFail($id);
        $number = $ticket->ticket_number;
        $ticket->delete();

        return redirect()->route('support-tickets.index')->with('success', "Support Ticket {$number} deleted successfully!");
    }
}
