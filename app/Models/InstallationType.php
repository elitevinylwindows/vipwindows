<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallationType extends Model
{
    protected $table = 'installation_types';

    protected $fillable = [
        'name', 'description', 'price', 'installer_pay', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'price'         => 'float',
        'installer_pay' => 'float',
        'is_active'     => 'boolean',
    ];

    /**
     * Profit margin per unit.
     */
    public function profit(): float
    {
        return $this->price - $this->installer_pay;
    }

    /**
     * Profit margin percentage.
     */
    public function marginPercent(): float
    {
        if ($this->price <= 0) return 0;
        return round((($this->price - $this->installer_pay) / $this->price) * 100, 1);
    }
}
