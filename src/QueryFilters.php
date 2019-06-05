<?php

namespace Kodilab\LaravelFilters;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class QueryFilters
{
    /**
     * Prefix used for get the filters from the request
     * @var string $prefix
     */
    protected $prefix;

    /**
     * Array of filters (without prefix) which are going to be applied
     * @var array $filters
     */
    protected $filters;

    /**
     * @var Builder
     */
    protected $query;

    /**
     * @var string
     */
    protected $model = Model::class;

    /**
     * QueryFilters constructor.
     *
     */
    public function __construct()
    {
        //
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * If the attribute does not exist, then return the filter which name is $name if it exists
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (isset($this->filters[$name])) {
            return $this->filters[$name];
        }

        return null;
    }

    /**
     * Apply filters
     *
     * @param Builder $query
     * @param array $input
     * @param string $prefix
     * @return Builder
     */
    public function apply(Builder $query, array $input = [], string $prefix = '')
    {
        $this->query = $query;
        $this->filters = $this->getFilters($input, $prefix);
        $this->model = get_class($this->query->getModel());

        foreach ($this->filters as $filter => $value) {

            if ($this->isOperator($filter)) {
                continue;
            }

            if (method_exists($this, $filter)) {
                call_user_func_array([$this, $filter], array_filter([$value]));

            } else {
                $this->defaultFilter($filter, $value);

            }
        }

        return $this->query;
    }

    /**
     * Default filter: order_desc will order the results descendingly by the $value attribute.
     * @param $value
     */
    protected function order_desc($value)
    {
        $this->order_by($value, 'desc');
    }

    /**
     * Default filter: order_desc will order the results ascendingly by the $value attribute.
     * @param $value
     */
    protected function order_asc($value)
    {
        $this->order_by($value, 'asc');
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
                return $this->query->orderBy($attribute, $direction);
            }
        }
        return $this->query;
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
                    $this->query->where($attribute, $operator, $value);

                } else {

                    $operator = $this->getFilterOperator($attribute, 'LIKE');
                    $this->query->where($attribute, $operator, '%' . $value . '%');

                }

            }
        }
        return $this->query;
    }

    /**
     * Returns, if exists, the operator defined for this filter
     *
     * @param string $filter
     * @param null $default
     * @return null|string
     */
    private function getFilterOperator(string $filter, $default = null)
    {
        $operator = null;

        if (isset($this->filters[$filter . '-op'])) {

            $operator = $this->filters[$filter . '-op'];

        }

        if (is_null($operator)) {

            return $default;

        }

        $operator = $this->operatorStringToSQLOperator($operator);

        return is_null($operator) ? $default : $operator;
    }

    /**
     * Return the equivalent symbol for SQL (ex. "lte" => '<=')
     * @param $operator
     * @return string
     */
    private function operatorStringToSQLOperator($operator)
    {
        switch ($operator) {

            case "eq":
                return '=';
            case 'neq':
                return '<>';
            case "gt":
                return '>';
            case 'gte':
                return '>=';
            case "lt":
                return '<';
            case 'lte':
                return '<=';
            default:
                return null;

        }
    }

    /**
     * Returns the filters name=>value which has the prefix defined from the input
     *
     * @param array $input
     * @param string $prefix
     * @return array
     */
    private function getFilters(array $input, string $prefix = '')
    {
        $filters = [];

        foreach ($input as $name => $value) {

            if ($this->hasPrefix($name, $prefix)) {
                $filters[$this->removePrefix($name, $prefix)] = $value;
            }
        }

        return $filters;
    }

    /**
     * Check whether a filter name starts with a prefix
     * @param string $filter
     * @param string $prefix
     * @return bool
     */
    private function hasPrefix(string $filter, string $prefix)
    {
        return substr($filter, 0, strlen($prefix)) === $prefix;
    }

    /**
     * Remove the prefix from the filter name
     * @param string $filter
     * @param string $prefix
     * @return null|string|string[]
     */
    private function removePrefix(string $filter, string $prefix)
    {
        return preg_replace("/^" . $prefix . "/", "", $filter);
    }

    /**
     * Check if the filter is an operator (start with the prefix and finish with -op)
     * @param string $filter
     * @return false|int
     */
    private function isOperator(string $filter)
    {
        return preg_match("/^" . $this->prefix . "-[\s\S]*-op$/", $filter);
    }

}