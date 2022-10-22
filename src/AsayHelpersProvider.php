<?php

namespace AsayHome\AsayHelpers;

use Illuminate\Support\ServiceProvider;

class AsayHelpersProvider extends ServiceProvider
{


    public function register()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../resources/database/migrations', 'asay-helpers');
    }

    public function boot()
    {
        //
    }
}
