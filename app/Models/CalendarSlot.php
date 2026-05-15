<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarSlot extends Model
{
    protected $table = 'install_calendar_slots';

    protected $fillable = ['slot_date', 'slot_time', 'max_bookings', 'current_bookings', 'created_by'];

    protected $casts = ['slot_date' => 'date'];

    public function isAvailable(): bool
    {
        return $this->current_bookings < $this->max_bookings;
    }

    public function bookingsRemaining(): int
    {
        return max(0, $this->max_bookings - $this->current_bookings);
    }
}
