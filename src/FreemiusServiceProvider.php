<?php
namespace Freemius\Laravel;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Freemius\Laravel\Http\Controllers\WebhookController;

class FreemiusServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/freemius.php',
            'freemius',
        );
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootRoutes();
        $this->bootPublishing();
    }

    /**
     * Boot the package routes.
     *
     * @return void
     */
    protected function bootRoutes()
    {
        if (Freemius::$registersRoutes) {
            Route::group([
                'prefix' => config('freemius.path'),
                'namespace' => 'Freemius\Laravel\Http\Controllers',
                'as' => 'freemius.',
            ], function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            });
        }
    }
   
    protected function bootPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/freemius.php' => $this->app->configPath('freemius.php'),
            ], 'freemius-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => $this->app->databasePath('migrations'),
            ], 'freemius-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => $this->app->resourcePath('views/vendor/freemius'),
            ], 'freemius-views');
        }
    }

    
}
