<?php

namespace Kodilab\LaravelFilters;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class QueryFilter
{
    /** @var Request $request */
    protected $request;

    /** @var array $parameters */
    protected $parameters;

    /** @var Builder $query */
    protected $query;

    /** @var string $class_name */
    protected $class_name;

    /** @var bool $paginate */
    protected $paginate;

    /** @var Collection|LengthAwarePaginator $collection */
    protected $collection;

    /** @var bool $is_filtered */
    protected $is_filtered;

    /**
     * QueryFilter constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->parameters = [];
        $this->class_name = null;
        $this->paginate = 10;
        $this->collection = null;
        $this->is_filtered = false;
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
            $this->parameters[$filter] = $value;

            if (method_exists($this, $filter))
            {
                call_user_func_array([$this, $filter], array_filter([$value]));
            }
            else {
                $this->defaultFilter($filter, $value);
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
        if($this->is_filtered === false)
        {
            $this->apply($query);
        }

        return $this->getCollection();
    }

    /**
     * Return the pagination html buttons when is paginated. This is a wrapper function which use the laravel links()
     *
     * @return \Illuminate\Support\HtmlString|null
     */
    public function links()
    {
        if ($this->is_filtered && $this->isPaginated())
        {
            return $this->getCollection()->appends($this->parametersApplied())->links();
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

                /** @var bool $not */
                $not = substr($value, 0, 1) === '!';
                /** @var string $value */
                $value = ltrim($value, '!');

                if($not)
                {
                    return $this->query->where($attribute, 'not like', $value);
                }

                return $this->query->where($attribute, 'like', $value);
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
        if ($this->is_filtered && is_null($this->collection))
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
}