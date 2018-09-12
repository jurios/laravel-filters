<?php

namespace Kodilab\LaravelFilters;

use Illuminate\Database\Eloquent\Builder;

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
    public function scopeFilters(Builder $query, QueryFilter $filters)
    {
        return $filters->apply($query);
    }

    /**
     * It returns the result collection or LengthAwarePaginator when it's paginated.
     * If filters are not applied, it will apply them and then it returns the result collection or LengthAwarePaginator.
     *
     * @param Builder $query
     * @param QueryFilter $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\LengthAwarePaginator|null
     */
    public function scopeFiltersResults(Builder $query, QueryFilter $filters)
    {
        return $filters->results($query);
    }
}