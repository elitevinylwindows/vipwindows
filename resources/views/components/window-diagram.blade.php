@php
/**
 * Window Diagram Blade Component - Vinyl PVC Style
 * Supports 500+ configuration codes via algorithmic parsing.
 *
 * Usage: @include('components.window-diagram', [
 *     'type'    => 'XO-B2T2',   // any config code
 *     'width'   => 36,           // inches (accepts "36 1/2" fractions)
 *     'height'  => 48,           // inches (accepts "48 3/8" fractions)
 *     'hinge'   => 'left',       // optional: left/right for casement
 *     'maxSize' => 250,          // optional: max SVG pixel size
 *     'color'   => 'WH',        // optional: exterior color (BK = black/obsidian)
 * ])
 *
 * CONFIG CODE GRAMMAR:
 *   [PREFIX-]MAIN_BODY[-SUFFIX]
 *
 *   MAIN BODY panels (read left-to-right):
 *     X  = operable/sliding sash (arrows shown)
 *     O  = fixed lite / picture panel
 *     PW = picture window (fixed)
 *     AW = awning (hinged at top)
 *     CL = casement left-hinged
 *     CR = casement right-hinged
 *     CM = casement
 *     SH = single-hung
 *     DH = double-hung
 *     SL = sliding (prefix, treat body as slider layout)
 *     SW = swing door (prefix)
 *     FSW = folding swing (prefix)
 *
 *   Number after panel type = count of that panel side-by-side
 *     e.g. AW2 = 2 awning panels, PW3 = 3 picture panels, OX2 = O then X×2
 *
 *   H/V suffix on panel group = stacked orientation
 *     2H = 2 panels stacked horizontally (side by side)
 *     2V = 2 panels stacked vertically
 *
 *   SUFFIX modifiers (after hyphen):
 *     B[n]  = bottom kick panel row with n columns
 *     T[n]  = top transom row with n columns
 *     T[n]B[n] or B[n]T[n] = both top and bottom
 *     M[n]  = mullion / middle divider row
 *     BA, TA = arch bottom/top
 */

// ─── PARSE WIDTH/HEIGHT (fraction support) ───
$_rawW = $width ?? 36;
$_rawH = $height ?? 48;
if (is_string($_rawW) && preg_match('/^(\d+)\s+(\d+)\/(\d+)$/', trim($_rawW), $_m)) {
    $w = intval($_m[1]) + intval($_m[2]) / intval($_m[3]);
} else {
    $w = floatval($_rawW);
}
if (is_string($_rawH) && preg_match('/^(\d+)\s+(\d+)\/(\d+)$/', trim($_rawH), $_m)) {
    $h = intval($_m[1]) + intval($_m[2]) / intval($_m[3]);
} else {
    $h = floatval($_rawH);
}

// ─── FIXED: Fraction display labels for dimensions: 36.5 → "36 1/2\"" ───
// Uses integer sixteenths instead of float array keys to avoid precision bugs
if (!function_exists('_wd2_fracLabel')) {
    function _wd2_fracLabel($dec) {
        $whole = intval($dec);
        $sixteenths = (int) round(($dec - $whole) * 16);

        // No fractional part
        if ($sixteenths <= 0) return $whole . '"';
        // Rounds up to next whole
        if ($sixteenths >= 16) return ($whole + 1) . '"';

        // Reduce fraction using GCD
        $num = $sixteenths;
        $den = 16;
        $a = $num; $b = $den;
        while ($b) { $t = $b; $b = $a % $b; $a = $t; }
        $gcd = $a;
        $num = $num / $gcd;
        $den = $den / $gcd;

        $frac = "{$num}/{$den}";
        return ($whole > 0 ? "{$whole} {$frac}" : $frac) . '"';
    }
}
$wLabel = _wd2_fracLabel($w);
$hLabel = _wd2_fracLabel($h);

// ─── COLOR THEME ───
$_colorParam = strtoupper(trim($color ?? 'WH'));
$_hexColor = $hexColor ?? null; // optional: pass a hex like '#8B4513' for custom color
$isDark = in_array($_colorParam, ['BLACK', 'BK', 'OBSIDIAN', 'BK-BK', 'BK-WH', 'WH-BK', 'DARK', 'BRONZE', 'BRZ']);
// If a hex color was provided, determine if it's dark based on luminance
if ($_hexColor && !$isDark) {
    $_hx = ltrim($_hexColor, '#');
    if (strlen($_hx) === 6) {
        $_lum = (0.299 * hexdec(substr($_hx,0,2)) + 0.587 * hexdec(substr($_hx,2,2)) + 0.114 * hexdec(substr($_hx,4,2))) / 255;
        $isDark = $_lum < 0.6;
    }
}

$maxPx = $maxSize ?? 250;
$hingeSide = $hinge ?? 'left';
$rawType = strtoupper(trim($type ?? 'PW'));
$hideDimensions = $noDimensions ?? false;
$_gridPattern = $gridPattern ?? '';
$_gridDetail = $gridDetail ?? '';

// ─── PARSE CUSTOM PANEL WIDTHS (proportional rendering) ───
$_mainWidths = !empty($mainWidths) ? array_map('floatval', explode(',', $mainWidths)) : [];
$_topWidths  = !empty($topWidths)  ? array_map('floatval', explode(',', $topWidths))  : [];
$_botWidths  = !empty($botWidths)  ? array_map('floatval', explode(',', $botWidths))  : [];
$_mainLabels = !empty($mainLabels) ? explode(',', $mainLabels) : [];
$_topLabels  = !empty($topLabels)  ? explode(',', $topLabels)  : [];
$_botLabels  = !empty($botLabels)  ? explode(',', $botLabels)  : [];
$_rowHeights = [];
if (!empty($rowHeights)) {
    $decoded = json_decode($rowHeights, true);
    if (is_array($decoded)) $_rowHeights = $decoded;
}

// ─── PARSE THE CONFIG CODE ───
// Split on first hyphen that separates prefix/suffix modifiers from the main body
// Examples: XO-B2T2 → main=XO, suffix=B2T2
//           OX       → main=OX, suffix=null
//           SL-OXXO-T4 → prefix=SL, main=OXXO, suffix=T4
//           AW2-T1B1 → main=AW2, suffix=T1B1

// Identify known prefixes
$prefixes = ['SL', 'SW', 'FSW', 'A'];
$prefix = null;
$mainBody = $rawType;
$suffix = '';

// Split by hyphens
$parts = explode('-', $rawType);

// Check if first part is a known prefix
if (count($parts) > 1 && in_array($parts[0], $prefixes)) {
    $prefix = array_shift($parts);
}

// The suffix parts are those matching B[n], T[n], M[n], BA, TA, TOCL, TOCR, BOX, BXO, etc at the end
// We work backwards from the parts to find suffix modifiers
$suffixParts = [];
$mainParts = [];
$foundMain = false;

foreach ($parts as $i => $part) {
    // Check if this part looks like a suffix modifier
    if (preg_match('/^[BTM]\d/', $part) || preg_match('/^[BTM]A/', $part) || in_array($part, ['TOCL', 'TOCR', '4V'])) {
        $suffixParts[] = $part;
    } else {
        $mainParts[] = $part;
    }
}

$mainBody = implode('-', $mainParts) ?: 'PW';
$suffix = implode('-', $suffixParts);

// Parse suffix for top/bottom/middle rows
$topCols = 0;
$bottomCols = 0;
$midCols = 0;
$hasTopArch = false;
$hasBottomArch = false;

if ($suffix) {
    if (preg_match('/T(\d+)/', $suffix, $m)) $topCols = (int)$m[1];
    if (preg_match('/B(\d+)/', $suffix, $m)) $bottomCols = (int)$m[1];
    if (preg_match('/M(\d+)/', $suffix, $m)) $midCols = (int)$m[1];
    if (str_contains($suffix, 'TA')) $hasTopArch = true;
    if (str_contains($suffix, 'BA')) $hasBottomArch = true;
}

// ─── PARSE MAIN BODY INTO PANEL SEQUENCE ───
// Each panel: ['type' => 'X'|'O'|'AW'|'CL'|'CR'|'SH'|'DH'|'PW'|'CM', 'count' => n, 'stack' => 'H'|'V'|null]

if (!function_exists('_wd_parse_panels')) {
    function _wd_parse_panels($body) {
        $panels = [];
        $len = strlen($body);
        $i = 0;
        
        $panelTypes = ['OXXO', 'OXO', 'AW', 'CL', 'CR', 'CM', 'SH', 'DH', 'PW', 'SL', 'SW', 'XO', 'OX', 'X', 'O', 'F'];
        
        while ($i < $len) {
            $matched = false;
            foreach ($panelTypes as $pt) {
                if (substr($body, $i, strlen($pt)) === $pt) {
                    $i += strlen($pt);
                    $count = 1;
                    $stack = null;
                    
                    // Check for trailing number
                    if ($i < $len && is_numeric($body[$i])) {
                        $numStr = '';
                        while ($i < $len && is_numeric($body[$i])) {
                            $numStr .= $body[$i];
                            $i++;
                        }
                        $count = (int)$numStr;
                    }
                    
                    // Check for H/V stack modifier
                    if ($i < $len && in_array($body[$i], ['H', 'V'])) {
                        $stack = $body[$i];
                        $i++;
                        // Could be another number after H/V like 2H3V
                        if ($i < $len && is_numeric($body[$i])) {
                            $i++; // skip
                        }
                    }
                    
                    // For compound types like OXXO, expand them
                    if ($pt === 'OXXO') {
                        $panels[] = ['type' => 'O', 'count' => 1, 'stack' => null];
                        $panels[] = ['type' => 'X', 'count' => 1, 'stack' => null];
                        $panels[] = ['type' => 'X', 'count' => 1, 'stack' => null];
                        $panels[] = ['type' => 'O', 'count' => 1, 'stack' => null];
                    } elseif ($pt === 'OXO') {
                        $panels[] = ['type' => 'O', 'count' => 1, 'stack' => null];
                        $panels[] = ['type' => 'X', 'count' => 1, 'stack' => null];
                        $panels[] = ['type' => 'O', 'count' => 1, 'stack' => null];
                    } elseif ($pt === 'XO') {
                        for ($c = 0; $c < $count; $c++) {
                            $panels[] = ['type' => 'X', 'count' => 1, 'stack' => $stack];
                            $panels[] = ['type' => 'O', 'count' => 1, 'stack' => $stack];
                        }
                    } elseif ($pt === 'OX') {
                        for ($c = 0; $c < $count; $c++) {
                            $panels[] = ['type' => 'O', 'count' => 1, 'stack' => $stack];
                            $panels[] = ['type' => 'X', 'count' => 1, 'stack' => $stack];
                        }
                    } else {
                        for ($c = 0; $c < $count; $c++) {
                            $panels[] = ['type' => $pt, 'count' => 1, 'stack' => $stack];
                        }
                    }
                    
                    $matched = true;
                    break;
                }
            }
            if (!$matched) $i++; // skip unknown char
        }
        
        return $panels ?: [['type' => 'PW', 'count' => 1, 'stack' => null]];
    }
}

