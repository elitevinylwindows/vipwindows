<?php

namespace App\Services;

use App\Models\ProfileDefault;
use App\Models\JambCombination;
use App\Models\SeriesTypeProfileSet;
use App\Models\ProfileComponent;
use App\Models\ProfileCatalog;
use App\Models\ProfileDeductionValue;
use App\Models\ProfileCutDeduction;
use Illuminate\Support\Facades\DB;

class WindowConfigurator
{
    /**
     * Configure a complete window and return all calculated data
     *
     * @param array $specs Window specifications
     * @return array Complete configuration with profiles, BOM, calculations
     */
    public function configure(array $specs): array
    {
        // 1. Validate inputs
        $this->validateSpecs($specs);

        // 2. Calculate frame depth based on glass configuration
        $frameDepth = $this->calculateFrameDepth($specs);

        // 3. Get all profiles for this configuration
        $profiles = $this->getProfiles($specs, $frameDepth);

        // 4. Generate Bill of Materials
        $bom = $this->generateBOM($profiles, $specs);

        // 5. Calculate measurements
        $measurements = $this->calculateMeasurements($specs, $profiles);

        // 6. Return complete configuration
        return [
            'success' => true,
            'series' => $specs['series'],
            'series_name' => $this->getSeriesName($specs['series']),
            'width' => $specs['width'],
            'height' => $specs['height'],
            'color' => $specs['color'] ?? 'WH',
            'fin' => $specs['fin'] ?? 'NF',
            'glass_type' => $specs['glass_type'] ?? 'DUAL_PANE',
            'frame_depth' => $frameDepth,
            'profiles' => $profiles,
            'bom' => $bom,
            'measurements' => $measurements,
            'validation' => $this->validateConfiguration($specs, $profiles, $frameDepth),
        ];
    }

    /**
     * Calculate frame depth based on glass configuration
     */
    protected function calculateFrameDepth(array $specs): float
    {
        $glassType = $specs['glass_type'] ?? 'DUAL_PANE';
        $glassThickness = $specs['glass_thickness'] ?? 0.125; // 1/8" per pane
        $spacerWidth = $specs['spacer_width'] ?? 0.5; // 1/2" spacer
        $clearance = $specs['clearance'] ?? 0.25; // 1/4" clearance each side

        switch ($glassType) {
            case 'SINGLE_PANE':
                return $glassThickness + ($clearance * 2);

            case 'DUAL_PANE':
                return ($glassThickness * 2) + $spacerWidth + ($clearance * 2);

            case 'TRIPLE_PANE':
                return ($glassThickness * 3) + ($spacerWidth * 2) + ($clearance * 2);

            case 'LAMINATED':
                return ($glassThickness * 2) + 0.030 + ($clearance * 2); // PVB layer

            default:
                // Default dual pane
                return 1.0; // 1 inch default
        }
    }

    /**
     * Get all profiles for the configuration
     */
    protected function getProfiles(array $specs, float $frameDepth): array
    {
        $series = $specs['series'];
        $color = $specs['color'] ?? 'WH';
        $fin = $specs['fin'] ?? 'NF';
        $material = $specs['material'] ?? 'PVC';

        $profiles = [];

        // Get standard profiles
        $profileTypes = ['HEADJAMB', 'SIDEJAMB', 'SILL', 'MULLION', 'INTERLOCK'];

        foreach ($profileTypes as $type) {
            $profile = ProfileDefault::getDefaultProfile($series, $type, $color, $fin, $material);

            if ($profile) {
                $profiles[$type] = [
                    'type' => $type,
                    'ident' => $profile->ident,
                    'article' => $profile->ident,
                    'color' => $profile->color ?? $color,
                    'fin' => $profile->fin ?? $fin,
                    'pricing_ident' => $profile->ident_pricing,
                    'bom_size_entry' => $profile->ident_bom_size_entry,
                ];
            }
        }

        // Get jamb combination based on depth
        $jambCombo = JambCombination::getJambForDepth($series, $frameDepth, $material, $color, $fin);

        if ($jambCombo) {
            $profiles['JAMB_ASSEMBLY'] = [
                'type' => 'JAMB_ASSEMBLY',
                'depth' => $frameDepth,
                'depth_range' => [
                    'min' => $jambCombo->start_depth,
                    'max' => $jambCombo->end_depth,
                ],
                'jamb1' => $jambCombo->jamb1,
                'jamb2' => $jambCombo->jamb2,
                'jamb3' => $jambCombo->jamb3,
                'cut_code' => $jambCombo->cut_code,
                'profiles' => $jambCombo->getJambProfiles(),
            ];
        }

        return $profiles;
    }

    /**
     * Generate Bill of Materials
     */
    protected function generateBOM(array $profiles, array $specs): array
    {
        $width = $specs['width'];
        $height = $specs['height'];
        $qty = $specs['quantity'] ?? 1;

        $bom = [];

        // Head Jamb
        if (isset($profiles['HEADJAMB'])) {
            $bom[] = [
                'type' => 'HEADJAMB',
                'article' => $profiles['HEADJAMB']['ident'],
                'description' => 'Head Jamb',
                'quantity' => 1 * $qty,
                'length' => $width,
                'unit' => 'INCHES',
                'total_length' => $width * $qty,
            ];
        }

        // Side Jambs (2 pieces)
        if (isset($profiles['SIDEJAMB'])) {
            $bom[] = [
                'type' => 'SIDEJAMB',
                'article' => $profiles['SIDEJAMB']['ident'],
                'description' => 'Side Jamb',
                'quantity' => 2 * $qty,
                'length' => $height,
                'unit' => 'INCHES',
                'total_length' => ($height * 2) * $qty,
            ];
        }

        // Sill
        if (isset($profiles['SILL'])) {
            $bom[] = [
                'type' => 'SILL',
                'article' => $profiles['SILL']['ident'],
                'description' => 'Sill',
                'quantity' => 1 * $qty,
                'length' => $width,
                'unit' => 'INCHES',
                'total_length' => $width * $qty,
            ];
        }

        // Jamb Assembly
        if (isset($profiles['JAMB_ASSEMBLY'])) {
            $perimeter = (($width + $height) * 2);

            foreach ($profiles['JAMB_ASSEMBLY']['profiles'] as $jambProfile) {
                $bom[] = [
                    'type' => 'JAMB_EXTRUSION',
                    'article' => $jambProfile,
                    'description' => 'Jamb Extrusion',
                    'quantity' => 1 * $qty,
                    'length' => $perimeter,
                    'unit' => 'INCHES',
                    'total_length' => $perimeter * $qty,
                ];
            }
        }

        return $bom;
    }

