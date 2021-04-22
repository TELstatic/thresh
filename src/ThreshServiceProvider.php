<?php

namespace TELstatic\Thresh;

use Illuminate\Support\ServiceProvider;

class ThreshServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'thresh');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Thresh::class, function () {
            return new Thresh();
        });

        $this->app->alias(Thresh::class, 'thresh');
    }

    public function provides()
    {
        return [Thresh::class, 'thresh'];
    }
}
