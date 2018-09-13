# laravel-filters

## Disclaimer
I'm not an English speaker thus you could find mistakes and typos in this readme (and in code comments). I really sorry about that and I'll try to fix them as soon as I spot them.

## What is this?
**Laravel-filters** is a solution that I use in my projects to deal with filters
when I'm requesting data from the database. This project is based on a Laracasts's
tutorial about filters.

Some of the features of this package are these:

* Filtering based on the URL variables defined on the request.
```
/this/is/the/url?qf-var1=a&qf-var2=b

// This will apply filters called var1 with value a and var2 with value b
```

* If desired, automatically filter model's attribute agnostically.
```
/this/is/the/url?qf-var1=a&qf-var2=b

// This will look for entities whose attribute var1 == a y var2 == b
// If var1 doesn't exists, it ignores this filter
```

* You can specific the comparision that it will be executed
```
/this/is/the/url?qf-var1=1&qf-var1-op=lte

// This will look for entities whose attribute var1 <= 1
// If var1 doesn't exists, it ignores this filter
```

* Designed to be used as a `laravel's scope` thus you can keep adding statements to the query after (or before) applying filters
```
Model::where('var1', 'a')->filters($filters)->where('var', 'b')->get();
```

* Convention over configuration. You can overwrite how a filter is applied

* Simplified and automatically pagination management
```
/this/is/the/url?qf-var1=a&qf-paginate=10
//This will paginate each 10 records

$filters->links() // Then you can call for pagination buttons using the Laravel pagination system
```

