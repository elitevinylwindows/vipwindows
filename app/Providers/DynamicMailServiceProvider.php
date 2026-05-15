<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

class DynamicMailServiceProvider extends ServiceProvider
{
    public function boot()
    {
        try {
            if (Schema::hasTable('settings')) {
                $settings = \App\Models\Setting::pluck('value', 'key')->toArray();

                if (!empty($settings['smtp_host'])) {
                    Config::set('mail.mailers.smtp.host', $settings['smtp_host']);
                }
                if (!empty($settings['smtp_port'])) {
                    Config::set('mail.mailers.smtp.port', (int) $settings['smtp_port']);
                }
                if (!empty($settings['smtp_username'])) {
                    Config::set('mail.mailers.smtp.username', $settings['smtp_username']);
                }
                if (!empty($settings['smtp_password'])) {
                    Config::set('mail.mailers.smtp.password', $settings['smtp_password']);
                }
                if (!empty($settings['smtp_encryption'])) {
                    Config::set('mail.mailers.smtp.encryption', $settings['smtp_encryption']);
                }
                if (!empty($settings['mail_from_address'])) {
                    Config::set('mail.from.address', $settings['mail_from_address']);
                }
                if (!empty($settings['mail_from_name'])) {
                    Config::set('mail.from.name', $settings['mail_from_name']);
                }
            }
        } catch (\Exception $e) {
            // Database might not be available yet (during migrations, etc.)
        }
    }
}