$panels = _wd_parse_panels($mainBody);
$numCols = count($panels);

// Determine row structure
$hasTop = $topCols > 0 || $hasTopArch;
$hasBottom = $bottomCols > 0 || $hasBottomArch;
$hasMid = $midCols > 0;

// Row height ratios — use custom heights if provided, else default proportions
if (!empty($_rowHeights) && count($_rowHeights) > 1) {
    $totalCustomH = array_sum($_rowHeights);
    $topRatio = $hasTop ? (($_rowHeights['top'] ?? 0) / $totalCustomH) : 0;
    $bottomRatio = $hasBottom ? (($_rowHeights['bottom'] ?? 0) / $totalCustomH) : 0;
    $midRatio = $hasMid ? 0.08 : 0;
    $mainRatio = max(0.1, 1.0 - $topRatio - $bottomRatio - $midRatio);
} else {
    $topRatio = $hasTop ? 0.18 : 0;
    $bottomRatio = $hasBottom ? 0.18 : 0;
    $midRatio = $hasMid ? 0.08 : 0;
    $mainRatio = 1.0 - $topRatio - $bottomRatio - $midRatio;
}

// ─── SVG SIZING ───
$scale = min(($maxPx) / $w, ($maxPx) / $h, 7);
$_hasCustomDimsForSizing = !empty($_mainWidths) || !empty($_topWidths) || !empty($_botWidths);
$_hasMultiRowHeights = !empty($_rowHeights) && count($_rowHeights) > 1;
$pad = $_hasCustomDimsForSizing ? 50 : 40;
$_extraLeft = 0;  // row-height labels now rendered inside the frame — no extra left space needed
$_extraBottom = $_hasCustomDimsForSizing ? 20 : 0; // extra space for per-panel width labels below
$fd = 3.25 * $scale;  // frame depth
$sd = 2.0 * $scale;   // sash depth
$svgW = $w * $scale + $pad * 2 + $_extraLeft + ($_hasCustomDimsForSizing ? 44 : 24);
$svgH = $h * $scale + $pad * 2 + $_extraBottom + ($_hasCustomDimsForSizing ? 44 : 24);
$sw = $w * $scale;     // scaled width
$sh2 = $h * $scale;    // scaled height
$ox = $pad + $_extraLeft;  // origin x — shifted right to make room for left dimensions
$oy = $pad;            // origin y
$uid = 'wd' . substr(md5(uniqid(mt_rand(), true)), 0, 6);

