<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileComponent extends Model
{
    protected $table = 'elitevw_profile_components';

    protected $fillable = [
        'profile_set_id', 'profile_code', 'type', 'orientation', 'description',
        'quantity', 'dimension_source', 'deduction_value', 'addition_value',
        'formula', 'fabrication_mark', 'cut_type',
        'offset_x', 'offset_y', 'mirror', 'rotation_angle',
        'field_number', 'sort_order', 'station',
    ];

    protected $casts = [
        'mirror' => 'boolean',
        'deduction_value' => 'float',
        'addition_value' => 'float',
        'quantity' => 'integer',
    ];

    public function profileSet()
    {
        return $this->belongsTo(ProfileSet::class, 'profile_set_id');
    }

    /**
     * Calculate the cut length for this component given window dimensions.
     */
    public function calculateLength(float $width, float $height): float
    {
        // If a formula is defined, evaluate it
        if ($this->formula) {
            return $this->evaluateFormula($this->formula, $width, $height);
        }

        // Get base dimension from source
        $base = match ($this->dimension_source) {
            'width'  => $width,
            'height' => $height,
            default  => 0.0,
        };

        // Apply deduction or addition
        $length = $base - (float)($this->deduction_value ?? 0) + (float)($this->addition_value ?? 0);

        return round(max(0, $length), 4);
    }

    /**
     * Evaluate a formula string with W and H placeholders.
     */
    protected function evaluateFormula(string $formula, float $width, float $height): float
    {
        // Replace {W}/{H} placeholders first (curly-brace style), then bare W/H
        $expr = str_ireplace(['{W}', '{H}'], [$width, $height], $formula);
        $expr = str_ireplace(['W', 'H'], [$width, $height], $expr);

        // Strip any remaining curly braces
        $expr = str_replace(['{', '}'], '', $expr);

        // Only allow safe characters: digits, decimal points, +, -, *, /, (, ), spaces
        if (!preg_match('/^[\d\.\+\-\*\/\(\)\s]+$/', $expr)) {
            return 0.0;
        }

        try {
            $result = eval("return (float)($expr);");
            return round(max(0, (float)$result), 4);
        } catch (\Throwable $e) {
            return 0.0;
        }
    }
}
