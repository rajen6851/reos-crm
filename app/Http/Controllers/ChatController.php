<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View
    {
        $currentUser = Auth::user();
        
        // Scope users list by company (SuperAdmin sees all or company users)
        $companyId = $currentUser->company_id;
        
        $availableUsers = User::query()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->where('id', '!=', $currentUser->id)
            ->with(['role'])
            ->orderBy('name')
            ->get();

        return view('chat.index', compact('availableUsers'));
    }

    public function fetchConversations(): JsonResponse
    {
        $currentUser = Auth::user();

        $chats = Chat::query()
            ->whereHas('participants', fn($q) => $q->where('user_id', $currentUser->id))
            ->with(['users.role', 'lastMessage.sender'])
            ->get()
            ->map(function ($chat) use ($currentUser) {
                $participant = $chat->participants->firstWhere('user_id', $currentUser->id);
                $lastReadAt = $participant ? $participant->last_read_at : null;

                $unreadCount = $chat->messages()
                    ->where('sender_id', '!=', $currentUser->id)
                    ->when($lastReadAt, fn($q) => $q->where('created_at', '>', $lastReadAt))
                    ->count();

                $otherUser = $chat->type === 'direct'
                    ? $chat->users->firstWhere('id', '!=', $currentUser->id)
                    : null;

                return [
                    'id' => $chat->id,
                    'type' => $chat->type,
                    'name' => $chat->getDisplayName($currentUser),
                    'other_user_id' => $otherUser ? $otherUser->id : null,
                    'other_user_role' => $otherUser && $otherUser->role ? $otherUser->role->name : null,
                    'last_message' => $chat->lastMessage ? $chat->lastMessage->message : 'No messages yet',
                    'last_message_time' => $chat->lastMessage ? $chat->lastMessage->created_at->diffForHumans() : '',
                    'unread_count' => $unreadCount,
                    'updated_at' => $chat->updated_at->timestamp,
                ];
            })
            ->sortByDesc('updated_at')
            ->values();

        return response()->json([
            'success' => true,
            'conversations' => $chats,
        ]);
    }

    public function fetchMessages(Chat $chat): JsonResponse
    {
        $currentUser = Auth::user();

        // Ensure user is participant
        $participant = $chat->participants()->where('user_id', $currentUser->id)->first();
        if (!$participant) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Update read timestamp
        $participant->update(['last_read_at' => now()]);

        $messages = $chat->messages()
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) use ($currentUser) {
                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $msg->sender ? $msg->sender->name : 'Unknown',
                    'message' => $msg->message,
                    'attachment_url' => $msg->attachment_path ? Storage::url($msg->attachment_path) : null,
                    'is_mine' => $msg->sender_id === $currentUser->id,
                    'created_at' => $msg->created_at->format('h:i A'),
                ];
            });

        return response()->json([
            'success' => true,
            'chat' => [
                'id' => $chat->id,
                'type' => $chat->type,
                'name' => $chat->getDisplayName($currentUser),
                'participants' => $chat->users->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'role' => $u->role ? $u->role->name : 'User']),
            ],
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request, Chat $chat): JsonResponse
    {
        $currentUser = Auth::user();

        $participant = $chat->participants()->where('user_id', $currentUser->id)->first();
        if (!$participant) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240', // 10MB limit
        ]);

        if (!$request->filled('message') && !$request->hasFile('attachment')) {
            return response()->json(['error' => 'Message or attachment required'], 422);
        }

        // Prevent accidental rapid duplicate message submission within 2 seconds
        if ($request->filled('message') && !$request->hasFile('attachment')) {
            $recentDuplicate = ChatMessage::where('chat_id', $chat->id)
                ->where('sender_id', $currentUser->id)
                ->where('message', $request->input('message'))
                ->where('created_at', '>=', now()->subSeconds(2))
                ->first();

            if ($recentDuplicate) {
                return response()->json([
                    'success' => true,
                    'message' => [
                        'id' => $recentDuplicate->id,
                        'sender_id' => $recentDuplicate->sender_id,
                        'sender_name' => $currentUser->name,
                        'message' => $recentDuplicate->message,
                        'attachment_url' => null,
                        'is_mine' => true,
                        'created_at' => $recentDuplicate->created_at->format('h:i A'),
                    ],
                ]);
            }
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('chat_attachments', 'public');
        }

        $msg = ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_id' => $currentUser->id,
            'message' => $request->input('message'),
            'attachment_path' => $attachmentPath,
        ]);

        $chat->touch(); // update updated_at timestamp
        $participant->update(['last_read_at' => now()]);

        // Dispatch Firebase Cloud Messaging Push Notification & Realtime Database Sync
        $firebaseService = app(\App\Services\FirebaseNotificationService::class);
        $firebaseService->syncChatToRealtimeDb($chat, $msg, $currentUser);
        $firebaseService->sendChatNotification(
            $currentUser,
            $chat,
            $msg->message ?? 'Attachment sent'
        );

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'sender_name' => $currentUser->name,
                'message' => $msg->message,
                'attachment_url' => $msg->attachment_path ? Storage::url($msg->attachment_path) : null,
                'is_mine' => true,
                'created_at' => $msg->created_at->format('h:i A'),
            ],
        ]);
    }

    public function startDirectChat(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $currentUser = Auth::user();
        $targetUserId = (int) $request->input('user_id');

        if ($targetUserId === $currentUser->id) {
            return response()->json(['error' => 'Cannot start chat with yourself'], 422);
        }

        // Check if direct chat already exists between currentUser and targetUser
        $existingChat = Chat::query()
            ->where('type', 'direct')
            ->whereHas('participants', fn($q) => $q->where('user_id', $currentUser->id))
            ->whereHas('participants', fn($q) => $q->where('user_id', $targetUserId))
            ->first();

        if ($existingChat) {
            return response()->json([
                'success' => true,
                'chat_id' => $existingChat->id,
            ]);
        }

        // Create new direct chat
        $chat = Chat::create([
            'company_id' => $currentUser->company_id,
            'type' => 'direct',
            'created_by' => $currentUser->id,
        ]);

        ChatParticipant::create(['chat_id' => $chat->id, 'user_id' => $currentUser->id, 'role' => 'admin']);
        ChatParticipant::create(['chat_id' => $chat->id, 'user_id' => $targetUserId, 'role' => 'member']);

        return response()->json([
            'success' => true,
            'chat_id' => $chat->id,
        ]);
    }

    public function createGroupChat(Request $request): JsonResponse
    {
        $currentUser = Auth::user();

        if (!$currentUser->isCompanyAdmin() && !$currentUser->isManager() && !$currentUser->isSaaSFounder()) {
            return response()->json(['error' => 'Unauthorized. Group chat creation is reserved for Admins and Managers.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'exists:users,id',
        ]);

        $chat = Chat::create([
            'company_id' => $currentUser->company_id,
            'type' => 'group',
            'name' => $request->input('name'),
            'created_by' => $currentUser->id,
        ]);

        // Add creator
        ChatParticipant::create(['chat_id' => $chat->id, 'user_id' => $currentUser->id, 'role' => 'admin']);

        // Add participants
        $participantIds = array_diff($request->input('participant_ids'), [$currentUser->id]);
        foreach ($participantIds as $uId) {
            ChatParticipant::create(['chat_id' => $chat->id, 'user_id' => $uId, 'role' => 'member']);
        }

        return response()->json([
            'success' => true,
            'chat_id' => $chat->id,
        ]);
    }
}
