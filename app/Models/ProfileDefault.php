<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfileDefault extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'series',
        'type',
        'ident',
        'color',
        'fin',
        'material',
        'from_angle',
        'to_angle',
        'ident_pricing',
        'ident_bom_size_entry',
        'inch_suffix',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'from_angle' => 'decimal:2',
        'to_angle' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get profiles for specific criteria with Cantor-style wildcard matching
     */
    public static function getProfiles($seriesCodes, $type = null, $color = null, $fin = null, $material = null)
    {
        $seriesCodes = is_array($seriesCodes) ? $seriesCodes : [$seriesCodes];

        $query = self::where('is_active', true);

        // Wildcard series matching
        $query->where(function ($q) use ($seriesCodes) {
            foreach ($seriesCodes as $code) {
                $q->orWhere(function ($subQ) use ($code) {
                    $subQ->whereRaw("CONCAT(',', REPLACE(series, ' ', ''), ',') LIKE ?", ["%,{$code},%"]);
                });
            }
        });

        if ($type) {
            $query->where('type', $type);
        }

        if ($color) {
            $query->where(function ($q) use ($color) {
                $q->where('color', $color)
                  ->orWhereNull('color')
                  ->orWhere('color', '*');
            });
        }

        if ($fin) {
            $query->where(function ($q) use ($fin) {
                $q->where('fin', $fin)
                  ->orWhereNull('fin')
                  ->orWhere('fin', '*');
            });
        }

        if ($material) {
            $query->where(function ($q) use ($material) {
                $q->where('material', $material)
                  ->orWhereNull('material');
            });
        }

        return $query->get();
    }

    /**
     * Get default profile for a specific type
     */
    public static function getDefaultProfile($seriesCodes, $type, $color = null, $fin = null, $material = null)
    {
        return self::getProfiles($seriesCodes, $type, $color, $fin, $material)->first();
    }

    /**
     * Get all profile types available
     */
    public static function getProfileTypes(): array
    {
        return [
            'HEADJAMB' => 'Head Jamb',
            'SIDEJAMB' => 'Side Jamb',
            'SILL' => 'Sill',
            'MULLION' => 'Mullion',
            'INTERLOCK' => 'Interlock',
            'CHECK_RAIL' => 'Check Rail',
            'MEETING_RAIL' => 'Meeting Rail',
            'BOTTOM_RAIL' => 'Bottom Rail',
            'TOP_RAIL' => 'Top Rail',
            'STILE' => 'Stile',
            'GLAZING_BEAD' => 'Glazing Bead',
            'SCREEN_FRAME' => 'Screen Frame',
            'BRICKMOULD' => 'Brick Mould',
            'BMSNAPON' => 'BM Snap-On',
            'BAY' => 'Bay/Bow',
            'DRYWALLRET' => 'Drywall Return',
            'HCLIP' => 'H-Clip',
            'DLFRAME' => 'DL Frame',
            'SLOPESILL' => 'Slope Sill',
        ];
    }

    /**
     * Get available colors
     */
    public static function getColors(): array
    {
        return [
            'WH' => 'White',
            'BE' => 'Beige/Almond',
            'BR' => 'Bronze',
            'BL' => 'Black',
            'PG' => 'Pine Green',
            'SG' => 'Sierra Green',
            '*' => 'Any Color',
        ];
    }

    /**
     * Get available fin types
     */
    public static function getFinTypes(): array
    {
        return [
            'NF' => 'Nail Fin',
            'J' => 'J-Channel',
            'N' => 'No Fin',
            '*' => 'Any Fin',
        ];
    }
}
