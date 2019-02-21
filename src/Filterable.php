<?php

namespace Kodilab\LaravelFilters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait Filterable
{
    /**
     * Apply filters $filters and return a Builder $query.
     *
     * @param Builder $query
     * @param QueryFilters $filters
     * @return FilterBuilder
     */
    public function scopeFilters(Builder $query, QueryFilters $filters)
    {
        return $filters->apply($query);
    }
}