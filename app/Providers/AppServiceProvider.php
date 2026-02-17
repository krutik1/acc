<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $mailer = \App\Models\Setting::get('mail_mailer');

            if ($mailer) {
                // Determine encryption (handle 'null' string or empty)
                $encryption = \App\Models\Setting::get('mail_encryption');
                if ($encryption === 'null' || empty($encryption)) {
                    $encryption = null;
                }

                config([
                    'mail.default' => $mailer,
                    'mail.mailers.smtp.host' => \App\Models\Setting::get('mail_host', config('mail.mailers.smtp.host')),
                    'mail.mailers.smtp.port' => \App\Models\Setting::get('mail_port', config('mail.mailers.smtp.port')),
                    'mail.mailers.smtp.encryption' => $encryption,
                    'mail.mailers.smtp.username' => \App\Models\Setting::get('mail_username', config('mail.mailers.smtp.username')),
                    'mail.mailers.smtp.password' => \App\Models\Setting::get('mail_password', config('mail.mailers.smtp.password')),
                    'mail.from.address' => \App\Models\Setting::get('mail_from_address', config('mail.from.address')),
                    'mail.from.name' => \App\Models\Setting::get('mail_from_name', config('mail.from.name')),
                ]);
            }
        }
    }
}