* Integrated with [laravel-debugbar](https://github.com/barryvdh/laravel-debugbar) if it's present in the project

## Getting started

### Installation
First, you have to add **laravel-filter** to your project using *composer*. First, add this repository to 
your project's `composer.json`:
```json
"repositories": [
  {
      "type": "vcs",
      "url": "https://github.com/jurios/laravel-filters"
  }
],
```

Then, just require **laravel-filters**:
```
composer require kodilab/laravel-filters
```

Then you need to publish the files that **laravel-filters** needs like configuration files:
```
php artisan vendor:publish --provider="Kodilab\LaravelFilters\QueryFilterProvider"
```

This will add a filters.php config file into your `configs/` directory. Take it a look to configure the package.

### Inject laravel-filters

You can inject **laravel-filters** using the `QueryFilter` class directly in your controller method:

```php
public function index(QueryFilter $request)
    {
        $events = Event::where('age', > , 18)->filters($request)->get();

        return view('events.index', compact('events', 'request', 'categories'));
    }
```
In this snippet we are filtering the model `Event`. In order to filter this model, you should add the trait `Filterable`
to the model.

```php
class Event extends Model
{
    ...
    use Filterable;
    ...
}
```

What `Filterable` does is adding two scopes that we use to filter (`filters(QueryFilter) and filterResults(QueryFilter`)

`filters())` scope returns `Illuminate\Database\Eloquent\Builder` thus you can add more statement to your query:
```php
$events = Event::->where('age', > 18)->filters($request)->where('age', <, 70)->get();
```

`filtersResults()` scope returns the `Illuminate\Database\Eloquent\Collection` when no is paginated or 
`Illuminate\Pagination\LengthAwarePaginator` when a pagination is requested. After calling `filtersResult()`, you can't add
more statements to the query:
```php
$events = Event::->where('age', > 18)->filtersResults($request)->where('age', <, 70); // This will fail
$events = Event::->where('age', > 18)->filtersResults($request)->get(); // This will fail, too.

$events = Event::->where('age', > 18)->filtersResults($request); // This works. Call to ->get() is not needed
```

We implement the `->get()` function internally because **laravel-filters** deal with pagination internally.

`filtersResults()` scope will apply the filters if they haven't been applied before:

```php
//Both do the same
$events = Event::->where('age', > 18)->filters($request)->filtersResults($request);
$events = Event::->where('age', > 18)->filtersResults($request);
```

### How filters are applied

Generally, a filter called in the URL is translated to a statement in the `Query Builder`. However, you could create
more complex filters that do more stuff than that. We'll see how to create or own custom filters later.

If you take a look to `QueryFilter` class which is the base class of the **laravel-filters** you will notice that a filter
is just a function that does things to the `Query Builder`. So, for example, the filter `qf-order_desc` will call
the function `order_desc($value)` being `$value` the value in the URL.

As you can see, to identify the filters in the URL and ignoring the variable which aren't, we use a prefix `qf-` which can
be configured in the configuration file. This prefix is `destroyed` before apply the filter so `order_desc` is the 
filter's name and will call the function `order_desc($value)` but is called in the URL by `qf-order_desc`

By default, there are some filters that they will applied automatically:

#### qf-order_desc=attribute
URL (example):
```
/this/is/the/url?....&qf-order_desc=age
```

If `age` is an attribute of the model (`age` is a column of the database table), then this statement is applied to the query:
```php
->orderBy('age', 'desc')
```

#### qf-order_asc=attribute
URL (example):
```
/this/is/the/url?....&qf-order_asc=age
```

If `age` is an attribute of the model (`age` is a column of the database table), then this statement is applied to the query:
```php
->orderBy('age', 'asc')
```

#### qf-paginate=10
URL(example):
```
/this/is/the/url?....&qf-paginate=10
```

This will apply a pagination of 10 records in the query. This filter is applied in a different way. Please, see the
pagination section to see the differences.

#### qf-*=value (being * a wildcard)
URL(example):
```
/this/is/the/url?....&qf-slots=10
```

When doesn't exists a specific function for a filter in the URL, then `QueryFilter` will looks whether the `wildcard` 
(in this case, `slots`) is a model's column in the database table. If it is, then it will add:
```php
->where('slots', 10)
```
If it isn't, it will ignored.

##### Ignore a filter
Sometimes for some reason you want **laravel-filters** ignore a filter. You can do it dynamically adding the filter name 
(the filter name without prefix) to the ignore list:

```php
public function index(QueryFilter $request)
{
    $request->ignore(['age', 'name']);
    
    $events = Event::where('age', > , 18)->filtersResults($request); //Pagination applied
    
}
```

If you are extending the **laravel-filters**'s `QueryFilter` class, you can define your ignore list statically:
```
class EventFilter extends QueryFilter
{
    protected $ignore = ['age', 'name'];
    ...
}
```

### Ok, but I want to create my own filters
You can create your own class extending the `QueryFilter` class. Then you can create your own filters and
overwrite the default ones, if you desire. In fact, is recommended creating a `QueryFilter` for every model that 
is `Filterable` in order to having distinct behaviours for the same filter in different models or 
creating model-specific filters.

### Pagination
**laravel-filters** considers pagination as a filter too as we can see above. However, pagination has some things to
consider. Pagination will be applied when we call to `filtersResults` scope thus we can't add more statement to the query.

Be careful with this:
```php
$events = Event::->where('age', > 18)
                 ->filters($request)
                 ->where('age', <, 70); // Pagination hasn't been applied

$events = Event::->where('age', > 18)
                 ->filters($request)
                 ->where('age', <, 70)
                 ->filtersResults($request); // Pagination has been applied
                 
$events = Event::->where('age', > 18)
                 ->where('age', <, 70)
                 ->filtersResults($request); // Pagination has been applied
```

When pagination has been applied, our `QueryFilter` class contains really usefull methods to be used in our views:

URL (example):
```
/this/is/the/url?....&qf-paginate=10
```

Controller:

```php
public function index(QueryFilter $request)
    {
        $events = Event::where('age', > , 18)->filtersResults($request); //Pagination applied
        
        $request->isPaginated(); // Returns whether it has been paginated
        $request->links(); // Will return the html buttons for pagination in case that is paginated
        
        return view('events.index,' compact('events, 'request'));
    
    }
```
In the view we can:
```

<div>
  {{ $request->links() }}
</div>
```
This will render the pagination navigation buttons in case that pagination has been applied. If it isn't, then it won't
render anything. What's more, it will add the parameters of the previous URL to this buttons in order to apply the same filters
when you change the page's pagination.