// ─── HELPER FUNCTIONS ───
if (!function_exists('_wd2_frame')) {
    function _wd2_frame($x, $y, $w, $h, $side, $uid) {
        $grads = ['top' => 'vH', 'bottom' => 'vHR', 'left' => 'vV', 'right' => 'vVR'];
        $g = $grads[$side];
        $isH = in_array($side, ['top', 'bottom']);
        $out = "<g filter=\"url(#{$uid}-fs)\">";
        $out .= "<rect x=\"{$x}\" y=\"{$y}\" width=\"{$w}\" height=\"{$h}\" fill=\"url(#{$uid}-{$g})\" stroke=\"#C0C0BC\" stroke-width=\"0.6\" rx=\"0.6\"/>";
        if ($isH) {
            $ly1 = $y + $h * 0.45; $ly2 = $y + $h * 0.55; $lx1 = $x + 3; $lx2 = $x + $w - 3;
            $out .= "<line x1=\"{$lx1}\" y1=\"{$ly1}\" x2=\"{$lx2}\" y2=\"{$ly1}\" stroke=\"#D5D5D0\" stroke-width=\"0.3\" opacity=\"0.6\"/>";
            $out .= "<line x1=\"{$lx1}\" y1=\"{$ly2}\" x2=\"{$lx2}\" y2=\"{$ly2}\" stroke=\"#FFFFFF\" stroke-width=\"0.3\" opacity=\"0.7\"/>";
        } else {
            $lx1 = $x + $w * 0.45; $lx2 = $x + $w * 0.55; $ly1 = $y + 3; $ly2 = $y + $h - 3;
            $out .= "<line x1=\"{$lx1}\" y1=\"{$ly1}\" x2=\"{$lx1}\" y2=\"{$ly2}\" stroke=\"#D5D5D0\" stroke-width=\"0.3\" opacity=\"0.6\"/>";
            $out .= "<line x1=\"{$lx2}\" y1=\"{$ly1}\" x2=\"{$lx2}\" y2=\"{$ly2}\" stroke=\"#FFFFFF\" stroke-width=\"0.3\" opacity=\"0.7\"/>";
        }
        $out .= "</g>";
        return $out;
    }

    function _wd2_sash_rect($x, $y, $w, $h, $uid) {
        return "<rect x=\"{$x}\" y=\"{$y}\" width=\"{$w}\" height=\"{$h}\" fill=\"url(#{$uid}-sH)\" stroke=\"#C0C0BC\" stroke-width=\"0.4\" rx=\"0.4\"/>";
    }

    function _wd2_glass($x, $y, $w, $h, $uid, $gridPattern = '', $gridDetail = '') {
        if ($w < 2 || $h < 2) return '';
        $rx = $x + $w * 0.08; $rw = max($w * 0.04, 0.5);
        $out = "<rect x=\"{$x}\" y=\"{$y}\" width=\"{$w}\" height=\"{$h}\" fill=\"url(#{$uid}-glass)\" stroke=\"#5B9BD5\" stroke-width=\"0.4\"/>";
        $out .= "<rect x=\"{$rx}\" y=\"".($y+2)."\" width=\"{$rw}\" height=\"".max($h-4,1)."\" fill=\"rgba(255,255,255,0.25)\" rx=\"1\"/>";
        // Draw grid lines if grid pattern is set
        if ($gridPattern && $gridPattern !== 'None' && $gridPattern !== 'N/A') {
            $out .= _wd2_grid_lines($x, $y, $w, $h, $gridPattern, $gridDetail);
        }
        return $out;
    }

    /**
     * Draw grid lines on glass based on grid pattern
     */
    function _wd2_grid_lines($x, $y, $w, $h, $pattern, $detail = '') {
        $svg = '';
        $stroke = '#666666';
        $sw = 1;
        $pad = 1; // small inset from glass edge

        // Parse grid detail "2W4H" → cols=2, rows=4
        $gCols = 2; $gRows = 2; // defaults
        if ($detail && preg_match('/(\d+)\s*W\s*(\d+)\s*H/i', $detail, $m)) {
            $gCols = max(1, intval($m[1]));
            $gRows = max(1, intval($m[2]));
        }

        switch ($pattern) {
            case 'Colonial':
                // Full grid: vertical and horizontal lines dividing glass into gCols × gRows
                for ($i = 1; $i < $gCols; $i++) {
                    $lx = $x + $pad + ($w - $pad*2) * $i / $gCols;
                    $svg .= "<line x1=\"{$lx}\" y1=\"".($y+$pad)."\" x2=\"{$lx}\" y2=\"".($y+$h-$pad)."\" stroke=\"{$stroke}\" stroke-width=\"{$sw}\"/>";
                }
                for ($i = 1; $i < $gRows; $i++) {
                    $ly = $y + $pad + ($h - $pad*2) * $i / $gRows;
                    $svg .= "<line x1=\"".($x+$pad)."\" y1=\"{$ly}\" x2=\"".($x+$w-$pad)."\" y2=\"{$ly}\" stroke=\"{$stroke}\" stroke-width=\"{$sw}\"/>";
                }
                break;

            case 'Marginal-12':
            case 'Marginal-18':
                // Perimeter grid: lines near top and bottom edges only
                $margin = ($pattern === 'Marginal-12') ? $h * 0.15 : $h * 0.22;
                // Top horizontal
                $svg .= "<line x1=\"".($x+$pad)."\" y1=\"".($y+$margin)."\" x2=\"".($x+$w-$pad)."\" y2=\"".($y+$margin)."\" stroke=\"{$stroke}\" stroke-width=\"{$sw}\"/>";
                // Bottom horizontal
                $svg .= "<line x1=\"".($x+$pad)."\" y1=\"".($y+$h-$margin)."\" x2=\"".($x+$w-$pad)."\" y2=\"".($y+$h-$margin)."\" stroke=\"{$stroke}\" stroke-width=\"{$sw}\"/>";
                // Left vertical
                $svg .= "<line x1=\"".($x+$margin*$w/$h)."\" y1=\"".($y+$pad)."\" x2=\"".($x+$margin*$w/$h)."\" y2=\"".($y+$h-$pad)."\" stroke=\"{$stroke}\" stroke-width=\"{$sw}\"/>";
                // Right vertical
                $svg .= "<line x1=\"".($x+$w-$margin*$w/$h)."\" y1=\"".($y+$pad)."\" x2=\"".($x+$w-$margin*$w/$h)."\" y2=\"".($y+$h-$pad)."\" stroke=\"{$stroke}\" stroke-width=\"{$sw}\"/>";
                break;

            case 'Queen':
                // Queen Anne: horizontal line near top, vertical lines in upper portion
                $topLine = $y + $h * 0.3;
                $svg .= "<line x1=\"".($x+$pad)."\" y1=\"{$topLine}\" x2=\"".($x+$w-$pad)."\" y2=\"{$topLine}\" stroke=\"{$stroke}\" stroke-width=\"{$sw}\"/>";
                // 3 vertical lines in upper section
                for ($i = 1; $i <= 3; $i++) {
                    $lx = $x + $pad + ($w - $pad*2) * $i / 4;
                    $svg .= "<line x1=\"{$lx}\" y1=\"".($y+$pad)."\" x2=\"{$lx}\" y2=\"{$topLine}\" stroke=\"{$stroke}\" stroke-width=\"{$sw}\"/>";
                }
                break;
        }
        return $svg;
    }

    function _wd2_mullion_v($x, $y, $h, $uid) {
        return "<rect x=\"".($x-1.2)."\" y=\"{$y}\" width=\"2.4\" height=\"{$h}\" fill=\"url(#{$uid}-vV)\" stroke=\"#C0C0BC\" stroke-width=\"0.4\"/>";
    }

    function _wd2_mullion_h($x, $y, $w, $uid) {
        return "<rect x=\"{$x}\" y=\"".($y-1.2)."\" width=\"{$w}\" height=\"2.4\" fill=\"url(#{$uid}-vH)\" stroke=\"#C0C0BC\" stroke-width=\"0.4\"/>";
    }

    function _wd2_arrow_left($cx, $cy, $sz = 10) {
        return "<g opacity=\"0.5\"><line x1=\"".($cx+$sz)."\" y1=\"{$cy}\" x2=\"".($cx-$sz)."\" y2=\"{$cy}\" stroke=\"#555\" stroke-width=\"0.8\"/>"
             . "<polygon points=\"".($cx-$sz).",{$cy} ".($cx-$sz+4).",".($cy-2)." ".($cx-$sz+4).",".($cy+2)."\" fill=\"#555\"/></g>";
    }

    function _wd2_arrow_right($cx, $cy, $sz = 10) {
        return "<g opacity=\"0.5\"><line x1=\"".($cx-$sz)."\" y1=\"{$cy}\" x2=\"".($cx+$sz)."\" y2=\"{$cy}\" stroke=\"#555\" stroke-width=\"0.8\"/>"
             . "<polygon points=\"".($cx+$sz).",{$cy} ".($cx+$sz-4).",".($cy-2)." ".($cx+$sz-4).",".($cy+2)."\" fill=\"#555\"/></g>";
    }

    function _wd2_arrow_up($cx, $cy, $sz = 10) {
        return "<g opacity=\"0.5\"><line x1=\"{$cx}\" y1=\"".($cy+$sz)."\" x2=\"{$cx}\" y2=\"".($cy-$sz)."\" stroke=\"#555\" stroke-width=\"0.8\"/>"
             . "<polygon points=\"{$cx},".($cy-$sz)." ".($cx-2).",".($cy-$sz+4)." ".($cx+2).",".($cy-$sz+4)."\" fill=\"#555\"/></g>";
    }

    function _wd2_handle($x, $y, $vert = true) {
        if ($vert) return "<rect x=\"".($x-1.2)."\" y=\"".($y-5)."\" width=\"2.4\" height=\"10\" rx=\"1.2\" fill=\"#999\" stroke=\"#AAA\" stroke-width=\"0.3\"/>";
        return "<rect x=\"".($x-5)."\" y=\"".($y-1.2)."\" width=\"10\" height=\"2.4\" rx=\"1.2\" fill=\"#999\" stroke=\"#AAA\" stroke-width=\"0.3\"/>";
    }

    function _wd2_casement_lines($x, $y, $w, $h, $side) {
        $out = "<g opacity=\"0.35\" stroke=\"#777\" stroke-width=\"0.5\" stroke-dasharray=\"4,3\">";
        if ($side === 'left') {
            $out .= "<line x1=\"{$x}\" y1=\"{$y}\" x2=\"".($x+$w)."\" y2=\"".($y+$h/2)."\"/>";
            $out .= "<line x1=\"{$x}\" y1=\"".($y+$h)."\" x2=\"".($x+$w)."\" y2=\"".($y+$h/2)."\"/>";
        } else {
            $out .= "<line x1=\"".($x+$w)."\" y1=\"{$y}\" x2=\"{$x}\" y2=\"".($y+$h/2)."\"/>";
            $out .= "<line x1=\"".($x+$w)."\" y1=\"".($y+$h)."\" x2=\"{$x}\" y2=\"".($y+$h/2)."\"/>";
        }
        $out .= "</g>";
        return $out;
    }

    function _wd2_awning_lines($x, $y, $w, $h) {
        return "<g opacity=\"0.35\" stroke=\"#777\" stroke-width=\"0.5\" stroke-dasharray=\"4,3\">"
             . "<line x1=\"{$x}\" y1=\"{$y}\" x2=\"".($x+$w/2)."\" y2=\"".($y+$h)."\"/>"
             . "<line x1=\"".($x+$w)."\" y1=\"{$y}\" x2=\"".($x+$w/2)."\" y2=\"".($y+$h)."\"/>"
             . "</g>";
    }

    function _wd2_hung_panel($x, $y, $w, $h, $type, $sd, $uid) {
        // Draw a hung window (top sash + bottom sash with meeting rail)
        $topH = $h / 2;
        $gap = 1;
        $svg = '';
        // Top sash glass
        $svg .= _wd2_glass($x + $sd + $gap, $y + $sd + $gap, $w - $sd*2 - $gap*2, $topH - $sd - $gap*2, $uid);
        // Top sash frame
        $svg .= _wd2_sash_rect($x + $gap, $y + $gap, $w - $gap*2, $sd, $uid); // top
        $svg .= _wd2_sash_rect($x + $gap, $y + $sd + $gap, $sd, $topH - $sd - $gap*2, $uid); // left
        $svg .= _wd2_sash_rect($x + $w - $sd - $gap, $y + $sd + $gap, $sd, $topH - $sd - $gap*2, $uid); // right
        
        // Meeting rail
        $svg .= "<rect x=\"{$x}\" y=\"".($y + $topH - 1.2)."\" width=\"{$w}\" height=\"2.4\" fill=\"#F5F5F3\" stroke=\"#C0C0BC\" stroke-width=\"0.4\" rx=\"0.2\"/>";
        
        // Bottom sash glass
        $svg .= _wd2_glass($x + $sd + $gap, $y + $topH + $gap + 1, $w - $sd*2 - $gap*2, $topH - $sd - $gap*2, $uid);
        // Bottom sash frame
        $svg .= _wd2_sash_rect($x + $gap, $y + $h - $sd - $gap, $w - $gap*2, $sd, $uid); // bottom
        $svg .= _wd2_sash_rect($x + $gap, $y + $topH + $gap + 1, $sd, $topH - $sd - $gap*2, $uid); // left
        $svg .= _wd2_sash_rect($x + $w - $sd - $gap, $y + $topH + $gap + 1, $sd, $topH - $sd - $gap*2, $uid); // right
        
        // Arrow on bottom sash
        $svg .= _wd2_arrow_up($x + $w/2, $y + $topH + $topH/2, min($topH * 0.15, 8));
        $svg .= _wd2_handle($x + $w/2, $y + $topH + $sd/2 + 2, false);
        
        if ($type === 'DH') {
            $svg .= _wd2_arrow_up($x + $w/2, $y + $topH/2 + 2, min($topH * 0.1, 6));
        }
        return $svg;
    }

    /**
     * Draw a single panel cell
     */
    function _wd2_panel($x, $y, $w, $h, $panelType, $sd, $uid, $hingeSide = 'left', $gridPattern = '', $gridDetail = '') {
        $svg = '';
        $gap = 1;

        switch ($panelType) {
            case 'X': // Operable slider sash
                // Sash frame
                $svg .= _wd2_sash_rect($x + $gap, $y + $gap, $w - $gap*2, $sd, $uid);
                $svg .= _wd2_sash_rect($x + $gap, $y + $h - $sd - $gap, $w - $gap*2, $sd, $uid);
                $svg .= _wd2_sash_rect($x + $gap, $y + $sd + $gap, $sd, $h - $sd*2 - $gap*2, $uid);
                $svg .= _wd2_sash_rect($x + $w - $sd - $gap, $y + $sd + $gap, $sd, $h - $sd*2 - $gap*2, $uid);
                // Glass
                $svg .= _wd2_glass($x + $sd + $gap + 1, $y + $sd + $gap + 1, $w - $sd*2 - $gap*2 - 2, $h - $sd*2 - $gap*2 - 2, $uid, $gridPattern, $gridDetail);
                // Arrow indicating sliding direction
                $arrowSz = min($w * 0.15, 10);
                $svg .= _wd2_arrow_left($x + $w/2, $y + $h/2, $arrowSz);
                $svg .= _wd2_handle($x + $sd + 3, $y + $h/2, true);
                break;

            case 'O': // Fixed panel
            case 'PW':
            case 'F':
                $svg .= _wd2_glass($x + $gap, $y + $gap, $w - $gap*2, $h - $gap*2, $uid, $gridPattern, $gridDetail);
                break;

            case 'AW': // Awning
                $svg .= _wd2_sash_rect($x + $gap, $y + $gap, $w - $gap*2, $sd, $uid);
                $svg .= _wd2_sash_rect($x + $gap, $y + $h - $sd - $gap, $w - $gap*2, $sd, $uid);
                $svg .= _wd2_sash_rect($x + $gap, $y + $sd + $gap, $sd, $h - $sd*2 - $gap*2, $uid);
                $svg .= _wd2_sash_rect($x + $w - $sd - $gap, $y + $sd + $gap, $sd, $h - $sd*2 - $gap*2, $uid);
                $svg .= _wd2_glass($x + $sd + $gap + 1, $y + $sd + $gap + 1, $w - $sd*2 - $gap*2 - 2, $h - $sd*2 - $gap*2 - 2, $uid, $gridPattern, $gridDetail);
                $svg .= _wd2_awning_lines($x, $y, $w, $h);
                $svg .= _wd2_handle($x + $w/2, $y + $h - $sd/2 - $gap, false);
                break;

            case 'CL': // Casement left-hinged
                $svg .= _wd2_sash_rect($x + $gap, $y + $gap, $w - $gap*2, $sd, $uid);
                $svg .= _wd2_sash_rect($x + $gap, $y + $h - $sd - $gap, $w - $gap*2, $sd, $uid);
                $svg .= _wd2_sash_rect($x + $gap, $y + $sd + $gap, $sd, $h - $sd*2 - $gap*2, $uid);
                $svg .= _wd2_sash_rect($x + $w - $sd - $gap, $y + $sd + $gap, $sd, $h - $sd*2 - $gap*2, $uid);
                $svg .= _wd2_glass($x + $sd + $gap + 1, $y + $sd + $gap + 1, $w - $sd*2 - $gap*2 - 2, $h - $sd*2 - $gap*2 - 2, $uid, $gridPattern, $gridDetail);
                $svg .= _wd2_casement_lines($x, $y, $w, $h, 'left');
                $svg .= _wd2_handle($x + $w - $sd/2 - $gap, $y + $h/2, true);
                break;

            case 'CR': // Casement right-hinged
                $svg .= _wd2_sash_rect($x + $gap, $y + $gap, $w - $gap*2, $sd, $uid);
                $svg .= _wd2_sash_rect($x + $gap, $y + $h - $sd - $gap, $w - $gap*2, $sd, $uid);
                $svg .= _wd2_sash_rect($x + $gap, $y + $sd + $gap, $sd, $h - $sd*2 - $gap*2, $uid);
                $svg .= _wd2_sash_rect($x + $w - $sd - $gap, $y + $sd + $gap, $sd, $h - $sd*2 - $gap*2, $uid);
                $svg .= _wd2_glass($x + $sd + $gap + 1, $y + $sd + $gap + 1, $w - $sd*2 - $gap*2 - 2, $h - $sd*2 - $gap*2 - 2, $uid, $gridPattern, $gridDetail);
                $svg .= _wd2_casement_lines($x, $y, $w, $h, 'right');
                $svg .= _wd2_handle($x + $sd/2 + $gap, $y + $h/2, true);
                break;

            case 'CM': // Casement (uses hinge param)
                $svg .= _wd2_sash_rect($x + $gap, $y + $gap, $w - $gap*2, $sd, $uid);
                $svg .= _wd2_sash_rect($x + $gap, $y + $h - $sd - $gap, $w - $gap*2, $sd, $uid);
                $svg .= _wd2_sash_rect($x + $gap, $y + $sd + $gap, $sd, $h - $sd*2 - $gap*2, $uid);
                $svg .= _wd2_sash_rect($x + $w - $sd - $gap, $y + $sd + $gap, $sd, $h - $sd*2 - $gap*2, $uid);
                $svg .= _wd2_glass($x + $sd + $gap + 1, $y + $sd + $gap + 1, $w - $sd*2 - $gap*2 - 2, $h - $sd*2 - $gap*2 - 2, $uid, $gridPattern, $gridDetail);
                $svg .= _wd2_casement_lines($x, $y, $w, $h, $hingeSide);
                $hx = $hingeSide == 'left' ? $x + $w - $sd/2 - $gap : $x + $sd/2 + $gap;
                $svg .= _wd2_handle($hx, $y + $h/2, true);
                break;

            case 'SH': // Single hung
                $svg .= _wd2_hung_panel($x, $y, $w, $h, 'SH', $sd, $uid);
                break;

            case 'DH': // Double hung
                $svg .= _wd2_hung_panel($x, $y, $w, $h, 'DH', $sd, $uid);
                break;

            default: // Fallback = fixed glass
                $svg .= _wd2_glass($x + $gap, $y + $gap, $w - $gap*2, $h - $gap*2, $uid);
                break;
        }
        return $svg;
    }

    function _wd2_dimH($x1, $x2, $y, $label) {
        $dy = $y + 16; $mid = ($x1 + $x2) / 2;
        return "<g opacity=\"0.65\"><line x1=\"{$x1}\" y1=\"".($y+2)."\" x2=\"{$x1}\" y2=\"".($dy+2)."\" stroke=\"#555\" stroke-width=\"0.35\"/><line x1=\"{$x2}\" y1=\"".($y+2)."\" x2=\"{$x2}\" y2=\"".($dy+2)."\" stroke=\"#555\" stroke-width=\"0.35\"/><line x1=\"".($x1+2)."\" y1=\"{$dy}\" x2=\"".($x2-2)."\" y2=\"{$dy}\" stroke=\"#555\" stroke-width=\"0.5\"/><polygon points=\"{$x1},{$dy} ".($x1+4).",".($dy-1.5)." ".($x1+4).",".($dy+1.5)."\" fill=\"#555\"/><polygon points=\"{$x2},{$dy} ".($x2-4).",".($dy-1.5)." ".($x2-4).",".($dy+1.5)."\" fill=\"#555\"/><text x=\"{$mid}\" y=\"".($dy+11)."\" text-anchor=\"middle\" font-size=\"9\" font-family=\"Segoe UI, system-ui\" font-weight=\"600\" fill=\"#444\">{$label}</text></g>";
    }

    function _wd2_dimV($x, $y1, $y2, $label) {
        $dx = $x + 16; $mid = ($y1 + $y2) / 2;
        return "<g opacity=\"0.65\"><line x1=\"".($x+2)."\" y1=\"{$y1}\" x2=\"".($dx+2)."\" y2=\"{$y1}\" stroke=\"#555\" stroke-width=\"0.35\"/><line x1=\"".($x+2)."\" y1=\"{$y2}\" x2=\"".($dx+2)."\" y2=\"{$y2}\" stroke=\"#555\" stroke-width=\"0.35\"/><line x1=\"{$dx}\" y1=\"".($y1+2)."\" x2=\"{$dx}\" y2=\"".($y2-2)."\" stroke=\"#555\" stroke-width=\"0.5\"/><polygon points=\"{$dx},{$y1} ".($dx-1.5).",".($y1+4)." ".($dx+1.5).",".($y1+4)."\" fill=\"#555\"/><polygon points=\"{$dx},{$y2} ".($dx-1.5).",".($y2-4)." ".($dx+1.5).",".($y2-4)."\" fill=\"#555\"/><text x=\"".($dx+5)."\" y=\"{$mid}\" text-anchor=\"start\" font-size=\"9\" font-family=\"Segoe UI, system-ui\" font-weight=\"600\" fill=\"#444\" transform=\"rotate(-90, ".($dx+5).", {$mid})\">{$label}</text></g>";
    }
}

