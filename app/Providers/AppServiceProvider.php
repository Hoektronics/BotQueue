<?php

namespace App\Providers;

use App;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;

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

        Horizon::auth(function () {
            /** @var Request $request */

            /** @var User $user */
            $user = Auth::user();

            return $user->is_admin;
        });

        Paginator::defaultView('vendor.pagination.paginator');
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
