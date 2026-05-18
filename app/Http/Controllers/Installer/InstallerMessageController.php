<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstallerMessageController extends Controller
{
    /**
     * Show the messaging inbox for an installer.
     */
    public function index()
    {
        $installer = Auth::guard('vip')->user();

        $conversations = Conversation::where('installer_id', $installer->id)
            ->with(['admin', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->get();

        // Total unread
        $totalUnread = 0;
        foreach ($conversations as $conv) {
            $conv->unread_count = $conv->unreadCountFor($installer->id);
            $totalUnread += $conv->unread_count;
        }

        return view('installer.messages.index', compact('conversations', 'totalUnread'));
    }

    /**
     * Show a conversation (JSON for AJAX load in right panel).
     */
    public function show($id)
    {
        $installer = Auth::guard('vip')->user();
        $conversation = Conversation::where('installer_id', $installer->id)->findOrFail($id);

        // Mark all messages from admin as read
        $conversation->messages()
            ->where('sender_id', '!=', $installer->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $conversation->messages()->with('sender')->get();

        return response()->json([
            'conversation' => $conversation->load('admin'),
            'messages' => $messages->map(fn($m) => [
                'id' => $m->id,
                'body' => $m->body,
                'sender_name' => $m->sender->name ?? 'Unknown',
                'sender_id' => $m->sender_id,
                'is_mine' => $m->sender_id === $installer->id,
                'read_at' => $m->read_at?->format('M d, g:i A'),
                'created_at' => $m->created_at->format('M d, g:i A'),
                'time_ago' => $m->created_at->diffForHumans(),
            ]),
        ]);
    }

    /**
     * Send a message in an existing conversation.
     */
    public function send(Request $request, $id)
    {
        $installer = Auth::guard('vip')->user();
        $conversation = Conversation::where('installer_id', $installer->id)->findOrFail($id);

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $installer->id,
            'body' => $validated['body'],
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'body' => $message->body,
                'sender_name' => $installer->name,
                'sender_id' => $installer->id,
                'is_mine' => true,
                'created_at' => $message->created_at->format('M d, g:i A'),
                'time_ago' => $message->created_at->diffForHumans(),
            ],
        ]);
    }

    /**
     * Get unread count (for badge polling).
     */
    public function unreadCount()
    {
        $installer = Auth::guard('vip')->user();
        $count = Message::whereHas('conversation', fn($q) => $q->where('installer_id', $installer->id))
            ->where('sender_id', '!=', $installer->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }
}
