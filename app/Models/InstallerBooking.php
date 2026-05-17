<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallerBooking extends Model
{
    protected $table = 'installer_bookings';

    protected $fillable = [
        'booking_number', 'installer_id', 'customer_id',
        'customer_name', 'customer_email', 'customer_phone',
        'install_address', 'install_city', 'install_state', 'install_zip',
        'booking_date', 'booking_time', 'service_type', 'description',
        'status', 'notes', 'installer_notes', 'quote_id',
    ];

    protected $casts = [
        'booking_date' => 'date',
    ];

    public function installer()
    {
        return $this->belongsTo(VipUser::class, 'installer_id');
    }

    public function customer()
    {
        return $this->belongsTo(VipUser::class, 'customer_id');
    }

    public function quote()
    {
        return $this->belongsTo(VipQuote::class, 'quote_id');
    }
}
