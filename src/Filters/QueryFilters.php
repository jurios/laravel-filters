<?php

namespace Kodilab\LaravelFilters\Filters;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class QueryFilters extends Filters
{
    /**
     * @var string
     */
    protected $model = Model::class;

    public function getModel()
    {
        return $this->model;
    }

    /**
     * Apply filters
     *
     * @param Builder $data
     * @param array $input
     * @param string $prefix
     * @return Builder
     */
    public function apply($data, array $input = [], string $prefix = '')
    {
        $this->model = get_class($data->getModel());

        return parent::apply($data, $input, $prefix);
    }

    /**
     * Default filter: order_by will order the results by the $attribute in the $direction direction. $direction can
     * be 'asc' or 'desc.
     *
     * @param $attribute
     * @param string $direction
     * @return Builder
     */
    protected function order_by($attribute, $direction = 'asc')
    {
        if (!is_null($attribute) || !is_null($this->model))
        {
            /** @var Model $instantiated_class */
            $instantiated_class = new $this->model;
            if (Schema::hasColumn($instantiated_class->getTable(), $attribute)) {
                return $this->results->orderBy($attribute, $direction);
            }
        }
        return $this->results;
    }

    /**
     * Default filter. If there is not a specific filter method, this filter method is fired
     * @param $attribute
     * @param $value
     * @return Builder
     */
    protected function defaultFilter($attribute, $value)
    {
        if (!is_null($attribute) || !is_null($this->model)) {
            /** @var Model $instantiated_class */
            $instantiated_class = new $this->model;
            if (Schema::hasColumn($instantiated_class->getTable(), $attribute)) {

                if (array_key_exists($attribute, $instantiated_class->getCasts()) &&
                    $instantiated_class->getCasts()[$attribute] !== 'string') {

                    $operator = $this->getFilterOperator($attribute, '=');
                    $this->results->where($attribute, $operator, $value);

                } else {

                    $operator = $this->getFilterOperator($attribute, 'LIKE');
                    $this->results->where($attribute, $operator, '%' . $value . '%');

                }

            }
        }
        return $this->results;
    }
}