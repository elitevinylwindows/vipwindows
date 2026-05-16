<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'vip_services';

    protected $fillable = [
        'name', 'code', 'description', 'base_price', 'cost_price',
        'unit', 'min_price', 'max_price', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'base_price' => 'float',
        'cost_price' => 'float',
        'min_price'  => 'float',
        'max_price'  => 'float',
        'is_active'  => 'boolean',
    ];

    public function installers()
    {
        return $this->belongsToMany(VipUser::class, 'vip_installer_services', 'service_id', 'installer_id')
            ->withPivot('custom_price')
            ->withTimestamps();
    }
}