    /**
     * Calculate additional measurements.
     *
     * Glass / sash / screen sizes are pulled from the Profile Management system:
     *   1. Find the ProfileSet linked to the series_type via SeriesTypeProfileSet
     *   2. Each ProfileSet has ProfileComponents -- some are typed as glass, sash, screen
     *   3. Each component has a dimension_source (width or height), a deduction_value,
     *      an addition_value, and optionally a formula
     *   4. calculateLength(W, H) evaluates: base - deduction + addition (or formula)
     */
    protected function calculateMeasurements(array $specs, array $profiles): array
    {
        $width  = (float) $specs['width'];
        $height = (float) $specs['height'];
        $seriesType = $specs['series_type'] ?? '';

        $glassFix  = ['w' => 0, 'h' => 0];
        $glassSash = ['w' => 0, 'h' => 0];
        $screen    = ['w' => 0, 'h' => 0];
        $deductions = [];
        $foundProfileSet = false;
        $seriesId = $specs['series'] ?? null;

        // Extract window type code from series_type (e.g. "DYNAMIC-XO" -> "XO", "IM-SH" -> "SH")
        $windowType = $this->extractWindowTypeCode($seriesType);

        // Resolve Series Type Mapping -> ProfileSet
        $mapping = null;
        if ($seriesType) {
            $mapping = SeriesTypeProfileSet::where('series_type', strtoupper($seriesType))
                ->with('profileSet.components')
                ->first();
        }
        if (!$mapping && $windowType && $windowType !== strtoupper($seriesType)) {
            $mapping = SeriesTypeProfileSet::where('series_type', $windowType)
                ->with('profileSet.components')
                ->first();
        }

        // Use mapping-level panel count & field widths (per-window-type aware)
        $panelCount = ($mapping && $mapping->panel_count > 0)
            ? $mapping->panel_count
            : $this->getPanelCount($windowType);
        $hasSash = $this->windowTypeHasSash($windowType);

        // Field widths: use mapping's formula if available, else simple division
        if ($mapping && $mapping->field_width_formula) {
            $fields = $mapping->getFieldWidths($width);
            $fixFieldWidth  = $fields['fix'];
            $sashFieldWidth = $fields['sash'];
        } else {
            $fixFieldWidth  = ($panelCount > 1) ? ($width / $panelCount) : $width;
            $sashFieldWidth = $fixFieldWidth;
        }
        $fieldWidth = $fixFieldWidth; // backward compat for fallback priorities

        $usedSource = 'none';

        if ($mapping && $mapping->profileSet) {
            $profileSet = $mapping->profileSet;
            $components = $profileSet->components ?? collect();
            $foundProfileSet = true;

            // Gather component data for transparency
            $allComps     = [];
            $profileCodes = [];

            // Load deduction manipulations for this profile set + product type
            $productTypeCode = $specs['product_type'] ?? null;
            $frameType       = $specs['frame_type'] ?? 'Retrofit';

            // Auto-resolve article from window type code if not explicitly passed
            $article = $specs['article'] ?? $windowType ?? '';

            // Auto-resolve product_type_code from configuration if not passed
            if (!$productTypeCode && $seriesType) {
                try {
                    $configRow = DB::table('elitevw_master_series_configurations as c')
                        ->join('elitevw_master_series_configuration_product_type as cp', 'c.id', '=', 'cp.series_configuration_id')
                        ->join('elitevw_master_product_types as pt', 'pt.id', '=', 'cp.product_type_id')
                        ->where('c.series_type', $seriesType)
                        ->select('pt.product_type')
                        ->first();
                    if ($configRow) {
                        $productTypeCode = $configRow->product_type;
                    }
                } catch (\Throwable $e) {
                    // Silently skip if tables don't exist or query fails
                }
            }

            $manipulations = \App\Models\DeductionManipulation::forSetAndProductType(
                $profileSet->id, $productTypeCode
            );

            // Load Cantor per-profile deduction values (PROAM data)
            $profileCodesInSet = $components->pluck('profile_code')->filter()->unique()->values()->toArray();
            $cantorDeductions = [];
            if (!empty($profileCodesInSet)) {
                $catalogIds = DB::table('elitevw_profile_catalog')
                    ->whereIn('profile_code', $profileCodesInSet)
                    ->pluck('id', 'profile_code');

                if ($catalogIds->isNotEmpty()) {
                    $dedRows = ProfileDeductionValue::whereIn('profile_catalog_id', $catalogIds->values())
                        ->where('deduction_value', '!=', 0)
                        ->get(['profile_catalog_id', 'deduction_id', 'deduction_value']);

                    // Flip catalog lookup: id -> code
                    $idToCode = $catalogIds->flip();
                    foreach ($dedRows as $row) {
                        $code = $idToCode[$row->profile_catalog_id] ?? null;
                        if ($code) {
                            $cantorDeductions[$code][$row->deduction_id] = (float) $row->deduction_value;
                        }
                    }
                }
            }

            // Load Cantor PROFTAB cut deductions (production-derived ABZUG values)
            $proftabDeductions = [];
            if (!empty($profileCodesInSet)) {
                $proftabRows = ProfileCutDeduction::whereIn('profile_code', $profileCodesInSet)
                    ->get(['profile_code', 'profile_type', 'position_code', 'abzug1', 'abzug2', 'abzug3']);
                foreach ($proftabRows as $row) {
                    $proftabDeductions[$row->profile_code][$row->position_code] = [
                        'type'   => $row->profile_type,
                        'abzug1' => (float) $row->abzug1,
                        'abzug2' => (float) $row->abzug2,
                        'abzug3' => (float) $row->abzug3,
                        'total'  => round((float)$row->abzug1 + (float)$row->abzug2 + (float)$row->abzug3, 6),
                    ];
                }
            }

            // Map Cantor component type codes to our types
            $cantorTypeMap = [
                'frame' => 'FR', 'retrofit_frame' => 'FR', 'jamb' => 'FR',
                'sash' => 'SH', 'sash_interlock' => 'SH', 'sash_rail' => 'SH',
                'mullion' => 'M', 'meeting_rail' => 'M',
            ];

            foreach ($components as $comp) {
                $type   = $comp->type ?? 'Other';
                $dimSrc = $comp->dimension_source ?? 'custom';
                $len    = $comp->calculateLength($width, $height);
                $orient = $comp->orientation ?? '';

                // Apply deduction manipulations
                $manipDelta = 0;
                $cantorCode = $cantorTypeMap[$type] ?? null;
                if ($cantorCode && $manipulations->count() > 0) {
                    $context = [
                        'frame_type'          => $frameType,
                        'article'             => $article,
                        'mullion_orientation'  => $specs['mullion_orientation'] ?? null,
                        'product_type_code'   => $productTypeCode,
                    ];
                    foreach ($manipulations as $manip) {
                        if ($manip->component_type !== $cantorCode) continue;
                        if ($manip->position && $manip->position !== $orient
                            && !in_array($manip->position, ['top','bottom','left','right'])) continue;
                        // Map top/bottom -> horizontal, left/right -> vertical
                        $manipOrient = match($manip->position) {
                            'top', 'bottom' => 'horizontal',
                            'left', 'right' => 'vertical',
                            default => $manip->position,
                        };
                        if ($manipOrient && $manipOrient !== $orient) continue;
                        if (!$manip->matchesContext($context)) continue;

                        $manipDelta += $manip->diff_size_1 + $manip->diff_size_2 + $manip->diff_size;
                    }
                    $len = round($len + $manipDelta, 4);
                }

                // Attach Cantor PROAM deduction values for this profile
                $compCantorDeds = $cantorDeductions[$comp->profile_code ?? ''] ?? [];

                // PROFTAB data: attach for reference/transparency only
                $compProftab = $proftabDeductions[$comp->profile_code ?? ''] ?? [];
                $proftabLage = null;

                $allComps[] = [
                    'profile_code' => $comp->profile_code ?? '',
                    'type'         => $type,
                    'dimension'    => $dimSrc,
                    'formula'      => $comp->formula ?? '',
                    'qty'          => (int) ($comp->quantity ?? 1),
                    'result'       => round(max(0, $len), 4),
                    'orientation'  => $orient,
                    'cut_type'     => $comp->cut_type ?? '',
                    'description'  => $comp->description ?? '',
                    'fab_mark'     => $comp->fabrication_mark ?? '',
                    'station'      => $comp->station ?? '',
                    'manip_delta'  => round($manipDelta, 4),
                    'cantor_deds'  => $compCantorDeds,
                    'proftab_data' => $compProftab,
                ];

                if ($comp->profile_code) $profileCodes[$comp->profile_code] = $type;
            }

            // CANTOR PROAM: Resolve glass stop deduction (ID 7)
            $cantorGlassStopFrame = 0;
            $cantorGlassStopSash  = 0;
            foreach ($profileCodes as $pCode => $pType) {
                $glassDed = $cantorDeductions[$pCode][7] ?? 0;
                if ($glassDed != 0) {
                    if (in_array($pType, ['frame', 'retrofit_frame', 'jamb'])) {
                        $cantorGlassStopFrame = max($cantorGlassStopFrame, $glassDed);
                    } elseif (in_array($pType, ['sash', 'sash_interlock', 'sash_rail'])) {
                        $cantorGlassStopSash = max($cantorGlassStopSash, $glassDed);
                    }
                }
            }

            // CANTOR PROFTAB: Resolve glass bead deductions from production data
            $proftabFrameH = 0; $proftabFrameV = 0;
            $proftabSashH  = 0; $proftabSashV  = 0;
            $hasProftabData = false;

            foreach ($profileCodes as $pCode => $pType) {
                $pDeds = $proftabDeductions[$pCode] ?? [];
                if (empty($pDeds)) continue;

                if (in_array($pType, ['frame', 'retrofit_frame', 'jamb'])) {
                    if (isset($pDeds[20])) {
                        $d = $pDeds[20];
                        $proftabFrameH = max($proftabFrameH, $d['total']);
                        $hasProftabData = true;
                    }
                    if (isset($pDeds[33])) {
                        $d = $pDeds[33];
                        $proftabFrameV = max($proftabFrameV, $d['total']);
                        $hasProftabData = true;
                    }
                } elseif (in_array($pType, ['sash', 'sash_interlock', 'sash_rail'])) {
                    if (isset($pDeds[20])) {
                        $d = $pDeds[20];
                        $proftabSashH = max($proftabSashH, $d['total']);
                        $hasProftabData = true;
                    }
                    if (isset($pDeds[33])) {
                        $d = $pDeds[33];
                        $proftabSashV = max($proftabSashV, $d['total']);
                        $hasProftabData = true;
                    }
                }
            }

            // PRIMARY: Mapping-level glass deductions (per window type)
            $fixHDed  = (float) ($mapping->fix_glass_h_deduction ?? 0);
            $fixVDed  = (float) ($mapping->fix_glass_v_deduction ?? 0);
            $sashHDed = (float) ($mapping->sash_glass_h_deduction ?? 0);
            $sashVDed = (float) ($mapping->sash_glass_v_deduction ?? 0);
            $screenHDed = (float) ($mapping->screen_h_deduction ?? 0);
            $screenVDed = (float) ($mapping->screen_v_deduction ?? 0);

            if ($fixHDed > 0 || $fixVDed > 0 || $sashHDed > 0 || $sashVDed > 0) {
                $usedSource = 'series_type_deductions';

                // Fix glass: uses fix field width
                $glassFix['w']  = round($fixFieldWidth - $fixHDed, 4);
                $glassFix['h']  = round($height - $fixVDed, 4);

                // Sash glass: uses sash field width (may differ from fix)
                if ($hasSash && ($sashHDed > 0 || $sashVDed > 0)) {
                    $glassSash['w'] = round($sashFieldWidth - $sashHDed, 4);
                    $glassSash['h'] = round($height - $sashVDed, 4);
                } else {
                    $glassSash['w'] = $glassFix['w'];
                    $glassSash['h'] = $glassFix['h'];
                }

                // Screen: uses sash field width (screen covers the operable opening)
                if ($screenHDed > 0 || $screenVDed > 0) {
                    $screen['w'] = round($sashFieldWidth - $screenHDed, 4);
                    $screen['h'] = round($height - $screenVDed, 4);
                } else {
                    $screen['w'] = round($sashFieldWidth - 3.0, 4);
                    $screen['h'] = round($height - 3.375, 4);
                }
            }
            // SECONDARY: Cantor PROAM per-profile glass stop deduction
            elseif ($cantorGlassStopFrame > 0 || $cantorGlassStopSash > 0) {
                $usedSource = 'cantor_proam';

                $frameGlassDed = $cantorGlassStopFrame ?: $cantorGlassStopSash;
                $sashGlassDed  = $cantorGlassStopSash ?: $cantorGlassStopFrame;

                $glassFix['w']  = round($fixFieldWidth - $frameGlassDed, 4);
                $glassFix['h']  = round($height - $frameGlassDed, 4);

                if ($hasSash) {
                    $glassSash['w'] = round($sashFieldWidth - $sashGlassDed, 4);
                    $glassSash['h'] = round($height - $sashGlassDed, 4);
                } else {
                    $glassSash['w'] = $glassFix['w'];
                    $glassSash['h'] = $glassFix['h'];
                }

                $screen['w'] = round($sashFieldWidth - 3.0, 4);
                $screen['h'] = round($height - 3.375, 4);
            }
            // FALLBACK: ProfileSet named deductions (legacy)
            else {
                $frameHDed      = (float) ($profileSet->frame_horizontal_deduction ?? 0);
                $frameVDed      = (float) ($profileSet->frame_vertical_deduction ?? 0);
                $psHDed         = (float) ($profileSet->sash_horizontal_deduction ?? 0);
                $psVDed         = (float) ($profileSet->sash_vertical_deduction ?? 0);
                $interlockDed   = (float) ($profileSet->interlock_deduction ?? 0);
                $meetingRailDed = (float) ($profileSet->meeting_rail_deduction ?? 0);

                if ($frameHDed > 0 || $frameVDed > 0 || $psHDed > 0 || $psVDed > 0) {
                    $usedSource = 'profile_set_deductions';
                    $glassFix['w']  = round($fieldWidth - $frameHDed, 4);
                    $glassFix['h']  = round($height - $frameVDed, 4);
                    if ($hasSash) {
                        $glassSash['w'] = round($fieldWidth - ($psHDed ?: $frameHDed), 4);
                        $glassSash['h'] = round($height - ($psVDed ?: $frameVDed), 4);
                    } else {
                        $glassSash['w'] = $glassFix['w'];
                        $glassSash['h'] = $glassFix['h'];
                    }
                    $screen['w'] = round($fieldWidth - ($interlockDed ?: $frameHDed), 4);
                    $screen['h'] = round($height - ($meetingRailDed ?: $frameVDed), 4);
                } else {
                    $usedSource = 'no_deductions';
                    $glassFix['w']  = round($fieldWidth, 4);
                    $glassFix['h']  = round($height, 4);
                    $glassSash['w'] = $glassFix['w'];
                    $glassSash['h'] = $glassFix['h'];
                    $screen['w']    = $glassFix['w'];
                    $screen['h']    = $glassFix['h'];
                }
            }

            // Apply Deduction Manipulation deltas to glass/sash/screen
            $glassHDelta = 0;
            $glassVDelta = 0;
            $sashHDelta  = 0;
            $sashVDelta  = 0;
            $manipDebug = [];

            if ($manipulations->count() > 0) {
                $manipContext = [
                    'frame_type'         => $frameType,
                    'article'            => $article,
                    'mullion_orientation' => $specs['mullion_orientation'] ?? null,
                    'product_type_code'  => $productTypeCode,
                ];
                foreach ($manipulations as $manip) {
                    $matched = $manip->matchesContext($manipContext);
                    $pos = strtolower(trim($manip->position ?? ''));
                    $cType = strtoupper(trim($manip->component_type ?? ''));

                    // Per-rule multipliers (stored in DB, fallback to legacy defaults)
                    $hMult = $manip->h_multiplier ?? 2;
                    $vMult = $manip->v_multiplier ?? 2;

                    $manipDebug[] = [
                        'seq' => $manip->seq,
                        'component' => $manip->component_type_label ?? $manip->component_type,
                        'position' => $manip->position,
                        'h_mult' => $hMult,
                        'v_mult' => $vMult,
                        'frame_type' => $manip->frame_type,
                        'article' => $manip->article_code,
                        'product_type' => $manip->product_type_code,
                        'matched' => $matched,
                        'diff1' => $manip->diff_size_1,
                        'diff3' => $manip->diff_size,
                    ];
                    if (!$matched) continue;

                    // Skip component types handled in the profile-component loop (FR, SH, M)
                    if (in_array($cType, ['FR', 'SH', 'M'])) continue;

                    // Respect position
                    $appliesToH = ($pos === '' || $pos === 'horizontal');
                    $appliesToV = ($pos === '' || $pos === 'vertical');

                    if ($cType === 'GF') {
                        $d = $manip->diff_size_1 ?: $manip->diff_size ?: 0;
                        if ($d != 0) {
                            if ($appliesToH) $glassHDelta += $d * $hMult;
                            if ($appliesToV) $glassVDelta += $d * $vMult;
                        }
                    } elseif ($cType === 'GS') {
                        $d = $manip->diff_size ?: $manip->diff_size_1 ?: 0;
                        if ($d != 0) {
                            if ($appliesToH) $sashHDelta += $d * $hMult;
                            if ($appliesToV) $sashVDelta += $d * $vMult;
                        }
                    } else {
                        if ($manip->diff_size_1 != 0) {
                            if ($appliesToH) $glassHDelta += $manip->diff_size_1 * $hMult;
                            if ($appliesToV) $glassVDelta += $manip->diff_size_1 * $vMult;
                        }
                        if ($manip->diff_size != 0) {
                            if ($appliesToH) $sashHDelta += $manip->diff_size * $hMult;
                            if ($appliesToV) $sashVDelta += $manip->diff_size * $vMult;
                        }
                    }
                }

                if ($glassHDelta != 0) {
                    $glassFix['w'] = round($glassFix['w'] + $glassHDelta, 4);
                }
                if ($glassVDelta != 0) {
                    $glassFix['h'] = round($glassFix['h'] + $glassVDelta, 4);
                }
                if ($sashHDelta != 0 && $hasSash) {
                    $glassSash['w'] = round($glassSash['w'] + $sashHDelta, 4);
                }
                if ($sashVDelta != 0 && $hasSash) {
                    $glassSash['h'] = round($glassSash['h'] + $sashVDelta, 4);
                }
            }

            // Build deduction transparency
            $deductions[] = [
                'source' => 'profile_set',
                'profile_set' => $profileSet->code ?? $profileSet->name ?? '',
                'series_type' => $mapping->series_type ?? '',
                'window_type' => $windowType,
                'panel_count' => $panelCount,
                'fix_field_width'  => round($fixFieldWidth, 4),
                'sash_field_width' => round($sashFieldWidth, 4),
                'has_sash'    => $hasSash,
                'data_source' => $usedSource,
                'fix_h_ded'   => $fixHDed,
                'fix_v_ded'   => $fixVDed,
                'sash_h_ded'  => $sashHDed,
                'sash_v_ded'  => $sashVDed,
                'screen_h_ded' => $screenHDed,
                'screen_v_ded' => $screenVDed,
                'manipulation_count' => $manipulations->count(),
                'manipulation_context' => [
                    'frame_type' => $frameType,
                    'article' => $article,
                    'product_type_code' => $productTypeCode,
                ],
                'glass_h_delta' => $glassHDelta ?? 0,
                'glass_v_delta' => $glassVDelta ?? 0,
                'sash_h_delta' => $sashHDelta ?? 0,
                'sash_v_delta' => $sashVDelta ?? 0,
                'manipulation_debug' => $manipDebug,
                'component_count' => count($allComps),
                'cantor_glass_stop_frame' => $cantorGlassStopFrame,
                'cantor_glass_stop_sash'  => $cantorGlassStopSash,
                'cantor_profile_deductions' => $cantorDeductions,
                'proftab_deductions' => $proftabDeductions,
                'proftab_frame_h' => $proftabFrameH,
                'proftab_frame_v' => $proftabFrameV,
                'proftab_sash_h'  => $proftabSashH,
                'proftab_sash_v'  => $proftabSashV,
            ];

            if (!empty($allComps)) {
                $deductions[] = [
                    'source' => 'components',
                    'items'  => $allComps,
                ];
            }
        }

        // FALLBACK: Deduction Manager formulas (only if NO ProfileSet found)
        if (!$foundProfileSet && $seriesId) {
            $query = DB::table('elitevw_master_glass_deduction_rules')
                ->where('series_id', $seriesId)
                ->where('active', true)
                ->orderBy('sort_order');

            $rules = $query->get();

            if ($rules->isNotEmpty()) {
                $usedSource = 'deduction_manager';
                $ruleDetails = [];

                foreach ($rules as $rule) {
                    $formula = $rule->formula;
                    $expr = str_ireplace(['{W}', '{H}', '{P}'], [$width, $height, $panelCount], $formula);
                    $expr = str_ireplace(['w', 'h', 'p'], [$width, $height, $panelCount], $expr);
                    $clean = preg_replace('/[^0-9.+\-*\/() ]/', '', $expr);
                    try {
                        $result = eval("return ($clean);");
                        $result = is_numeric($result) ? round((float)$result, 4) : 0;
                    } catch (\Throwable $e) {
                        $result = 0;
                    }

                    $comp = $rule->component;
                    $dim  = $rule->dimension;

                    if ($comp === 'glass_fix'  && $dim === 'width')  $glassFix['w']  = $result;
                    if ($comp === 'glass_fix'  && $dim === 'height') $glassFix['h']  = $result;
                    if ($comp === 'glass_sash' && $dim === 'width')  $glassSash['w'] = $result;
                    if ($comp === 'glass_sash' && $dim === 'height') $glassSash['h'] = $result;
                    if ($comp === 'screen'     && $dim === 'width')  $screen['w']    = $result;
                    if ($comp === 'screen'     && $dim === 'height') $screen['h']    = $result;

                    $ruleDetails[] = [
                        'component'   => $comp,
                        'dimension'   => $dim,
                        'formula'     => $formula,
                        'description' => $rule->description ?? '',
                        'result'      => $result,
                    ];
                }

                $deductions[] = [
                    'source' => 'deduction_manager',
                    'series_id' => $seriesId,
                    'panel_count' => $panelCount,
                    'rules' => $ruleDetails,
                ];
            }
        }

        // Build per-field glass sizes
        $glassFields = $this->buildGlassFields(
            $windowType, $glassFix, $glassSash,
            $seriesType, $width, $height,
            $fixHDed ?? 0, $fixVDed ?? 0,
            $sashHDed ?? 0, $sashVDed ?? 0,
            $mapping->profile_set_id ?? null
        );

        return [
            'rough_opening' => [
                'width'  => round($width, 4),
                'height' => round($height, 4),
            ],
            'glass_fix' => $glassFix,
            'glass_sash' => $glassSash,
            'glass_fields' => $glassFields,
            'screen' => $screen,
            'glass_size' => $glassFix, // backward compat
            'manual_width'  => round($width, 4),
            'manual_height' => round($height, 4),
            'area_sqft'  => round(($width * $height) / 144, 2),
            'perimeter'  => ($width + $height) * 2,
            'deductions' => $deductions,
            'cut_list'   => $allComps ?? [],
            'from_profile_management' => $foundProfileSet,
        ];
    }

