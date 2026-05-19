<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use SoftDeletes;

    protected $table = 'vip_jobs';

    protected $fillable = [
        'job_number', 'quote_id', 'invoice_id', 'service_id', 'customer_name',
        'customer_email', 'customer_phone', 'install_address', 'install_city',
        'install_state', 'install_zip', 'description', 'status', 'priority',
        'assigned_to', 'assignment_type', 'crew_id', 'scheduled_date', 'end_date',
        'scheduled_time', 'estimated_duration', 'actual_start',
        'actual_end', 'notes', 'completion_notes', 'created_by', 'image',
        'reschedule_reason', 'rescheduled_at', 'rescheduled_from_date', 'rescheduled_from_time',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'end_date' => 'date',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
        'rescheduled_at' => 'datetime',
        'rescheduled_from_date' => 'date',
    ];

    public function assignee()
    {
        return $this->belongsTo(VipUser::class, 'assigned_to');
    }

    public function crew()
    {
        return $this->belongsTo(Crew::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function creator()
    {
        return $this->belongsTo(VipUser::class, 'created_by');
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function jobNotes()
    {
        return $this->hasMany(JobNote::class)->orderBy('created_at', 'desc');
    }

    public function jobItems()
    {
        return $this->hasMany(JobItem::class)->orderBy('sort_order');
    }

    public function timeLogs()
    {
        return $this->hasMany(JobTimeLog::class)->orderByDesc('clock_in');
    }

    /**
     * Get the currently active time log (clocked in, not out) for a user.
     */
    public function activeTimeLog($userId = null)
    {
        return $this->timeLogs()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->whereNull('clock_out')
            ->first();
    }
}
