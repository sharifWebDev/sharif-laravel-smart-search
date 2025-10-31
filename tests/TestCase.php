<?php

namespace LaravelSmartSearch\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;
use LaravelSmartSearch\SmartSearchServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();

        Factory::guessFactoryNamesUsing(
            fn(string $modelName) => 'LaravelSmartSearch\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            SmartSearchServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Package configuration for testing
        config()->set('smart-search', [
            'defaults' => [
                'mode' => 'like',
                'deep' => true,
                'max_relation_depth' => 2,
                'search_operator' => 'or',
            ],
            'columns' => [
                'excluded' => ['id', 'created_at', 'updated_at', 'deleted_at'],
                'prioritized' => ['name', 'title', 'email'],
                'max_per_table' => 10,
            ],
            'relations' => [
                'auto_discover' => true,
                'max_depth' => 2,
                'excluded' => ['password'],
            ],
        ]);
    }

    protected function setUpDatabase(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
    }
}