// ─── BUILD SVG ───
$svg = '';

// Outer frame
$svg .= _wd2_frame($ox, $oy, $sw, $fd, 'top', $uid);
$svg .= _wd2_frame($ox, $oy + $sh2 - $fd, $sw, $fd, 'bottom', $uid);
$svg .= _wd2_frame($ox, $oy + $fd, $fd, $sh2 - $fd * 2, 'left', $uid);
$svg .= _wd2_frame($ox + $sw - $fd, $oy + $fd, $fd, $sh2 - $fd * 2, 'right', $uid);

// Inner area
$ix = $ox + $fd;
$iy = $oy + $fd;
$iw = $sw - $fd * 2;
$ih = $sh2 - $fd * 2;

// Calculate row heights
$topH = $hasTop ? $ih * $topRatio : 0;
$bottomH = $hasBottom ? $ih * $bottomRatio : 0;
$midH = $hasMid ? $ih * $midRatio : 0;
$mainH = $ih - $topH - $bottomH - $midH;

$mullionThick = 2.4;

// ─── PROPORTIONAL WIDTH HELPER ───
// Converts an array of inch widths to pixel widths that fill the inner width ($iw)
$_propWidths = function(array $inchWidths, int $colCount, float $totalPixW) use ($iw) {
    if (empty($inchWidths) || count($inchWidths) !== $colCount) {
        // Equal division fallback
        $w = $totalPixW / max($colCount, 1);
        return array_fill(0, $colCount, $w);
    }
    $totalInch = array_sum($inchWidths);
    if ($totalInch <= 0) {
        $w = $totalPixW / max($colCount, 1);
        return array_fill(0, $colCount, $w);
    }
    return array_map(function($iw) use ($totalInch, $totalPixW) {
        return ($iw / $totalInch) * $totalPixW;
    }, $inchWidths);
};

// ─── TOP TRANSOM ROW ───
if ($hasTop) {
    $tCols = $topCols > 0 ? $topCols : $numCols;
    $_topColWidths = $_propWidths($_topWidths, $tCols, $iw);
    $_topCx = $ix;
    for ($c = 0; $c < $tCols; $c++) {
        $colW = $_topColWidths[$c];
        $cx = $_topCx;
        $_topCx += $colW;
        $svg .= _wd2_glass($cx + 1, $iy + 1, $colW - 2, $topH - 2, $uid);
        if ($c > 0) {
            $svg .= _wd2_mullion_v($cx, $iy, $topH, $uid);
        }
        // Per-panel width label
        if (count($_topWidths) === $tCols && !$hideDimensions) {
            $dimLabel = _wd2_fracLabel($_topWidths[$c]);
            $svg .= "<text x=\"".($cx + $colW/2)."\" y=\"".($iy + $topH - 4)."\" text-anchor=\"middle\" font-size=\"7\" fill=\"#64748b\" opacity=\"0.8\">{$dimLabel}</text>";
        }
    }
    // Horizontal mullion between top and main
    $svg .= _wd2_mullion_h($ix, $iy + $topH, $iw, $uid);
}

// ─── MAIN PANEL ROW ───
$mainY = $iy + $topH + ($hasTop ? $mullionThick/2 : 0);
$mainAvailH = $mainH - ($hasTop ? $mullionThick/2 : 0) - ($hasBottom ? $mullionThick/2 : 0);

if ($numCols > 0) {
    $_mainColWidths = $_propWidths($_mainWidths, $numCols, $iw);
    $_mainCx = $ix;
    for ($c = 0; $c < $numCols; $c++) {
        $colW = $_mainColWidths[$c];
        $cx = $_mainCx;
        $_mainCx += $colW;
        $panelType = $panels[$c]['type'] ?? 'O';
        $svg .= _wd2_panel($cx, $mainY, $colW, $mainAvailH, $panelType, $sd, $uid, $hingeSide, $_gridPattern, $_gridDetail);

        // Draw vertical mullion between panels
        if ($c > 0) {
            // Draw interlock for sliders (X next to O or O next to X)
            $prevType = $panels[$c-1]['type'] ?? 'O';
            $currType = $panelType;
            if (($prevType === 'X' || $currType === 'X') && ($prevType === 'O' || $currType === 'O' || $prevType === 'X' || $currType === 'X')) {
                $svg .= "<rect x=\"".($cx - 1)."\" y=\"{$mainY}\" width=\"2\" height=\"{$mainAvailH}\" fill=\"#F5F5F3\" stroke=\"#C0C0BC\" stroke-width=\"0.4\"/>";
            } else {
                $svg .= _wd2_mullion_v($cx, $mainY, $mainAvailH, $uid);
            }
        }
        // Per-panel width label (only if custom widths provided)
        if (count($_mainWidths) === $numCols && !$hideDimensions) {
            $dimLabel = _wd2_fracLabel($_mainWidths[$c]);
            $svg .= "<text x=\"".($cx + $colW/2)."\" y=\"".($mainY + $mainAvailH - 4)."\" text-anchor=\"middle\" font-size=\"8\" fill=\"#1e40af\" font-weight=\"bold\" opacity=\"0.7\">{$dimLabel}</text>";
        }
    }
}

