<?php

namespace App\Providers;

use App\Services\Payment\FapshiProvider;
use App\Services\Payment\PaymentProvider;
use App\Services\Payment\SandboxProvider;
use App\Services\Sms\HttpSmsProvider;
use App\Services\Sms\LogSmsProvider;
use App\Services\Sms\SmsProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Driver Mobile Money sélectionné via config (services.payment.driver).
        $this->app->bind(PaymentProvider::class, function () {
            return match (config('services.payment.driver', 'sandbox')) {
                'fapshi' => new FapshiProvider(config('services.fapshi')),
                default => new SandboxProvider(),
            };
        });

        // Passerelle SMS sélectionnée via config (services.sms.driver).
        $this->app->bind(SmsProvider::class, function () {
            return match (config('services.sms.driver', 'log')) {
                'http' => new HttpSmsProvider(config('services.sms.http')),
                default => new LogSmsProvider(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
