<?php

namespace Sharif\LaravelSmartSearch;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Traits\Macroable;
use Sharif\LaravelSmartSearch\Contracts\Searchable;
use Sharif\LaravelSmartSearch\Exceptions\SmartSearchException;

class SmartSearchManager
{
    use Macroable;

    public function __construct(
        protected Application $app
    ) {}

    /**
     * Apply smart search to query builder.
     */
    public function apply(Builder $query, string $search, array $options = []): Builder
    {
        $model = $query->getModel();

        if (!in_array(Searchable::class, class_implements($model))) {
            throw new SmartSearchException(
                "Model [" . get_class($model) . "] must implement Searchable contract."
            );
        }

        return $model->applySmartSearch($query, $search, $options);
    }

    /**
     * Get search configuration.
     */
    public function config(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return config('smart-search');
        }

        return config("smart-search.{$key}", $default);
    }

    /**
     * Check if search is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->config('enabled', true);
    }

    /**
     * Dynamically handle calls to the manager.
     */
    public function __call(string $method, array $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        throw new SmartSearchException("Method [{$method}] does not exist.");
    }
}
