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
     * @param string $filter_class
     * @param array $input
     * @param string $prefix
     * @return Builder
     */
    public function scopeFilters(Builder $query, string $filter_class, array $input = [], string $prefix = '')
    {
        /** @var QueryFilters $filters */
        $filters = new $filter_class($input, $prefix);
        $filters->setModel(get_class($this));

        return $filters->apply($query);
    }
}