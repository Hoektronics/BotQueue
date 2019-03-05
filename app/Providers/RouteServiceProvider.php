<?php

namespace App\Providers;

use App;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Contracts\Auth\Factory as Auth;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Route::model('bot', App\Bot::class);
        Route::model('host', App\Host::class);
        Route::model('host_request', App\HostRequest::class);
        Route::model('job', App\Job::class);
        Route::model('user', App\User::class);
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapHostRoutes();

        $this->mapChannelRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace('App\Http\Controllers\Api')
            ->group(base_path('routes/api.php'));
    }

    /**
     * Define the "host" routes for the application.
     *
     * @return void
     */
    protected function mapHostRoutes()
    {
        Route::post('host', 'App\Http\Controllers\HostApiController@command');
    }

    /**
     * Define the broadcast channel route for the application.
     *
     * @return void
     */
    protected function mapChannelRoutes()
    {
        Route::post('/broadcasting/auth', 'App\Http\Controllers\BroadcastController@auth')
            ->middleware('resolve_host');
    }
}
