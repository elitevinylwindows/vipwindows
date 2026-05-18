<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $table = 'conversations';

    protected $fillable = [
        'admin_id',
        'installer_id',
        'subject',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(VipUser::class, 'admin_id');
    }

    public function installer()
    {
        return $this->belongsTo(VipUser::class, 'installer_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Count unread messages for a given user.
     */
    public function unreadCountFor(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Get the other participant (from the perspective of the given user).
     */
    public function otherParticipant(int $userId): ?VipUser
    {
        return $this->admin_id === $userId ? $this->installer : $this->admin;
    }
}
