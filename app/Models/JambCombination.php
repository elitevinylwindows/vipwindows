<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JambCombination extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'series',
        'material',
        'color',
        'fin',
        'start_depth',
        'end_depth',
        'jamb1',
        'jamb2',
        'jamb3',
        'cut_code',
        'tolerance',
        'tolerance_start',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'start_depth' => 'decimal:3',
        'end_depth' => 'decimal:3',
        'tolerance' => 'decimal:3',
        'tolerance_start' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    /**
     * Get jamb combination for specific frame depth
     */
    public static function getJambForDepth($seriesCodes, $frameDepth, $material = null, $color = null, $fin = null)
    {
        $seriesCodes = is_array($seriesCodes) ? $seriesCodes : [$seriesCodes];

        $query = self::where('is_active', true)
            ->where('start_depth', '<=', $frameDepth)
            ->where('end_depth', '>=', $frameDepth);

        // Wildcard series matching
        $query->where(function ($q) use ($seriesCodes) {
            foreach ($seriesCodes as $code) {
                $q->orWhere(function ($subQ) use ($code) {
                    $subQ->whereRaw("CONCAT(',', REPLACE(series, ' ', ''), ',') LIKE ?", ["%,{$code},%"]);
                });
            }
        });

        if ($material) {
            $query->where(function ($q) use ($material) {
                $q->where('material', $material)
                  ->orWhereNull('material');
            });
        }

        if ($color) {
            $query->where(function ($q) use ($color) {
                $q->where('color', $color)
                  ->orWhereNull('color');
            });
        }

        if ($fin) {
            $query->where(function ($q) use ($fin) {
                $q->where('fin', $fin)
                  ->orWhereNull('fin');
            });
        }

        // Order by most specific match first
        $query->orderByRaw('
            (CASE WHEN material IS NOT NULL THEN 1 ELSE 0 END +
             CASE WHEN color IS NOT NULL THEN 1 ELSE 0 END +
             CASE WHEN fin IS NOT NULL THEN 1 ELSE 0 END) DESC
        ');

        return $query->first();
    }

    /**
     * Get min/max depth range for series
     */
    public static function getDepthRange($seriesCodes, $material = null, $color = null, $fin = null)
    {
        $seriesCodes = is_array($seriesCodes) ? $seriesCodes : [$seriesCodes];

        $query = self::where('is_active', true);

        $query->where(function ($q) use ($seriesCodes) {
            foreach ($seriesCodes as $code) {
                $q->orWhere(function ($subQ) use ($code) {
                    $subQ->whereRaw("CONCAT(',', REPLACE(series, ' ', ''), ',') LIKE ?", ["%,{$code},%"]);
                });
            }
        });

        if ($material) {
            $query->where('material', $material);
        }

        if ($color) {
            $query->where('color', $color);
        }

        if ($fin) {
            $query->where('fin', $fin);
        }

        $result = $query->selectRaw('MIN(start_depth) as min_depth, MAX(end_depth) as max_depth')
            ->first();

        return [
            'min' => $result->min_depth ?? 0,
            'max' => $result->max_depth ?? 0,
        ];
    }

    /**
     * Get all jamb profiles for this combination
     */
    public function getJambProfiles(): array
    {
        $profiles = [];

        if ($this->jamb1 && $this->jamb1 !== '-') {
            $profiles[] = $this->jamb1;
        }

        if ($this->jamb2 && $this->jamb2 !== '-') {
            $profiles[] = $this->jamb2;
        }

        if ($this->jamb3 && $this->jamb3 !== '-') {
            $profiles[] = $this->jamb3;
        }

        return $profiles;
    }

    /**
     * Check if frame depth is within this combination's range
     */
    public function isWithinDepthRange($depth): bool
    {
        return $depth >= $this->start_depth && $depth <= $this->end_depth;
    }
}
