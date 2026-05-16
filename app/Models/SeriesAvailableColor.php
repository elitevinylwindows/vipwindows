<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeriesAvailableColor extends Model
{
    protected $table = 'elitevw_master_series_available_colors';

    protected $fillable = ['series_id', 'color_code', 'color_name'];

    public function series()
    {
        return $this->belongsTo(Series::class, 'series_id');
    }
}