// ─── MIDDLE MULLION ROW (if M modifier) ───
if ($hasMid) {
    $midY = $mainY + $mainAvailH;
    $svg .= _wd2_mullion_h($ix, $midY, $iw, $uid);
}

// ─── BOTTOM KICK PANEL ROW ───
if ($hasBottom) {
    $bottomY = $iy + $ih - $bottomH;
    $bCols = $bottomCols > 0 ? $bottomCols : $numCols;
    $_botColWidths = $_propWidths($_botWidths, $bCols, $iw);
    // Horizontal mullion between main and bottom
    $svg .= _wd2_mullion_h($ix, $bottomY, $iw, $uid);
    $_botCx = $ix;
    for ($c = 0; $c < $bCols; $c++) {
        $colW = $_botColWidths[$c];
        $cx = $_botCx;
        $_botCx += $colW;
        $svg .= _wd2_glass($cx + 1, $bottomY + 2, $colW - 2, $bottomH - 3, $uid);
        if ($c > 0) {
            $svg .= _wd2_mullion_v($cx, $bottomY, $bottomH, $uid);
        }
        // Per-panel width label
        if (count($_botWidths) === $bCols && !$hideDimensions) {
            $dimLabel = _wd2_fracLabel($_botWidths[$c]);
            $svg .= "<text x=\"".($cx + $colW/2)."\" y=\"".($bottomY + $bottomH - 4)."\" text-anchor=\"middle\" font-size=\"7\" fill=\"#64748b\" opacity=\"0.8\">{$dimLabel}</text>";
        }
    }
}

// ─── FIELD LABELS / NUMBERS ───
// Show panel type labels (X, O, XR, XFIX, AW, etc.) inside each panel.
// If explicit labels are provided via mainLabels/topLabels/botLabels, use those;
// otherwise auto-generate from parsed panel types.
$_autoMainLabels = [];
foreach ($panels as $p) {
    $t = $p['type'] ?? 'O';
    // Map panel types to display labels
    $labelMap = ['X' => 'X', 'O' => 'O', 'AW' => 'AW', 'CL' => 'CL', 'CR' => 'CR', 'CM' => 'CM',
                 'SH' => 'SH', 'DH' => 'DH', 'PW' => 'PW', 'SL' => 'SL', 'SW' => 'SW', 'F' => 'FIX'];
    $_autoMainLabels[] = $labelMap[$t] ?? $t;
}

$fieldNum = 1;
// Main panels
if ($numCols > 0) {
    $_fnMainW = $_propWidths($_mainWidths, $numCols, $iw);
    $_fnCx = $ix;
    for ($c = 0; $c < $numCols; $c++) {
        $colW = $_fnMainW[$c];
        $cx = $_fnCx + $colW / 2;
        $_fnCx += $colW;
        $cy = $mainY + $mainAvailH / 2;
        if (isset($_mainLabels[$c]) && $_mainLabels[$c]) {
            $label = $_mainLabels[$c];
        } elseif (isset($_autoMainLabels[$c])) {
            $label = $_autoMainLabels[$c];
        } else {
            $label = $fieldNum;
        }
        $svg .= "<text x=\"{$cx}\" y=\"".($cy + 3)."\" text-anchor=\"middle\" font-size=\"".min(max($colW * 0.2, 8), 16)."\" font-family=\"Arial, sans-serif\" font-weight=\"bold\" fill=\"#1565C0\" opacity=\"0.75\">{$label}</text>";
        $fieldNum++;
    }
}
// Top panels
if ($hasTop) {
    $tCols = $topCols > 0 ? $topCols : $numCols;
    $_fnTopW = $_propWidths($_topWidths, $tCols, $iw);
    $_fnCx = $ix;
    for ($c = 0; $c < $tCols; $c++) {
        $colW = $_fnTopW[$c];
        $cx = $_fnCx + $colW / 2;
        $_fnCx += $colW;
        $cy = $iy + $topH / 2;
        if (isset($_topLabels[$c]) && $_topLabels[$c]) {
            $label = $_topLabels[$c];
        } else {
            $label = 'T' . ($c + 1);
        }
        $svg .= "<text x=\"{$cx}\" y=\"".($cy + 3)."\" text-anchor=\"middle\" font-size=\"".min(max($colW * 0.18, 7), 13)."\" font-family=\"Arial, sans-serif\" font-weight=\"bold\" fill=\"#1565C0\" opacity=\"0.75\">{$label}</text>";
        $fieldNum++;
    }
}
// Bottom panels
if ($hasBottom) {
    $bCols = $bottomCols > 0 ? $bottomCols : $numCols;
    $_fnBotW = $_propWidths($_botWidths, $bCols, $iw);
    $bottomY = $iy + $ih - $bottomH;
    $_fnCx = $ix;
    for ($c = 0; $c < $bCols; $c++) {
        $colW = $_fnBotW[$c];
        $cx = $_fnCx + $colW / 2;
        $_fnCx += $colW;
        $cy = $bottomY + $bottomH / 2;
        if (isset($_botLabels[$c]) && $_botLabels[$c]) {
            $label = $_botLabels[$c];
        } else {
            $label = 'B' . ($c + 1);
        }
        $svg .= "<text x=\"{$cx}\" y=\"".($cy + 3)."\" text-anchor=\"middle\" font-size=\"".min(max($colW * 0.18, 7), 13)."\" font-family=\"Arial, sans-serif\" font-weight=\"bold\" fill=\"#1565C0\" opacity=\"0.75\">{$label}</text>";
        $fieldNum++;
    }
}

