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
     * @param QueryFilter $filters
     * @param bool $return_collection
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|Builder|Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\LengthAwarePaginator|null
     */
    public function scopeFilters(Builder $query, QueryFilter $filters, Model $context = null)
    {
        return $filters->apply($query, $context);
    }

    /**
     * It returns the result collection or LengthAwarePaginator when it's paginated of the query.
     * Be careful, it you didn't call filters($filters) scope before, it will return the results without filtering
     *
     * @param Builder $query
     * @param QueryFilter $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\LengthAwarePaginator|null
     */
    public function scopeResults(Builder $query, QueryFilter $filters)
    {
        return $filters->results($query);
    }
}