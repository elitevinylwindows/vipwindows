<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExteriorColor extends Model
{
    protected $table = 'elitevw_master_colors_exterior_colors';

    protected $fillable = ['name', 'code', 'hex_color'];
}
