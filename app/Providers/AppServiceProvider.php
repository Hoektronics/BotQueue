<?php

namespace App\Providers;

use App\HostRequest;
use App\Observers\HostRequestObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        HostRequest::observe(HostRequestObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
