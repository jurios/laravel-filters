<?php

namespace Kodilab\LaravelFilters\Tests\Unit;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Kodilab\LaravelFilters\Filters\CollectionFilters;
use Kodilab\LaravelFilters\Tests\TestCase;

class CollectionFilterTest extends TestCase
{
    use WithFaker;

    /** @var Request $request */
    protected $request;

    /** @var Collection */
    protected $collection;

    /** @var CollectionFilters */
    protected $filters;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->request = \Illuminate\Support\Facades\Request::instance();
        $this->collection = new Collection();

        $this->filters = new CollectionFilters();
    }

    public function test_default_filter()
    {

        $filter_name = 'id';
        $filter_value = $this->faker->unique()->numberBetween();
        $this->request->merge([$filter_name => $filter_value]);

        $this->collection->add([$filter_name => $filter_value]);
        $this->collection->add([$filter_name => $this->faker->unique()->numberBetween()]);



        $this->assertEquals(1, count($this->filters->apply($this->collection, $this->request->all())));
    }

    public function test_order_by()
    {
        $filter_name = 'order_desc';
        $filter_value = 'id';
        $value = $this->faker->unique()->numberBetween();

        $this->filters->set('reset_keys_when_sort', true);

        $this->collection->add([$filter_value => $value - 1]);
        $this->collection->add([$filter_value => $value]);


        $this->request->merge([$filter_name => $filter_value]);

        $this->assertEquals($this->collection[1][$filter_value], $value);

        $this->collection = $this->filters->apply($this->collection, $this->request->all());

        $this->assertEquals($this->collection[1][$filter_value], $value - 1);

    }
}