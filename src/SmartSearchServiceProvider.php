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
        // Merge package configuration
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
        // Only publish when running in console
        if ($this->app->runningInConsole()) {
            $this->publishResources();
        }

        // Register custom query builder macros
        $this->registerMacros();
    }

    /**
     * Publish package resources such as config file.
     */
    protected function publishResources(): void
    {
        $this->publishes([
            __DIR__ . '/../config/smart-search.php' => config_path('smart-search.php'),
        ], 'smart-search-config');

        // Optional: Publish all package resources under a global tag
        $this->publishes([
            __DIR__ . '/../config/smart-search.php' => config_path('smart-search.php'),
        ], 'smart-search');
    }

    /**
     * Register query builder macros for SmartSearch.
     */
    protected function registerMacros(): void
    {
        if (class_exists(BuilderMacros::class)) {
            BuilderMacros::register();
        }
    }
}
