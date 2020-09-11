<?php

namespace App\Providers;

use App;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
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

        Relation::morphMap([
            'bots' => App\Models\Bot::class,
            'clusters' => App\Models\Cluster::class,
        ]);
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