// ─── PER-PANEL DIMENSION LINES (when custom widths set) ───
// These show individual panel width dimensions below, similar to a blueprint
$_hasCustomDims = !empty($_mainWidths) || !empty($_topWidths) || !empty($_botWidths);
if ($_hasCustomDims && !$hideDimensions) {
    $dimArrowSz = 3;
    $dimStroke = '#1B4F72';

    // Main panel width dimensions (below the main row)
    if (!empty($_mainWidths) && count($_mainWidths) === $numCols && $numCols > 1) {
        $_dmW = $_propWidths($_mainWidths, $numCols, $iw);
        $_dmCx = $ix;
        $_dmY = $oy + $sh2 + 28; // below the overall width dimension
        for ($c = 0; $c < $numCols; $c++) {
            $colW = $_dmW[$c];
            $x1 = $_dmCx; $x2 = $_dmCx + $colW;
            // Extension lines
            $svg .= "<line x1=\"{$x1}\" y1=\"".($oy + $sh2 + 2)."\" x2=\"{$x1}\" y2=\"".($_dmY + 3)."\" stroke=\"{$dimStroke}\" stroke-width=\"0.3\" stroke-dasharray=\"1,1\" opacity=\"0.5\"/>";
            $svg .= "<line x1=\"{$x2}\" y1=\"".($oy + $sh2 + 2)."\" x2=\"{$x2}\" y2=\"".($_dmY + 3)."\" stroke=\"{$dimStroke}\" stroke-width=\"0.3\" stroke-dasharray=\"1,1\" opacity=\"0.5\"/>";
            // Dimension line with arrows
            if ($colW > 20) {
                $svg .= "<line x1=\"".($x1 + $dimArrowSz)."\" y1=\"{$_dmY}\" x2=\"".($x2 - $dimArrowSz)."\" y2=\"{$_dmY}\" stroke=\"{$dimStroke}\" stroke-width=\"0.5\" opacity=\"0.7\"/>";
                $svg .= "<polygon points=\"{$x1},{$_dmY} ".($x1+$dimArrowSz).",".($_dmY-1.5)." ".($x1+$dimArrowSz).",".($_dmY+1.5)."\" fill=\"{$dimStroke}\" opacity=\"0.7\"/>";
                $svg .= "<polygon points=\"{$x2},{$_dmY} ".($x2-$dimArrowSz).",".($_dmY-1.5)." ".($x2-$dimArrowSz).",".($_dmY+1.5)."\" fill=\"{$dimStroke}\" opacity=\"0.7\"/>";
            }
            // Label
            $dimLabel = _wd2_fracLabel($_mainWidths[$c]);
            $svg .= "<text x=\"".($x1 + $colW/2)."\" y=\"".($_dmY + 10)."\" text-anchor=\"middle\" font-size=\"8\" font-weight=\"600\" fill=\"{$dimStroke}\" opacity=\"0.8\">{$dimLabel}</text>";
            $_dmCx += $colW;
        }
    }

    // Row height dimensions — rendered INSIDE the left frame member as overlaid labels
    // This prevents any clipping by parent containers with overflow:hidden
    if (!empty($_rowHeights) && count($_rowHeights) > 1) {
        $_lhX = $ox + $fd + 4; // just inside the left frame edge
        if (isset($_rowHeights['top']) && $hasTop) {
            $ry1 = $oy; $ry2 = $oy + $topH * ($sh2 / $ih);
            $dimLabel = _wd2_fracLabel($_rowHeights['top']);
            if (($ry2 - $ry1) > 15) {
                $svg .= "<line x1=\"{$_lhX}\" y1=\"".($ry1 + $fd + 2)."\" x2=\"{$_lhX}\" y2=\"".($ry2 - 2)."\" stroke=\"{$dimStroke}\" stroke-width=\"0.6\" opacity=\"0.5\"/>";
                $svg .= "<polygon points=\"{$_lhX},".($ry1+$fd+2)." ".($_lhX-1.5).",".($ry1+$fd+2+$dimArrowSz)." ".($_lhX+1.5).",".($ry1+$fd+2+$dimArrowSz)."\" fill=\"{$dimStroke}\" opacity=\"0.6\"/>";
                $svg .= "<polygon points=\"{$_lhX},".($ry2-2)." ".($_lhX-1.5).",".($ry2-2-$dimArrowSz)." ".($_lhX+1.5).",".($ry2-2-$dimArrowSz)."\" fill=\"{$dimStroke}\" opacity=\"0.6\"/>";
                $mid = ($ry1 + $ry2) / 2;
                $svg .= "<rect x=\"".($_lhX - 2)."\" y=\"".($mid - 16)."\" width=\"14\" height=\"32\" rx=\"2\" fill=\"#fff\" opacity=\"0.85\"/>";
                $svg .= "<text x=\"{$_lhX}\" y=\"{$mid}\" text-anchor=\"middle\" font-size=\"7.5\" font-weight=\"700\" fill=\"{$dimStroke}\" opacity=\"0.9\" transform=\"rotate(-90,{$_lhX},{$mid})\">{$dimLabel}</text>";
            }
        }
        if (isset($_rowHeights['main'])) {
            $ry1 = $oy + ($hasTop ? $topH * ($sh2 / $ih) : 0);
            $ry2 = $oy + $sh2 - ($hasBottom ? $bottomH * ($sh2 / $ih) : 0);
            $dimLabel = _wd2_fracLabel($_rowHeights['main']);
            if (($ry2 - $ry1) > 15) {
                $svg .= "<line x1=\"{$_lhX}\" y1=\"".($ry1 + 2)."\" x2=\"{$_lhX}\" y2=\"".($ry2 - 2)."\" stroke=\"{$dimStroke}\" stroke-width=\"0.6\" opacity=\"0.5\"/>";
                $svg .= "<polygon points=\"{$_lhX},".($ry1+2)." ".($_lhX-1.5).",".($ry1+2+$dimArrowSz)." ".($_lhX+1.5).",".($ry1+2+$dimArrowSz)."\" fill=\"{$dimStroke}\" opacity=\"0.6\"/>";
                $svg .= "<polygon points=\"{$_lhX},".($ry2-2)." ".($_lhX-1.5).",".($ry2-2-$dimArrowSz)." ".($_lhX+1.5).",".($ry2-2-$dimArrowSz)."\" fill=\"{$dimStroke}\" opacity=\"0.6\"/>";
                $mid = ($ry1 + $ry2) / 2;
                $svg .= "<rect x=\"".($_lhX - 2)."\" y=\"".($mid - 16)."\" width=\"14\" height=\"32\" rx=\"2\" fill=\"#fff\" opacity=\"0.85\"/>";
                $svg .= "<text x=\"{$_lhX}\" y=\"{$mid}\" text-anchor=\"middle\" font-size=\"7.5\" font-weight=\"700\" fill=\"{$dimStroke}\" opacity=\"0.9\" transform=\"rotate(-90,{$_lhX},{$mid})\">{$dimLabel}</text>";
            }
        }
        if (isset($_rowHeights['bottom']) && $hasBottom) {
            $ry1 = $oy + $sh2 - $bottomH * ($sh2 / $ih);
            $ry2 = $oy + $sh2;
            $dimLabel = _wd2_fracLabel($_rowHeights['bottom']);
            if (($ry2 - $ry1) > 15) {
                $svg .= "<line x1=\"{$_lhX}\" y1=\"".($ry1 + 2)."\" x2=\"{$_lhX}\" y2=\"".($ry2 - $fd - 2)."\" stroke=\"{$dimStroke}\" stroke-width=\"0.6\" opacity=\"0.5\"/>";
                $svg .= "<polygon points=\"{$_lhX},".($ry1+2)." ".($_lhX-1.5).",".($ry1+2+$dimArrowSz)." ".($_lhX+1.5).",".($ry1+2+$dimArrowSz)."\" fill=\"{$dimStroke}\" opacity=\"0.6\"/>";
                $svg .= "<polygon points=\"{$_lhX},".($ry2-$fd-2)." ".($_lhX-1.5).",".($ry2-$fd-2-$dimArrowSz)." ".($_lhX+1.5).",".($ry2-$fd-2-$dimArrowSz)."\" fill=\"{$dimStroke}\" opacity=\"0.6\"/>";
                $mid = ($ry1 + $ry2) / 2;
                $svg .= "<rect x=\"".($_lhX - 2)."\" y=\"".($mid - 16)."\" width=\"14\" height=\"32\" rx=\"2\" fill=\"#fff\" opacity=\"0.85\"/>";
                $svg .= "<text x=\"{$_lhX}\" y=\"{$mid}\" text-anchor=\"middle\" font-size=\"7.5\" font-weight=\"700\" fill=\"{$dimStroke}\" opacity=\"0.9\" transform=\"rotate(-90,{$_lhX},{$mid})\">{$dimLabel}</text>";
            }
        }
    }
}

// ─── DIMENSIONS (with fraction labels) ───
if (!$hideDimensions) {
    $svg .= _wd2_dimH($ox, $ox + $sw, $oy + $sh2, $wLabel);
    $svg .= _wd2_dimV($ox + $sw, $oy, $oy + $sh2, $hLabel);

    // (Per-row height labels on left are rendered above via the dimension-line arrows section)
}

// ─── CONFIG LABEL ───
if (!$hideDimensions) {
    $svg .= "<text x=\"".($ox + $sw / 2)."\" y=\"".($oy - 8)."\" text-anchor=\"middle\" font-size=\"10\" font-family=\"Arial, sans-serif\" font-weight=\"bold\" fill=\"#333\">{$rawType}</text>";
}

// ─── BUILD GRADIENT DEFS IN PHP (no Blade directives inside SVG) ───
if ($_hexColor && strlen(ltrim($_hexColor, '#')) === 6) {
    // Generate gradient shades from the provided hex color
    $_hx = ltrim($_hexColor, '#');
    $_r = hexdec(substr($_hx,0,2)); $_g = hexdec(substr($_hx,2,2)); $_b = hexdec(substr($_hx,4,2));
    $_shade = function($r,$g,$b,$factor) {
        return sprintf('#%02X%02X%02X',
            max(0, min(255, (int)($r * $factor))),
            max(0, min(255, (int)($g * $factor))),
            max(0, min(255, (int)($b * $factor)))
        );
    };
    $_fg = [$_shade($_r,$_g,$_b,1.05), $_shade($_r,$_g,$_b,0.97), $_shade($_r,$_g,$_b,0.88), $_shade($_r,$_g,$_b,0.78)];
    $_sg = [$_shade($_r,$_g,$_b,1.02), $_shade($_r,$_g,$_b,0.95), $_shade($_r,$_g,$_b,0.82)];
} elseif ($isDark) {
    $_fg = ['#4A4A4A', '#3A3A3A', '#2A2A2A', '#1A1A1A'];
    $_sg = ['#3E3E3E', '#333333', '#1E1E1E'];
} else {
    $_fg = ['#FFFFFF', '#FAFAFA', '#F0F0EE', '#DADAD6'];
    $_sg = ['#FCFCFB', '#F7F7F5', '#D8D8D4'];
}

$defs = '';
$defs .= "<linearGradient id=\"{$uid}-vH\" x1=\"0\" y1=\"0\" x2=\"0\" y2=\"1\"><stop offset=\"0%\" stop-color=\"{$_fg[0]}\"/><stop offset=\"25%\" stop-color=\"{$_fg[1]}\"/><stop offset=\"80%\" stop-color=\"{$_fg[2]}\"/><stop offset=\"100%\" stop-color=\"{$_fg[3]}\"/></linearGradient>";
$defs .= "<linearGradient id=\"{$uid}-vHR\" x1=\"0\" y1=\"1\" x2=\"0\" y2=\"0\"><stop offset=\"0%\" stop-color=\"{$_fg[0]}\"/><stop offset=\"25%\" stop-color=\"{$_fg[1]}\"/><stop offset=\"80%\" stop-color=\"{$_fg[2]}\"/><stop offset=\"100%\" stop-color=\"{$_fg[3]}\"/></linearGradient>";
$defs .= "<linearGradient id=\"{$uid}-vV\" x1=\"0\" y1=\"0\" x2=\"1\" y2=\"0\"><stop offset=\"0%\" stop-color=\"{$_fg[0]}\"/><stop offset=\"25%\" stop-color=\"{$_fg[1]}\"/><stop offset=\"80%\" stop-color=\"{$_fg[2]}\"/><stop offset=\"100%\" stop-color=\"{$_fg[3]}\"/></linearGradient>";
$defs .= "<linearGradient id=\"{$uid}-vVR\" x1=\"1\" y1=\"0\" x2=\"0\" y2=\"0\"><stop offset=\"0%\" stop-color=\"{$_fg[0]}\"/><stop offset=\"25%\" stop-color=\"{$_fg[1]}\"/><stop offset=\"80%\" stop-color=\"{$_fg[2]}\"/><stop offset=\"100%\" stop-color=\"{$_fg[3]}\"/></linearGradient>";
$defs .= "<linearGradient id=\"{$uid}-glass\" x1=\"0\" y1=\"0\" x2=\"1\" y2=\"1\"><stop offset=\"0%\" stop-color=\"#9ECAE9\"/><stop offset=\"40%\" stop-color=\"#87BFEA\"/><stop offset=\"100%\" stop-color=\"#6BAED6\"/></linearGradient>";
$defs .= "<linearGradient id=\"{$uid}-sH\" x1=\"0\" y1=\"0\" x2=\"0\" y2=\"1\"><stop offset=\"0%\" stop-color=\"{$_sg[0]}\"/><stop offset=\"35%\" stop-color=\"{$_sg[1]}\"/><stop offset=\"100%\" stop-color=\"{$_sg[2]}\"/></linearGradient>";
$defs .= "<linearGradient id=\"{$uid}-sV\" x1=\"0\" y1=\"0\" x2=\"1\" y2=\"0\"><stop offset=\"0%\" stop-color=\"{$_sg[0]}\"/><stop offset=\"35%\" stop-color=\"{$_sg[1]}\"/><stop offset=\"100%\" stop-color=\"{$_sg[2]}\"/></linearGradient>";
$defs .= "<filter id=\"{$uid}-fs\"><feDropShadow dx=\"1\" dy=\"1\" stdDeviation=\"1.5\" flood-opacity=\"0.12\"/></filter>";
$defs .= "<filter id=\"{$uid}-is\"><feDropShadow dx=\"0.5\" dy=\"0.5\" stdDeviation=\"0.6\" flood-opacity=\"0.08\"/></filter>";

// ─── SHAPE SUPPORT ───
// When a shape code is provided, draw the window natively as that shape
// (frame follows the shape outline, glass fills the interior).
$_shapeCode = strtoupper(trim($shapeCode ?? ''));
$_shapeSvg = '';

