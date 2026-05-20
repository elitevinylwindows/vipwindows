<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
        'read_at',
        'attachment',
        'attachment_name',
        'attachment_type',
        'attachment_size',
        'message_type',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'attachment_size' => 'integer',
    ];

    public function sender()
    {
        return $this->belongsTo(VipUser::class, 'sender_id');
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function isVoiceNote(): bool
    {
        return $this->message_type === 'voice';
    }

    public function isFile(): bool
    {
        return $this->message_type === 'file';
    }

    public function isImage(): bool
    {
        if (!$this->attachment_type) return false;
        return str_starts_with($this->attachment_type, 'image/');
    }

    public function attachmentUrl(): ?string
    {
        if (!$this->attachment) return null;

        // Voice notes need to be streamed through a controller for correct MIME type
        if ($this->message_type === 'voice') {
            return url('/messages/audio/' . $this->id);
        }

        return asset('storage/' . $this->attachment);
    }

    public function formattedSize(): string
    {
        $bytes = $this->attachment_size ?? 0;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }
}
