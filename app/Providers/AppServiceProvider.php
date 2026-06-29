<?php

namespace App\Providers;

use App\Services\Payment\FapshiProvider;
use App\Services\Payment\PaymentProvider;
use App\Services\Payment\SandboxProvider;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