    /**
     * Parse suffix from a series_type to get bottom/top panel counts.
     */
    protected function parseSuffix(string $seriesType): array
    {
        $upper = strtoupper(trim($seriesType));

        $bottom = ['count' => 0, 'code' => null];
        $top    = ['count' => 0, 'code' => null];

        // Type-code bottom: -BOXOX, -BXOXO, -BOX, etc.
        if (preg_match('/(?:^|-)B([XO]{2,})/i', $upper, $m)) {
            $code = strtoupper($m[1]);
            $bottom = ['count' => strlen($code), 'code' => $code];
        }
        // Numeric bottom: -B1, -B3, -B4
        elseif (preg_match('/(?:^|-)B(\d)/i', $upper, $m)) {
            $bottom = ['count' => (int) $m[1], 'code' => null];
        }

        // Type-code top: -TOXOX, etc.
        if (preg_match('/(?:^|-)T([XO]{2,})/i', $upper, $m)) {
            $code = strtoupper($m[1]);
            $top = ['count' => strlen($code), 'code' => $code];
        }
        // Numeric top: -T1, -T3
        elseif (preg_match('/(?:^|-)T(\d)/i', $upper, $m)) {
            $top = ['count' => (int) $m[1], 'code' => null];
        }

        return ['bottom' => $bottom, 'top' => $top];
    }

