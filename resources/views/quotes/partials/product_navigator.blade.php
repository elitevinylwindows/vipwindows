{{-- Visual Product Navigator --}}
<div class="card mb-3" id="productNavigator">
    <div class="card-header py-2">
        <h6 class="mb-0">
            <i data-feather="grid" style="width:16px;height:16px;"></i>
            {{ __('Select Window Type') }}
        </h6>
    </div>
    <div class="card-body py-2">
        <div class="row g-2" id="windowTypeGrid">
            @php
                // Pull window types from master data so names stay in sync
                $windowTypes = \App\Http\Controllers\Master\SeriesWindowTypeController::baseWindowTypes();
                // Merge any custom types from the DB
                $dbWtRows = \Illuminate\Support\Facades\DB::table('elitevw_master_series_window_types')
                    // ->where('parent_id', parentId()) // Removed: VIP Windows is single-tenant
                    ->select('window_type_code', 'window_type_name')
                    ->distinct()
                    ->get();
                $baseCodes = collect($windowTypes)->pluck('code')->toArray();
                // Override base names with DB names if they've been edited
                $dbNameMap = $dbWtRows->pluck('window_type_name', 'window_type_code')->toArray();
                foreach ($windowTypes as &$_wt) {
                    if (isset($dbNameMap[$_wt['code']])) {
                        $_wt['name'] = $dbNameMap[$_wt['code']];
                    }
                }
                unset($_wt);
                // Add any custom types not in base list
                foreach ($dbWtRows as $row) {
                    if (!in_array($row->window_type_code, $baseCodes)) {
                        $windowTypes[] = ['code' => $row->window_type_code, 'name' => $row->window_type_name];
                    }
                }
            @endphp
            @foreach($windowTypes as $wt)
            <div class="col-4 col-sm-3 col-md-2">
                <div class="window-type-card text-center p-2 border rounded"
                     data-type-code="{{ $wt['code'] }}"
                     style="cursor:pointer; transition: all 0.2s ease;"
                     onclick="selectWindowType('{{ $wt['code'] }}', '{{ $wt['name'] }}')">
                    <div class="window-icon mb-1" style="height:50px; display:flex; align-items:center; justify-content:center;">
                        <svg viewBox="0 0 60 70" width="45" height="52" class="window-svg" data-type="{{ $wt['code'] }}">
                            {{-- Window frame outer --}}
                            <rect x="2" y="2" width="56" height="66" rx="2" fill="none" stroke="#1B4F72" stroke-width="2.5"/>

                            @if($wt['code'] === 'SH')
                            {{-- Single Hung: top fixed, bottom slides up --}}
                            <line x1="2" y1="35" x2="58" y2="35" stroke="#1B4F72" stroke-width="1.5"/>
                            <rect x="6" y="38" width="48" height="26" rx="1" fill="none" stroke="#3498DB" stroke-width="1" stroke-dasharray="2"/>
                            <line x1="30" y1="38" x2="30" y2="64" stroke="#ccc" stroke-width="0.5"/>
                            <circle cx="30" cy="62" r="2" fill="#1B4F72"/>

                            @elseif($wt['code'] === 'DH')
                            {{-- Double Hung: both sashes slide --}}
                            <line x1="2" y1="35" x2="58" y2="35" stroke="#1B4F72" stroke-width="1.5"/>
                            <rect x="6" y="5" width="48" height="27" rx="1" fill="none" stroke="#3498DB" stroke-width="1" stroke-dasharray="2"/>
                            <rect x="6" y="38" width="48" height="26" rx="1" fill="none" stroke="#3498DB" stroke-width="1" stroke-dasharray="2"/>
                            <circle cx="30" cy="32" r="2" fill="#1B4F72"/>
                            <circle cx="30" cy="62" r="2" fill="#1B4F72"/>

                            @elseif($wt['code'] === 'CM')
                            {{-- Casement: hinged on side --}}
                            <line x1="30" y1="2" x2="30" y2="68" stroke="#1B4F72" stroke-width="1.5"/>
                            <line x1="2" y1="2" x2="30" y2="35" stroke="#ccc" stroke-width="0.5"/>
                            <line x1="2" y1="68" x2="30" y2="35" stroke="#ccc" stroke-width="0.5"/>
                            <line x1="58" y1="2" x2="30" y2="35" stroke="#ccc" stroke-width="0.5"/>
                            <line x1="58" y1="68" x2="30" y2="35" stroke="#ccc" stroke-width="0.5"/>
                            <circle cx="27" cy="35" r="2" fill="#1B4F72"/>
                            <circle cx="33" cy="35" r="2" fill="#1B4F72"/>

                            @elseif($wt['code'] === 'HS')
                            {{-- Horizontal Slider --}}
                            <line x1="30" y1="2" x2="30" y2="68" stroke="#1B4F72" stroke-width="1.5"/>
                            <rect x="32" y="5" width="23" height="60" rx="1" fill="none" stroke="#3498DB" stroke-width="1" stroke-dasharray="2"/>
                            <line x1="32" y1="35" x2="55" y2="35" stroke="#ccc" stroke-width="0.5"/>
                            <circle cx="33" cy="35" r="2" fill="#1B4F72"/>

                            @elseif($wt['code'] === 'PW')
                            {{-- Picture Window: single pane, no moving parts --}}
                            <rect x="6" y="6" width="48" height="58" rx="1" fill="none" stroke="#87CEEB" stroke-width="0.5"/>
                            <line x1="6" y1="6" x2="54" y2="64" stroke="#E8F4FD" stroke-width="0.5"/>

                            @elseif($wt['code'] === 'AW')
                            {{-- Awning: hinged at top --}}
                            <line x1="2" y1="35" x2="30" y2="68" stroke="#ccc" stroke-width="0.5"/>
                            <line x1="58" y1="35" x2="30" y2="68" stroke="#ccc" stroke-width="0.5"/>
                            <circle cx="30" cy="66" r="2" fill="#1B4F72"/>

                            @elseif($wt['code'] === 'SLD')
                            {{-- Sliding Door --}}
                            <line x1="30" y1="2" x2="30" y2="68" stroke="#1B4F72" stroke-width="1.5"/>
                            <rect x="32" y="5" width="23" height="60" rx="1" fill="none" stroke="#3498DB" stroke-width="1" stroke-dasharray="2"/>
                            <rect x="5" y="48" width="10" height="16" rx="1" fill="none" stroke="#1B4F72" stroke-width="1"/>
                            <circle cx="33" cy="40" r="2.5" fill="#1B4F72"/>

                            @elseif($wt['code'] === 'SWD')
                            {{-- Swing Door --}}
                            <line x1="2" y1="2" x2="30" y2="35" stroke="#ccc" stroke-width="0.5"/>
                            <line x1="2" y1="68" x2="30" y2="35" stroke="#ccc" stroke-width="0.5"/>
                            <rect x="5" y="28" width="10" height="14" rx="1" fill="none" stroke="#1B4F72" stroke-width="1"/>
                            <circle cx="50" cy="35" r="2.5" fill="#1B4F72"/>

                            @else
                            {{-- Specialty / Default --}}
                            <line x1="2" y1="2" x2="58" y2="68" stroke="#ccc" stroke-width="0.5"/>
                            <line x1="58" y1="2" x2="2" y2="68" stroke="#ccc" stroke-width="0.5"/>
                            <polygon points="30,12 48,55 12,55" fill="none" stroke="#1B4F72" stroke-width="1"/>
                            @endif
                        </svg>
                    </div>
                    <small class="d-block" style="font-size:10px; line-height:1.2;">{{ __($wt['name']) }}</small>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Selected indicator --}}
        <div id="selectedTypeIndicator" class="mt-2 d-none">
            <small class="text-muted">Selected: <strong id="selectedTypeName"></strong></small>
            <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" onclick="clearWindowType()">{{ __('Clear') }}</button>
        </div>
    </div>
