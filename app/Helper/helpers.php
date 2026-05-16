<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('getSettingsValByName')) {
    /**
     * Get a setting value by its key name.
     */
    function getSettingsValByName($key)
    {
        try {
            $value = DB::table('settings')->where('key', $key)->value('value');
            return $value ?? '';
        } catch (\Throwable $e) {
            return '';
        }
    }
}

if (!function_exists('getMarkup')) {
    /**
     * Apply markup percentage from the series markup table.
     */
    function getMarkup($seriesId, $price)
    {
        $markup = DB::table('elitevw_master_markup')
            ->where('series_id', $seriesId)
            ->value('percentage');

        if ($markup) {
            $price = round((float)$price * (1 + (float)$markup / 100), 2);
        }

        return $price;
    }
}

if (!function_exists('toFloat')) {
    /**
     * Convert a value to float safely.
     */
    function toFloat($value)
    {
        if (is_null($value) || $value === '') {
            return 0.0;
        }

        // Remove commas and currency symbols
        $value = preg_replace('/[^0-9.\-]/', '', (string)$value);

        return (float)$value;
    }
}

if (!function_exists('fractionToDecimal')) {
    /**
     * Convert a fraction string (e.g. "1/8", "3 1/2") to decimal.
     */
    function fractionToDecimal($fraction)
    {
        $fraction = trim((string)$fraction);

        if (is_numeric($fraction)) {
            return (float)$fraction;
        }

        // Handle mixed numbers like "3 1/2"
        if (preg_match('/^(\d+)\s+(\d+)\/(\d+)$/', $fraction, $m)) {
            return (float)$m[1] + ((float)$m[2] / (float)$m[3]);
        }

        // Handle simple fractions like "1/8"
        if (preg_match('/^(\d+)\/(\d+)$/', $fraction, $m)) {
            return (float)$m[1] / (float)$m[2];
        }

        return 0.0;
    }
}

if (!function_exists('decimalToFraction')) {
    /**
     * Convert a decimal to the nearest common fraction string.
     */
    function decimalToFraction($decimal, $precision = 16)
    {
        $decimal = (float)$decimal;
        $whole = floor($decimal);
        $frac = $decimal - $whole;

        if ($frac < 0.001) {
            return (string)(int)$whole;
        }

        $bestNum = 0;
        $bestDen = 1;
        $bestDiff = $frac;

        for ($den = 2; $den <= $precision; $den++) {
            $num = round($frac * $den);
            $diff = abs($frac - ($num / $den));
            if ($diff < $bestDiff) {
                $bestDiff = $diff;
                $bestNum = (int)$num;
                $bestDen = $den;
            }
        }

        if ($bestNum == 0) {
            return (string)(int)$whole;
        }

        if ($whole > 0) {
            return "{$whole} {$bestNum}/{$bestDen}";
        }

        return "{$bestNum}/{$bestDen}";
    }
}