    /**
     * Extract the window type code from a full series_type string.
     */
    protected function extractWindowTypeCode(string $seriesType): string
    {
        $upper = strtoupper(trim($seriesType));

        // Strip suffixes first
        $stripped = preg_replace('/-(B[XO]+|T[XO]+|B\d|T\d|BA|TA|M\d|T\dB\d|T\dB[XO]+).*$/i', '', $upper);

        // Known base window type codes -- ordered longest-first
        $knownTypes = [
            'XOXOX','OXOXO','OXXOO','OOXOO',
            'OXXO','XOOX','XOXO','OXOX',
            'SHOSH','CLCR','XXO','OXX','OOX','XOO','XOX',
            'SHSH','XO','OX',
            'SH','PW','XR','CL','CR','AW',
        ];

        // Strip series/brand prefix for matching
        $base = preg_replace('/^(DYNAMIC|PRESTIGE|IM|GS|GX|GSCO|\d{4})-/i', '', $stripped);

        foreach ($knownTypes as $code) {
            if (preg_match('/^'.$code.'\d*$/i', $base)) {
                return $code;
            }
        }

        // Compound type check
        $compoundPattern = '/^(?:(?:OXXO|XOOX|XOXO|OXOX|OXOXO|XOXOX|AW|CL|CR|CM|SH|DH|PW|SW|SL|XO|OX|X|O|F)\d*[HV]?)+$/';
        if (preg_match($compoundPattern, $base)) {
            return $base;
        }

        // Fallback: strip trailing digits
        $base = preg_replace('/\d+$/', '', $base);
        return $base;
    }

