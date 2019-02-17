<?php

namespace Kodilab\LaravelFilters;

use Illuminate\Database\Eloquent\Builder;

class FilterBuilder
{
    /** @var QueryFilter $filters */
    protected $filters;

    /** @var Builder $builder */
    protected $builder;

    public function __construct(Builder $builder, QueryFilter $filters)
    {
        $this->builder = $builder;
        $this->filters = $filters;
    }

    public function __set($name, $value)
    {
        return $this->builder->$name = $value;
    }

    public function __get($name)
    {
        return $this->builder->$name;
    }

    public function __isset($name)
    {
        return isset($this->builder);
    }

    public function __unset($name)
    {
        unset($this->builder);
    }

    public function __call($name, $arguments)
    {
        $target = $this->builder;

        if ($name === 'get')
        {
            $target = $this;
        }

        return call_user_func_array([$target, $name], array_filter($arguments));
    }

    public function get($columns = ['*'])
    {
        if(!is_null($this->filters) && $this->filters->getPagination() > 0)
        {
            $results = $this->builder->paginate($this->filters->getPagination(), $columns);
            $this->filters->setResults($results);

            return $results;
        }

        $results = $this->builder->get($columns);
        $this->filters->setResults($results);

        return $results;
    }
}