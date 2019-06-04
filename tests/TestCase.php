<?php

namespace DinhQuocHan\QueryFilters\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use DinhQuocHan\QueryFilters\QueryFilterServiceProvider;

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
