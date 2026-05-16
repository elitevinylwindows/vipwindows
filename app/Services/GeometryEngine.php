<?php

namespace App\Services;

use App\Models\ProfileSet;

class GeometryEngine
{
    public function generate(ProfileSet $profileSet, float $width, float $height): array
    {
        $cuts = [];

        // FRAME (2 width + 2 height)
        $cuts[] = [
            'profile_code' => 'FEI-8002',
            'length' => round($width, 4),
            'quantity' => 2
        ];

        $cuts[] = [
            'profile_code' => 'FEI-8002',
            'length' => round($height, 4),
            'quantity' => 2
        ];

        // MULLION
        $mullionLength = round($width - (2 * $profileSet->frame_pocket), 4);

        $cuts[] = [
            'profile_code' => 'FEI-8006',
            'length' => $mullionLength,
            'quantity' => 1
        ];

        // SASH
        $openingHeight = $height / 2;

        $sashHeight = round($openingHeight - $profileSet->sash_vertical_deduction, 4);
        $sashWidth  = round($width - (2 * $profileSet->interlock_overlap), 4);

        $cuts[] = [
            'profile_code' => 'FEI-8005',
            'length' => $sashWidth,
            'quantity' => 2
        ];

        $cuts[] = [
            'profile_code' => 'FEI-8005',
            'length' => $sashHeight,
            'quantity' => 2
        ];

        return $cuts;
    }
}
