<?php

namespace Markpurser\LaravelCrudKit;

use Illuminate\Support\ServiceProvider;

class CrudKitServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        $this->loadViewsFrom(__DIR__.'/views', 'laravel-crudkit');

        $this->publishes([
            __DIR__.'/config/crudkit.php' => config_path('crudkit.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/assets' => public_path('laravel-crudkit'),
        ], 'public');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
