<?php

namespace AsayHome\AsayHelpers;

use Illuminate\Support\ServiceProvider;

class AsayHelpersProvider extends ServiceProvider
{


    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/asay-helpers.php' => config_path('asay-helpers.php'),
        ]);
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'asay-helpers');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'asay-helpers');
        $this->loadMigrationsFrom(__DIR__ . '/../resources/database/migrations', 'asay-helpers');
    }
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/asay-helpers.php',
            'asay-helpers'
        );
    }
}
