<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechMeasure extends Model
{
    protected $table = 'tech_measures';

    protected $fillable = [
        'calendar_event_id',
        'job_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'address',
        'city',
        'state',
        'zip',
        'status',          // pending, in_progress, completed, converted
        'assigned_to',     // vip_user id (tech/installer)
        'crew_id',
        'started_at',
        'completed_at',
        'notes',
        'frame_type',
        'has_grids',
        'grid_list',
        'grid_pattern',
        'created_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'has_grids' => 'boolean',
    ];

    public function calendarEvent()
    {
        return $this->belongsTo(CalendarEvent::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function items()
    {
        return $this->hasMany(TechMeasureItem::class)->orderBy('sort_order');
    }

    public function photos()
    {
        return $this->hasMany(TechMeasurePhoto::class);
    }

    public function assignee()
    {
        return $this->belongsTo(VipUser::class, 'assigned_to');
    }

    public function crew()
    {
        return $this->belongsTo(Crew::class);
    }

    public function creator()
    {
        return $this->belongsTo(VipUser::class, 'created_by');
    }

    /**
     * Full address string.
     */
    public function fullAddress(): string
    {
        return trim(implode(', ', array_filter([
            $this->address, $this->city, $this->state
        ]))) . ($this->zip ? ' ' . $this->zip : '');
    }
}
