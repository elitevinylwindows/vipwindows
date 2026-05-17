<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallerAvailabilityOverride extends Model
{
    protected $table = 'installer_availability_overrides';

    protected $fillable = [
        'installer_id', 'override_date', 'is_available',
        'start_time', 'end_time', 'max_bookings_per_slot', 'reason',
    ];

    protected $casts = [
        'override_date' => 'date',
        'is_available'  => 'boolean',
    ];

    public function installer()
    {
        return $this->belongsTo(VipUser::class, 'installer_id');
    }
}
