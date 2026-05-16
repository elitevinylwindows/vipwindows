<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    protected $table = 'elitevw_sales_quote_items';

    protected $fillable = [
        'quote_id', 'description', 'width', 'height', 'glass', 'grid',
        'qty', 'price', 'cost_price', 'total', 'discount',
        'retrofit_bottom_only', 'no_logo_lock', 'double_lock',
        'custom_lock_position', 'custom_vent_latch', 'knocked_down',
        'internal_note', 'item_comment', 'checked_count',
        'color_config', 'color_exterior', 'color_exterior_custom',
        'color_interior', 'color_interior_custom',
        'frame_type', 'fin_type', 'glass_type', 'spacer',
        'tempered', 'specialty_glass', 'tempered_fields',
        'grid_pattern', 'grid_profile', 'grid_detail',
        'series_id', 'series_type',
        'is_modification', 'modification_date',
        'addon',
        'shape_definition_id', 'shape_params', 'shape_code',
        'panel_dimensions',
    ];

    protected $casts = [
        'shape_params' => 'array',
        'panel_dimensions' => 'array',
    ];

    public function getPriceAttribute($value)
    {
        $addons = json_decode($this->addon, true);
        if (!$addons) return (float) $value;
        return (float) (array_sum(array_values($addons)) + $value);
    }

    public function getTotalAttribute($value)
    {
        $addons = json_decode($this->addon, true);
        if (!$addons) return (float) $value;
        return (float) (array_sum(array_values($addons)) + $value);
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class, 'quote_id');
    }
}
