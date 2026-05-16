<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileDeductionValue extends Model
{
    protected $table = 'elitevw_profile_deduction_values';

    protected $fillable = [
        'profile_catalog_id',
        'deduction_id',
        'deduction_name',
        'deduction_value',
    ];

    protected $casts = [
        'deduction_value' => 'float',
        'deduction_id' => 'integer',
    ];

    public function profileCatalog()
    {
        return $this->belongsTo(ProfileCatalog::class, 'profile_catalog_id');
    }
}
