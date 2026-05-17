<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Series;
use App\Models\SeriesConfiguration;
use App\Models\ColorConfiguration;
use App\Models\ExteriorColor;
use App\Models\InteriorColor;
use App\Models\LaminateColor;
use App\Services\WindowConfigurator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InstallerQuoteController extends Controller
{
    /**
     * List installer's quotes.
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = Quote::where('entered_by', Auth::user()->name);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $quotes = $query->latest()->paginate(20);

        return view('installer.quotes.index', compact('quotes', 'status'));
    }

    /**
     * Load shared configurator data used by create & edit.
     */
    private function loadConfiguratorData(): array
    {
        $seriesList = Series::pluck('series', 'id');

        $laminateColors = LaminateColor::all();

        $seriesAvailableColors = DB::table('elitevw_master_series_available_colors')
            ->get()->groupBy('series_id')
            ->map(fn($colors) => $colors->map(fn($c) => ['code' => $c->color_code, 'name' => $c->color_name])->values());

        $seriesWindowTypes = DB::table('elitevw_master_series_window_types')
            ->get()->groupBy('series_id')
            ->map(fn($types) => $types->pluck('window_type_code')->values());

        $activeConfigNames = SeriesConfiguration::where('is_active', true)
            ->pluck('series_type')->map(fn($t) => strtoupper(trim($t)))->toArray();

        $allConfigurations = [];
        foreach (Series::with('configurations')->get() as $series) {
            $configs = [];
            foreach ($series->configurations as $conf) {
                if (!empty($activeConfigNames) && !in_array(strtoupper(trim($conf->series_type)), $activeConfigNames)) continue;
                $configs[] = [
                    'name' => $conf->series_type,
                    'category' => $conf->category,
                    'image' => $conf->image,
                    'series' => $series->series,
                    'product_category' => $conf->product_category ?? null,
                ];
            }
            $allConfigurations[(string) $series->id] = $configs;
        }

        // Product areas for configuration lookup
        $productAreas = [];
        $productTypeRecords = DB::table('elitevw_master_productkeys_producttypes')
            ->select('product_type', 'series', 'description')
            ->get()
            ->keyBy('product_type');

        $descToCategoryMap = function ($desc) {
            $desc = strtoupper($desc ?? '');
            $cats = [];
            if (str_contains($desc, 'SLIDING DOOR')) return ['SLD'];
            if (str_contains($desc, 'SWING DOOR') || str_contains($desc, 'FOLDING')) return ['SWD'];
            if (str_contains($desc, 'HORIZONTAL SLID') || str_contains($desc, 'DOUBLE SLIDING')) $cats[] = 'SLIDER';
            if (str_contains($desc, 'SINGLE HUNG')) $cats[] = 'SH';
            if (str_contains($desc, 'DOUBLE HUNG')) $cats[] = 'DH';
            if (str_contains($desc, 'CASEMENT') || str_contains($desc, 'AWNING')) { $cats[] = 'CM'; $cats[] = 'AW'; }
            if (str_contains($desc, 'PICTURE')) $cats[] = 'PW';
            return $cats;
        };

        $rawAreas = DB::table('elitevw_master_productkeys_productareas')->get();
        foreach ($rawAreas as $area) {
            $codes = json_decode($area->product_types ?? '[]', true) ?: [];
            $seriesCategories = [];
            foreach ($codes as $code) {
                $code = trim($code);
                if (isset($productTypeRecords[$code])) {
                    $pt = $productTypeRecords[$code];
                    $seriesName = strtoupper(trim($pt->series ?? ''));
                    $cats = $descToCategoryMap($pt->description);
                    if ($seriesName && !empty($cats)) {
                        if (!isset($seriesCategories[$seriesName])) $seriesCategories[$seriesName] = [];
                        $seriesCategories[$seriesName] = array_values(array_unique(array_merge($seriesCategories[$seriesName], $cats)));
                    }
                }
            }
            $productAreas[] = [
                'product_area' => $area->product_area,
                'description' => $area->description,
                'series_categories' => $seriesCategories,
            ];
        }

        $glassOptions = DB::table('elitevw_master_glass_options')
            ->get()->groupBy('position')
            ->map(fn($items) => $items->pluck('glass_type')->values());

        $seriesPaneTypes = DB::table('elitevw_master_series_pane_types')
            ->get()->groupBy('series_id')
            ->map(fn($rows) => $rows->pluck('pane_type')->toArray());

        return compact(
            'seriesList', 'allConfigurations', 'activeConfigNames',
            'productAreas', 'glassOptions', 'seriesPaneTypes',
            'laminateColors'
        );
    }

    /**
     * Show quote creation form with full configurator.
     */
    public function create()
    {
        $prefix = 'IQ-' . strtoupper(substr(Auth::user()->name, 0, 2)) . '-';
        $lastQuote = Quote::where('quote_number', 'like', $prefix . '%')->latest('id')->first();
        $nextNum = $lastQuote ? ((int) substr($lastQuote->quote_number, strlen($prefix))) + 1 : 1;
        $quoteNumber = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        $data = $this->loadConfiguratorData();

        return view('installer.quotes.create', array_merge($data, [
            'quoteNumber' => $quoteNumber,
            'customerNumber' => null,
            'customerName' => null,
            'entryDate' => now()->toDateString(),
            'expectedDelivery' => now()->addDays(14)->toDateString(),
            'validUntil' => now()->addDays(30)->toDateString(),
            'enteredBy' => Auth::user()->name,
            'quote' => null,
            'quoteItems' => collect(),
            'taxRate' => 0,
        ]));
    }

    /**
     * Store a new quote header.
     */
    public function store(Request $request)
    {
        $quote = Quote::create([
            'quote_number' => $request->quote_number ?: $this->generateQuoteNumber(),
            'customer_number' => $request->customer_number,
            'customer_name' => $request->customer_name,
            'reference' => $request->reference,
            'entry_date' => $request->entry_date ?: now(),
            'expected_delivery' => $request->expected_delivery,
            'valid_until' => $request->valid_until,
            'status' => 'draft',
            'entered_by' => Auth::user()->name,
            'tax_rule_id' => $request->tax_rule_id,
            'billing_name' => $request->billing_name ?? $request->customer_name,
            'billing_address' => $request->billing_address,
            'billing_city' => $request->billing_city,
            'billing_state' => $request->billing_state,
            'billing_zip' => $request->billing_zip,
            'billing_email' => $request->customer_email,
            'billing_phone' => $request->customer_phone,
            'order_type' => $request->order_type,
            'is_special_order' => $request->boolean('is_special_order'),
            'measurement_type' => $request->measurement_type ?? 'Imperial',
        ]);

        return response()->json([
            'success' => true,
            'quote_id' => $quote->id,
            'redirect' => route('installer.quotes.edit', $quote->id),
        ]);
    }

    /**
     * Show quote edit form (same view as create, with existing data).
     */
    public function edit($id)
    {
        $quote = Quote::where('entered_by', Auth::user()->name)->with('items')->findOrFail($id);
        $data = $this->loadConfiguratorData();

        return view('installer.quotes.create', array_merge($data, [
            'quoteNumber' => $quote->quote_number,
            'customerNumber' => $quote->customer_number,
            'customerName' => $quote->billing_name,
            'entryDate' => $quote->entry_date,
            'expectedDelivery' => $quote->expected_delivery,
            'validUntil' => $quote->valid_until,
            'enteredBy' => $quote->entered_by,
            'quote' => $quote,
            'quoteItems' => $quote->items,
            'taxRate' => 0,
        ]));
    }

    /**
     * Update quote header fields.
     */
    public function update(Request $request, $id)
    {
        $quote = Quote::where('entered_by', Auth::user()->name)->findOrFail($id);

        $quote->update([
            'quote_number'      => $request->quote_number ?: $quote->quote_number,
            'customer_number'   => $request->customer_number,
            'reference'         => $request->reference,
            'entry_date'        => $request->entry_date ?: $quote->entry_date,
            'expected_delivery' => $request->expected_delivery,
            'valid_until'       => $request->valid_until,
            'tax_rule_id'       => $request->tax_rule_id,
            'billing_name'      => $request->billing_name,
            'billing_address'   => $request->billing_address,
            'billing_city'      => $request->billing_city,
            'billing_state'     => $request->billing_state,
            'billing_zip'       => $request->billing_zip,
        ]);

        return response()->json([
            'success' => true,
            'quote_id' => $quote->id,
            'redirect' => route('installer.quotes.edit', $quote->id),
        ]);
    }

    /**
     * Return quote detail as JSON.
     */
    public function show($id)
    {
        $quote = Quote::where('entered_by', Auth::user()->name)->with('items')->findOrFail($id);

        return response()->json([
            'quote' => [
                'id'                => $quote->id,
                'quote_number'      => $quote->quote_number,
                'status'            => $quote->status,
                'billing_name'      => $quote->billing_name,
                'billing_email'     => $quote->billing_email,
                'billing_address'   => $quote->billing_address,
                'billing_city'      => $quote->billing_city,
                'billing_state'     => $quote->billing_state,
                'billing_zip'       => $quote->billing_zip,
                'customer_number'   => $quote->customer_number,
                'reference'         => $quote->reference,
                'entry_date'        => $quote->entry_date,
                'expected_delivery' => $quote->expected_delivery,
                'valid_until'       => $quote->valid_until,
                'entered_by'        => $quote->entered_by,
                'discount'          => $quote->discount,
                'notes'             => $quote->notes,
                'created_at'        => $quote->created_at?->format('M d, Y'),
            ],
            'items' => $quote->items->map(fn($i) => [
                'id'          => $i->id,
                'description' => $i->description,
                'series_type' => $i->series_type,
                'width'       => $i->width,
                'height'      => $i->height,
                'glass'       => $i->glass,
                'grid'        => $i->grid,
                'qty'         => $i->qty,
                'price'       => number_format($i->getRawOriginal('price'), 2),
                'total'       => number_format($i->getRawOriginal('total'), 2),
                'discount'    => $i->discount,
                'color_config'   => $i->color_config,
                'color_exterior' => $i->color_exterior,
                'color_interior' => $i->color_interior,
            ]),
            'summary' => [
                'items_count' => $quote->items->count(),
                'subtotal'    => number_format($quote->items->sum(fn($i) => $i->getRawOriginal('total')), 2),
            ],
        ]);
    }

    /**
     * Save draft (auto-save).
     */
    public function saveDraft(Request $request, $id)
    {
        $quote = Quote::where('entered_by', Auth::user()->name)->findOrFail($id);
        $quote->update($request->only([
            'customer_number', 'reference', 'entry_date', 'expected_delivery',
            'valid_until', 'status', 'billing_name', 'billing_address',
            'billing_city', 'billing_state', 'billing_zip', 'tax_rule_id',
            'discount', 'notes',
        ]));

        return response()->json(['success' => true]);
    }

    /**
     * Store/update a line item.
     */
    public function storeItem(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'description' => 'required|string',
                'series_id' => 'required|integer',
                'series_type' => 'required|string',
                'item_id' => 'nullable|integer',
                'width' => 'required|numeric',
                'height' => 'required|numeric',
                'glass' => 'nullable|string',
                'grid' => 'nullable|string',
                'qty' => 'required|numeric',
                'price' => 'required|numeric',
                'total' => 'required|numeric',
                'discount' => 'required|numeric',
                'item_comment' => 'nullable|string',
                'internal_note' => 'nullable|string',
                'color_config' => 'nullable|string',
                'color_exterior' => 'nullable|string',
                'color_interior' => 'nullable|string',
                'frame_type' => 'nullable|string',
                'fin_type' => 'nullable|string',
                'glass_type' => 'nullable|string',
                'spacer' => 'nullable|string',
                'tempered' => 'nullable|string',
                'tempered_fields' => 'nullable|array',
                'specialty_glass' => 'nullable|string',
                'grid_pattern' => 'nullable|string',
                'grid_profile' => 'nullable|string',
                'retrofit_bottom_only' => 'nullable|boolean',
                'no_logo_lock' => 'nullable|boolean',
                'double_lock' => 'nullable|boolean',
                'custom_lock_position' => 'nullable|boolean',
                'custom_vent_latch' => 'nullable|boolean',
                'knocked_down' => 'nullable|boolean',
                'addon' => 'nullable|string',
                'shape_definition_id' => 'nullable|integer',
                'shape_params' => 'nullable|string',
                'shape_code' => 'nullable|string|max:50',
                'panel_dimensions' => 'nullable|string',
            ]);

            $isUpdate = false;
            $data = [
                'description' => $request->description,
                'series_id' => $request->series_id,
                'series_type' => $request->series_type,
                'width' => $request->width,
                'height' => $request->height,
                'glass' => $request->glass,
                'grid' => $request->grid,
                'qty' => $request->qty,
                'price' => $request->price,
                'total' => $request->total,
                'discount' => $request->discount,
                'item_comment' => $request->item_comment,
                'internal_note' => $request->internal_note,
                'color_config' => $request->color_config,
                'color_exterior' => $request->color_exterior,
                'color_exterior_custom' => $request->color_exterior_custom,
                'color_interior' => $request->color_interior,
                'color_interior_custom' => $request->color_interior_custom,
                'frame_type' => $request->frame_type,
                'fin_type' => $request->fin_type,
                'glass_type' => $request->glass_type,
                'spacer' => $request->spacer,
                'tempered' => $request->tempered,
                'specialty_glass' => $request->specialty_glass,
                'tempered_fields' => json_encode($request->tempered_fields ?? []),
                'grid_pattern' => $request->grid_pattern,
                'grid_profile' => $request->grid_profile,
                'grid_detail' => $request->grid_detail,
                'retrofit_bottom_only' => $request->boolean('retrofit_bottom_only'),
                'no_logo_lock' => $request->boolean('no_logo_lock'),
                'double_lock' => $request->boolean('double_lock'),
                'custom_lock_position' => $request->boolean('custom_lock_position'),
                'custom_vent_latch' => $request->boolean('custom_vent_latch'),
                'knocked_down' => $request->boolean('knocked_down'),
                'checked_count' => collect([$request->retrofit_bottom_only, $request->no_logo_lock, $request->double_lock, $request->custom_lock_position, $request->custom_vent_latch])->filter()->count(),
                'addon' => $request->addon,
                'shape_definition_id' => $request->shape_definition_id ?: null,
                'shape_params' => $request->shape_params ? json_decode($request->shape_params, true) : null,
                'shape_code' => $request->shape_code ?: null,
                'panel_dimensions' => $request->panel_dimensions ? json_decode($request->panel_dimensions, true) : null,
            ];

            if (isset($validated['item_id']) && $existing = QuoteItem::find($validated['item_id'])) {
                $existing->update($data);
                $item = $existing;
                $isUpdate = true;
            } else {
                $data['quote_id'] = $id;
                $item = QuoteItem::create($data);
            }

            return response()->json([
                'success' => true,
                'item_id' => $item->id,
                'item' => $item,
                'is_update' => $isUpdate,
                'modifications' => [],
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a line item.
     */
    public function deleteItem($id, $itemId)
    {
        QuoteItem::where('quote_id', $id)->where('id', $itemId)->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Check price from matrix.
     */
    public function checkPrice(Request $request)
    {
        $seriesType = DB::table('elitevw_master_series_types')
            ->where('series_id', $request->series_id)
            ->where('series_type', $request->series_type)
            ->first();

        if (!$seriesType) {
            $allTypes = DB::table('elitevw_master_series_types')
                ->where('series_id', $request->series_id)->get();
            foreach ($allTypes as $row) {
                $decoded = json_decode($row->series_type, true);
                $types = is_array($decoded) ? $decoded : explode(',', $row->series_type);
                $types = array_map(fn($t) => strtoupper(trim($t)), $types);
                if (in_array(strtoupper(trim($request->series_type)), $types)) {
                    $seriesType = $row;
                    break;
                }
            }
        }

        if (!$seriesType) {
            return response()->json(['price' => 0, 'discount' => 0]);
        }

        $price = DB::table('elitevw_master_price_price_matrices')
            ->where('series_id', $request->series_id)
            ->where('series_type_id', $seriesType->id)
            ->where('width', $request->width)
            ->where('height', $request->height)
            ->value('price');

        $price = getMarkup($request->series_id, $price ?? 0);

        return response()->json(['price' => $price, 'discount' => 0]);
    }

    /**
     * Panel layout API.
     */
    public function panelLayout(Request $request)
    {
        $configurator = new WindowConfigurator();
        $seriesType = $request->input('series_type', '');
        $width = (float) $request->input('width', 36);
        $height = (float) $request->input('height', 60);

        $panelCount = $configurator->getPanelCount($seriesType);
        $fieldLayout = $configurator->getFieldLayout($seriesType, $width);

        $panels = [];
        foreach ($fieldLayout as $i => $field) {
            $panels[] = [
                'position' => $i,
                'label' => $field['type'] ?? ('Panel ' . ($i + 1)),
                'width' => round($field['width'] ?? ($width / max($panelCount, 1)), 3),
                'height' => round($height, 3),
            ];
        }

        return response()->json([
            'panel_count' => $panelCount,
            'panels' => $panels,
        ]);
    }

    /**
     * Apply per-item discounts.
     */
    public function applyDiscounts(Request $request, $id)
    {
        $quote = Quote::where('entered_by', Auth::user()->name)->findOrFail($id);
        $discounts = $request->input('discounts', []);
        $totalDiscount = 0;

        foreach ($discounts as $d) {
            $itemId = $d['item_id'] ?? null;
            $discount = floatval($d['discount'] ?? 0);
            if ($itemId && $discount >= 0) {
                QuoteItem::where('id', $itemId)
                    ->where('quote_id', $quote->id)
                    ->update(['discount' => $discount]);
                $totalDiscount += $discount;
            }
        }

        $quote->update(['discount' => $totalDiscount]);

        return response()->json([
            'success' => true,
            'total_discount' => $totalDiscount,
            'message' => 'Discounts applied successfully.',
        ]);
    }

    /**
     * Send quote to customer via email.
     */
    public function sendToCustomer(Request $request, $id)
    {
        $quote = Quote::where('entered_by', Auth::user()->name)->with('items')->findOrFail($id);

        $request->validate(['email' => 'required|email']);

        try {
            $html = view('emails.quote-summary', ['quote' => $quote])->render();

            Mail::html($html, function ($message) use ($request, $quote) {
                $message->to($request->email)
                        ->subject('Your Quote #' . $quote->quote_number);
            });

            $quote->update([
                'status' => 'sent',
                'billing_email' => $request->email,
            ]);

            return response()->json(['success' => true, 'message' => 'Quote sent to ' . $request->email]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Shape data for the shape picker.
     */
    public function shapes()
    {
        $categories = DB::table('elitevw_shape_categories')
            ->where('is_active', true)->orderBy('sort_order')->get();

        $definitions = DB::table('elitevw_shape_definitions')
            ->where('is_active', true)->orderBy('sort_order')->get()->groupBy('category_id');

        $result = $categories->map(function ($cat) use ($definitions) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'active_definitions' => ($definitions[$cat->id] ?? collect())->map(fn($d) => [
                    'id' => $d->id, 'code' => $d->code, 'name' => $d->name,
                    'description' => $d->description ?? '', 'svg_path' => $d->svg_path ?? '',
                    'params' => json_decode($d->params ?? '{}', true),
                ])->values(),
            ];
        })->values();

        return response()->json($result);
    }

    /**
     * Series → NFRC mapping.
     */
    public function seriesMap()
    {
        try {
            $map = DB::table('elitevw_master_series_configurations as sc')
                ->join('elitevw_master_series as s', 's.id', '=', 'sc.series_id')
                ->select('sc.name as series_type', 's.series')
                ->get()->pluck('series', 'series_type')->toArray();
        } catch (\Exception $e) {
            $map = [];
        }

        return response()->json(['map' => $map, 'variant' => 'main']);
    }

    /**
     * Delete a quote.
     */
    public function destroy($id)
    {
        $quote = Quote::where('entered_by', Auth::user()->name)->findOrFail($id);
        $quote->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('installer.quotes.index')->with('success', 'Quote deleted.');
    }

    /**
     * Generate installer-specific quote number.
     */
    private function generateQuoteNumber(): string
    {
        $prefix = 'IQ-' . strtoupper(substr(Auth::user()->name, 0, 2)) . '-';
        $last = Quote::where('quote_number', 'like', $prefix . '%')
            ->orderByRaw("CAST(SUBSTRING(quote_number, " . (strlen($prefix) + 1) . ") AS UNSIGNED) DESC")
            ->first();

        $nextNum = $last ? ((int) substr($last->quote_number, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }
}