    /**
     * Determine number of panels based on window type.
     */
    protected function getPanelCount(string $windowType): int
    {
        $known = match ($windowType) {
            'XO', 'OX'                          => 2,
            'OXXO', 'XOOX', 'XOXO', 'OXOX'     => 4,
            'OXOXO', 'XOXOX', 'OXXOO', 'OOXOO' => 5,
            'OOX', 'XOO', 'XXO', 'OXX'          => 3,
            'XOX'                                => 3,
            'SH', 'SHSH'                         => 1,
            'SHOSH'                               => 3,
            'PW', 'XR'                            => 1,
            'CLCR'                                => 2,
            'CL', 'CR'                            => 1,
            'AW'                                  => 1,
            default                               => null,
        };

        if ($known !== null) return $known;

        $layout = $this->autoFieldLayout($windowType);
        return count($layout) > 0 ? count($layout) : 1;
    }

    /**
     * Determine if a window type has operable sash panels.
     */
    protected function windowTypeHasSash(string $windowType): bool
    {
        return !in_array($windowType, ['PW', 'XR'], true);
    }

    /**
     * Get the field layout for a window type.
     */
    protected function getFieldLayout(string $windowType): array
    {
        return match ($windowType) {
            'PW'    => [
                ['position' => 1, 'type' => 'fix', 'label' => 'FIX'],
            ],
            'XO'    => [
                ['position' => 1, 'type' => 'sash', 'label' => 'XR'],
                ['position' => 2, 'type' => 'fix',  'label' => 'XFIX'],
            ],
            'OX'    => [
                ['position' => 1, 'type' => 'fix',  'label' => 'XFIX'],
                ['position' => 2, 'type' => 'sash', 'label' => 'XL'],
            ],
            'XOX'   => [
                ['position' => 1, 'type' => 'sash', 'label' => 'XL'],
                ['position' => 2, 'type' => 'fix',  'label' => 'XFIX'],
                ['position' => 3, 'type' => 'sash', 'label' => 'XR'],
            ],
            'OOX'   => [
                ['position' => 1, 'type' => 'fix',  'label' => 'FIX'],
                ['position' => 2, 'type' => 'fix',  'label' => 'FIX'],
                ['position' => 3, 'type' => 'sash', 'label' => 'XR'],
            ],
            'XOO'   => [
                ['position' => 1, 'type' => 'sash', 'label' => 'XL'],
                ['position' => 2, 'type' => 'fix',  'label' => 'FIX'],
                ['position' => 3, 'type' => 'fix',  'label' => 'FIX'],
            ],
            'XOXO'  => [
                ['position' => 1, 'type' => 'sash', 'label' => 'XL'],
                ['position' => 2, 'type' => 'fix',  'label' => 'FIX'],
                ['position' => 3, 'type' => 'fix',  'label' => 'FIX'],
                ['position' => 4, 'type' => 'sash', 'label' => 'XR'],
            ],
            'XOOX'  => [
                ['position' => 1, 'type' => 'sash', 'label' => 'XL'],
                ['position' => 2, 'type' => 'fix',  'label' => 'FIX'],
                ['position' => 3, 'type' => 'fix',  'label' => 'FIX'],
                ['position' => 4, 'type' => 'sash', 'label' => 'XR'],
            ],
            'OXXO'  => [
                ['position' => 1, 'type' => 'fix',  'label' => 'FIX'],
                ['position' => 2, 'type' => 'sash', 'label' => 'XL'],
                ['position' => 3, 'type' => 'sash', 'label' => 'XR'],
                ['position' => 4, 'type' => 'fix',  'label' => 'FIX'],
            ],
            'OXOXO'  => [
                ['position' => 1, 'type' => 'fix',  'label' => 'FIX'],
                ['position' => 2, 'type' => 'sash', 'label' => 'XL'],
                ['position' => 3, 'type' => 'fix',  'label' => 'FIX'],
                ['position' => 4, 'type' => 'sash', 'label' => 'XR'],
                ['position' => 5, 'type' => 'fix',  'label' => 'FIX'],
            ],
            'XXO'   => [
                ['position' => 1, 'type' => 'sash', 'label' => 'XL'],
                ['position' => 2, 'type' => 'sash', 'label' => 'XR'],
                ['position' => 3, 'type' => 'fix',  'label' => 'FIX'],
            ],
            'SH'    => [
                ['position' => 1, 'type' => 'fix',  'label' => 'XFIX'],
                ['position' => 2, 'type' => 'sash', 'label' => 'XU'],
            ],
            'SHSH'  => [
                ['position' => 1, 'type' => 'fix',  'label' => 'XFIX'],
                ['position' => 2, 'type' => 'sash', 'label' => 'XU'],
                ['position' => 3, 'type' => 'fix',  'label' => 'XFIX'],
                ['position' => 4, 'type' => 'sash', 'label' => 'XU'],
            ],
            'SHOSH' => [
                ['position' => 1, 'type' => 'sash', 'label' => 'SH-L'],
                ['position' => 2, 'type' => 'fix',  'label' => 'FIX'],
                ['position' => 3, 'type' => 'sash', 'label' => 'SH-R'],
            ],
            'CL'    => [
                ['position' => 1, 'type' => 'sash', 'label' => 'SASH'],
            ],
            'CR'    => [
                ['position' => 1, 'type' => 'sash', 'label' => 'SASH'],
            ],
            'CLCR'  => [
                ['position' => 1, 'type' => 'sash', 'label' => 'CL'],
                ['position' => 2, 'type' => 'sash', 'label' => 'CR'],
            ],
            'AW'    => [
                ['position' => 1, 'type' => 'sash', 'label' => 'SASH'],
            ],
            default => $this->autoFieldLayout($windowType),
        };
    }

