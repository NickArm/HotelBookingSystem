<?php

namespace App\Providers;

use App\Models\SmtpSetting;
use Config;
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
        //
        if (\Schema::hasTable('smtp_settings')) {
            $smtpsetting = SmtpSetting::first();

            if ($smtpsetting) {
                $data = [
                    'driver' => $smtpsetting->mailer,
                    'host' => $smtpsetting->host,
                    'port' => $smtpsetting->port,
                    'username' => $smtpsetting->username,
                    'password' => $smtpsetting->password,
                    'encryption' => $smtpsetting->encryption,
                    'from' => [
                        'address' => $smtpsetting->from_address,
                        'name' => 'Easyhotel',
                    ],
                ];
                Config::set('mail', $data);
            }

        } // end if
    }
}
