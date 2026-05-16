<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileCatalog extends Model
{
    protected $table = 'elitevw_profile_catalog';

    protected $fillable = [
        'profile_code',
        'description',
        'profile_type',
        'manufacturer',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function deductionValues()
    {
        return $this->hasMany(ProfileDeductionValue::class, 'profile_catalog_id');
    }

    public function cutDeductions()
    {
        return $this->hasMany(ProfileCutDeduction::class, 'profile_code', 'profile_code');
    }
}
