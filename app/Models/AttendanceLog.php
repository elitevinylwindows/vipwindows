<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $table = 'attendance_logs';

    protected $fillable = [
        'user_id', 'clock_in', 'clock_out', 'total_minutes', 'notes', 'date',
    ];

    protected $casts = [
        'clock_in'  => 'datetime',
        'clock_out' => 'datetime',
        'date'      => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(VipUser::class, 'user_id');
    }

    public function isActive(): bool
    {
        return $this->clock_in && !$this->clock_out;
    }

    /**
     * Get the currently active (open) clock-in for a user.
     */
    public static function activeFor($userId)
    {
        return static::where('user_id', $userId)
            ->whereNull('clock_out')
            ->latest('clock_in')
            ->first();
    }

    /**
     * Format total_minutes into "Xh Ym".
     */
    public function durationFormatted(): string
    {
        if (!$this->total_minutes) return '—';
        $h = intdiv($this->total_minutes, 60);
        $m = $this->total_minutes % 60;
        return "{$h}h {$m}m";
    }
}