    /**
     * Auto-generate field layout for compound/unknown window types.
     */
    protected function autoFieldLayout(string $windowType): array
    {
        $type = strtoupper(trim($windowType));

        $panelTokens = [
            'OXOXO','XOXOX','OXXOO','OOXOO',
            'OXXO','XOOX','XOXO','OXOX',
            'XXO','OXX','OOX','XOO','XOX','OXO',
            'AW','CL','CR','CM','SH','DH','PW','SW',
            'XO','OX',
            'SL','F',
            'X','O',
        ];

        $sashTypes = ['X','AW','CL','CR','CM','SH','DH','SL','SW'];

        $fields = [];
        $pos = 1;
        $len = strlen($type);
        $i = 0;

        while ($i < $len) {
            $matched = false;

            foreach ($panelTokens as $tok) {
                $tokLen = strlen($tok);
                if (substr($type, $i, $tokLen) === $tok) {
                    $i += $tokLen;

                    $count = 1;
                    if ($i < $len && ctype_digit($type[$i])) {
                        $count = (int) $type[$i];
                        $i++;
                    }

                    if ($i < $len && in_array($type[$i], ['H', 'V'])) {
                        $i++;
                    }

                    if (strlen($tok) >= 2 && preg_match('/^[XO]+$/', $tok)) {
                        $subLayout = match ($tok) {
                            'OXXO'  => [['t'=>'fix','l'=>'FIX'],['t'=>'sash','l'=>'XL'],['t'=>'sash','l'=>'XR'],['t'=>'fix','l'=>'FIX']],
                            'XOOX'  => [['t'=>'sash','l'=>'XL'],['t'=>'fix','l'=>'FIX'],['t'=>'fix','l'=>'FIX'],['t'=>'sash','l'=>'XR']],
                            'XOXO'  => [['t'=>'sash','l'=>'XL'],['t'=>'fix','l'=>'FIX'],['t'=>'sash','l'=>'XR'],['t'=>'fix','l'=>'FIX']],
                            'OXOX'  => [['t'=>'fix','l'=>'FIX'],['t'=>'sash','l'=>'XL'],['t'=>'fix','l'=>'FIX'],['t'=>'sash','l'=>'XR']],
                            'OXOXO' => [['t'=>'fix','l'=>'FIX'],['t'=>'sash','l'=>'XL'],['t'=>'fix','l'=>'FIX'],['t'=>'sash','l'=>'XR'],['t'=>'fix','l'=>'FIX']],
                            'XOXOX' => [['t'=>'sash','l'=>'XL'],['t'=>'fix','l'=>'FIX'],['t'=>'sash','l'=>'XR'],['t'=>'fix','l'=>'FIX'],['t'=>'sash','l'=>'X3']],
                            default => array_map(fn($ch) => $ch === 'X'
                                ? ['t'=>'sash','l'=>'X']
                                : ['t'=>'fix','l'=>'FIX'], str_split($tok)),
                        };
                        for ($c = 0; $c < $count; $c++) {
                            foreach ($subLayout as $sf) {
                                $fields[] = ['position' => $pos++, 'type' => $sf['t'], 'label' => $sf['l']];
                            }
                        }
                    } else {
                        $isSash = in_array($tok, $sashTypes);
                        for ($c = 0; $c < $count; $c++) {
                            $fields[] = [
                                'position' => $pos++,
                                'type'     => $isSash ? 'sash' : 'fix',
                                'label'    => $isSash ? strtoupper($tok) : 'FIX',
                            ];
                        }
                    }

                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                $i++;
            }
        }

        return count($fields) > 0 ? $fields : [['position' => 1, 'type' => 'fix', 'label' => 'FIX']];
    }

    /**
     * Build per-field glass size array from the field layout and calculated sizes.
     */
    protected function buildGlassFields(
        string $windowType,
        array $glassFix,
        array $glassSash,
        string $seriesType = '',
        float $totalWidth = 0,
        float $totalHeight = 0,
        float $fixHDed = 0,
        float $fixVDed = 0,
        float $sashHDed = 0,
        float $sashVDed = 0,
        ?int $profileSetId = null
    ): array {
        $layout = $this->getFieldLayout($windowType);
        $suffix = $this->parseSuffix($seriesType);
        $fields = [];
        $pos = 1;

        $hasTop = $suffix['top']['count'] > 0;
        $hasBot = $suffix['bottom']['count'] > 0;
        $numSections = 1 + ($hasTop ? 1 : 0) + ($hasBot ? 1 : 0);
        $hMullionHeight = 0.25;
        $numHMullions = $numSections - 1;
        $availableHeight = $totalHeight - ($numHMullions * $hMullionHeight);
        $sectionHeight = $numSections > 1 ? ($availableHeight / $numSections) : $totalHeight;

        // Look up PW deductions for B/T sections
        $sectionFixHDed = $fixHDed;
        $sectionFixVDed = $fixVDed;
        if ($profileSetId && ($hasTop || $hasBot)) {
            $pwMapping = SeriesTypeProfileSet::where('profile_set_id', $profileSetId)
                ->where('series_type', 'PW')
                ->first();
            if ($pwMapping) {
                $sectionFixHDed = (float) ($pwMapping->fix_glass_h_deduction ?: $fixHDed);
                $sectionFixVDed = (float) ($pwMapping->fix_glass_v_deduction ?: $fixVDed);
            }
        }

        // Helper: build fields for a suffix section (T or B)
        $buildSectionFields = function(array $section, float $secH) use ($totalWidth, $sectionFixHDed, $sectionFixVDed, $sashHDed, $sashVDed, &$pos, &$fields) {
            if ($section['count'] <= 0) return;

            $numPanels = $section['count'];
            $code = $section['code'];

            if ($code) {
                $sectionLayout = $this->getFieldLayout($code);
                $panelCount = count($sectionLayout);
                $panelWidth = $totalWidth / max($panelCount, 1);

                foreach ($sectionLayout as $sf) {
                    $isSash = ($sf['type'] === 'sash');
                    $w = round($panelWidth - ($isSash ? $sashHDed : $sectionFixHDed), 4);
                    $h = round($secH - ($isSash ? $sashVDed : $sectionFixVDed), 4);
                    $fields[] = [
                        'field'  => $pos,
                        'label'  => $sf['label'],
                        'type'   => $sf['type'],
                        'width'  => $w,
                        'height' => $h,
                    ];
                    $pos++;
                }
            } else {
                $glassW = round($totalWidth - $sectionFixHDed, 4);
                $glassH = round($secH - $sectionFixVDed, 4);

                for ($i = 0; $i < $numPanels; $i++) {
                    $fields[] = [
                        'field'  => $pos,
                        'label'  => 'FIX',
                        'type'   => 'fix',
                        'width'  => $glassW,
                        'height' => $glassH,
                    ];
                    $pos++;
                }
            }
        };

        // Top transom panels (T suffix)
        $buildSectionFields($suffix['top'], $sectionHeight);

        // Main panels (base window type)
        $hungTypes = ['SH', 'DH', 'SHSH', 'SHOSH'];
        $hasMeetingRail = in_array($windowType, $hungTypes);
        $effectiveHeight = $hasMeetingRail ? ($sectionHeight / 2) : $sectionHeight;
        $heightReduction = $totalHeight - $effectiveHeight;

        foreach ($layout as $field) {
            $isSash = ($field['type'] === 'sash');
            $glass = $isSash ? $glassSash : $glassFix;
            $fields[] = [
                'field'  => $pos,
                'label'  => $field['label'],
                'type'   => $field['type'],
                'width'  => $glass['w'],
                'height' => round($glass['h'] - $heightReduction, 4),
            ];
            $pos++;
        }

        // Bottom panels (B suffix)
        $buildSectionFields($suffix['bottom'], $sectionHeight);

        return $fields;
    }

    /**
     * Public: get the full panel layout for a series type, including T/B suffix rows.
     */
    public function getFullPanelLayout(string $seriesType): array
    {
        $windowType = $this->extractWindowTypeCode($seriesType);
        $mainLayout = $this->getFieldLayout($windowType);
        $suffix = $this->parseSuffix($seriesType);

        $panels = [];
        $pos = 1;

        // Top row
        if ($suffix['top']['count'] > 0) {
            $topCode = $suffix['top']['code'];
            if ($topCode) {
                $topLayout = $this->getFieldLayout($topCode);
                $ti = 0;
                foreach ($topLayout as $f) {
                    $ti++;
                    $panels[] = ['position' => $pos++, 'type' => $f['type'], 'label' => 'T' . $ti . ' ' . $f['label'], 'row' => 'top'];
                }
            } else {
                for ($i = 0; $i < $suffix['top']['count']; $i++) {
                    $panels[] = ['position' => $pos++, 'type' => 'fix', 'label' => 'T' . ($i + 1), 'row' => 'top'];
                }
            }
        }

        // Main row
        $mainIdx = 0;
        foreach ($mainLayout as $f) {
            $mainIdx++;
            $label = $f['label'];
            if (count($mainLayout) > 1 && $label === 'FIX') {
                $label = $f['label'];
            }
            $panels[] = ['position' => $pos++, 'type' => $f['type'], 'label' => $label, 'row' => 'main'];
        }

        // Bottom row
        if ($suffix['bottom']['count'] > 0) {
            $botCode = $suffix['bottom']['code'];
            if ($botCode) {
                $botLayout = $this->getFieldLayout($botCode);
                $bi = 0;
                foreach ($botLayout as $f) {
                    $bi++;
                    $panels[] = ['position' => $pos++, 'type' => $f['type'], 'label' => 'B' . $bi . ' ' . $f['label'], 'row' => 'bottom'];
                }
            } else {
                $botStart = $pos;
                for ($i = 0; $i < $suffix['bottom']['count']; $i++) {
                    $panels[] = ['position' => $pos++, 'type' => 'fix', 'label' => 'B' . ($i + 1), 'row' => 'bottom'];
                }
            }
        }

        $rows = array_values(array_unique(array_column($panels, 'row')));

        return [
            'panels'      => $panels,
            'rows'        => $rows,
            'mainCount'   => count($mainLayout),
            'topCount'    => $suffix['top']['count'],
            'bottomCount' => $suffix['bottom']['count'],
            'windowType'  => $windowType,
        ];
    }

    /**
     * Validate configuration
     */
    protected function validateConfiguration(array $specs, array $profiles, float $frameDepth): array
    {
        $errors = [];
        $warnings = [];

        $requiredProfiles = ['HEADJAMB', 'SIDEJAMB', 'SILL'];
        foreach ($requiredProfiles as $type) {
            if (!isset($profiles[$type])) {
                $errors[] = "Missing {$type} profile for this configuration";
            }
        }

        if (!isset($profiles['JAMB_ASSEMBLY'])) {
            $errors[] = "No jamb combination found for frame depth: {$frameDepth}\"";
        }

        if ($specs['width'] < 12) {
            $warnings[] = "Width is very small (< 12 inches)";
        }

        if ($specs['height'] < 12) {
            $warnings[] = "Height is very small (< 12 inches)";
        }

        if ($specs['width'] > 120) {
            $warnings[] = "Width exceeds standard size (> 120 inches)";
        }

        if ($specs['height'] > 120) {
            $warnings[] = "Height exceeds standard size (> 120 inches)";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate input specs
     */
    protected function validateSpecs(array $specs): void
    {
        if (!isset($specs['series'])) {
            throw new \InvalidArgumentException('Series is required');
        }

        if (!isset($specs['width']) || !isset($specs['height'])) {
            throw new \InvalidArgumentException('Width and height are required');
        }

        if ($specs['width'] <= 0 || $specs['height'] <= 0) {
            throw new \InvalidArgumentException('Width and height must be positive');
        }
    }

    /**
     * Get series name from code
     */
    protected function getSeriesName(string $code): string
    {
        $seriesNames = [
            '5100' => 'Single Hung (HS)',
            '5200' => 'Double Hung (DH)',
            '5300' => 'Sliding (SL)',
            'WI' => 'Windows',
            'DO' => 'Doors',
            'PC' => 'Picture',
            'PW' => 'Patio Door',
        ];

        return $seriesNames[$code] ?? $code;
    }
}
