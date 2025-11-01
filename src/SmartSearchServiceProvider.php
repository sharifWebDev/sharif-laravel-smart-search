<?php

namespace Sharifuddin\LaravelSmartSearch;

use Illuminate\Support\ServiceProvider;
use Sharifuddin\LaravelSmartSearch\Macros\BuilderMacros;

class SmartSearchServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/smart-search.php',
            'smart-search'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishResources();
        }

        $this->registerMacros();
    }

    /**
     * Publish package resources.
     */
    protected function publishResources(): void
    {
        $this->publishes([
            __DIR__ . '/../config/smart-search.php' => config_path('smart-search.php'),
        ], 'smart-search-config');
    }

    /**
     * Register query builder macros.
     */
    protected function registerMacros(): void
    {
        // Only register macros if the class exists
        if (class_exists(BuilderMacros::class)) {
            BuilderMacros::register();
        }
    }
}
