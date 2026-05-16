<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InteriorColor extends Model
{
    protected $table = 'elitevw_master_colors_interior_colors';

    protected $fillable = ['name', 'code', 'hex_color'];
}
