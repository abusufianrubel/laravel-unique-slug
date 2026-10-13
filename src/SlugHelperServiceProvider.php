<?php

namespace Sufian\SlugHelper;

use Illuminate\Support\ServiceProvider;

class SlugHelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('slug-helper', function () {
            return new SlugHelper();
        });

        $this->mergeConfigFrom(
            __DIR__.'/../config/slug-helper.php', 'slug-helper'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish the package's configuration file to the Laravel application
        $this->publishes([
            __DIR__.'/../config/slug-helper.php' => config_path('slug-helper.php'),
        ], 'slug-helper-config');
    }
}
