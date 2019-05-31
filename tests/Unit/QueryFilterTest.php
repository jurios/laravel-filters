<?php

namespace Kodilab\LaravelFilters\Tests\Unit;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\HtmlString;
use Kodilab\LaravelFilters\QueryFilters;
use Kodilab\LaravelFilters\Tests\Resources\TestModels\TestModel;
use Kodilab\LaravelFilters\Tests\TestCase;

class QueryFilterTest extends TestCase
{
    use WithFaker;

    /** @var Request $request */
    protected $request;

    protected function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->request = \Illuminate\Support\Facades\Request::instance();
    }

    public function test_filters_are_accesible_as_attributes()
    {
        $filter_name = $this->faker->unique()->word;
        $filter_value = $this->faker->unique()->numberBetween();

        $filters = new QueryFilters($this->request->all());

        $this->assertNull($filters->$filter_name);

        $this->request->merge([$filter_name => $filter_value]);
        $filters = new QueryFilters($this->request->all());

        $this->assertEquals($filters->$filter_name, $filter_value);

        $filter_name = $this->faker->unique()->word;
        $this->assertNull($filters->$filter_name);
    }

    public function test_default_filter()
    {
        $filter_name = 'id';
        $filter_value = $this->faker->unique()->numberBetween();
        $this->request->merge([$filter_name => $filter_value]);

        $this->assertSQLContainsString("where \"id\" = ?", TestModel::filters(QueryFilters::class, $this->request->all())->toSql());
    }

    public function test_default_filter_not_applied_if_the_field_does_not_exist()
    {
        $filter_name = $this->faker->unique()->word;
        $filter_value = $this->faker->unique()->numberBetween();
        $this->request->merge([$filter_name => $filter_value]);

        $this->assertSQLNotContainsString("where \"id\" = ?", TestModel::filters(QueryFilters::class, $this->request->all())->toSql());
    }

    public function test_order_by()
    {
        $filter_name = 'order_desc';
        $filter_value = 'id';

        $this->request->merge([$filter_name => $filter_value]);

        $this->assertSQLContainsString("order by \"id\" desc", TestModel::filters(QueryFilters::class, $this->request->all())->toSql());
    }

    public function test_order_by_only_works_for_existing_fields()
    {
        $filter_name = 'order_desc';
        $filter_value = $this->faker->unique()->word;

        $this->request->merge([$filter_name => $filter_value]);

        $this->assertSQLNotContainsString("order by \"id\" desc", TestModel::filters(QueryFilters::class, $this->request->all())->toSql());
    }

    public function test_no_prefixed_filters_are_ignored()
    {
        $filter_name = 'id';
        $filter_value = $this->faker->unique()->numberBetween();

        $this->request->merge([$filter_name => $filter_value]);

        $this->assertSQLNotContainsString("where \"id\" = ?", TestModel::filters(QueryFilters::class, $this->request->all(), $this->faker->name)->toSql());

        $prefix = $this->faker->word;

        $this->request->merge([$prefix . $filter_name => $filter_value]);

        $this->assertSQLContainsString("where \"id\" = ?", TestModel::filters(QueryFilters::class, $this->request->all(), $prefix)->toSql());

    }

    public function test_prefix_equals_as_filter_name_should_be_remove_the_prefix_and_keep_the_filter_name()
    {
        $prefix = 'id';
        $filter_name = $prefix;
        $filter_value = $this->faker->unique()->numberBetween();

        $this->request->merge([$prefix . $filter_name => $filter_value]);

        $this->assertSQLContainsString("where \"id\" = ?", TestModel::filters(QueryFilters::class, $this->request->all(), $prefix)->toSql());
    }
}