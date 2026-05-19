<?php

namespace App\Providers;

use App\Models\TicketOrder;
use App\Observers\TicketOrderObserver;
use Illuminate\Support\Facades\URL;
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
        TicketOrder::observe(TicketOrderObserver::class);

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
