<?php

namespace Kodilab\LaravelFilters;

use Illuminate\Support\ServiceProvider;

class FiltersProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/config.php';
        $views_path = __DIR__ . '/../views';

        $this->publishes([
            $configPath => config_path('filters.php'),
        ]);

        $this->loadViewsFrom($views_path, 'filters');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__ . '/../config/config.php';

        $this->mergeConfigFrom(
            $configPath, 'filters'
        );
    }
}
