<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobItem extends Model
{
    protected $table = 'vip_job_items';

    protected $fillable = [
        'job_id',
        'service_id',
        'description',
        'item_type',
        'qty',
        'unit_pay',
        'total_pay',
        'completed',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'qty'       => 'decimal:2',
        'unit_pay'  => 'float',
        'total_pay' => 'float',
        'completed' => 'boolean',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function service()
    {
        return $this->belongsTo(\App\Models\Service::class);
    }
}
