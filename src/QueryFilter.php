<?php

namespace DinhQuocHan\QueryFilters;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

abstract class QueryFilter
{
    /**
     * The ignored filters.
     *
     * @var array
     */
    protected $ignore = [];

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

    /**
     * Per page value is allowed.
     *
     * @var array
     */
    protected $allowsPerPage = [
        1, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 100,
    ];

    /**
     * Laravel Request.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Eloquent builder.
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * The array of trait initializers that will be called on each new instance.
     *
     * @var array
     */
    protected static $traitInitializers = [];

    /**
     * QueryFilter constructor.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        static::boot();
    }

    /**
     * The "booting" filter.
     *
     * @return void
     */
    public static function boot()
    {
        static::bootTraits();
    }

    /**
     * Boot all of the bootable traits on the filter.
     *
     * @return void
     */
    protected static function bootTraits()
    {
        $class = static::class;

        $booted = [];

        static::$traitInitializers[$class] = [];

        foreach (class_uses_recursive($class) as $trait) {
            $method = 'boot'.class_basename($trait);

            if (method_exists($class, $method) && ! in_array($method, $booted)) {
                forward_static_call([$class, $method]);

                $booted[] = $method;
            }

            if (method_exists($class, $method = 'initialize'.class_basename($trait))) {
                static::$traitInitializers[$class][] = $method;

                static::$traitInitializers[$class] = array_unique(
                    static::$traitInitializers[$class]
                );
            }
        }
    }

    /**
     * Initialize any initializable traits on the filter.
     *
     * @return void
     */
    protected function initializeTraits()
    {
        foreach (static::$traitInitializers[static::class] as $method) {
            $this->{$method}();
        }
    }

    /**
     * Apply filters.
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder|string  $model
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @throws \Exception
     */
    public function of($model)
    {
        $this->setQuery($model);

        return $this->handle();
    }

    /**
     * Set query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder|string  $model
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @throws \Exception
     */
    public function setQuery($model)
    {
        if ($model instanceof  Model) {
            return $this->query = $model->newQuery();
        }

        if ($model instanceof Builder) {
            return $this->query = $model;
        }

        if (is_string($model) && class_exists($model)) {
            return $this->query = call_user_func([$model, 'query']);
        }

        throw new Exception(sprintf('Parameter [$query] is not instance of %s::class.', Builder::class));
    }

    /**
     * Get query builder.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Get request instance.
     *
     * @return \Illuminate\Http\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Build an "index" query for the filter.
     *
     * @return void
     */
    public function indexQuery()
    {
        //
    }

    /**
     * Execute before handle filters.
     *
     * @return void
     */
    public function beforeHandle()
    {
        //
    }

    /**
     * Execute after handle filters.
     *
     * @return void
     */
    public function afterHandle()
    {
        //
    }

    /**
     * Handle filters.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle()
    {
        $this->indexQuery();

        $this->beforeHandle();

        foreach ($this->getFilters() as $filter => $value) {
            $filter = Str::camel($filter);

            if (! $this->shouldIgnore($filter) && method_exists($this, $filter)) {
                call_user_func([$this, $filter], $value);
            }
        }

        $this->afterHandle();

        $this->initializeTraits();

        return $this->getQuery();
    }

    /**
     * Get all filters.
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = $this->getRequest()->query();

        ksort($filters);

        return $filters;
    }

    /**
     * Determine if filter's name should be ignored.
     *
     * @param  string  $filter
     * @return bool
     */
    protected function shouldIgnore($filter)
    {
        if (Str::startsWith($filter, '__') ||
            Str::startsWith($filter, 'boot') ||
            Str::startsWith($filter, 'initialize')) {
            return true;
        }

        return in_array($filter, $this->ignoredFilters());
    }

    /**
     * Get ignored filters.
     *
     * @return array
     */
    protected function ignoredFilters()
    {
        return array_merge([
            'of',
            'indexQuery',
            'setQuery',
            'getQuery',
            'getRequest',
            'beforeHandle',
            'afterHandle',
            'handle',
            'getFilters',
            'ignoreFilters',
        ], $this->ignore);
    }

    /**
     * Set the number of models to return per page.
     *
     * @param  int  $perPage
     * @return void
     */
    public function perPage($perPage)
    {
        if (in_array((int) $perPage, $this->allowsPerPage)) {
            $this->getQuery()->getModel()->setPerPage($perPage);
        }
    }

    /**
     * Alias to set the "offset" value of the query.
     *
     * @param  int  $index
     * @return void
     */
    public function skip($index)
    {
        $this->offset($index);
    }

    /**
     * Alias to set the "offset" value of the query.
     *
     * @param  int  $index
     * @return void
     */
    public function offset($index)
    {
        $this->getQuery()->skip($index);
    }

    /**
     * Alias to set the "limit" value of the query.
     *
     * @param  int  $value
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function take($value)
    {
        return $this->limit($value);
    }

    /**
     * Alias to set the "limit" value of the query.
     *
     * @param  int  $value
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function limit($value)
    {
        return $this->getQuery()->limit($value);
    }
}
