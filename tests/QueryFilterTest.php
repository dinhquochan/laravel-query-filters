<?php

namespace DinhQuocHan\QueryFilters\Tests;

use DinhQuocHan\QueryFilters\QueryFilter;
use DinhQuocHan\QueryFilters\SearchableQueryFilter;
use DinhQuocHan\QueryFilters\SortableQueryFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class QueryFilterTest extends TestCase
{
    /** @test */
    public function it_can_handle_filter()
    {
        $testModelFilters = (new TestModelFilters(new Request()))->of(TestModel::class)->toSql();

        $expected = TestModel::query()->toSql();

        $this->assertEquals($testModelFilters, $expected);
    }

    /** @test */
    public function it_can_handle_sample_filter()
    {
        $fakeRequest = new Request(['sample' => 'foo']);

        $testModelFilters = (new TestModelFilters($fakeRequest))->of(TestModel::class)->toSql();

        $expected = TestModel::query()->where('sample', 'foo')->toSql();

        $this->assertEquals($expected, $testModelFilters);
    }

    /** @test */
    public function it_can_handle_skip_and_take_filter()
    {
        $fakeRequest = new Request(['skip' => 10, 'take' => 15]);

        $testModelFilters = (new TestModelFilters($fakeRequest))->of(TestModel::class)->toSql();

        $expected = TestModel::query()->skip(10)->take(15)->toSql();

        $this->assertEquals($expected, $testModelFilters);
    }

    /** @test */
    public function it_can_handle_offset_and_limit_filter()
    {
        $fakeRequest = new Request(['offset' => 10, 'limit' => 15]);

        $testModelFilters = (new TestModelFilters($fakeRequest))->of(TestModel::class)->toSql();

        $expected = TestModel::query()->offset(10)->limit(15)->toSql();

        $this->assertEquals($expected, $testModelFilters);
    }

    /** @test */
    public function it_can_set_per_page()
    {
        $fakeRequest = new Request(['per_page' => 20]);

        $perPage = (new TestModelFilters($fakeRequest))->of(TestModel::class)->getModel()->getPerPage();

        $expected = TestModel::query()->where('sample', 'foo')->getModel()->setPerPage(20)->getPerPage();

        $this->assertEquals($expected, $perPage);
    }

    /** @test */
    public function it_can_handle_sort_by_filter()
    {
        $testModelFilters = new TestModelWithSortableFilters(new Request());

        $testModel = $testModelFilters->of(TestModel::class)->toSql();

        $expected = TestModel::query()->orderBy('created_at', 'asc')->toSql();

        $this->assertEquals($testModel, $expected);

        $fakeRequest = new Request(['sort_by' => 'id', 'sort' => 'desc']);

        $testModelFilters = new TestModelWithSortableFilters($fakeRequest);

        $testModel = $testModelFilters->of(TestModel::class)->toSql();

        $expected = TestModel::query()->orderBy('id', 'desc')->toSql();

        $this->assertEquals($testModel, $expected);
    }

    /** @test */
    public function it_can_handle_search_filter()
    {
        $fakeRequest = new Request(['search' => 'foo']);

        $testModelFilters = new TestModelWithSearchableFilters($fakeRequest);

        $testModel = $testModelFilters->of(TestModel::class)->toSql();

        $expected = TestModel::query()->where(function ($query) {
            $query->orWhere('title', 'like', '%foo%');
            $query->orWhere('tags', 'like', '%foo%');
        })->toSql();

        $this->assertEquals($testModel, $expected);
    }

    /** @test */
    public function it_searchable_starts_with()
    {
        $fakeRequest = new Request(['search' => 'foo*']);

        $testModelFilters = new TestModelWithSearchableFilters($fakeRequest);

        $testModel = $testModelFilters->of(TestModel::class)->toSql();

        $expected = TestModel::query()->where(function ($query) {
            $query->orWhere('title', 'like', 'foo%');
            $query->orWhere('tags', 'like', 'foo%');
        })->toSql();

        $this->assertEquals($testModel, $expected);
    }

    /** @test */
    public function it_searchable_ends_with()
    {
        $fakeRequest = new Request(['search' => '*foo']);

        $testModelFilters = new TestModelWithSearchableFilters($fakeRequest);

        $testModel = $testModelFilters->of(TestModel::class)->toSql();

        $expected = TestModel::query()->where(function ($query) {
            $query->orWhere('title', 'like', '%foo');
            $query->orWhere('tags', 'like', '%foo');
        })->toSql();

        $this->assertEquals($testModel, $expected);
    }

    /** @test */
    public function it_searchable_specific_column()
    {
        $fakeRequest = new Request(['search' => 'foo', 'search_by' => 'title']);

        $testModelFilters = new TestModelWithSearchableFilters($fakeRequest);

        $testModel = $testModelFilters->of(TestModel::class)->toSql();

        $expected = TestModel::query()->where('title', 'like', '%foo%')->toSql();

        $this->assertEquals($testModel, $expected);
    }

    /** @test */
    public function it_can_not_searchable_not_exists_column()
    {
        $fakeRequest = new Request(['search' => 'foo', 'search_by' => 'bar']);

        $testModelFilters = new TestModelWithSearchableFilters($fakeRequest);

        $testModel = $testModelFilters->of(TestModel::class)->toSql();

        $expected = TestModel::query()->toSql();

        $this->assertEquals($testModel, $expected);
    }
}

class TestModel extends Model
{
    //
}

class TestModelFilters extends QueryFilter
{
    public function sample($value)
    {
        $this->getQuery()->where('sample', $value);
    }
}

class TestModelWithSortableFilters extends QueryFilter
{
    use SortableQueryFilter;

    protected $sortable = ['id'];

    protected $sortDirection = 'asc';

    protected $sortBy = 'created_at';
}

class TestModelWithSearchableFilters extends QueryFilter
{
    use SearchableQueryFilter;

    protected $searchable = ['title', 'tags'];
}
