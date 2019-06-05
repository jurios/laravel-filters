# Laravel-filters

`Laravel-filters` is a Laravel package for dealing with Eloquent models filtering.
This project is based on a Laracasts videotutorial (LINK WIP).

With `laravel-filters` each filter you apply will add an statement to the `QueryBuilder` in
a simple, clean and maintainable way:

```

// GET /cars?color=red&order_desc=created_at

$cars = Car::where(...)->filters(QueryFilter::class, $request->all())->get();

```

With the previous code, we are requesting cars which `color` column on database contains
the `red` value and then ordered by column `created_at` in a descendant direction.
This is only the default filter behaviour. You can override the behaviour of each filter or create new ones
in order to obtain the desired filter behaviour. Let's step by step.

## 1 - Getting Started
Just install the package using `composer` with:

```
composer require kodilab/laravel-filters dev-master
```

Then, for each model you want to filter, add the `Filterable` trait:

```
use Illuminate\Database\Eloquent\Model;
use Kodilab\LaravelFilters\Traits\Filterable;

class Car extends Model
{
    use Filterable;
    
    ...
}
```

That's all you need. Now you can using filters as it was a `scope` for that model:

```
Car::filters(QueryFilter::class, ['filterA' => 'valueFilterA'])->get();
```

### The `filters()` scope
The `Filterable` trait just add an scope to the model which is `filters()` which has the following
signature (signature as scope):

#### filters(string $filter_class, array $input = [], string $prefix = '')

* **filter_class**: The filter class. You can use it the default one (QueryFilter) or extend it for create new filters 
                    or override them (This step will be explained later). For the moment, it always be `QueryFilter:class`.
* **input**: An associative array of filter => value
* **prefix**: Sometimes, specially when you use directly the `Request` array (`$request->all()`), you don't want to use
                all items of that array. You can identify the items you want to use with a prefix. For example, 
                if you define `qf` as a prefix, `qf-name` will be take from the array as a filter 
                (and will filter by the `name` column) and `color` will be ignored as it doesn't start with `qf-`.
                
### Default filter behaviour
When the filtering process starts, every item on the `input` array goes through the same process. Let's imagine that 
`$input = ['color' => 'red']` is the input array. `laravel-filters` will look for a custom method called `color` 
on the class `$filter_class`. If it exists, then will apply what that method does (that's how you can define 
custom filters and overriding filters. We'll see it on detail in the following sections). In case a custom method 
doesn't exist, then it will try to look for a column on the model's database table with the `color` name. 
If it exists, it will filter by that column. Otherwise, it will just ignore the filter.

You can go further, and add an `operator`. Now, let's imagine you define this input: 
`$input = ['year' => '2000', 'year-op' => 'gte']`. When a filter has an `-op` suffix, has a special meaning: is a
filter operator addon. It will modify the filtering default behaviour. In this case, as we define the operator as `gte`, 
it will filter by the column `year` which value is `>=` than `2000`.

Here you can see the default operators available and the meaning: 

```
"eq" => '='
"neq"=> '<>'
"gt" => '>'
"gte" => '>='
"lt" => '<'
'lte' => '<='
```

Let's see a more complex example using the request array in a controller method: 

```
// GET /cars?color=red&color-op=neq&year=2000&year-op=gt

public function index(Request $request)
{
    $cars = Cars::filters(QueryFilter:class, $request->all());
}
```
In this case, `$cars` will contain all cars which `color != red` (using `neq` operator) which `year > 2000`.

### Ordering methods
Ordering isn't considered as a "filter" but `laravel-filters` provides some util methods which help you ordering.
You can use the "filter" `order_desc` and `order_asc` to indicates how to order the results. As we did in the filters,
you just need to indicate the column name you want to use for ordering.

Taking as example the previous one you can define the ordering like this:

```
// GET /cars?color=red&color-op=neq&year=2000&year-op=gt&order_desc=color

public function index(Request $request)
{
    $cars = Cars::filters(QueryFilter:class, $request->all());
}
``` 

We'll have the cars which color isn't red which year is greater than 2000 and ordered by the column `color`.

## 2 - Custom filters
Sometimes you would like create more filters or overriding the existing ones. This is really easy, just extend the
QueryFilter class and create your own methods.

Usually, you will have an extended QueryFilter class for each Model as each Model will have its own custom scoped 
filters and requirements.

Let's create a extended QueryFilter class for the `Car` model class:

```
class CarFilter extends QueryFilter
{
    protected function color($value)
    {
        //Here we are overriding the filter "name". 
        
        $this->query->where('color_wheels', $value)->where('color', $value);
    }
}
```

Now, if we use the same exaple as before:

```
// GET /cars?color=red&color-op=neq&year=2000&year-op=gt&order_desc=color

public function index(Request $request)
{
    $cars = Cars::filters(CarFilter:class, $request->all());
}
```
As you can see, we changed the `QueryFilter:class` as argument of the `filters()` to use the new extended 
class `CarFilter::class`.

In this case, we get the red cars which wheels are red...etc (as we override the filter `color`, the operator is ignored).

(WIP)