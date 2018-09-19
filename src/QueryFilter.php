<?php

namespace Kodilab\LaravelFilters;


use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
     * The request parameters being applied in order to be used later
     *
     * @var array $parameters
     */
    protected $parameters;

    /**
     * The query being generated
     *
     * @var Builder $query
     */
    protected $query;

    /**
     * The model being filtered
     *
     * @var string $class_name
     */
    protected $class_name;

    /**
     * Returns whether paginate filter is applied
     *
     * @var bool $paginate
     */
    protected $paginate;

    /**
     * The collection result after apply filters
     *
     * @var Collection|LengthAwarePaginator
     */
    protected $collection;

    /**
     * Returns whether this instance has been applied
     * @var bool
     */
    protected $is_filtered;

    /**
     * The prefix used
     * @var string
     */
    protected $prefix;

    /**
     * The attributes that should be ignored.
     *
     * @var array
     */
    protected $ignore = [];

    protected $debugBar;

    /**
     * QueryFilter constructor.
     * @param Request $request
     */
    public function __construct(Request $request, Repository $config)
    {
        $this->request = $request;
        $this->parameters = [];
        $this->class_name = null;
        $this->paginate = 10;
        $this->collection = null;
        $this->is_filtered = false;
        $this->prefix = $config->get('filters.prefix');

        if (class_exists(\Barryvdh\Debugbar\LaravelDebugbar::class))
        {
            $this->debugBar = app(\Barryvdh\Debugbar\LaravelDebugbar::class);
        }
    }

    // Getters

    /**
     * Return the parameters applied. This is usefull specially to generate the links()
     * @return array
     */
    public function parametersApplied()
    {
        return $this->parameters;
    }

    /**
     * Return the request filters which will be applied.
     * @return array
     */
    public function filters()
    {
        return $this->request->all();
    }

    /**
     * Return the request class
     * @return Request
     */
    public function request()
    {
        return $this->request;
    }

    // Setters

    /**
     * Merge an array of ignores to the ignore list
     *
     * @param array $ignores
     */
    public function ignore(array $ignores)
    {
        foreach ($ignores as $ignore)
        {
            $ignore = $this->clearPrefix($ignore);

            if (!$this->shouldBeIgnored($ignore))
            {
                $this->ignore[] = $ignore;
            }
        }
    }

    // Status

    /**
     * Returns true if the filters applied are paginated
     * @return bool
     */
    public function isPaginated()
    {
        return $this->paginate > 0;
    }

    /**
     * Returns true if filters has been applied (apply() function has been called for this instance)
     * @return mixed
     */
    public function isFiltered()
    {
        return $this->is_filtered;
    }

    /**
     * Apply filters from request array. It returns a Builder in order to apply more scopes
     * @param Builder $query
     * @return Builder
     */
    public function apply(Builder $query)
    {
        $this->is_filtered = true;

        $this->class_name = get_class($query->getModel());
        $this->query = $query;

        foreach ($this->filters() as $filter => $value)
        {
            if ($this->hasPrefix($filter) && !$this->isOperator($filter) && !$this->shouldBeIgnored($filter))
            {
                $filter = $this->clearPrefix($filter);
                $operator = $this->getOperatorFilter($filter);

                $this->debugFilter($filter, $operator, $value);

                if (method_exists($this, $filter)) {
                    call_user_func_array([$this, $filter], array_filter([$value]));
                } else {
                    $this->defaultFilter($filter, $value);
                }
            }
        }

        return $this->query;
    }

    /**
     * Return the results collection in case that is no paginated. Returns the paginate results when is paginated
     * If filters are no applied, it applies the filters and then returns the result
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|Builder[]|Collection|LengthAwarePaginator|null
     */
    public function results(Builder $query)
    {
        $this->query = $query;

        foreach ($this->filters() as $filter => $value)
        {
            $this->parameters[$filter] = $value;
        }

        return $this->getCollection();
    }

    /**
     * Return the pagination html buttons when is paginated. This is a wrapper function which use the laravel links()
     *
     * @return \Illuminate\Support\HtmlString|null
     */
    public function links($view = null, $data = [])
    {
        if ($this->isPaginated())
        {
            return $this->getCollection()->appends($this->parametersApplied())->links($view, $data);
        }

        return null;
    }

    /**
     * Define the pagination. This is not applied until get() function is called.
     * @param int $value
     * @return Builder
     */
    public function paginate($value = 0)
    {
        if (!is_null($value) && is_int((int)$value)) {
            $this->paginate = (int)$value;
        }

        return $this->query;
    }

    /**
     * Default filter: order_desc will order the results descendingly by the $value attribute.
     * @param $value
     */
    public function order_desc($value)
    {
        $this->order_by($value, 'desc');
    }

    /**
     * Default filter: order_desc will order the results ascendingly by the $value attribute.
     * @param $value
     */
    public function order_asc($value)
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
    public function order_by($attribute, $direction = 'asc')
    {
        if (!is_null($attribute) || !is_null($this->class_name))
        {
            /** @var Model $instantiated_class */
            $instantiated_class = new $this->class_name;

            if (Schema::hasColumn($instantiated_class->getTable(), $attribute)) {
                return $this->query->orderBy($attribute, $direction);
            }
        }

        return $this->query;
    }

    /**
     * When a filter name is defined in the $this->request array but there isn't a filter function defined then this
     * function is called. It will filter the results where the database column named $attribute is like $value or the
     * opposite if $value starts with '!'.
     *
     * @param $attribute
     * @param $value
     * @return Builder
     */
    private function defaultFilter($attribute, $value)
    {
        if (!is_null($attribute) || !is_null($this->class_name))
        {
            /** @var Model $instantiated_class */
            $instantiated_class = new $this->class_name;

            if (\Illuminate\Support\Facades\Schema::hasColumn($instantiated_class->getTable(), $attribute)) {

                if (array_key_exists($attribute, $instantiated_class->getCasts()) &&
                    $instantiated_class->getCasts()[$attribute] !== 'string')
                {
                    $operator = $this->getOperatorFilter($attribute, '=');
                    $this->query->where($attribute, $operator, $value );
                }
                else {
                    $operator = $this->getOperatorFilter($attribute, 'LIKE');
                    $this->query->where($attribute, $operator, '%' . $value . '%');
                }
            }
        }

        return $this->query;
    }

    /**
     * To avoid calling database to get the results when we need them, it is cached in memory.
     *
     * @return Collection|LengthAwarePaginator|null
     */
    private function getCollection()
    {
        if (is_null($this->collection))
        {
            if ($this->isPaginated())
            {
                $this->collection = $this->query->paginate($this->paginate);
            }
            else {
                $this->collection = $this->query->get();
            }
        }

        return $this->collection;
    }

    /**
     * Check if the filter starts with the configured prefix (default: qf)
     * @param string $filter
     * @return false|int
     */
    private function hasPrefix(string $filter)
    {
        return preg_match("/^" . $this->prefix . "-[\s\S]*$/", $filter);
    }

    /**
     * Remove the prefix (default: qf) for the filter given
     * @param string $filter
     * @return null|string|string[]
     */
    private function clearPrefix(string $filter)
    {
        return preg_replace("/^" . $this->prefix . "-/", "", $filter);
    }

    /**
     * Add the prefix to a filter name
     *
     * @param string $filter
     * @return string
     */
    public function addPrefix(string $filter)
    {
        return $this->prefix . '-' . $filter;
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
     * Get the operator, if exists, for the filter given (either with prefix or not)
     * @param string $filter
     * @return string
     */
    private function getOperatorFilter(string $filter, $default = null)
    {
        $filter = $this->clearPrefix($filter);

        $operator_string = $this->request->input($this->prefix . '-' . $filter . '-op');

        if (is_null($operator_string))
        {
            return $default;
        }

        $operator = $this->operatorStringToSQLOperator($operator_string);

        return is_null($operator) ? $default : $operator;
    }

    /**
     * Return the equivalent symbol for SQL (ex. "lte" => '<=')
     * @param $operator
     * @return string
     */
    private function operatorStringToSQLOperator($operator)
    {
        switch ($operator)
        {
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
     * Show debug filter message in LaravelDebugbar
     * @param $filter
     * @param $operator
     * @param $value
     * @param bool $applied
     */
    private function debugFilter($filter, $operator, $value, $applied = true)
    {
        if (!is_null($this->debugBar))
        {
            if ($applied)
            {
                $method = method_exists($this, $filter) ? $filter . '()' : 'defaultFilter()';
                $this->debugBar->addMessage("Filter applied: `" . $filter . "`\tOperator: `" . $operator . "`\t Value: `" . $value . "`\t Method: `" . $method . "`", 'info');
            }
            else {
                $this->debugBar->addMessage("Filter ignored: `" . $filter . "`", 'warning');
            }
        }
    }

    /**
     * Returns whether a filter should be ignored
     *
     * @param $filter
     * @return bool
     */
    private function shouldBeIgnored($filter)
    {
        $filter = $this->clearPrefix($filter);

        $found = in_array($filter, $this->ignore);

        if ($found)
        {
            $this->debugFilter($filter, null, null, false);
        }

        return $found;
    }
}