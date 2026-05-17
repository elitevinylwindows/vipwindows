<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallationOrder extends Model
{
    protected $table = 'installation_orders'; // shared table from Enterprise DB

    protected $fillable = [
        'quote_id', 'portal_user_id', 'customer_name', 'customer_email', 'customer_phone',
        'install_address', 'install_address2', 'install_city', 'install_state', 'install_zip',
        'notes', 'status', 'scheduled_date', 'scheduled_slot', 'technician_id', 'created_by', 'completed_at',
        'source', 'service_type', 'description',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function quoteItems()
    {
        return $this->hasMany(QuoteItem::class, 'quote_id', 'quote_id');
    }

    public function technician()
    {
        return $this->belongsTo(VipUser::class, 'technician_id');
    }

    public function calendarSlot()
    {
        if (!$this->scheduled_date || !$this->scheduled_slot) return null;
        return CalendarSlot::where('slot_date', $this->scheduled_date)
            ->where('slot_time', $this->scheduled_slot)->first();
    }
}
