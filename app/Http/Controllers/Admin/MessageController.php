<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\VipUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Show the messaging inbox.
     */
    public function index()
    {
        $admin = Auth::guard('vip')->user();

        $conversations = Conversation::where('admin_id', $admin->id)
            ->with(['installer', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->get();

        // Installers that don't have a conversation yet (for "New Message")
        $installers = VipUser::where('role', 'installer')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Total unread
        $totalUnread = 0;
        foreach ($conversations as $conv) {
            $conv->unread_count = $conv->unreadCountFor($admin->id);
            $totalUnread += $conv->unread_count;
        }

        return view('admin.messages.index', compact('conversations', 'installers', 'totalUnread'));
    }

    /**
     * Show a conversation (JSON for AJAX load in right panel).
     */
    public function show($id)
    {
        $admin = Auth::guard('vip')->user();
        $conversation = Conversation::where('admin_id', $admin->id)->findOrFail($id);

        // Mark all messages from installer as read
        $conversation->messages()
            ->where('sender_id', '!=', $admin->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $conversation->messages()->with('sender')->get();

        return response()->json([
            'conversation' => $conversation->load('installer'),
            'messages' => $messages->map(fn($m) => [
                'id' => $m->id,
                'body' => $m->body,
                'sender_name' => $m->sender->name ?? 'Unknown',
                'sender_id' => $m->sender_id,
                'is_mine' => $m->sender_id === $admin->id,
                'read_at' => $m->read_at?->format('M d, g:i A'),
                'created_at' => $m->created_at->format('M d, g:i A'),
                'time_ago' => $m->created_at->diffForHumans(),
                'attachment' => $m->attachment,
            ]),
        ]);
    }

    /**
     * Send a message in an existing conversation.
     */
    public function send(Request $request, $id)
    {
        $admin = Auth::guard('vip')->user();
        $conversation = Conversation::where('admin_id', $admin->id)->findOrFail($id);

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $admin->id,
            'body' => $validated['body'],
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'body' => $message->body,
                'sender_name' => $admin->name,
                'sender_id' => $admin->id,
                'is_mine' => true,
                'created_at' => $message->created_at->format('M d, g:i A'),
                'time_ago' => $message->created_at->diffForHumans(),
            ],
        ]);
    }

    /**
     * Start a new conversation with an installer.
     */
    public function startConversation(Request $request)
    {
        $admin = Auth::guard('vip')->user();

        $validated = $request->validate([
            'installer_id' => 'required|exists:vip_users,id',
            'body' => 'required|string|max:5000',
        ]);

        // Check if conversation already exists
        $conversation = Conversation::where('admin_id', $admin->id)
            ->where('installer_id', $validated['installer_id'])
            ->first();

        if (!$conversation) {
            $installer = VipUser::findOrFail($validated['installer_id']);
            $conversation = Conversation::create([
                'admin_id' => $admin->id,
                'installer_id' => $validated['installer_id'],
                'subject' => 'Chat with ' . $installer->name,
                'last_message_at' => now(),
            ]);
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $admin->id,
            'body' => $validated['body'],
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
        ]);
    }

    /**
     * Get unread count (for badge polling).
     */
    public function unreadCount()
    {
        $admin = Auth::guard('vip')->user();
        $count = Message::whereHas('conversation', fn($q) => $q->where('admin_id', $admin->id))
            ->where('sender_id', '!=', $admin->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }
}
