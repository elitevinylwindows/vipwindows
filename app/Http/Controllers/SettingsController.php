<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\EmailTemplate;
use App\Models\Invoice;
use App\Models\Job;
use App\Models\Service;
use App\Models\Setting;
use App\Models\TechMeasure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        // Load hourly services (Tech Measure, Service, Repair — not Installation)
        $hourlyServices = Service::where('installer_pay_type', 'per_hour')
            ->orWhere(function ($q) {
                $q->whereIn('code', ['tech_measure', 'service', 'repair']);
            })
            ->orderBy('sort_order')
            ->get();

        // Defaults
        $defaults = [
            'company_name'          => 'VIP Windows',
            'company_phone'         => '(562) 368-0313',
            'company_email'         => 'info@vipwindows.net',
            'company_address'       => '',
            'business_hours'        => 'Mon-Fri 8am - 5pm',
            'booking_enabled'       => '1',
            'consultation_enabled'  => '1',
            'zoom_api_key'          => '',
            'zoom_api_secret'       => '',
            'teams_tenant_id'       => '',
            'teams_client_id'       => '',
            'teams_client_secret'   => '',
            'mail_from_name'        => 'VIP Windows',
            'mail_from_address'     => 'info@vipwindows.net',
            'smtp_host'             => 'smtp.zoho.com',
            'smtp_port'             => '465',
            'smtp_username'         => '',
            'smtp_password'         => '',
            'smtp_encryption'       => 'ssl',
            'qb_username'           => '',
            'qb_password'           => '',
            'qb_company_file'       => '',
            'qb_wc_url'             => url('/admin/quickbooks/wc'),
            'qb_sync_invoices'      => '0',
            'qb_sync_customers'     => '0',
            'qb_sync_payments'      => '0',
            'license_number'        => '',
            'sales_tax_rate'        => '10.75',
            'cc_fee_visa'           => '2',
            'cc_fee_amex'           => '2.5',
            'estimate_terms'        => 'Due on receipt',
            'estimate_footer'       => 'If the above prices, specifications and conditions are satisfactory and hereby accepted, the company requires signatures when orders are placed. By signing, customer has agreed Not to cancel the order or put a stop payment on orders that have been paid by Visa, M/C, check and/or cash. Estimate valid only 30 days.',
        ];

        $settings = array_merge($defaults, $settings);

        // Load email templates
        $templates = EmailTemplate::orderBy('id')->get();
        $placeholders = EmailTemplate::placeholders();

        return view('settings.index', compact('settings', 'hourlyServices', 'templates', 'placeholders'));
    }

    public function update(Request $request)
    {
        $fields = $request->except('_token');

        foreach ($fields as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value ?? '']
            );
        }

        return redirect()->route('admin.settings.index')->with('success', 'Settings saved successfully.');
    }

    /**
     * Preview how many records would be deleted.
     */
    public function truncatePreview(Request $request)
    {
        $validated = $request->validate([
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'categories'  => 'required|array|min:1',
            'categories.*' => 'in:tech_measures,installations,services,repairs',
        ]);

        $start = $validated['start_date'];
        $end   = $validated['end_date'];
        $cats  = $validated['categories'];
        $counts = [];

        if (in_array('tech_measures', $cats)) {
            $tmIds = TechMeasure::whereBetween('created_at', ["$start 00:00:00", "$end 23:59:59"])->pluck('id');
            $eventIds = TechMeasure::whereBetween('created_at', ["$start 00:00:00", "$end 23:59:59"])
                ->whereNotNull('calendar_event_id')->pluck('calendar_event_id');
            $counts[] = [
                'label' => 'Tech Measures',
                'count' => $tmIds->count(),
            ];
            $counts[] = [
                'label' => 'Tech Measure Items',
                'count' => DB::table('tech_measure_items')->whereIn('tech_measure_id', $tmIds)->count(),
            ];
            $counts[] = [
                'label' => 'Tech Measure Photos',
                'count' => DB::table('tech_measure_photos')->whereIn('tech_measure_id', $tmIds)->count(),
            ];
            $counts[] = [
                'label' => 'Linked Calendar Events',
                'count' => $eventIds->count(),
            ];
        }

        if (in_array('installations', $cats)) {
            $jobIds = Job::whereBetween('created_at', ["$start 00:00:00", "$end 23:59:59"])->pluck('id');
            $counts[] = [
                'label' => 'Installation Jobs',
                'count' => $jobIds->count(),
            ];
            $counts[] = [
                'label' => 'Job Items',
                'count' => DB::table('job_items')->whereIn('job_id', $jobIds)->count(),
            ];
            $counts[] = [
                'label' => 'Job Time Logs',
                'count' => DB::table('job_time_logs')->whereIn('job_id', $jobIds)->count(),
            ];
            $counts[] = [
                'label' => 'Job Notes',
                'count' => DB::table('job_notes')->whereIn('job_id', $jobIds)->count(),
            ];
            $invoiceIds = Invoice::whereBetween('created_at', ["$start 00:00:00", "$end 23:59:59"])->pluck('id');
            $counts[] = [
                'label' => 'Invoices',
                'count' => $invoiceIds->count(),
            ];
        }

        if (in_array('services', $cats)) {
            $svcServiceIds = Service::where('code', 'service')->pluck('id');
            $svcEventCount = CalendarEvent::whereIn('service_id', $svcServiceIds)
                ->whereBetween('event_date', [$start, $end])->count();
            $counts[] = [
                'label' => 'Service Events',
                'count' => $svcEventCount,
            ];
        }

        if (in_array('repairs', $cats)) {
            $repairServiceIds = Service::where('code', 'repair')->pluck('id');
            $repairEventCount = CalendarEvent::whereIn('service_id', $repairServiceIds)
                ->whereBetween('event_date', [$start, $end])->count();
            $counts[] = [
                'label' => 'Repair Events',
                'count' => $repairEventCount,
            ];
        }

        return response()->json(['counts' => $counts]);
    }

    /**
     * Execute the truncation.
     */
    public function truncate(Request $request)
    {
        $validated = $request->validate([
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'categories'  => 'required|array|min:1',
            'categories.*' => 'in:tech_measures,installations,services,repairs',
        ]);

        $start = $validated['start_date'];
        $end   = $validated['end_date'];
        $cats  = $validated['categories'];
        $deleted = [];

        DB::beginTransaction();
        try {
            if (in_array('tech_measures', $cats)) {
                $tmIds = TechMeasure::whereBetween('created_at', ["$start 00:00:00", "$end 23:59:59"])->pluck('id');
                $eventIds = TechMeasure::whereBetween('created_at', ["$start 00:00:00", "$end 23:59:59"])
                    ->whereNotNull('calendar_event_id')->pluck('calendar_event_id');

                // Delete children first
                $photoCount = DB::table('tech_measure_photos')->whereIn('tech_measure_id', $tmIds)->delete();
                $itemCount = DB::table('tech_measure_items')->whereIn('tech_measure_id', $tmIds)->delete();
                $tmCount = TechMeasure::whereIn('id', $tmIds)->delete();
                // Delete linked calendar events
                $evCount = CalendarEvent::whereIn('id', $eventIds)->delete();

                $deleted[] = "{$tmCount} tech measures, {$itemCount} items, {$photoCount} photos, {$evCount} events";
            }

            if (in_array('installations', $cats)) {
                $jobIds = Job::whereBetween('created_at', ["$start 00:00:00", "$end 23:59:59"])->pluck('id');

                $noteCount = DB::table('job_notes')->whereIn('job_id', $jobIds)->delete();
                $logCount = DB::table('job_time_logs')->whereIn('job_id', $jobIds)->delete();
                $jiCount = DB::table('job_items')->whereIn('job_id', $jobIds)->delete();
                $jobCount = Job::whereIn('id', $jobIds)->forceDelete();

                // Invoices
                $invoiceIds = Invoice::whereBetween('created_at', ["$start 00:00:00", "$end 23:59:59"])->pluck('id');
                DB::table('invoice_items')->whereIn('invoice_id', $invoiceIds)->delete();
                $invCount = Invoice::whereIn('id', $invoiceIds)->forceDelete();

                $deleted[] = "{$jobCount} jobs, {$jiCount} job items, {$logCount} time logs, {$noteCount} notes, {$invCount} invoices";
            }

            if (in_array('services', $cats)) {
                $svcServiceIds = Service::where('code', 'service')->pluck('id');
                $svcCount = CalendarEvent::whereIn('service_id', $svcServiceIds)
                    ->whereBetween('event_date', [$start, $end])->delete();
                $deleted[] = "{$svcCount} service events";
            }

            if (in_array('repairs', $cats)) {
                $repairServiceIds = Service::where('code', 'repair')->pluck('id');
                $repairCount = CalendarEvent::whereIn('service_id', $repairServiceIds)
                    ->whereBetween('event_date', [$start, $end])->delete();
                $deleted[] = "{$repairCount} repair events";
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Deleted: ' . implode('; ', $deleted) . '.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Truncation failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update a service's hourly pay rate (AJAX).
     */
    public function updateRate(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'installer_pay' => 'required|numeric|min:0',
        ]);

        $service->update(['installer_pay' => $validated['installer_pay']]);

        return response()->json(['success' => true, 'message' => 'Rate updated.']);
    }
}