if ($_shapeCode) {
    $_sp = json_decode($shapeParams ?? '{}', true) ?: [];
    // Outer bounds (leave margin for dimensions)
    $_margin = $hideDimensions ? 4 : 4;
    $_x1 = $ox; $_y1 = $oy; $_x2 = $ox + $sw; $_y2 = $oy + $sh2;
    $_cx = ($_x1 + $_x2) / 2;
    $_h1Ratio = isset($_sp['H1']) && $h > 0 ? floatval($_sp['H1']) / $h : 0.5;
    $_w1Ratio = isset($_sp['W1']) && $w > 0 ? floatval($_sp['W1']) / $w : 0.25;

    // Helper to inset a path by $fd (frame depth) — generates inner glass path
    // We build both outer and inner paths per shape type
    $_outerD = null;
    $_innerD = null;
    $_f = $fd; // frame thickness

    // ── Helper: inset a convex polygon by uniform distance $_f ──
    // Computes exact inner polygon with uniform perpendicular frame thickness
    $_insetPoly = function(array $pts, float $f): array {
        $n = count($pts);
        if ($n < 3) return $pts;
        // Determine winding direction (positive area = CCW in screen coords)
        $area = 0;
        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $area += $pts[$i][0] * $pts[$j][1] - $pts[$j][0] * $pts[$i][1];
        }
        $sign = $area > 0 ? 1 : -1; // +1 CCW, -1 CW
        // Compute inset edge lines
        $lines = [];
        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $dx = $pts[$j][0] - $pts[$i][0];
            $dy = $pts[$j][1] - $pts[$i][1];
            $len = sqrt($dx * $dx + $dy * $dy);
            if ($len < 0.001) continue;
            // Inward normal
            $nx = -$sign * $dy / $len;
            $ny =  $sign * $dx / $len;
            $lines[] = [
                'px' => $pts[$i][0] + $f * $nx,
                'py' => $pts[$i][1] + $f * $ny,
                'dx' => $dx, 'dy' => $dy,
            ];
        }
        // Intersect adjacent inset lines to find inner vertices
        $inner = [];
        $m = count($lines);
        for ($i = 0; $i < $m; $i++) {
            $j = ($i + 1) % $m;
            $l1 = $lines[$i]; $l2 = $lines[$j];
            $det = $l1['dx'] * $l2['dy'] - $l1['dy'] * $l2['dx'];
            if (abs($det) < 0.0001) continue;
            $t = (($l2['px'] - $l1['px']) * $l2['dy'] - ($l2['py'] - $l1['py']) * $l2['dx']) / $det;
            $inner[] = [round($l1['px'] + $t * $l1['dx'], 2), round($l1['py'] + $t * $l1['dy'], 2)];
        }
        return $inner;
    };
    // Convert vertex array to SVG path "d" string
    $_polyToD = function(array $pts): string {
        if (empty($pts)) return '';
        $d = 'M' . $pts[0][0] . ',' . $pts[0][1];
        for ($i = 1; $i < count($pts); $i++) {
            $d .= ' L' . $pts[$i][0] . ',' . $pts[$i][1];
        }
        return $d . ' Z';
    };

    if (str_contains($_shapeCode, 'HALF_ROUND') || str_contains($_shapeCode, 'HALFRND') || $_shapeCode === 'M1' || $_shapeCode === 'S03' || $_shapeCode === 'S49') {
        $_r = ($_x2 - $_x1) / 2;
        $_archH = min($_r, ($_y2 - $_y1) * 0.45);
        $_archY = $_y1 + $_archH;
        $_outerD = "M{$_x1},{$_y2} L{$_x1},{$_archY} A{$_r},{$_archH} 0 0,1 {$_x2},{$_archY} L{$_x2},{$_y2} Z";
        // Inner (inset by frame depth)
        $_ir = $_r - $_f; $_iah = max(1, $_archH - $_f);
        $_iay = $_y1 + $_f + $_iah;
        $_innerD = "M".($_x1+$_f).",".($_y2-$_f)." L".($_x1+$_f).",{$_iay} A{$_ir},{$_iah} 0 0,1 ".($_x2-$_f).",{$_iay} L".($_x2-$_f).",".($_y2-$_f)." Z";
    } elseif (str_contains($_shapeCode, 'ARCH') || $_shapeCode === 'M2' || $_shapeCode === 'M5') {
        $_r = ($_x2 - $_x1) / 2;
        $_archY = $_y1 + $_r;
        $_outerD = "M{$_x1},{$_y2} L{$_x1},{$_archY} A{$_r},{$_r} 0 0,1 {$_x2},{$_archY} L{$_x2},{$_y2} Z";
        $_ir = $_r - $_f;
        $_iay = $_y1 + $_f + $_ir;
        $_innerD = "M".($_x1+$_f).",".($_y2-$_f)." L".($_x1+$_f).",{$_iay} A{$_ir},{$_ir} 0 0,1 ".($_x2-$_f).",{$_iay} L".($_x2-$_f).",".($_y2-$_f)." Z";
    } elseif (str_contains($_shapeCode, 'RAKE_UP_LEFT') || $_shapeCode === 'S15' || $_shapeCode === 'RAKE') {
        $_h1y = $_y1 + ($_y2 - $_y1) * (1 - $_h1Ratio);
        $_outerPts = [[$_x1,$_y2], [$_x1,$_y1], [$_x2,$_h1y], [$_x2,$_y2]];
        $_outerD = $_polyToD($_outerPts);
        $_innerD = $_polyToD($_insetPoly($_outerPts, $_f));
    } elseif (str_contains($_shapeCode, 'RAKE_UP_RIGHT') || $_shapeCode === 'S17') {
        $_h1y = $_y1 + ($_y2 - $_y1) * (1 - $_h1Ratio);
        $_outerPts = [[$_x1,$_h1y], [$_x2,$_y1], [$_x2,$_y2], [$_x1,$_y2]];
        $_outerD = $_polyToD($_outerPts);
        $_innerD = $_polyToD($_insetPoly($_outerPts, $_f));
    } elseif (str_contains($_shapeCode, 'RAKE_DOWN_RIGHT') || $_shapeCode === 'S23') {
        $_h1y = $_y1 + ($_y2 - $_y1) * $_h1Ratio;
        $_outerPts = [[$_x1,$_y1], [$_x2,$_y1], [$_x2,$_y2], [$_x1,$_h1y]];
        $_outerD = $_polyToD($_outerPts);
        $_innerD = $_polyToD($_insetPoly($_outerPts, $_f));
    } elseif (str_contains($_shapeCode, 'RAKE_DOWN_LEFT') || $_shapeCode === 'S25') {
        $_h1y = $_y1 + ($_y2 - $_y1) * $_h1Ratio;
        $_outerPts = [[$_x1,$_y1], [$_x2,$_y1], [$_x2,$_h1y], [$_x1,$_y2]];
        $_outerD = $_polyToD($_outerPts);
        $_innerD = $_polyToD($_insetPoly($_outerPts, $_f));
    } elseif (str_contains($_shapeCode, 'RAKE_RIGHT_TOP') || $_shapeCode === 'S27') {
        $_w1x = $_x2 - ($_x2 - $_x1) * $_h1Ratio;
        $_outerPts = [[$_x1,$_y1], [$_x2,$_y1], [$_x2,$_y2], [$_w1x,$_y2]];
        $_outerD = $_polyToD($_outerPts);
        $_innerD = $_polyToD($_insetPoly($_outerPts, $_f));
    } elseif (str_contains($_shapeCode, 'RAKE_LEFT_TOP') || $_shapeCode === 'S29') {
        $_w1x = $_x1 + ($_x2 - $_x1) * $_h1Ratio;
        $_outerPts = [[$_x1,$_y1], [$_x2,$_y1], [$_w1x,$_y2], [$_x1,$_y2]];
        $_outerD = $_polyToD($_outerPts);
        $_innerD = $_polyToD($_insetPoly($_outerPts, $_f));
    } elseif (str_contains($_shapeCode, 'TRI') || str_contains($_shapeCode, 'TRIANGLE')) {
        $_outerPts = [[$_x1,$_y2], [$_cx,$_y1], [$_x2,$_y2]];
        $_outerD = $_polyToD($_outerPts);
        $_innerD = $_polyToD($_insetPoly($_outerPts, $_f));
    } elseif (str_contains($_shapeCode, 'OCT') || str_contains($_shapeCode, 'OCTAGON')) {
        $_ins = ($_x2 - $_x1) * 0.29;
        $_outerPts = [
            [$_x1+$_ins, $_y1], [$_x2-$_ins, $_y1],
            [$_x2, $_y1+$_ins], [$_x2, $_y2-$_ins],
            [$_x2-$_ins, $_y2], [$_x1+$_ins, $_y2],
            [$_x1, $_y2-$_ins], [$_x1, $_y1+$_ins],
        ];
        $_outerD = $_polyToD($_outerPts);
        $_innerD = $_polyToD($_insetPoly($_outerPts, $_f));
    } elseif (str_contains($_shapeCode, 'QUARTER') || str_contains($_shapeCode, 'QTR') || $_shapeCode === 'S61' || $_shapeCode === 'S62') {
        $_r = min($_x2 - $_x1, $_y2 - $_y1);
        // Arc center at bottom-left corner (_x1, _y2)
        // Outer: left edge up to arc start, arc sweeps to bottom edge
        $_outerD = "M{$_x1},{$_y2} L{$_x1},".($_y2-$_r)." A{$_r},{$_r} 0 0,1 ".($_x1+$_r).",{$_y2} Z";
        // Inner glass: same center, uniform _f inset
        // Left frame: x1+f. Bottom frame: y2-f. Inner arc radius: r-f, center still (x1, y2)
        // Inner arc: from (x1, y2-(r-f)) to (x1+(r-f), y2)
        // Left straight: from (x1+f, y2-f) up to where it meets the inner arc
        // The inner arc at x=x1+f has y = y2 - sqrt((r-f)^2 - f^2)
        $_ir = $_r - $_f;
        $_meetY = $_y2 - sqrt(max(0, $_ir*$_ir - $_f*$_f));
        $_meetX = $_x1 + sqrt(max(0, $_ir*$_ir - $_f*$_f));
        $_innerD = "M".($_x1+$_f).",".($_y2-$_f)." L".($_x1+$_f).",{$_meetY} A{$_ir},{$_ir} 0 0,1 {$_meetX},".($_y2-$_f)." Z";
    } elseif (str_contains($_shapeCode, 'CIRCLE') || $_shapeCode === 'S48') {
        $_rx = ($_x2 - $_x1) / 2; $_ry = ($_y2 - $_y1) / 2;
        $_outerD = "M{$_cx},{$_y1} A{$_rx},{$_ry} 0 1,1 {$_cx},{$_y2} A{$_rx},{$_ry} 0 1,1 {$_cx},{$_y1}";
        $_irx = $_rx - $_f; $_iry = $_ry - $_f;
        $_icy = ($_y1 + $_y2) / 2;
        $_innerD = "M{$_cx},".($_icy - $_iry)." A{$_irx},{$_iry} 0 1,1 {$_cx},".($_icy + $_iry)." A{$_irx},{$_iry} 0 1,1 {$_cx},".($_icy - $_iry);
    } elseif (str_contains($_shapeCode, 'TRAP') || str_contains($_shapeCode, 'TRAPEZ')) {
        $_ins = ($_x2 - $_x1) * $_w1Ratio;
        $_outerPts = [[$_x1,$_y2], [$_x1+$_ins,$_y1], [$_x2-$_ins,$_y1], [$_x2,$_y2]];
        $_outerD = $_polyToD($_outerPts);
        $_innerD = $_polyToD($_insetPoly($_outerPts, $_f));
    } elseif (str_contains($_shapeCode, 'PEAK') || str_contains($_shapeCode, 'PEAKED')) {
        $_peakH = ($_y2 - $_y1) * $_h1Ratio;
        $_outerPts = [[$_x1,$_y2], [$_x1,$_y1+$_peakH], [$_cx,$_y1], [$_x2,$_y1+$_peakH], [$_x2,$_y2]];
        $_outerD = $_polyToD($_outerPts);
        $_innerD = $_polyToD($_insetPoly($_outerPts, $_f));
    } elseif (str_contains($_shapeCode, 'CLIP_LT') || $_shapeCode === 'S04') {
        $_cw = ($_x2 - $_x1) * $_w1Ratio; $_ch = ($_y2 - $_y1) * $_h1Ratio;
        $_outerPts = [[$_x1+$_cw,$_y1], [$_x2,$_y1], [$_x2,$_y2], [$_x1,$_y2], [$_x1,$_y1+$_ch]];
        $_outerD = $_polyToD($_outerPts);
        $_innerD = $_polyToD($_insetPoly($_outerPts, $_f));
    } elseif (str_contains($_shapeCode, 'CLIP_RT') || $_shapeCode === 'S06') {
        $_cw = ($_x2 - $_x1) * $_w1Ratio; $_ch = ($_y2 - $_y1) * $_h1Ratio;
        $_outerPts = [[$_x1,$_y1], [$_x2-$_cw,$_y1], [$_x2,$_y1+$_ch], [$_x2,$_y2], [$_x1,$_y2]];
        $_outerD = $_polyToD($_outerPts);
        $_innerD = $_polyToD($_insetPoly($_outerPts, $_f));
    } elseif (str_contains($_shapeCode, 'CLIP_RB') || $_shapeCode === 'S09') {
        $_cw = ($_x2 - $_x1) * $_w1Ratio; $_ch = ($_y2 - $_y1) * $_h1Ratio;
        $_outerPts = [[$_x1,$_y1], [$_x2,$_y1], [$_x2,$_y2-$_ch], [$_x2-$_cw,$_y2], [$_x1,$_y2]];
        $_outerD = $_polyToD($_outerPts);
        $_innerD = $_polyToD($_insetPoly($_outerPts, $_f));
    } elseif (str_contains($_shapeCode, 'CLIP_LB') || $_shapeCode === 'S12') {
        $_cw = ($_x2 - $_x1) * $_w1Ratio; $_ch = ($_y2 - $_y1) * $_h1Ratio;
        $_outerPts = [[$_x1,$_y1], [$_x2,$_y1], [$_x2,$_y2], [$_x1+$_cw,$_y2], [$_x1,$_y2-$_ch]];
        $_outerD = $_polyToD($_outerPts);
        $_innerD = $_polyToD($_insetPoly($_outerPts, $_f));
    } elseif (str_contains($_shapeCode, 'HEXAGON') || str_contains($_shapeCode, 'HEX')) {
        $_ins = ($_y2 - $_y1) * 0.25;
        $_outerPts = [[$_cx,$_y1], [$_x2,$_y1+$_ins], [$_x2,$_y2-$_ins], [$_cx,$_y2], [$_x1,$_y2-$_ins], [$_x1,$_y1+$_ins]];
        $_outerD = $_polyToD($_outerPts);
        $_innerD = $_polyToD($_insetPoly($_outerPts, $_f));
    } elseif (str_contains($_shapeCode, 'PENTAGON') || str_contains($_shapeCode, 'PENT')) {
        $_pentMid = $_y1 + ($_y2 - $_y1) * 0.4;
        $_outerPts = [[$_x1,$_y2], [$_x1,$_pentMid], [$_cx,$_y1], [$_x2,$_pentMid], [$_x2,$_y2]];
        $_outerD = $_polyToD($_outerPts);
        $_innerD = $_polyToD($_insetPoly($_outerPts, $_f));
    } elseif (str_contains($_shapeCode, 'GOTHIC')) {
        // Gothic: pointed arch at top (two arcs meeting at center peak)
        $_r = ($_x2 - $_x1) * 0.7;
        $_peakY = $_y1;
        $_springY = $_y1 + ($_y2 - $_y1) * 0.35;
        $_outerD = "M{$_x1},{$_y2} L{$_x1},{$_springY} A{$_r},{$_r} 0 0,1 {$_cx},{$_peakY} A{$_r},{$_r} 0 0,1 {$_x2},{$_springY} L{$_x2},{$_y2} Z";
        $_ir = $_r - $_f;
        // For the inner peak, compute where two concentric arcs meet
        // The peak inset is approximately f / sin(half-peak-angle); for gothic arches this is ~f*1.2
        $_halfW = ($_x2 - $_x1) / 2;
        $_gothicAngle = atan2($_springY - $_peakY, $_halfW);
        $_peakInset = $_gothicAngle > 0.01 ? $_f / sin($_gothicAngle) : $_f * 1.5;
        $_peakInset = min($_peakInset, $_f * 3); // cap it for safety
        $_innerD = "M".($_x1+$_f).",".($_y2-$_f)." L".($_x1+$_f).",".($_springY)." A{$_ir},{$_ir} 0 0,1 {$_cx},".($_peakY+$_peakInset)." A{$_ir},{$_ir} 0 0,1 ".($_x2-$_f).",".($_springY)." L".($_x2-$_f).",".($_y2-$_f)." Z";
    } elseif (str_contains($_shapeCode, 'DIAMOND') || str_contains($_shapeCode, 'RHOMBUS')) {
        $_cy = ($_y1 + $_y2) / 2;
        $_outerPts = [[$_cx,$_y1], [$_x2,$_cy], [$_cx,$_y2], [$_x1,$_cy]];
        $_outerD = $_polyToD($_outerPts);
        $_innerD = $_polyToD($_insetPoly($_outerPts, $_f));
    }

    // Build native shaped window SVG
    if ($_outerD && $_innerD) {
        // Frame: use clip-path with even-odd rule to create the frame ring
        $defs .= "<clipPath id=\"{$uid}-shapeGlassClip\"><path d=\"{$_innerD}\"/></clipPath>";

        // Outer shape filled with frame gradient
        $_shapeSvg .= "<path d=\"{$_outerD}\" fill=\"url(#{$uid}-vH)\" stroke=\"#C0C0BC\" stroke-width=\"1\" stroke-linejoin=\"round\"/>";
        // Inner frame line
        $_shapeSvg .= "<path d=\"{$_innerD}\" fill=\"none\" stroke=\"#C0C0BC\" stroke-width=\"0.6\" stroke-linejoin=\"round\"/>";
        // Glass fill
        $_shapeSvg .= "<path d=\"{$_innerD}\" fill=\"url(#{$uid}-glass)\" stroke=\"none\"/>";
        // Outer edge highlight
        $_shapeSvg .= "<path d=\"{$_outerD}\" fill=\"none\" stroke=\"#B0B0AC\" stroke-width=\"0.8\" stroke-linejoin=\"round\"/>";

        // Field number "1" in center of glass
        $_fcx = ($_x1 + $_x2) / 2;
        $_fcy = ($_y1 + $_y2) / 2;
        // Shift center down a bit for top-heavy shapes
        if (str_contains($_shapeCode, 'TRI') || str_contains($_shapeCode, 'HALF_ROUND') || str_contains($_shapeCode, 'ARCH') || str_contains($_shapeCode, 'PEAK')) {
            $_fcy = $_fcy + ($_y2 - $_y1) * 0.1;
        }
        $_shapeSvg .= "<text x=\"{$_fcx}\" y=\"".($_fcy + 3)."\" text-anchor=\"middle\" font-size=\"".min(max($sw * 0.12, 8), 16)."\" font-family=\"Arial, sans-serif\" font-weight=\"bold\" fill=\"#1565C0\" opacity=\"0.75\">1</text>";

        // Add dimensions
        if (!$hideDimensions) {
            $_shapeSvg .= _wd2_dimH($ox, $ox + $sw, $oy + $sh2, $wLabel);
            $_shapeSvg .= _wd2_dimV($ox + $sw, $oy, $oy + $sh2, $hLabel);
            // Config label
            $_shapeSvg .= "<text x=\"".($ox + $sw / 2)."\" y=\"".($oy - 8)."\" text-anchor=\"middle\" font-size=\"10\" font-family=\"Arial, sans-serif\" font-weight=\"bold\" fill=\"#333\">{$rawType}</text>";
        }
    }
}

@endphp

<svg width="{{ $svgW }}" height="{{ $svgH }}" viewBox="0 0 {{ $svgW }} {{ $svgH }}" xmlns="http://www.w3.org/2000/svg" style="background:transparent; max-width:100%; height:auto;">
  <defs>{!! $defs !!}</defs>
@if($_shapeSvg)
  {!! $_shapeSvg !!}
@else
  {!! $svg !!}
@endif
</svg>