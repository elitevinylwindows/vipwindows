<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    protected $fillable = [
        'customer_name', 'customer_email', 'customer_phone',
        'scheduled_at', 'duration', 'platform', 'meeting_link',
        'notes', 'address', 'status', 'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(VipUser::class, 'created_by');
    }
}
