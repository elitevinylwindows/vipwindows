<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobTimeLog extends Model
{
    protected $table = 'job_time_logs';

    protected $fillable = [
        'job_id', 'user_id', 'clock_in', 'clock_out', 'total_minutes', 'earnings', 'pay_status', 'notes',
    ];

    protected $casts = [
        'clock_in'  => 'datetime',
        'clock_out' => 'datetime',
        'earnings'  => 'float',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function user()
    {
        return $this->belongsTo(VipUser::class, 'user_id');
    }

    /**
     * Check if this log is still active (clocked in, not out).
     */
    public function isActive(): bool
    {
        return $this->clock_in && !$this->clock_out;
    }
}
