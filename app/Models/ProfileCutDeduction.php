<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Per-profile, per-position cut deductions derived from Cantor PROFTAB.
 */
class ProfileCutDeduction extends Model
{
    protected $table = 'elitevw_profile_cut_deductions';

    protected $fillable = [
        'profile_code',
        'profile_type',
        'position_code',
        'abzug1',
        'abzug2',
        'abzug3',
        'sample_count',
    ];

    protected $casts = [
        'position_code' => 'integer',
        'abzug1' => 'float',
        'abzug2' => 'float',
        'abzug3' => 'float',
        'sample_count' => 'integer',
    ];

    /**
     * Total deduction for this profile/position.
     */
    public function totalDeduction(): float
    {
        return $this->abzug1 + $this->abzug2 + $this->abzug3;
    }

    /**
     * Get cut length given an input dimension.
     */
    public function cutLength(float $inputDimension): float
    {
        return round($inputDimension - $this->abzug1 - $this->abzug2 - $this->abzug3, 4);
    }

    /**
     * Position name for display.
     */
    public function positionName(): string
    {
        return match ($this->position_code) {
            20  => 'Bottom',
            24  => 'Top',
            33  => 'Left',
            34  => 'Right',
            16  => 'Glass Bead H',
            32  => 'Glass Bead V',
            64  => 'Mullion H',
            128 => 'Mullion V',
            default => 'Position ' . $this->position_code,
        };
    }

    /**
     * Profile type name for display.
     */
    public function profileTypeName(): string
    {
        return match ($this->profile_type) {
            'RA' => 'Frame',
            'FL' => 'Sash',
            'K'  => 'Glass Bead',
            'GT' => 'Glazing',
            'SP' => 'Special',
            'SW' => 'Swing',
            'VB' => 'Vertical Bar',
            default => $this->profile_type,
        };
    }

    public function profileCatalog()
    {
        return $this->belongsTo(ProfileCatalog::class, 'profile_code', 'profile_code');
    }
}
