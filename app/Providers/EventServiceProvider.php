<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     */
    protected $listen = [
        \Laravel\Cashier\Events\SubscriptionCreated::class => [
            \App\Listeners\HandleSubscriptionCreated::class,
        ],
        \Laravel\Cashier\Events\SubscriptionUpdated::class => [
            \App\Listeners\HandleSubscriptionUpdated::class,
        ],
        \Laravel\Cashier\Events\SubscriptionCancelled::class => [
            \App\Listeners\HandleSubscriptionCancelled::class,
        ],
    ];

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
    }
}
