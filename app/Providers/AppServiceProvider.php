<?php

namespace App\Providers;

use File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Validator;

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

        Validator::extend('extension', function ($attribute, $value, $parameters, $validator) {
            return in_array(File::extension($value->getClientOriginalName()), $parameters);
        });

        Validator::replacer('extension', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':values', implode(', ', $parameters), $message);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
