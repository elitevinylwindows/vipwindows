<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeriesTypeProfileSet extends Model
{
    protected $table = 'elitevw_series_type_profile_sets';

    protected $fillable = [
        'series_type',
        'profile_set_id',
        'panel_count',
        'field_width_formula',
        'fix_glass_h_deduction',
        'fix_glass_v_deduction',
        'sash_glass_h_deduction',
        'sash_glass_v_deduction',
        'screen_h_deduction',
        'screen_v_deduction',
    ];

    protected $casts = [
        'panel_count' => 'integer',
        'fix_glass_h_deduction' => 'float',
        'fix_glass_v_deduction' => 'float',
        'sash_glass_h_deduction' => 'float',
        'sash_glass_v_deduction' => 'float',
        'screen_h_deduction' => 'float',
        'screen_v_deduction' => 'float',
    ];

    public function profileSet()
    {
        return $this->belongsTo(ProfileSet::class, 'profile_set_id');
    }

    /**
     * Calculate field widths for fix and sash panels based on window type.
     *
     * @return array ['fix' => float, 'sash' => float]
     */
    public function getFieldWidths(float $totalWidth): array
    {
        $formula = $this->field_width_formula ?? 'W';

        // Named formulas for special cases
        $named = match ($formula) {
            'XOX'  => ['fix' => $totalWidth / 2, 'sash' => $totalWidth / 4],
            'XOXO' => ['fix' => $totalWidth / 4, 'sash' => $totalWidth / 4],
            default => null,
        };
        if ($named) return $named;

        // W/N pattern (W, W/2, W/3, W/4, W/5, W/6, etc.)
        if (preg_match('/^W(?:\/(\d+))?$/', $formula, $m)) {
            $divisor = isset($m[1]) ? (int) $m[1] : 1;
            $fw = $divisor > 0 ? $totalWidth / $divisor : $totalWidth;
            return ['fix' => $fw, 'sash' => $fw];
        }

        return ['fix' => $totalWidth, 'sash' => $totalWidth];
    }

    /**
     * Does this window type have operable sash panels?
     */
    public function hasSash(): bool
    {
        return $this->sash_glass_h_deduction > 0 || $this->sash_glass_v_deduction > 0;
    }
}
