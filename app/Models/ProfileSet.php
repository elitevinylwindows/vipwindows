<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileSet extends Model
{
    protected $table = 'elitevw_profile_sets';

    protected $fillable = [
        'code', 'name', 'frame_pocket', 'interlock_overlap', 'sash_vertical_deduction',
        'frame_horizontal_deduction', 'frame_vertical_deduction', 'sash_horizontal_deduction',
        'interlock_deduction', 'meeting_rail_deduction', 'miter_angle',
        'frame_cut_type', 'sash_cut_type', 'manufacturer_system', 'product_type',
        'is_active', 'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function components()
    {
        return $this->hasMany(ProfileComponent::class, 'profile_set_id')->orderBy('sort_order');
    }

    public function seriesTypes()
    {
        return $this->hasMany(SeriesTypeProfileSet::class, 'profile_set_id');
    }

    public function deductionManipulations()
    {
        return $this->hasMany(DeductionManipulation::class, 'profile_set_id')->orderBy('seq');
    }
}
