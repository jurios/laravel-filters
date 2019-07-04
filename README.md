# Laravel-filters

`Laravel-filters` is a Laravel package for dealing with Eloquent models filtering and also Collection filtering.
This project is based on a [Laracasts videotutorial](https://laracasts.com/).

With `laravel-filters` you could apply filter in a simple, clean and maintainable way.

By default, you filters works directly with the data structure. When you are filtering  a `Collection`, the filtering
is done based on the Collection's `array` structure. In case you are filtering an `Eloquent model`, 
then the filtering is based on the database structure of that `model`. 

However, you can override an specific filter or create a new one in order to define how the filter works. 
We'll cover this in the next section. Let's work with default filters for now.

For example, this is how we would call the filters in a `Controller`:

```

// GET /cars?color=red&order_desc=created_at


$cars = Car::where(...)->filters(QueryFilter::class, $request->all())->get();

```

We are calling to the filters and giving the request query parameters array 
(in this case, `['color' => 'red', 'order_desc' => 'created_at']`). Therefore, `laravel-filters` will apply a 
filter called `color` with the value `red` and the "filter" 
(is not a filter properly, we explain this later) `order_desc` with the value `created_at`.

As we did neither override nor created any custom filters, filters will be applied using a default behaviour as explained
before.

So, in this case, we are requesting car `Eloquent models` which `color` column on database contains the `red` value 
and ordered by `created_at` column in a descendant direction. As you can see, we filtered the results with no extra code.
Easy and clean.

As mentioned before, this is only the default filter behaviour. You can override filters or create new ones
in order to obtain the desired filter behaviour. But, let's step by step.

## 1 - Getting Started
Just install the package using `composer` with:

```
composer require kodilab/laravel-filters ^1.0.0
```

## 2 - Eloquent Model Filters
As mentioned in the introduction, you are able to filter `Eloquent models` and `Collections`. 
In order to make a `model` ready for be filterable, add the `Filterable` trait:

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

### 2.1 - The `filters()` scope
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
                
### 2.2 - Default filter behaviour explained
When the filtering process starts, every item on the `input` array goes through the same process. Let's imagine 
`$input = ['color' => 'red']` is the input array. `laravel-filters` will look for a custom method called `color` 
on the class `$filter_class`. If it exists, then will apply what that method does (that's how you can define 
custom filters and overriding filters. We'll see it on detail in the following sections). In case a custom method 
doesn't exist, then default behaviour is fired: It will try to look for a column on the model's database table with the 
`color` name. If it exists, it will filter by that column. Otherwise, it will just ignore the filter.

### 2.3 - Working with default behaviour using operators
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

## 3 - Collection filters
`Collection` filtering works in the same way as it works for `Collection`.
In order to use filters in a collection you have two options here:

#### 3.1 - Instance manually the filters
This is the most conservative way as you don't need to create an extended `Collection`. However you must instance
the filter by your own every time you want to filter.

This is an example using filtering in a `Controller` method:

```
public function index(Request $request)
{
    //Cars contains a collection
    $data = new Collection($data);
    
    //Instance the filters
    $filters = new CollectionFilters();
    
    //Then apply the filters. Apply() will return the filtered collection
    $cars = $filters->apply($data, $request->all());
}
```
In this case, instead of using `QueryFilters` for the filter class, we are using the `CollectionFilters`.
This is important, when you work with `Eloquent models` you must use `QueryFilters` or and extended class of `QueryFilters`.
When you work with `Collection`, then `CollectionFilters` must be used or an extended class of it.

#### 3.2 - Extend the `Collection`
This way is a bit complex, however it will let you use filters in a very similar way as you use for `Eloquent models`. 
First you should create a Collection class which extends from the `\Illuminate\Support\Collection` and add a method
called `filters()`. Remember use this new class instead of the original `Collection` when you want to filter a collection.

```
class Collection extends \Illuminate\Support\Collection
{
    public function filters(string $filter_class, array $input = [], string $prefix = '')
    {
        /** @var CollectionFilters $filters */
        $filters = new $filter_class();

        return $filters->apply($this, $input, $prefix);
    }
}
```

Then you can use `Collection` filters in a similar way as Model filtering:

```
public function index(Request $request)
{
    //Cars contains a collection. Remember use the extended Collection you created before
    $data = new Collection($data);
    
    $cars = $data->filters(CollectionFilters::class, $request->all());
}
```

Again, we are using here `CollectionFilters` instead of `QueryFilters` as we are working with `Collections`.

## 4 - Custom filters
Until now, we described the default behaviour. No code is needed. Everything is working under the hood.
However, sometimes you would like create more filters or overriding the existing ones. 
This is really easy, just extend the `QueryFilters` or `CollectionFilters` class and create your own methods.

Usually, you will have an extended `QueryFilters` class for each Model as each Model will have its own custom 
filters and requirements. And you will have an extended CollectionFilters for each Collection you have in your
project.

Let's create a extended QueryFilters class for the `Car` model class:

```
class CarFilters extends QueryFilters
{
    /*
     * We are overridin the filter "color" in order to filter cars with an specific color which wheels are the same color
     */
    protected function color($value)
    {
        //The idea here is adding statements to the $this->results (QueryBuilder containing the results)
        // as we are extending a QueryFilters class
        
        // If you are extending from CollectionFilters, $this->results contains the Collection.
        
        //We change the behaviour, now filter color will filter by the car color AND the wheels color. 
        //The operator for this filter will be ignored
        
        $this->results->where('color_wheels', $value)->where('color', $value);
        
        //The new results MUST be returned
        
        return $this->results;
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
