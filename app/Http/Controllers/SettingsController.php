<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Http\Request;

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
