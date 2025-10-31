<?php

namespace LaravelSmartSearch\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelSmartSearch\Traits\SmartSearch;
use Illuminate\Database\Eloquent\Model;

class SmartSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return ['LaravelSmartSearch\SmartSearchServiceProvider'];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /** @test */
    public function it_can_perform_basic_search()
    {
        // Create a test model that uses the trait
        $model = new class extends Model {
            use SmartSearch;

            protected $table = 'test_models';
            protected $fillable = ['name', 'description'];
        };

        // This is a simple test to verify the trait can be used
        $this->assertTrue(method_exists($model, 'scopeApplySmartSearch'));
    }

    /** @test */
    public function it_returns_query_builder_instance()
    {
        $model = new class extends Model {
            use SmartSearch;

            protected $table = 'test_models';
            protected $fillable = ['name', 'description'];
        };

        $query = $model->applySmartSearch('test');

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $query);
    }
}
