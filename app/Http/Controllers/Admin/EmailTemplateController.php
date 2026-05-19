<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\JobNotification;
use App\Models\EmailTemplate;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailTemplateController extends Controller
{
    /**
     * Show all email templates for editing.
     */
    public function index()
    {
        $templates = EmailTemplate::orderBy('id')->get();
        $placeholders = EmailTemplate::placeholders();

        return view('admin.email-templates.index', compact('templates', 'placeholders'));
    }

    /**
     * Update a single template.
     */
    public function update(Request $request, $id)
    {
        $template = EmailTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:500',
            'body' => 'required|string|max:10000',
            'is_active' => 'nullable|boolean',
        ]);

        $template->update([
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.email-templates.index')
            ->with('success', "Template \"{$template->name}\" updated.");
    }

    /**
     * Send a job notification email using a template.
     */
    public function sendJobEmail(Request $request, $jobId)
    {
        $job = Job::with('service')->findOrFail($jobId);

        $validated = $request->validate([
            'template_slug' => 'required|string',
        ]);

        $template = EmailTemplate::where('slug', $validated['template_slug'])->firstOrFail();

        if (!$template->is_active) {
            return response()->json(['error' => 'This template is currently disabled.'], 422);
        }

        if (empty($job->customer_email)) {
            return response()->json(['error' => 'No customer email on this job.'], 422);
        }

        $data = $this->buildPlaceholderData($job);
        $rendered = $template->render($data);

        Mail::to($job->customer_email)->send(
            new JobNotification($rendered['subject'], $rendered['body'], $job->customer_name)
        );

        return response()->json([
            'success' => true,
            'message' => "\"{$template->name}\" sent to {$job->customer_email}.",
        ]);
    }

    /**
     * Preview a rendered template for a specific job.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:email_templates,id',
            'job_id' => 'required|exists:vip_jobs,id',
        ]);

        $template = EmailTemplate::findOrFail($request->template_id);
        $job = Job::with('service')->findOrFail($request->job_id);

        $data = $this->buildPlaceholderData($job);
        $rendered = $template->render($data);

        return response()->json([
            'subject' => $rendered['subject'],
            'body' => $rendered['body'],
        ]);
    }

    /**
     * Build placeholder data from a job.
     */
    private function buildPlaceholderData(Job $job): array
    {
        return [
            'customer_name'   => $job->customer_name ?? 'Customer',
            'job_number'      => $job->job_number,
            'scheduled_date'  => $job->scheduled_date ? $job->scheduled_date->format('l, F j, Y') : 'TBD',
            'scheduled_time'  => $job->scheduled_time ?? 'TBD',
            'install_address' => trim(implode(', ', array_filter([
                $job->install_address,
                $job->install_city,
                $job->install_state,
                $job->install_zip,
            ]))) ?: 'TBD',
            'service_name'    => $job->service->name ?? 'Installation',
            'company_phone'   => '(562) 368-0313',
            'company_name'    => 'VIP Windows',
        ];
    }
}
