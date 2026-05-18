<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $table = 'calendar_events';

    protected $fillable = [
        'title', 'description', 'event_date', 'event_time', 'end_time',
        'end_date', 'color', 'service_id', 'created_by', 'address',
        'customer_name', 'customer_email', 'customer_phone',
    ];

    protected $casts = [
        'event_date' => 'date',
        'end_date' => 'date',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function creator()
    {
        return $this->belongsTo(VipUser::class, 'created_by');
    }
}
