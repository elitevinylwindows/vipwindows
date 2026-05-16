<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobNote extends Model
{
    protected $table = 'vip_job_notes';

    protected $fillable = [
        'job_id', 'note', 'added_by',
    ];

    public function author()
    {
        return $this->belongsTo(VipUser::class, 'added_by');
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