</div>

<style>
.window-type-card:hover {
    background-color: #EBF5FB !important;
    border-color: #1B4F72 !important;
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(27, 79, 114, 0.15);
}
.window-type-card.selected {
    background-color: #D4E6F1 !important;
    border-color: #1B4F72 !important;
    box-shadow: 0 0 0 2px #1B4F72;
}
.window-type-card.selected small {
    color: #1B4F72;
    font-weight: 600;
}
</style>

<script>
// Series → Window Types mapping from master data
window._seriesWindowTypes = @json($seriesWindowTypes ?? []);
// All configurations with product_category for window type filtering
window._allConfigsForFilter = @json($allConfigurations ?? []);

function selectWindowType(code, name) {
    // Remove previous selection
    document.querySelectorAll('.window-type-card').forEach(c => c.classList.remove('selected'));
    // Select this one
    document.querySelector(`.window-type-card[data-type-code="${code}"]`).classList.add('selected');
    // Show indicator
    document.getElementById('selectedTypeIndicator').classList.remove('d-none');
    document.getElementById('selectedTypeName').textContent = name;

    // Filter the configuration dropdown to show only matching configs
    filterConfigurationsByType(code);

    // Show/hide Shape section for PW (Picture Window)
    var shapeSection = document.getElementById('shapeSection');
    if (shapeSection) {
        shapeSection.style.display = (code === 'PW') ? '' : 'none';
        // If switching away from PW, clear shape selection
        if (code !== 'PW') {
            clearShapeSelection();
        }
    }
}

