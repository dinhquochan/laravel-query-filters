# Laravel Query Filters

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dinhquochan/laravel-query-filters.svg?style=flat-square)](https://packagist.org/packages/dinhquochan/laravel-query-filters)
[![Build Status](https://img.shields.io/travis/dinhquochan/laravel-query-filters/master.svg?style=flat-square)](https://travis-ci.org/dinhquochan/laravel-query-filters)
[![Total Downloads](https://img.shields.io/packagist/dt/dinhquochan/laravel-query-filters.svg?style=flat-square)](https://packagist.org/packages/dinhquochan/laravel-query-filters)

Laravel Query Filters for [Laravel](https://laravel.com/).

## Requirements

- PHP >= 7.2.0
- Laravel >= 5.6

## Installation

You can install the package via composer:

```bash
composer require dinhquochan/laravel-query-filters
```

If you don't use auto-discovery, add the ServiceProvider to the providers array in config/app.php

```php
\DinhQuocHan\QueryFilters\QueryFilterServiceProvider::class,
```
## Basic usage

Create basic filter `app/Filters/PostFilter.php`:

```php
<?php

namespace App\Http\Filters;

use DinhQuocHan\QueryFilters\QueryFilter;

class PostFilter extends QueryFilter
{
    /**
     * Filter by user id.
     *
     * @param  int  $id
     * @return void
     */
    public function userId($id)
    {
        $this->getQuery()->where('user_id', $id);
    }
}
```

In `App\Http\Controllers\PostController`:

```php
<?php

namespace App\Http\Controllers;

use App\Post;
use App\Http\Filters\PostFilter;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Http\Filters\PostFilter  $filter
     * @return \Illuminate\Http\Response
     */
    public function index(PostFilter $filter)
    {
        $posts = $filter->of(Post::class)->get();
        // or $filter->of(Post::query())->get();
        // or $filter->of(new Post())->get();

        // Send it to view.
        return view('posts.index', compact('posts'));
    }
}
```

### Making a new filter

The package included an artisan command to create a new filter.

```bash
php artisan make:filter PostFilter
```

This filter will have the `App\Http\Filters` namespace and will be saved in `app/Http/Filters`.

or into a custom namespace, say, `App\Blog`

```bash
php artisan make:filter "Blog/PostFilter"
```

This filter will have the `App\Blog` namespace and will be saved in `app/Blog`.


## Available traits

### Sortable

Allow to sort items, you must add `$sortable` property, default if not call `sort` and `sort_by` in request, the trait will add default sorting column to query:

```php
<?php

namespace App\Http\Filters;

use DinhQuocHan\QueryFilters\Sortable;
use DinhQuocHan\QueryFilters\QueryFilter;

class PostFilter extends QueryFilter
{
    use Sortable;

    /**
     * Sort direction.
     *
     * @var string
     */
    protected $sortDirection = 'desc';

    /**
     * Default sort by column.
     *
     * @var string
     */
    protected $sortBy = 'created_at';

    /**
     * Sortable columns.
     *
     * @var array
     */
    protected $sortable = [
        'created_at',
    ];
}
```

Example:

```
> your-url?sort_by=id
> SELECT * FROM `posts` ORDER BY `id` ASC

> your-url?sort_by=id&sort=desc
> SELECT * FROM `posts` ORDER BY `id` DESC
```

### Searchable

Allow to search items, you must add `$searchable` property:

```php
<?php

namespace App\Http\Filters;

use DinhQuocHan\QueryFilters\Searchable;
use DinhQuocHan\QueryFilters\QueryFilter;

class PostFilter extends QueryFilter
{
    use Searchable;

    /**
     * Searchable columns.
     *
     * @var array
     */
    protected $searchable = [
        'id', 'title',
    ];
}
```

Example:

```
> your-url?search=foo or your-url?q=foo
> SELECT * FROM `posts` WHERE (`id` LIKE '%foo%' OR `title` LIKE '%foo%')

> your-url?search=foo*
> SELECT * FROM `posts` WHERE (`id` LIKE 'foo%' OR `title` LIKE 'foo%')

> your-url?search=*foo
> SELECT * FROM `posts` WHERE (`id` LIKE '%foo' OR `title` LIKE '%foo')

// your-url?search=foo&search_by=title
// SELECT * FROM `posts` WHERE `title` LIKE '%foo%'
```
### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email contact@dinhquochan.com instead of using the issue tracker.

## Credits

- [Dinh Quoc Han](https://github.com/dinhquochan)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
