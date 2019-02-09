<?php

namespace Kodilab\LaravelFilters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait Filterable
{
    /**
     * Apply filters $filters and return a Builder $query.
     * @param Builder $query
     * @param QueryFilter $filters
     * @return Builder
     */
    public function scopeFilters(Builder $query, QueryFilter $filters)
    {
        return $filters->apply($query);
    }

    /**
     * Returns the collection after filtering the results. If filters() scope has been called previously, then it doesn't
     * filter again.
     *
     * @param Builder $query
     * @param QueryFilter $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function scopeGetFiltered(Builder $query, QueryFilter $filters)
    {
        return $filters->results($query);
    }
}