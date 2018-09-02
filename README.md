# laravel-filters

## Disclaimer
I'm not an English speaker thus you could find mistakes and typos in this readme (and in code comments). I really sorry about that and I'll try to fix them as soon as I spot them.

## What is this?
**Laravel-filters** is a solution that I use in my projects to deal with filters
when I'm requesting data from the database. This project is based on a Laracasts's
tutorial about filters.

Some of the features of this package are these:

* Filtering based on the URL variables defined on the request.

* Works agnostically from the model. You can use it independently of the model
schema.

* Designed to be used as a `laravel's scope` thus, you can keep adding statements to the query after (or before) applying filters

* Convention over configuration. Most of the filters has defined a convention method
so you don't have to code them.

* Simplified and automatically pagination management

## How does it work?
**Laravel-filters** parse every `variable` on the request. Once you have an instance
of Laravel's `QueryBuilder` query you can apply all the filters to this query.
This will return the Laravel's `QueryBuilder` again thus you can add more statements.

As it manages pagination too, you can request the results, after create the full query, as a Eloquent's `Collection` (in case pagination is disabled) either a `LengthAwarePaginator` (in case a pagination is defined).

What's more, it has helpers in order to generate the `links()` method adding the
filters applied to the links so you don't have to deal with it.

## Getting started

First you would have to add this package to you `composer.json`.

TODO

Alternatively, at this package has a few files at this moment, you can copy them
and paste them where you want.

Once you have this package installed, 3 things (1 of them is optional) has to be done to make it work out.

#### Add filterable traits to the models
As **Laravel-filters** is designed to be used as a scope, this trait add two simple
scopes which can be replaced by whatever scope you want. This is just to set the
model up fastly.

Scope `filters()` will be used to "apply" the filters on the query which is being
building

Scope `getFiltersResults()` will be used to "get" the `Collection` (when no pagination)
or the `LengthAwarePaginator` when a pagination is defined. What's more, if you call
to `getFiltersResults()` without a previous call to `filters()` it will apply the filters for you before returns the result.

To add the `Filterable` trait to a model you have to add this:
TODO

#### Inject QueryFilter to the controller
**Laravel-filters** can be injected to a controller's method adding it as a argument of the method. Once it is added, you just need to call the previous scopes to apply the filters.

#### (Optional) Return the filter status to the view
**Laravel-filters** has valued information about the filters (which one has been applied, pagination information data...) thus you can send it to the view in order to get information in the view like:

* Pagination: check if there is pagination, get the `links()` to add the pagination buttons

* Filters: Check if filter has been applied
