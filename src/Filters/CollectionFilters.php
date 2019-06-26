<?php

namespace Kodilab\LaravelFilters\Filters;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CollectionFilters extends Filters
{
    protected $reset_keys_when_sort = false;

    /**
     * Apply filters
     *
     * @param Collection $data
     * @param array $input
     * @param string $prefix
     * @return Builder|Collection
     * @throws \Exception
     */
    public function apply($data, array $input = [], string $prefix = '')
    {
        return parent::apply($data, $input, $prefix);
    }

    public function set(string $parameter, $value)
    {
        if (property_exists($this, $parameter)) {
            $this->$parameter = $value;
        }
    }

    /**
     * Default filter: order_by will order the results by the $attribute in the $direction direction. $direction can
     * be 'asc' or 'desc.
     *
     * @param $attribute
     * @param string $direction
     * @return Builder|Collection|void
     */
    protected function order_by($attribute, $direction = 'asc')
    {
        if ($direction === 'desc') {
            $result = $this->results->sortByDesc($attribute);
        }

        if ($direction === 'asc') {
            $result = $this->results->sortBy($attribute);
        }

        if ($this->reset_keys_when_sort) {
            return $result->values();
        }

        return $result;
    }

    /**
     * Default filter. If there is not a specific filter method, this filter method is fired
     *
     * @param $attribute
     * @param $value
     * @return Builder|Collection
     */
    protected function defaultFilter($attribute, $value)
    {
        $attribute_exists = false;

        foreach ($this->results as $item) {

            if ($item instanceof Arrayable) {
                $item = $item->toArray();
            }

            if (key_exists($attribute, $item)) {
                $attribute_exists = true;
                break;
            }
        }

        if (!$attribute_exists) {
            return $this->results;
        }

        $operator = $this->getFilterOperator($attribute, '=');

        return $this->results->where($attribute, $operator, $value);
    }
}