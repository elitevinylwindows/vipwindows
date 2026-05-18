<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechMeasurePhoto extends Model
{
    protected $table = 'tech_measure_photos';

    protected $fillable = [
        'tech_measure_id',
        'tech_measure_item_id',
        'file_path',
        'caption',
        'uploaded_by',
    ];

    public function techMeasure()
    {
        return $this->belongsTo(TechMeasure::class);
    }

    public function item()
    {
        return $this->belongsTo(TechMeasureItem::class, 'tech_measure_item_id');
    }

    public function uploader()
    {
        return $this->belongsTo(VipUser::class, 'uploaded_by');
    }
}
