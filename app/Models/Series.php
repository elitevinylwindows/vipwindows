<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    protected $table = 'elitevw_master_series';
    protected $fillable = ['series'];

    public function configurations()
    {
        return $this->hasMany(SeriesType::class, 'series_id');
    }

    public function seriesTypes()
    {
        return $this->hasMany(SeriesType::class, 'series_id');
    }

    public function availableColors()
    {
        return $this->hasMany(SeriesAvailableColor::class, 'series_id');
    }
}
