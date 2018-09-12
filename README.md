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
/this/is/the/url?var1=a&paginate=10
//This will paginate each 10 records

$filters->links() // Then you can call for pagination buttons using the Laravel pagination system
```

* Integrated with [laravel-debugbar](https://github.com/barryvdh/laravel-debugbar) if it's present in the project

## Getting started