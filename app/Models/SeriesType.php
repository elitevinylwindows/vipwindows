<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeriesType extends Model
{
    protected $table = 'elitevw_master_series_types';

    protected $fillable = [
        'series_id', 'series_type', 'category', 'image', 'product_category',
    ];

    public function series()
    {
        return $this->belongsTo(Series::class, 'series_id');
    }
}
