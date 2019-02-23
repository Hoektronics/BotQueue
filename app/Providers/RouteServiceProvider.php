<?php

namespace App\Providers;

use App;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

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
        Route::prefix('host')
            ->middleware('throttle:1,10')
            ->middleware('bindings')
            ->namespace('App\Http\Controllers\Host')
            ->group(function($router) {
                /** @var Router $router */
                $router->post('requests', 'HostRequestController@create');
                $router->get('requests/{host_request}', 'HostRequestController@show');
                $router->post('requests/{host_request}/access', 'HostRequestController@access');
            });

        Route::prefix('host')
            ->middleware('host')
            ->namespace('App\Http\Controllers\Host')
            ->group(base_path('routes/host.php'));
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
