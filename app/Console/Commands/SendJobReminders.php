<?php

namespace App\Console\Commands;

use App\Mail\JobNotification;
use App\Models\EmailTemplate;
use App\Models\Job;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendJobReminders extends Command
{
    protected $signature = 'jobs:send-reminders';
    protected $description = 'Send day-before reminder emails for jobs scheduled tomorrow';

    public function handle(): int
    {
        $template = EmailTemplate::where('slug', 'day-before-reminder')
            ->where('is_active', true)
            ->first();

        if (!$template) {
            $this->warn('Day-before reminder template is missing or disabled.');
            return 1;
        }

        $tomorrow = now()->addDay()->toDateString();

        $jobs = Job::with('service')
            ->whereDate('scheduled_date', $tomorrow)
            ->whereNotNull('customer_email')
            ->whereIn('status', ['scheduled', 'pending'])
            ->get();

        $sent = 0;
        foreach ($jobs as $job) {
            $data = [
                'customer_name'   => $job->customer_name ?? 'Customer',
                'job_number'      => $job->job_number,
                'scheduled_date'  => $job->scheduled_date->format('l, F j, Y'),
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

            $rendered = $template->render($data);

            try {
                Mail::to($job->customer_email)->send(
                    new JobNotification($rendered['subject'], $rendered['body'], $job->customer_name)
                );
                $sent++;
                $this->info("Sent reminder for {$job->job_number} → {$job->customer_email}");
            } catch (\Exception $e) {
                $this->error("Failed for {$job->job_number}: {$e->getMessage()}");
            }
        }

        $this->info("Done. {$sent} reminder(s) sent.");
        return 0;
    }
}
