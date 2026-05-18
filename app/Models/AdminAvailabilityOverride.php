<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAvailabilityOverride extends Model
{
    protected $table = 'admin_availability_overrides';

    protected $fillable = [
        'override_date', 'is_available', 'start_time', 'end_time',
        'max_bookings_per_slot', 'reason', 'created_by',
    ];

    protected $casts = [
        'override_date' => 'date',
        'is_available'  => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(VipUser::class, 'created_by');
    }
}
