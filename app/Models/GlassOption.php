<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlassOption extends Model
{
    protected $table = 'elitevw_master_glass_options';

    protected $fillable = [
        'parent_id',
        'glass_type',
        'position',
    ];
}
