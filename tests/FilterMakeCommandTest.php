<?php

namespace DinhQuocHan\QueryFilters\Tests;

class FilterMakeCommandTest extends TestCase
{
    /** @test */
    public function it_can_create_a_filter()
    {
        $this->artisan('make:filter', [
            'name' => 'PostFilter',
            '--force' => true,
        ])->expectsOutput('Filter created successfully.');

        $shouldOutputFilePath = $this->app['path'].'/Http/Filters/PostFilter.php';

        $this->assertTrue(file_exists($shouldOutputFilePath), 'File exists in default app/Http/Filters folder');

        $contents = file_get_contents($shouldOutputFilePath);

        $this->assertContains('namespace App\Http\Filters;', $contents);

        $this->assertContains('class PostFilter extends QueryFilter', $contents);
    }

    /** @test */
    public function it_can_create_a_filter_with_a_custom_namespace()
    {
        $this->artisan('make:filter', [
            'name' => 'Blog/CategoryFilter',
            '--force' => true,
        ])->expectsOutput('Filter created successfully.');

        $shouldOutputFilePath = $this->app['path'].'/Blog/CategoryFilter.php';

        $this->assertTrue(file_exists($shouldOutputFilePath), 'File exists in custom app/Blog folder');

        $contents = file_get_contents($shouldOutputFilePath);

        $this->assertContains('namespace App\Blog;', $contents);

        $this->assertContains('class CategoryFilter extends QueryFilter', $contents);
    }
}
