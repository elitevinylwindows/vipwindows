<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Deduction Manipulation -- per-product-type size deltas applied
 * on top of the base ProfileComponent formulas.
 */
class DeductionManipulation extends Model
{
    protected $table = 'elitevw_deduction_manipulations';

    protected $fillable = [
        'profile_set_id',
        'profile_set_code',
        'seq',
        'field_number',
        'field_label',
        'component_type',
        'component_type_label',
        'position',
        'h_multiplier',
        'v_multiplier',
        'frame_type',
        'article_code',
        'mullion_orientation',
        'diff_size_1',
        'diff_size_2',
        'diff_size',
        'gaps',
        'product_type_code',
        'additional_condition',
        'product_variable',
        'is_active',
    ];

    protected $casts = [
        'diff_size_1' => 'float',
        'diff_size_2' => 'float',
        'diff_size'   => 'float',
        'gaps'        => 'float',
        'is_active'     => 'boolean',
        'seq'           => 'integer',
        'field_number'  => 'integer',
        'h_multiplier'  => 'integer',
        'v_multiplier'  => 'integer',
    ];

    public function profileSet()
    {
        return $this->belongsTo(ProfileSet::class, 'profile_set_id');
    }

    /**
     * Get all active manipulations for a given profile set.
     */
    public static function forSetAndProductType(int $profileSetId, ?string $productTypeCode): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('profile_set_id', $profileSetId)
            ->where('is_active', true)
            ->orderBy('seq')
            ->get();
    }

    /**
     * Check if this manipulation matches the current order context.
     */
    public function matchesContext(array $context = []): bool
    {
        // Product type filter
        if ($this->product_type_code) {
            $actual = $context['product_type_code'] ?? null;
            if ($actual && strcasecmp($this->product_type_code, $actual) !== 0) {
                return false;
            }
        }

        // Frame type filter
        if ($this->frame_type) {
            $actual = $context['frame_type'] ?? 'Retrofit';
            $manipFT  = strtolower(trim($this->frame_type));
            $actualFT = strtolower(trim($actual));
            if (strpos($manipFT, $actualFT) !== 0 && strpos($actualFT, $manipFT) !== 0) {
                return false;
            }
        }

        // Article filter
        if ($this->article_code) {
            $actual = $context['article'] ?? '';
            if (strcasecmp($this->article_code, $actual) !== 0) {
                return false;
            }
        }

        // Mullion orientation
        if ($this->mullion_orientation) {
            $actual = $context['mullion_orientation'] ?? null;
            if ($actual && strcasecmp($this->mullion_orientation, $actual) !== 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Apply this manipulation's deltas to a base cut length.
     */
    public function applyTo(float $baseLength, string $orientation): float
    {
        if ($this->position && $this->position !== $orientation) {
            return $baseLength;
        }

        return $baseLength + $this->diff_size_1 + $this->diff_size_2 + $this->diff_size;
    }
}
