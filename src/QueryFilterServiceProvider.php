<?php

namespace DinhQuocHan\QueryFilters;

use Illuminate\Support\ServiceProvider;

class QueryFilterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            FilterMakeCommand::class,
        ]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
