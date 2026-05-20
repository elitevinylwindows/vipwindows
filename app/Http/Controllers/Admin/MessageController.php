<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\VipUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('vip')->user();

        $conversations = Conversation::where('admin_id', $admin->id)
            ->with(['installer', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->get();

        // All active staff for new conversations (admins, technicians, schedulers, installers)
        $installers = VipUser::where('status', 'active')
            ->where('id', '!=', $admin->id)
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        $totalUnread = 0;
        foreach ($conversations as $conv) {
            $conv->unread_count = $conv->unreadCountFor($admin->id);
            $totalUnread += $conv->unread_count;
        }

        return view('admin.messages.index', compact('conversations', 'installers', 'totalUnread'));
    }

    public function show($id)
    {
        $admin = Auth::guard('vip')->user();
        $conversation = Conversation::where('admin_id', $admin->id)->findOrFail($id);

        $conversation->messages()
            ->where('sender_id', '!=', $admin->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $conversation->messages()->with('sender')->get();

        return response()->json([
            'conversation' => $conversation->load('installer'),
            'messages' => $messages->map(fn($m) => $this->formatMessage($m, $admin->id)),
        ]);
    }

    public function send(Request $request, $id)
    {
        $admin = Auth::guard('vip')->user();
        $conversation = Conversation::where('admin_id', $admin->id)->findOrFail($id);

        $request->validate([
            'body' => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:20480',
            'voice_note' => 'nullable|file|max:10240',
        ]);

        $data = [
            'conversation_id' => $conversation->id,
            'sender_id' => $admin->id,
            'body' => $request->input('body', ''),
            'message_type' => 'text',
        ];

        // Handle voice note
        if ($request->hasFile('voice_note')) {
            $file = $request->file('voice_note');
            $path = $file->store('messages/voice/' . $conversation->id, 'public');

            // Determine MIME type — PHP's finfo often fails on webm/ogg
            $mimeType = $file->getMimeType();
            if (!$mimeType || $mimeType === 'application/octet-stream') {
                $ext = strtolower($file->getClientOriginalExtension());
                $audioMimeMap = ['webm' => 'audio/webm', 'ogg' => 'audio/ogg', 'mp4' => 'audio/mp4', 'm4a' => 'audio/mp4', 'wav' => 'audio/wav'];
                $mimeType = $audioMimeMap[$ext] ?? 'audio/webm';
            }

            $data['attachment'] = $path;
            $data['attachment_name'] = 'Voice message';
            $data['attachment_type'] = $mimeType;
            $data['attachment_size'] = $file->getSize();
            $data['message_type'] = 'voice';
        }
        // Handle file attachment
        elseif ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('messages/files/' . $conversation->id, 'public');
            $data['attachment'] = $path;
            $data['attachment_name'] = $file->getClientOriginalName();
            $data['attachment_type'] = $file->getMimeType();
            $data['attachment_size'] = $file->getSize();
            $data['message_type'] = 'file';
        }

        if (empty($data['body']) && $data['message_type'] === 'text') {
            return response()->json(['error' => 'Message cannot be empty.'], 422);
        }

        $message = Message::create($data);
        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => $this->formatMessage($message->load('sender'), $admin->id),
        ]);
    }

    public function startConversation(Request $request)
    {
        $admin = Auth::guard('vip')->user();

        $validated = $request->validate([
            'installer_id' => 'required|exists:vip_users,id',
            'body' => 'required|string|max:5000',
        ]);

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
            'message_type' => 'text',
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
        ]);
    }

    public function deleteMessage($conversationId, $messageId)
    {
        $admin = Auth::guard('vip')->user();
        $message = Message::where('conversation_id', $conversationId)
            ->where('sender_id', $admin->id)
            ->findOrFail($messageId);

        if ($message->attachment) {
            Storage::disk('public')->delete($message->attachment);
        }

        $message->delete();

        return response()->json(['success' => true]);
    }

    public function unreadCount()
    {
        $admin = Auth::guard('vip')->user();
        $count = Message::whereHas('conversation', fn($q) => $q->where('admin_id', $admin->id))
            ->where('sender_id', '!=', $admin->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Stream a voice note audio file with correct headers.
     */
    public function streamAudio($messageId)
    {
        $message = Message::findOrFail($messageId);

        if (!$message->attachment || !Storage::disk('public')->exists($message->attachment)) {
            abort(404, 'Audio file not found.');
        }

        $fullPath = Storage::disk('public')->path($message->attachment);
        $fileSize = Storage::disk('public')->size($message->attachment);

        // Determine correct MIME type — PHP's finfo often fails on webm
        $mimeType = $message->attachment_type;
        if (!$mimeType || $mimeType === 'application/octet-stream') {
            $ext = pathinfo($message->attachment, PATHINFO_EXTENSION);
            $mimeMap = [
                'webm' => 'audio/webm',
                'ogg'  => 'audio/ogg',
                'mp4'  => 'audio/mp4',
                'm4a'  => 'audio/mp4',
                'wav'  => 'audio/wav',
                'mp3'  => 'audio/mpeg',
            ];
            $mimeType = $mimeMap[$ext] ?? 'audio/webm';
        }

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Length' => $fileSize,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function formatMessage(Message $m, int $myId): array
    {
        return [
            'id' => $m->id,
            'body' => $m->body,
            'sender_name' => $m->sender->name ?? 'Unknown',
            'sender_id' => $m->sender_id,
            'is_mine' => $m->sender_id === $myId,
            'read_at' => $m->read_at?->format('M d, g:i A'),
            'created_at' => $m->created_at->format('M d, g:i A'),
            'time_ago' => $m->created_at->diffForHumans(),
            'message_type' => $m->message_type ?? 'text',
            'attachment_url' => $m->attachmentUrl(),
            'attachment_name' => $m->attachment_name,
            'attachment_type' => $m->attachment_type,
            'attachment_size' => $m->formattedSize(),
            'is_image' => $m->isImage(),
        ];
    }
}
