# Laravel-filters

`Laravel-filters` is a Laravel package for dealing with Eloquent models filtering.
This project is based on a [Laracasts videotutorial](https://laracasts.com/).

With `laravel-filters` each filter you apply will add an statement to the `QueryBuilder` in
a simple, clean and maintainable way.

By default, your filters will filter the results based on the model's database column names. However, you can override
an specific filter to change this behaviour or create your custom ones. We'll cover this in the next section. Let's work
with default filters for now.

For example, this is how we would call the filters in a `Controller`:

```

// GET /cars?color=red&order_desc=created_at


$cars = Car::where(...)->filters(QueryFilter::class, $request->all())->get();

```

We are calling to the filters and giving the request query parameters array. Therefore, `laravel-filters` will apply a 
filter called `color` with the value `red` and the "filter" 
(is not a filter properly, we explain this later) `order_desc` with the value `created_at`.

As we did neither override nor created any custom filters, filters will be applied using a default behaviour:

So, we are requesting cars which `color` column on database contains the `red` value 
and ordered by `created_at` column in a descendant direction.

As explained before, this is only the default filter behaviour. You can override filters or create new ones
in order to obtain the desired filter behaviour. But, let's step by step.

## 1 - Getting Started
Just install the package using `composer` with:

```
composer require kodilab/laravel-filters ^1.0.0
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
Car::filters(QueryFilters::class, ['filterA' => 'valueFilterA'])->get();
```

### The `filters()` scope
The `Filterable` trait just add an scope to the model which is `filters()` which has the following
signature (signature as scope):

#### filters(string $filter_class, array $input = [], string $prefix = '')

* **filter_class**: The filter class. You can use it the default one (QueryFilters) or extend it for create new filters 
                    or override them (This step will be explained later). For the moment, we use `QueryFilters:class`.
* **input**: An associative array of `[filter => value]` items. 
* **prefix**: Sometimes, specially when you use directly the `Request` array (`$request->all()`) for the input, 
                you don't want to use all items of that array. You can identify the items you want to use with a prefix. 
                For example, if you define `qf-` as a prefix, `qf-name` will be take from the array as a filter
                (and will filter by the `name` column) and `color` will be ignored as it doesn't start with `qf-`.
                
### Default filter behaviour
When the filtering process starts, every item on the `input` array goes through the same process. Let's imagine that 
`$input = ['color' => 'red']` is the input array. `laravel-filters` will look for a custom method called `color` 
on the class `$filter_class`. If it exists, then will apply what that method does (that's how you can define 
custom filters and overriding filters. We'll see it on detail in the following sections). In case a custom method 
doesn't exist, then default behaviour is fired: It will try to look for a column on the model's database table with the `color` name. 
If it exists, it will filter by that column. Otherwise, it will just ignore the filter.

Until now, the default behaviour filter is filtering be equality. You can go further, and add an `operator` in order to
change the comparison. 
Imagine you define this input: `$input = ['year' => '2000', 'year-op' => 'gte']`. When a filter has an `-op` suffix, 
has a special meaning: is a filter operator addon. It will modify the filtering default behaviour. In this case, as we 
define the operator as `gte`, it will filter by the column `year` which value is `>=` than `2000` (instead of using `=`).

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
    $cars = Cars::filters(QueryFilters:class, $request->all());
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
    $cars = Cars::filters(QueryFilters:class, $request->all());
}
``` 

We'll have the cars which color isn't red which year is greater than 2000 and ordered by the column `color`.

## 2 - Custom filters
Until now, we described the default behaviour. No code is needed. Everything is working under the hood.
However, sometimes you would like create more filters or overriding the existing ones. 
This is really easy, just extend the QueryFilters class and create your own methods.

Usually, you will have an extended QueryFilters class for each Model as each Model will have its own custom 
filters and requirements.

Let's create a extended QueryFilters class for the `Car` model class:

```
class CarFilters extends QueryFilters
{
    protected function color($value)
    {
        //Here we are overriding the filter "name".
        //The idea here is adding statements to the $this->query (QueryBuilder containing the results)
        
        //We change the behaviour, now filter color will filter by the car color AND the wheels color. The operator will be ignored
        $this->query->where('color_wheels', $value)->where('color', $value);
    }
}
```

Now, if we use the same request as before:

```
// GET /cars?color=red&color-op=neq&year=2000&year-op=gt&order_desc=color

public function index(Request $request)
{
    $cars = Cars::filters(CarFilter:class, $request->all());
}
```
As you can see, we changed the `QueryFilters:class` as argument of the `filters()` to use the new extended 
class `CarFilters::class`.

In this case, we get the red cars which wheels are red too as we are using our custom version of the filter `color` .

You can easily access to the operator in your custom filter with `$this->getFilterOperator(string $filter_name, $default = null)` which returns
the comparison symbol for using directly in the `where` statement (`=, !=, <>, <, <=, >, >=`) in case it exists.
