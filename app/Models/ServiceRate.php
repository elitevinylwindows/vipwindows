<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRate extends Model
{
    protected $table = 'vip_service_rates';

    protected $fillable = ['category', 'name', 'description', 'cost_rate', 'charge_rate', 'unit', 'is_active', 'sort_order'];
}
