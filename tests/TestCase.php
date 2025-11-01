<?php

namespace Sharifuddin\LaravelSmartSearch\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Sharifuddin\LaravelSmartSearch\SmartSearchServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->withFactories(__DIR__ . '/Database/factories');

        Factory::guessFactoryNamesUsing(
            fn(string $modelName) => 'Sharifuddin\\LaravelSmartSearch\\Tests\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
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
        config()->set('smart-search', require __DIR__ . '/../config/smart-search.php');
    }

    protected function setUpDatabase(): void
    {
        $migrations = [
            __DIR__ . '/Database/Migrations/create_users_table.php',
            __DIR__ . '/Database/Migrations/create_categories_table.php',
            __DIR__ . '/Database/Migrations/create_products_table.php',
            __DIR__ . '/Database/Migrations/create_tags_table.php',
            __DIR__ . '/Database/Migrations/create_product_tag_table.php',
        ];

        foreach ($migrations as $migration) {
            if (file_exists($migration)) {
                include $migration;

                $migrationClass = require $migration;
                if (is_object($migrationClass)) {
                    $migrationClass->up();
                }
            }
        }
    }

    /**
     * Define database migrations.
     */
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
    }
}
