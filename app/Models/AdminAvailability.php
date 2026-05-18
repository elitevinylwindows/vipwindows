<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAvailability extends Model
{
    protected $table = 'admin_availability';

    protected $fillable = [
        'day_of_week', 'is_available', 'start_time', 'end_time',
        'slot_duration', 'max_bookings_per_slot', 'created_by',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(VipUser::class, 'created_by');
    }

    public static function dayName(int $day): string
    {
        return ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][$day] ?? '';
    }

    public static function dayAbbr(int $day): string
    {
        return ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][$day] ?? '';
    }
}
