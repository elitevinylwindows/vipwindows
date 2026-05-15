<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceArea extends Model
{
    protected $fillable = ['name', 'description', 'state', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
