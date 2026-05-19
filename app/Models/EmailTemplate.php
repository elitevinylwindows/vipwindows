<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $table = 'email_templates';

    protected $fillable = [
        'slug', 'name', 'subject', 'body', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Available placeholder tokens with descriptions.
     */
    public static function placeholders(): array
    {
        return [
            '{{customer_name}}'   => 'Customer full name',
            '{{job_number}}'      => 'Job reference number (e.g. JOB-00012)',
            '{{scheduled_date}}'  => 'Scheduled date (e.g. Monday, June 15, 2026)',
            '{{scheduled_time}}'  => 'Scheduled time (e.g. 9:00 AM)',
            '{{install_address}}' => 'Installation address',
            '{{service_name}}'    => 'Service name',
            '{{company_phone}}'   => 'Company phone number',
            '{{company_name}}'    => 'Company name (VIP Windows)',
        ];
    }

    /**
     * Replace placeholders in subject/body with actual values.
     */
    public function render(array $data): array
    {
        $subject = $this->subject;
        $body = $this->body;

        foreach ($data as $key => $value) {
            $token = '{{' . $key . '}}';
            $subject = str_replace($token, $value ?? '', $subject);
            $body = str_replace($token, $value ?? '', $body);
        }

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }
}
