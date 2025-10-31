<?php

namespace LaravelSmartSearch;

use Illuminate\Support\ServiceProvider;

class SmartSearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge package configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/smart-search.php',
            'smart-search'
        );
    }

    public function boot(): void
    {
        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/smart-search.php' => config_path('smart-search.php'),
            ], 'smart-search-config');
        }
    }
}
