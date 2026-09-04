<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\Request;

class SupportTicketApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = SupportTicket::with(['user:id,name,email', 'assignedTo:id,name,email']);

        if ($user->isSales() || $user->isBroker()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->orderBy('updated_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'required|in:Technical,Billing,Inventory,Lead/CRM,General',
            'priority' => 'required|in:low,medium,high,urgent',
            'description' => 'required|string',
        ]);

        $user = $request->user();

        $ticket = SupportTicket::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'subject' => $request->subject,
            'category' => $request->category,
            'priority' => $request->priority,
            'description' => $request->description,
            'status' => 'open',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Support ticket created successfully',
            'data' => $ticket
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $ticket = SupportTicket::with(['user:id,name,email', 'assignedTo:id,name,email', 'replies.user:id,name,email'])->findOrFail($id);

        if (($user->isSales() || $user->isBroker()) && $ticket->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to support ticket'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $ticket
        ]);
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $user = $request->user();
        $ticket = SupportTicket::findOrFail($id);

        if (($user->isSales() || $user->isBroker()) && $ticket->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to reply on this ticket'
            ], 403);
        }

        $reply = SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $request->message,
            'is_internal_note' => $request->boolean('is_internal_note'),
        ]);

        $ticket->touch();

        return response()->json([
            'success' => true,
            'message' => 'Reply posted successfully',
            'data' => $reply
        ]);
    }
}
