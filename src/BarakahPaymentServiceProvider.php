<?php

namespace RelicSoftware\BarakahPayment;

use Illuminate\Support\ServiceProvider;

class BarakahPaymentServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge package configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/barakah.php',
            'barakah'
        );

        // Bind singleton BarakahClient
        $this->app->singleton(BarakahClient::class, function ($app) {
            return new BarakahClient($app['config']['barakah'] ?? []);
        });

        $this->app->alias(BarakahClient::class, 'barakah');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish configuration file
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/barakah.php' => config_path('barakah.php'),
            ], 'barakah-config');
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [BarakahClient::class, 'barakah'];
    }
}

