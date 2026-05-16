<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeriesConfiguration extends Model
{
    protected $table = 'elitevw_master_series_configurations';

    protected $fillable = ['series_type', 'category', 'image', 'product_type_id', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function productTypes()
    {
        return $this->belongsToMany(
            ProductType::class,
            'elitevw_master_series_configuration_product_type',
            'series_configuration_id',
            'product_type_id'
        )->withTimestamps();
    }
}
