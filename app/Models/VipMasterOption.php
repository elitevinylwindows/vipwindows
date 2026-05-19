<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VipMasterOption extends Model
{
    protected $table = 'vip_master_options';

    protected $fillable = [
        'category',
        'name',
        'code',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope to a specific category.
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to active items only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get active options for a category (for dropdowns).
     */
    public static function optionsFor(string $category)
    {
        return static::where('category', $category)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
