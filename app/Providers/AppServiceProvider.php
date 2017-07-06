<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use STS\Filesystem\VfsFilesystemServiceProvider;

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
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() === 'testing') {
            if (class_exists(VfsFilesystemServiceProvider::class)) {
                $this->app->register(VfsFilesystemServiceProvider::class);
            }
        }
    }
}