function clearWindowType() {
    document.querySelectorAll('.window-type-card').forEach(c => c.classList.remove('selected'));
    document.getElementById('selectedTypeIndicator').classList.add('d-none');
    // Reset configuration filter
    filterConfigurationsByType(null);
    // Hide shape section
    var shapeSection = document.getElementById('shapeSection');
    if (shapeSection) { shapeSection.style.display = 'none'; clearShapeSelection(); }
}

function filterConfigurationsByType(typeCode) {
    // Store the active window-type filter globally so the searchable dropdown can use it
    window._activeWindowTypeCode = typeCode || null;

    // Map window type codes to product_category values used on configurations
    var codeToCategories = {
        'SH':  ['SH'],
        'DH':  ['DH'],
        'CM':  ['CM', 'AW'],
        'HS':  ['SLIDER', 'HS'],
        'PW':  ['PW'],
        'AW':  ['AW', 'CM'],
        'SLD': ['SLD'],
        'SWD': ['SWD'],
        'XX':  ['XX', 'SPECIALTY']
    };
    var allowedCategories = typeCode ? (codeToCategories[typeCode] || [typeCode]) : null;

    // Filter the hidden select options by product_category
    var hiddenSelect = document.getElementById('seriesTypeSelect');
    var searchInput = document.getElementById('configSearchInput');
    var seriesId = document.getElementById('seriesSelect')?.value;

    if (hiddenSelect && seriesId) {
        // Get all configs for this series from the allConfigurations data
        var allConfigs = window._allConfigsForFilter || {};
        var seriesConfigs = allConfigs[seriesId] || [];

        if (typeCode && allowedCategories && seriesConfigs.length > 0) {
            // Find config names whose product_category matches the window type
            var matchingNames = [];
            seriesConfigs.forEach(function(cfg) {
                var cat = (cfg.product_category || '').toUpperCase();
                if (allowedCategories.some(function(ac) { return cat.indexOf(ac) >= 0; })) {
                    matchingNames.push(cfg.name);
                }
            });

            // Hide non-matching options in the select, show matching ones
            hiddenSelect.querySelectorAll('option').forEach(function(opt) {
                if (!opt.value) return; // keep placeholder
                opt.style.display = matchingNames.includes(opt.value) ? '' : 'none';
                opt.disabled = !matchingNames.includes(opt.value);
            });

            // Clear search and show filtered list
            if (searchInput) {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        } else {
            // No filter — show all options
            hiddenSelect.querySelectorAll('option').forEach(function(opt) {
                opt.style.display = '';
                opt.disabled = false;
            });
            if (searchInput) {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
    }

    // Also filter the lookup modal if it has category buttons
    var configLookup = document.getElementById('configLookupModal');
    if (configLookup) {
        window._preselectedTypeCode = typeCode;
    }
}

/**
 * Filter window type cards based on the selected series.
 * If no types are assigned to a series, all types are shown (default).
 */
function filterWindowTypesBySeries(seriesId) {
    var mapping = window._seriesWindowTypes || {};
    var allowedCodes = mapping[seriesId] || null; // null = show all

    document.querySelectorAll('.window-type-card').forEach(function(card) {
        var code = card.dataset.typeCode;
        var wrapper = card.closest('.col-4, .col-sm-3, .col-md-2');
        if (!wrapper) wrapper = card.parentElement;

        if (!allowedCodes || allowedCodes.length === 0) {
            // No restriction — show all
            wrapper.style.display = '';
        } else {
            wrapper.style.display = allowedCodes.includes(code) ? '' : 'none';
        }
    });

    // Clear any current selection if it's now hidden
    var selected = document.querySelector('.window-type-card.selected');
    if (selected) {
        var selWrapper = selected.closest('.col-4, .col-sm-3, .col-md-2') || selected.parentElement;
        if (selWrapper && selWrapper.style.display === 'none') {
            clearWindowType();
        }
    }
}
</script>
