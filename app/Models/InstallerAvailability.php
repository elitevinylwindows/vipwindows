<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallerAvailability extends Model
{
    protected $table = 'installer_availability';

    protected $fillable = [
        'installer_id', 'day_of_week', 'start_time', 'end_time',
        'slot_duration', 'max_bookings_per_slot', 'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    public function installer()
    {
        return $this->belongsTo(VipUser::class, 'installer_id');
    }

    /**
     * Generate time slots for this day's availability.
     */
    public function generateSlots(): array
    {
        if (!$this->is_available) return [];

        $slots = [];
        $start = strtotime($this->start_time);
        $end = strtotime($this->end_time);
        $duration = $this->slot_duration * 60; // convert to seconds

        while ($start + $duration <= $end) {
            $slots[] = [
                'start' => date('H:i', $start),
                'end'   => date('H:i', $start + $duration),
                'label' => date('g:i A', $start) . ' – ' . date('g:i A', $start + $duration),
            ];
            $start += $duration;
        }

        return $slots;
    }

    public static function dayName(int $day): string
    {
        return ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][$day] ?? '';
    }
}
