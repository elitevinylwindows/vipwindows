<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColorConfiguration extends Model
{
    protected $table = 'elitevw_master_colors_color_configurations';

    protected $fillable = [
        'name',
        'code',
        'exterior_color_id',
        'interior_color_id',
        'exterior_source',
        'interior_source',
    ];

    /** Resolve the exterior side to its referenced color record (exterior or laminate). */
    public function exteriorSide()
    {
        if (!$this->exterior_color_id) return null;
        return $this->exterior_source === 'laminate'
            ? LaminateColor::find($this->exterior_color_id)
            : ExteriorColor::find($this->exterior_color_id);
    }

    /** Resolve the interior side to its referenced color record. */
    public function interiorSide()
    {
        if (!$this->interior_color_id) return null;
        return $this->interior_source === 'laminate'
            ? LaminateColor::find($this->interior_color_id)
            : InteriorColor::find($this->interior_color_id);
    }
}
