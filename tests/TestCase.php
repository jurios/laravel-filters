<?php

namespace Kodilab\LaravelFilters\Tests;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected $test_files_path;

    protected $factories_path;

    protected $test_model_name = 'test_model';

    protected $test_model_table = 'test_models';

    protected $fallback_locale;

    protected function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->artisan('migrate')->run();

        $this->loadMigrationsFrom(__DIR__ . DIRECTORY_SEPARATOR . 'migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            \Kodilab\LaravelFilters\FiltersProvider::class
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $config = include 'config/config.php';

        $app['config']->set('filters', $config);
    }
}
