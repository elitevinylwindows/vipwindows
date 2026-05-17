<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallerService extends Model
{
    protected $table = 'installer_services';

    protected $fillable = [
        'installer_id', 'name', 'description', 'price',
        'price_type', 'estimated_duration', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function installer()
    {
        return $this->belongsTo(VipUser::class, 'installer_id');
    }

    public function priceLabel(): string
    {
        $labels = [
            'flat'     => '',
            'per_unit' => '/unit',
            'per_hour' => '/hr',
            'per_sqft' => '/sq ft',
        ];
        return '$' . number_format($this->price, 2) . ($labels[$this->price_type] ?? '');
    }
}
