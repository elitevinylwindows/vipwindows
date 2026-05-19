<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $table = 'calendar_events';

    protected $fillable = [
        'title', 'description', 'event_date', 'event_time', 'end_time',
        'end_date', 'color', 'service_id', 'crew_id', 'created_by', 'address',
        'customer_name', 'customer_email', 'customer_phone', 'installation_types',
        'reschedule_reason', 'rescheduled_at', 'rescheduled_from_date', 'rescheduled_from_time',
        'event_status', 'related_event_id',
    ];

    protected $casts = [
        'event_date' => 'date',
        'end_date' => 'date',
        'installation_types' => 'array',
        'rescheduled_at' => 'datetime',
        'rescheduled_from_date' => 'date',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
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
     * The previous event this was rescheduled from.
     */
    public function previousEvent()
    {
        return $this->belongsTo(CalendarEvent::class, 'related_event_id');
    }

    /**
     * The new event that replaced this one (if rescheduled).
     */
    public function rescheduledTo()
    {
        return $this->hasOne(CalendarEvent::class, 'related_event_id');
    }
}
