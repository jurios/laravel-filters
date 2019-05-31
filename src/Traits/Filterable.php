<?php

namespace Kodilab\LaravelFilters\Traits;

use Illuminate\Database\Eloquent\Builder;
use Kodilab\LaravelFilters\FilterBuilder;
use Kodilab\LaravelFilters\QueryFilters;

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