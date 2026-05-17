<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobItem extends Model
{
    protected $table = 'vip_job_items';

    protected $fillable = [
        'job_id',
        'description',
        'item_type',
        'qty',
        'completed',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'qty'       => 'decimal:2',
        'completed' => 'boolean',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
