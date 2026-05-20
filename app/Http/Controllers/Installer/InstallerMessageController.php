<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\VipUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InstallerMessageController extends Controller
{
    public function index()
    {
        $installer = Auth::guard('vip')->user();

        $conversations = collect();
        $totalUnread = 0;

        try {
            $conversations = Conversation::where('installer_id', $installer->id)
                ->with(['admin', 'latestMessage'])
                ->orderByDesc('last_message_at')
                ->get();

            foreach ($conversations as $conv) {
                $conv->unread_count = $conv->unreadCountFor($installer->id);
                $totalUnread += $conv->unread_count;
            }
        } catch (\Exception $e) {
            \Log::error('Messages index error: ' . $e->getMessage());
        }

        $admins = VipUser::whereIn('role', ['admin', 'technician'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('installer.messages.index', compact('conversations', 'totalUnread', 'admins'));
    }

    public function show($id)
    {
        try {
            $installer = Auth::guard('vip')->user();

            $conversation = Conversation::where(function ($q) use ($installer) {
                $q->where('installer_id', $installer->id)
                  ->orWhere('admin_id', $installer->id);
            })->findOrFail($id);

            $conversation->messages()
                ->where('sender_id', '!=', $installer->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            $messages = $conversation->messages()->with('sender')->get();

            return response()->json([
                'conversation' => $conversation->load('admin'),
                'messages' => $messages->map(fn($m) => $this->formatMessage($m, $installer->id)),
            ]);
        } catch (\Exception $e) {
            \Log::error('Message show error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load messages: ' . $e->getMessage()], 500);
        }
    }

    public function send(Request $request, $id)
    {
        try {
            $installer = Auth::guard('vip')->user();

            // Find conversation — allow both installer_id OR admin_id match
            $conversation = Conversation::where(function ($q) use ($installer) {
                $q->where('installer_id', $installer->id)
                  ->orWhere('admin_id', $installer->id);
            })->findOrFail($id);

            $request->validate([
                'body' => 'nullable|string|max:5000',
                'attachment' => 'nullable|file|max:20480',
                'voice_note' => 'nullable|file|max:10240',
            ]);

            $data = [
                'conversation_id' => $conversation->id,
                'sender_id' => $installer->id,
                'body' => $request->input('body', ''),
                'message_type' => 'text',
            ];

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
            } elseif ($request->hasFile('attachment')) {
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
                'message' => $this->formatMessage($message->load('sender'), $installer->id),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Message send DB error: ' . $e->getMessage());
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            \Log::error('Message send error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function startConversation(Request $request)
    {
        try {
            $installer = Auth::guard('vip')->user();

            $validated = $request->validate([
                'admin_id' => 'required|exists:vip_users,id',
                'body' => 'required|string|max:5000',
            ]);

            $conversation = Conversation::where('installer_id', $installer->id)
                ->where('admin_id', $validated['admin_id'])
                ->first();

            if (!$conversation) {
                $admin = VipUser::findOrFail($validated['admin_id']);
                $conversation = Conversation::create([
                    'admin_id' => $validated['admin_id'],
                    'installer_id' => $installer->id,
                    'subject' => 'Chat with ' . $admin->name,
                    'last_message_at' => now(),
                ]);
            }

            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $installer->id,
                'body' => $validated['body'],
                'message_type' => 'text',
            ]);

            $conversation->update(['last_message_at' => now()]);

            return response()->json([
                'success' => true,
                'conversation_id' => $conversation->id,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Start conversation DB error: ' . $e->getMessage());
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            \Log::error('Start conversation error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function deleteMessage($conversationId, $messageId)
    {
        $installer = Auth::guard('vip')->user();
        $message = Message::where('conversation_id', $conversationId)
            ->where('sender_id', $installer->id)
            ->findOrFail($messageId);

        if ($message->attachment) {
            Storage::disk('public')->delete($message->attachment);
        }

        $message->delete();

        return response()->json(['success' => true]);
    }

    public function unreadCount()
    {
        $installer = Auth::guard('vip')->user();
        $count = Message::whereHas('conversation', fn($q) => $q->where('installer_id', $installer->id))
            ->where('sender_id', '!=', $installer->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
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
