# laravel-filters

## Disclaimer
I'm not an English speaker thus you could find mistakes and typos in this readme (and in code comments). I really sorry about that and I'll try to fix them as soon as I spot them.

## What is this?
**Laravel-filters** allows you to define and apply filters into a Query Builder depending on the query
parameters of the request.

This package is based on the QueryFilter class explained in a Laracasts videotutorial.

## Getting started

### Installation
First, you have to add **laravel-filter** to your project using *composer*.

```
composer require kodilab/laravel-filters
```

Then you need to publish the files that **laravel-filters** needs like configuration files:
```
php artisan vendor:publish --provider="Kodilab\LaravelFilters\QueryFilterProvider"
```

This will add a filters.php config file into your `configs/` directory. Take it a look to configure the package.

## How start filtering

### Adding the trait
First, you must add the trait `Filterable` to the models which are going to be filtered.
```(php)

class Thing extends Model 
{
    use Filterable;
}

```

### Default filters

That trait will add the scope `filters()` which will be used to apply the filters when we build 
the QueryBuilder.

```(php)
// this/is/the/url?field1=a&field2=b

//Controller

public function index(QueryFilters $filters)
{
    $things = Thing::where('color', 'blue)->filters($filters)->where('size', 'long')->get();

    return view('some_view', compact(things, filters));
}

```
In that case, only the `Things` which are `color=blue`, `size=long`, `field1=a` and `field2=b` will be retrieved.

Look how automatically every parameter name is considered as it was the name of the model attribute.
In case that name is not a model attribute, then is ignored.

### Filter prefixes
Not every query parameter in the URL must be a filter. You can use a prefix to ignore the parameters which are not
filters:

```(php)
// this/is/the/url?field1=a&qf-field2=b

//Controller

public function index(Request $request)
{
    $filters = new QueryFilters($request, 'qf');
    
    $things = Thing::where('color', 'blue)->filters($filters)->where('size', 'long')->get();

    return view('some_view', compact(things, filters));
}

```
Here, we set `qf` as a prefix of our filters. Therefore, only the `Things` which are `color=blue`, `size=long` 
and `field2=b` will be retrieved. `field1=a` is ignored because it doesn't start with `qf`.

Notice here that you can define different groups of filters (by using different prefixes) which is useful if you want
to use multiple QueryBuilders in the same request:

```(php)
public function index(Request $request)
{
    $filtersF = new QueryFilters($request, 'qf');
    
    $filtersA = new QueryFilters($request, 'qa');
    
    $thingsF = ThingF::where('color', 'blue)->filters($filtersF)->where('size', 'long')->get();
    
    $thingsA = ThingA::where('color', 'blue)->filters($filtersA)->where('size', 'long')->get();

    ...
}
```


### Default filter operations

In some cases, you need to filter when a value is `>=, >, <, <=`. In that case, you 
can define the kind of operation the filter should apply adding the `*-op` method (being `*` the filter name) 
as a query parameter.


```(php)
// this/is/the/url?field1=a&field2=b&field2-op=lte

//Controller

public function index(QueryFilters $filters)
{
    $things = Thing::where('color', 'blue)->filters($filters)->where('size', 'long')->get();

    return view('some_view', compact(things, filters));
}

```
Here only the `Things` which are `color=blue`, `size=long`, `field1=a` and `field2<=b` will be retrieved.

You can use these filters operations:

<table>
    <thead>
        <tr>
            <th> Operation Query Parameter </th>
            <th> Symbol equivalence </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td> *-op=eq </td>
            <td> = </td>      
        </tr>
        <tr>
            <td> *-op=neq </td>
            <td> <> </td>      
        </tr>
        <tr>
            <td> *-op=gt </td>
            <td> > </td>      
        </tr>
        <tr>
            <td> *-op=gte </td>
            <td> >= </td>      
        </tr>
        <tr>
            <td> *-op=lt </td>
            <td> < </td>      
        </tr>
        <tr>
            <td> *-op=lte </td>
            <td> <= </td>      
        </tr>
    </tbody>
</table>

### Ordering
You can use `order_asc=field` or `order_desc=field` for ordering the results by a model attribute.

```(php)
// this/is/the/url?field1=a&field2=b&order_desc=field2

//Controller

public function index(QueryFilters $filters)
{
    $things = Thing::where('color', 'blue)->filters($filters)->where('size', 'long')->get();

    return view('some_view', compact(things, filters));
}

```
In that case, only the `Things` which are `color=blue`, `size=long`, `field1=a` and `field2=b` will be retrieved ordered
by `field2` descending.

If you need to order by something more complex than a simple model attribute, then you can overwrite the 
`order_desc` and `order_asc` filters. Filter overwriting is explained later. 

### Pagination
Pagination is considered another filter and therefore it has their own filter:

```
```(php)
// this/is/the/url?field1=a&field2=b&paginate=10

//Controller

public function index(QueryFilters $filters)
{
    $things = Thing::where('color', 'blue)->filters($filters)->where('size', 'long')->get();

    return view('some_view', compact(things, filters));
}

```
In this case, a pagination of 10 records will be applied.
In your views you can call to `$filters->links()` in order to render the pagination buttons. 
If no pagination is applied, then anything is rendered. (Notice that calling `$filters->links()` 
when there isn't pagination won't throw an error)

### Custom filters (Filter overwriting)

You can overwrite any filter explained before or create your own just extending the class `QueryFilters`. In the next example, we want to
change the behaviour of `field1` filter and create a new fillter called `new_cool_filter`:

```

class NewQueryFilters extends QueryFilters
{
    public function field1($value)
    {
        $this->query->where('field1', 'something');
    }
    
    public function new_cool_filter($value)
    {
        $this->query->cool_scope();
    }
}
```

Then, in the controller methods where you want to use this new filters just use this class
```(php)
public function index(NewQueryFilters $filters)
{
    ...
}
```