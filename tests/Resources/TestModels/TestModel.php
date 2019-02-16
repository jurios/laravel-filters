<?php

namespace Kodilab\LaravelFilters\Tests\Resources\TestModels;

use Illuminate\Database\Eloquent\Model;
use Kodilab\LaravelFilters\Filterable;

class TestModel extends Model
{
    use Filterable;

    protected $table = 'test_models';

    public $timestamps = false;
}