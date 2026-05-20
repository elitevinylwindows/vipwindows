<?php

namespace App\Http\Controllers;

use App\Mail\JobNotification;
use App\Models\CalendarEvent;
use App\Models\Crew;
use App\Models\EmailTemplate;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Job;
use App\Models\JobItem;
use App\Models\Service;
use App\Models\Setting;
use App\Models\TechMeasure;
use App\Models\TechMeasureItem;
use App\Models\TechMeasurePhoto;
use App\Models\VipMasterOption;
use App\Models\InstallationType;
use App\Models\VipUser;
use App\Services\EstimatePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class TechMeasureController extends Controller
{
    /**
     * List all tech measures (admin view).
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');

        $query = TechMeasure::with(['assignee', 'crew', 'calendarEvent'])
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $measures = $query->paginate(50);

        $techs = VipUser::whereIn('role', ['installer', 'technician'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $crews = Crew::where('status', 'active')->orderBy('name')->get();

        $unitOptions = VipMasterOption::optionsFor('unit');
        $frameTypeOptions = VipMasterOption::optionsFor('frame_type');
        $gridOptions = VipMasterOption::optionsFor('grid');
        $patternOptions = VipMasterOption::optionsFor('pattern');

        $installationTypes = InstallationType::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.tech-measures.index', compact('measures', 'status', 'techs', 'crews', 'unitOptions', 'frameTypeOptions', 'gridOptions', 'patternOptions', 'installationTypes'));
    }

    /**
     * Show a tech measure detail (admin view with all items and photos).
     */
    public function show($id)
    {
        $measure = TechMeasure::with(['items.photos', 'photos', 'assignee', 'crew', 'calendarEvent'])
            ->findOrFail($id);

        // For JSON request (from left rail)
        if (request()->wantsJson()) {
            return response()->json([
                'measure' => $measure,
                'items' => $measure->items->map(function ($item) {
                    return array_merge($item->toArray(), [
                        'photos' => $item->photos->map(fn($p) => [
                            'id' => $p->id,
                            'url' => asset('storage/' . $p->file_path),
                            'caption' => $p->caption,
                        ]),
                    ]);
                }),
                'photos' => $measure->photos->where('tech_measure_item_id', null)->map(fn($p) => [
                    'id' => $p->id,
                    'url' => asset('storage/' . $p->file_path),
                    'caption' => $p->caption,
                ]),
            ]);
        }

        return view('admin.tech-measures.show', compact('measure'));
    }

    /**
     * Update tech measure details (admin edit).
     */
    public function update(Request $request, $id)
    {
        $measure = TechMeasure::findOrFail($id);

        $validated = $request->validate([
            'customer_name'  => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'address'        => 'nullable|string|max:500',
            'notes'          => 'nullable|string',
        ]);

        $measure->update($validated);

        return response()->json(['success' => true, 'measure' => $measure->fresh()]);
    }

    /**
     * Send email to tech measure customer (admin).
     */
    public function sendEmail(Request $request, $id)
    {
        $measure = TechMeasure::findOrFail($id);

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if (!$measure->customer_email) {
            return response()->json(['error' => 'No customer email on file.'], 422);
        }

        \Illuminate\Support\Facades\Mail::raw($validated['message'], function ($mail) use ($measure, $validated) {
            $mail->to($measure->customer_email)
                 ->subject($validated['subject']);
        });

        return response()->json(['success' => true, 'message' => 'Email sent to ' . $measure->customer_email]);
    }

    /**
     * Create a tech measure from a calendar event.
     */
    public function createFromEvent(Request $request)
    {
        $validated = $request->validate([
            'calendar_event_id' => 'required|exists:calendar_events,id',
        ]);

        $event = CalendarEvent::with('crew')->findOrFail($validated['calendar_event_id']);

        // Check if measure already exists for this event
        $existing = TechMeasure::where('calendar_event_id', $event->id)->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'measure_id' => $existing->id,
                'message' => 'Tech measure already exists.',
            ]);
        }

        $measure = TechMeasure::create([
            'calendar_event_id' => $event->id,
            'customer_name' => $event->customer_name,
            'customer_email' => $event->customer_email,
            'customer_phone' => $event->customer_phone,
            'address' => $event->address,
            'status' => 'pending',
            'assigned_to' => $event->crew?->members()->first()?->id,
            'crew_id' => $event->crew_id,
            'created_by' => Auth::guard('vip')->id(),
        ]);

        return response()->json([
            'success' => true,
            'measure_id' => $measure->id,
        ]);
    }

    /**
     * Add a measurement item (admin).
     */
    public function addItem(Request $request, $id)
    {
        $measure = TechMeasure::findOrFail($id);

        $validated = $request->validate([
            'room_label'   => 'nullable|string|max:100',
            'description'  => 'nullable|string|max:500',
            'series_type'  => 'nullable|string|max:100',
            'width'        => 'nullable',
            'height'       => 'nullable',
            'qty'          => 'nullable|integer|min:1',
            'notes'        => 'nullable|string|max:500',
        ]);

        $item = $measure->items()->create(array_merge($validated, [
            'qty' => $validated['qty'] ?? 1,
        ]));

        return response()->json(['success' => true, 'item' => $item]);
    }

    /**
     * Update a measurement item (admin).
     */
    public function updateItem(Request $request, $measureId, $itemId)
    {
        $item = TechMeasureItem::where('tech_measure_id', $measureId)->findOrFail($itemId);
        $item->update($request->all());

        return response()->json(['success' => true, 'item' => $item->fresh()]);
    }

    /**
     * Remove a measurement item (admin).
     */
    public function removeItem($measureId, $itemId)
    {
        $item = TechMeasureItem::where('tech_measure_id', $measureId)->findOrFail($itemId);

        foreach ($item->photos as $photo) {
            Storage::disk('public')->delete($photo->file_path);
            $photo->delete();
        }

        $item->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Update notes / frame type (admin).
     */
    public function updateNotes(Request $request, $id)
    {
        $measure = TechMeasure::findOrFail($id);
        $data = $request->only(['notes', 'frame_type', 'retrofit_bottom_only', 'block_frame_bottom']);
        $measure->update($data);

        return response()->json(['success' => true]);
    }

    /**
     * Update grid settings (admin).
     */
    public function updateGrids(Request $request, $id)
    {
        $measure = TechMeasure::findOrFail($id);
        $measure->update($request->only(['has_grids', 'grid_list', 'grid_pattern']));

        return response()->json(['success' => true]);
    }

    /**
     * Upload a photo (admin).
     */
    public function uploadPhoto(Request $request, $id)
    {
        $request->validate([
            'photo'   => 'required|image|max:10240',
            'item_id' => 'nullable|integer',
            'caption' => 'nullable|string|max:255',
        ]);

        $measure = TechMeasure::findOrFail($id);
        $path = $request->file('photo')->store('tech-measures/' . $id, 'public');

        $photo = TechMeasurePhoto::create([
            'tech_measure_id'      => $measure->id,
            'tech_measure_item_id' => $request->input('item_id'),
            'file_path'            => $path,
            'caption'              => $request->input('caption'),
        ]);

        return response()->json(['success' => true, 'photo' => $photo]);
    }

    /**
     * Delete a photo (admin).
     */
    public function deletePhoto($measureId, $photoId)
    {
        $photo = TechMeasurePhoto::where('tech_measure_id', $measureId)->findOrFail($photoId);
        Storage::disk('public')->delete($photo->file_path);
        $photo->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Convert a completed tech measure to a job.
     */
    public function convertToQuote(Request $request, $id)
    {
        $measure = TechMeasure::with('items')->findOrFail($id);

        if ($measure->status === 'converted') {
            return response()->json(['error' => 'This tech measure has already been converted to a job.'], 422);
        }

        if ($measure->items->isEmpty()) {
            return response()->json(['error' => 'No measurement items to convert.'], 422);
        }

        // Validate PDF attachment
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:20480',
        ]);

        // Store the PDF
        $pdfPath = $request->file('pdf')->store('tech-measures/' . $id . '/job-docs', 'public');

        // Parse line items and measurement prices
        $lineItems = json_decode($request->input('line_items', '[]'), true) ?: [];
        $measurementPrices = json_decode($request->input('measurement_prices', '[]'), true) ?: [];

        // Calculate totals
        $lineItemsTotal = collect($lineItems)->sum('total');
        $measurementsTotal = collect($measurementPrices)->sum('price');
        $grandTotal = $lineItemsTotal + $measurementsTotal;

        // Generate job number
        $lastJob = Job::withTrashed()->orderByDesc('id')->first();
        $nextNum = $lastJob ? (int) substr($lastJob->job_number, 4) + 1 : 1;
        $jobNumber = 'JOB-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

        // Get the Installation service for the job
        $installService = Service::where('code', 'installation')->first();

        // Determine status based on scheduling
        $scheduledDate = $request->input('scheduled_date');
        $jobStatus = $scheduledDate ? 'scheduled' : 'pending';

        // Create the Job record
        $job = Job::create([
            'job_number'         => $jobNumber,
            'service_id'         => $installService?->id,
            'customer_name'      => $measure->customer_name,
            'customer_email'     => $measure->customer_email,
            'customer_phone'     => $measure->customer_phone,
            'install_address'    => $measure->address,
            'install_city'       => $measure->city,
            'install_state'      => $measure->state,
            'install_zip'        => $measure->zip,
            'description'        => 'Converted from tech measure. ' . ($measure->notes ?? ''),
            'status'             => $jobStatus,
            'priority'           => 'normal',
            'assignment_type'    => $measure->crew_id ? 'crew' : 'installer',
            'assigned_to'        => $measure->assigned_to,
            'crew_id'            => $measure->crew_id,
            'scheduled_date'     => $scheduledDate ?: null,
            'scheduled_time'     => $request->input('scheduled_time') ?: null,
            'end_date'           => $request->input('end_date') ?: null,
            'estimated_duration' => $request->input('estimated_duration') ?: null,
            'notes'              => $measure->notes,
            'created_by'         => Auth::guard('vip')->id(),
        ]);

        // Create job line items from the service line items
        $sortOrder = 0;
        foreach ($lineItems as $item) {
            if (empty($item['service_id']) || empty($item['qty'])) continue;

            $service = Service::find($item['service_id']);
            if (!$service) continue;

            // Calculate installer pay
            $unitPay = 0;
            switch ($service->installer_pay_type) {
                case 'percentage':
                    $unitPay = $service->base_price * ($service->installer_pay / 100);
                    break;
                case 'per_job':
                    $unitPay = $service->installer_pay;
                    break;
                default:
                    $unitPay = $service->installer_pay ?? 0;
            }

            JobItem::create([
                'job_id'      => $job->id,
                'service_id'  => $service->id,
                'description' => $item['service_name'] ?? $service->name,
                'item_type'   => 'service',
                'qty'         => $item['qty'],
                'unit_pay'    => $unitPay,
                'total_pay'   => $unitPay * $item['qty'],
                'sort_order'  => $sortOrder++,
            ]);
        }

        // --- Create Invoice ---
        $lastInvoice = Invoice::withTrashed()->orderByDesc('id')->first();
        $nextInvNum = $lastInvoice ? (int) substr($lastInvoice->invoice_number, 4) + 1 : 1;
        $invoiceNumber = 'INV-' . str_pad($nextInvNum, 5, '0', STR_PAD_LEFT);

        $taxRate = (float) (Setting::where('key', 'sales_tax_rate')->value('value') ?? 10.75);
        $subtotal = $grandTotal;
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $invoiceTotal = $subtotal + $taxAmount;

        $customerAddress = trim(implode(', ', array_filter([
            $measure->address, $measure->city, $measure->state, $measure->zip,
        ])));

        $invoice = Invoice::create([
            'invoice_number'  => $invoiceNumber,
            'customer_name'   => $measure->customer_name,
            'customer_email'  => $measure->customer_email,
            'customer_phone'  => $measure->customer_phone,
            'customer_address'=> $customerAddress ?: null,
            'billing_address' => $customerAddress ?: null,
            'status'          => 'draft',
            'subtotal'        => $subtotal,
            'tax_rate'        => $taxRate,
            'tax_amount'      => $taxAmount,
            'total'           => $invoiceTotal,
            'amount_paid'     => 0,
            'balance_due'     => $invoiceTotal,
            'notes'           => "Created from tech measure conversion — Job {$jobNumber}",
            'created_by'      => Auth::guard('vip')->id(),
        ]);

        // Add service line items to invoice
        $invSortOrder = 0;
        foreach ($lineItems as $item) {
            if (empty($item['service_id']) || empty($item['qty'])) continue;
            $unitPrice = $item['unit_price'] ?? 0;
            $itemTotal = ($item['qty'] ?? 1) * $unitPrice;

            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => $item['service_name'] ?? 'Service',
                'qty'         => $item['qty'],
                'unit_price'  => $unitPrice,
                'total'       => $itemTotal,
                'sort_order'  => $invSortOrder++,
            ]);
        }

        // Add measurement items with prices to invoice
        foreach ($measure->items as $idx => $mItem) {
            $mPrice = collect($measurementPrices)->firstWhere('item_id', $mItem->id);
            $price = $mPrice ? ($mPrice['price'] ?? 0) : 0;
            if ($price <= 0) continue;

            $desc = trim(implode(' | ', array_filter([
                ($mItem->qty > 1 ? $mItem->qty . 'x' : ''),
                $mItem->width ? $mItem->width . ' × ' . ($mItem->height ?? '') : '',
                $mItem->description,
                $mItem->room_label,
            ])));

            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => $desc ?: "Measurement item #{$idx}",
                'qty'         => $mItem->qty ?: 1,
                'unit_price'  => $price / ($mItem->qty ?: 1),
                'total'       => $price,
                'sort_order'  => $invSortOrder++,
            ]);
        }

        // Link invoice to job
        $job->update(['invoice_id' => $invoice->id]);

        // Store conversion data on the measure and link to the new job
        $measure->update([
            'status'       => 'converted',
            'converted_at' => now(),
            'converted_by' => Auth::guard('vip')->id(),
            'job_id'       => $job->id,
            'job_data'     => json_encode([
                'line_items'         => $lineItems,
                'measurement_prices' => $measurementPrices,
                'line_items_total'   => $lineItemsTotal,
                'measurements_total' => $measurementsTotal,
                'grand_total'        => $grandTotal,
                'pdf_path'           => $pdfPath,
                'invoice_id'         => $invoice->id,
                'invoice_number'     => $invoiceNumber,
            ]),
        ]);

        // Generate estimate PDF page and merge with uploaded PDF
        $estimateError = null;
        try {
            $estimateService = new EstimatePdfService();
            $mergedPdfPath = $estimateService->generateAndMerge($job);
            if ($mergedPdfPath) {
                $pdfPath = $mergedPdfPath; // Use merged PDF for email attachment
            }
        } catch (\Exception $e) {
            $estimateError = $e->getMessage();
            \Log::error("Estimate PDF generation failed: " . $e->getMessage());
        }

        // Send email notification to customer
        $emailSent = false;
        if (!empty($job->customer_email)) {
            try {
                $placeholderData = [
                    'customer_name'   => $job->customer_name ?? 'Customer',
                    'job_number'      => $job->job_number,
                    'scheduled_date'  => $job->scheduled_date ? $job->scheduled_date->format('l, F j, Y') : 'To be scheduled',
                    'scheduled_time'  => $job->scheduled_time ?: 'To be confirmed',
                    'install_address' => trim(implode(', ', array_filter([
                        $job->install_address, $job->install_city, $job->install_state, $job->install_zip,
                    ]))) ?: 'TBD',
                    'service_name'    => $installService->name ?? 'Installation',
                    'company_phone'   => '(562) 368-0313',
                    'company_name'    => 'VIP Windows',
                ];

                // Try to use a "job_created" email template if one exists
                $template = EmailTemplate::where('slug', 'job_created')->where('is_active', true)->first();

                if ($template) {
                    $rendered = $template->render($placeholderData);
                    $emailSubject = $rendered['subject'];
                    $emailBody = $rendered['body'];
                } else {
                    // Fallback: build a default email
                    $emailSubject = "Your Installation Job {$jobNumber} — VIP Windows";
                    $emailBody = "Dear {$placeholderData['customer_name']},\n\n"
                        . "Thank you for choosing VIP Windows! Your installation job has been created.\n\n"
                        . "Job Number: {$jobNumber}\n"
                        . "Address: {$placeholderData['install_address']}\n"
                        . "Scheduled Date: {$placeholderData['scheduled_date']}\n"
                        . "Scheduled Time: {$placeholderData['scheduled_time']}\n\n"
                        . "We will be in touch to confirm the details. If you have any questions, please don't hesitate to call us at {$placeholderData['company_phone']}.\n\n"
                        . "Best regards,\nVIP Windows";
                }

                Mail::to($job->customer_email)->send(
                    new JobNotification($emailSubject, $emailBody, $job->customer_name ?? 'Customer', $pdfPath)
                );
                $emailSent = true;
            } catch (\Exception $e) {
                $emailError = $e->getMessage();
                \Log::warning("Failed to send job creation email for {$jobNumber}: " . $emailError);
            }
        }

        $message = "Job {$jobNumber} & Invoice {$invoiceNumber} created successfully.";
        if ($estimateError) {
            $message .= " (Estimate PDF failed: {$estimateError})";
        }
        if ($emailSent) {
            $message .= " Notification sent to {$job->customer_email}.";
        } elseif (empty($job->customer_email)) {
            $message .= " No customer email on file — notification not sent.";
        } elseif (!empty($emailError)) {
            $message .= " Email failed: {$emailError}";
        }

        return response()->json([
            'success'        => true,
            'message'        => $message,
            'job_id'         => $job->id,
            'job_number'     => $jobNumber,
            'invoice_id'     => $invoice->id,
            'invoice_number' => $invoiceNumber,
            'email_sent'     => $emailSent,
        ]);
    }
}
