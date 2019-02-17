<?php

namespace Kodilab\LaravelFilters;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class QueryFilter
{
    /**
     * The request
     *
     * @var Request $request
     */
    protected $request;

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
     * Indicates the pagination. If it is 0, no pagination will be applied
     * @var int $pagination
     */
    protected $pagination;

    /**
     * Model which are being filtered
     *
     * @var Model
     */
    protected $model;

    /**
     * @var Builder
     */
    protected $query;

    /**
     * Returns whether the filters has been applied
     *
     * @var bool $is_filtered
     */
    protected $is_filtered;

    /**
     * QueryFilter constructor.
     *
     * @param Request $request
     * @param string|null $prefix
     */
    public function __construct(Request $request, string $prefix = null)
    {
        $this->filters = [];
        $this->request = $request;
        $this->setPrefix($prefix);
        $this->pagination = 0;

        $this->extractFiltersFromRequest();
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
     * @return FilterBuilder
     */
    public function apply(Builder $query)
    {
        $this->is_filtered = true;

        $this->model = get_class($query->getModel());
        $this->query = $query;

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

        return $this->getFilterBuilder();
    }

    /**
     * Returns the pagination
     * @return int
     */
    public function getPagination()
    {
        return $this->pagination;
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
     * Default filter for pagination
     * @param $value
     */
    protected function paginate($value)
    {
        $this->pagination = $value;
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
     * Set a prefix
     *
     * @param null $prefix
     */
    private function setPrefix($prefix = null)
    {
        if (is_null($prefix)) {

            $prefix = "";

        }

        $this->prefix = $prefix;
        $this->extractFiltersFromRequest();
    }

    /**
     * Extract the filters which has the prefix defined from the request
     */
    private function extractFiltersFromRequest()
    {
        $filters = $this->request->all();

        foreach ($filters as $filter => $value) {

            if ($this->hasPrefix($filter, $this->prefix)) {

                $this->filters[$this->removePrefix($filter, $this->prefix)] = $value;

            }
        }
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
        return preg_replace("/^" . $prefix . "-/", "", $filter);
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

    /**
     * Returns the FilterBuilder for the Builder used
     *
     * @return FilterBuilder
     */
    private function getFilterBuilder()
    {
        return new FilterBuilder($this->query, $this);
    }
}