<?php

namespace DinhQuocHan\QueryFilters\Tests;

use DinhQuocHan\QueryFilters\QueryFilterServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [QueryFilterServiceProvider::class];
    }
}
