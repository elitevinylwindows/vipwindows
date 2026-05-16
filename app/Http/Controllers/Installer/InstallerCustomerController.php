<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\Job;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstallerCustomerController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Gather unique customers from quotes, jobs, and invoices created by this installer
        $quoteCustomers = Quote::where('created_by', $userId)
            ->whereNotNull('billing_name')
            ->select(
                'billing_name as name',
                DB::raw("MAX(billing_email) as email"),
                DB::raw("MAX(billing_phone) as phone"),
                DB::raw("MAX(billing_address) as address"),
                DB::raw("COUNT(*) as quote_count"),
                DB::raw("MAX(created_at) as last_activity")
            )
            ->groupBy('billing_name')
            ->get();

        $jobCustomers = Job::where('assigned_to', $userId)
            ->whereNotNull('customer_name')
            ->select(
                'customer_name as name',
                DB::raw("MAX(customer_email) as email"),
                DB::raw("MAX(customer_phone) as phone"),
                DB::raw("CONCAT(MAX(install_address), ', ', MAX(install_city), ', ', MAX(install_state), ' ', MAX(install_zip)) as address"),
                DB::raw("COUNT(*) as job_count"),
                DB::raw("MAX(created_at) as last_activity")
            )
            ->groupBy('customer_name')
            ->get();

        $invoiceCustomers = Invoice::where('created_by', $userId)
            ->whereNotNull('customer_name')
            ->select(
                'customer_name as name',
                DB::raw("MAX(customer_email) as email"),
                DB::raw("MAX(customer_phone) as phone"),
                DB::raw("MAX(customer_address) as address"),
                DB::raw("COUNT(*) as invoice_count"),
                DB::raw("MAX(created_at) as last_activity")
            )
            ->groupBy('customer_name')
            ->get();

        // Merge into unique customer list by name
        $customers = collect();

        foreach ($quoteCustomers as $c) {
            $customers[$c->name] = [
                'name' => $c->name,
                'email' => $c->email,
                'phone' => $c->phone,
                'address' => $c->address,
                'quotes' => $c->quote_count,
                'jobs' => 0,
                'invoices' => 0,
                'last_activity' => $c->last_activity,
            ];
        }

        foreach ($jobCustomers as $c) {
            if (isset($customers[$c->name])) {
                $customers[$c->name]['jobs'] = $c->job_count;
                $customers[$c->name]['email'] = $customers[$c->name]['email'] ?: $c->email;
                $customers[$c->name]['phone'] = $customers[$c->name]['phone'] ?: $c->phone;
                if ($c->last_activity > $customers[$c->name]['last_activity']) {
                    $customers[$c->name]['last_activity'] = $c->last_activity;
                }
            } else {
                $customers[$c->name] = [
                    'name' => $c->name,
                    'email' => $c->email,
                    'phone' => $c->phone,
                    'address' => $c->address,
                    'quotes' => 0,
                    'jobs' => $c->job_count,
                    'invoices' => 0,
                    'last_activity' => $c->last_activity,
                ];
            }
        }

        foreach ($invoiceCustomers as $c) {
            if (isset($customers[$c->name])) {
                $customers[$c->name]['invoices'] = $c->invoice_count;
                $customers[$c->name]['email'] = $customers[$c->name]['email'] ?: $c->email;
                $customers[$c->name]['phone'] = $customers[$c->name]['phone'] ?: $c->phone;
                if ($c->last_activity > $customers[$c->name]['last_activity']) {
                    $customers[$c->name]['last_activity'] = $c->last_activity;
                }
            } else {
                $customers[$c->name] = [
                    'name' => $c->name,
                    'email' => $c->email,
                    'phone' => $c->phone,
                    'address' => $c->address,
                    'quotes' => 0,
                    'jobs' => 0,
                    'invoices' => $c->invoice_count,
                    'last_activity' => $c->last_activity,
                ];
            }
        }

        $customers = $customers->sortByDesc('last_activity')->values();

        return view('installer.customers.index', compact('customers'));
    }
}
