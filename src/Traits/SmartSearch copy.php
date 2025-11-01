<?php

namespace Sharif\LaravelSmartSearch\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

trait SmartSearch
{
    public function scopeApplySmartSearch(
        Builder $query,
        ?string $search = null,
        array $columns = [],
        array $options = []
    ): Builder {
        $search = $search ?: request()?->input('query') ?: request()?->input('search');

        if (!$search || empty(trim($search))) {
            return $query;
        }

        $config = array_merge([
            'deep' => true,
            'max_relation_depth' => 2,
            'mode' => 'like',
        ], config('smart-search.defaults', []), $options);

        $search = trim($search);

        return $query->where(function ($q) use ($search, $columns, $config) {
            $this->applySearchConditions($q, $search, $columns, $config);
        });
    }

    protected function applySearchConditions($query, string $search, array $columns, array $config): void
    {
        $model = $this;
        $table = $model->getTable();

        // Determine searchable columns
        $searchableColumns = empty($columns)
            ? $this->getSearchableColumns($model)
            : $columns;

        // Apply priority sorting
        $searchableColumns = $this->prioritizeColumns($searchableColumns);

        // Local column search
        foreach ($searchableColumns as $column) {
            $searchTerm = $this->formatSearchTerm($search, $config['mode']);
            $query->orWhere("{$table}.{$column}", 'LIKE', $searchTerm);
        }

        // Relation search if enabled and depth allows
        $maxDepth = $config['max_relation_depth'] ?? 2;
        $deepEnabled = $config['deep'] ?? true;

        if ($deepEnabled && $maxDepth > 0) {
            $this->applyRelationSearch($query, $search, $model, $config);
        }
    }

    protected function applyRelationSearch($query, string $search, $model, array $config): void
    {
        $relations = $this->getSearchableRelations($model, $config);

        foreach ($relations as $relationName => $relatedModel) {
            $query->orWhereHas($relationName, function ($relQuery) use ($search, $relatedModel, $config) {
                $nestedConfig = [
                    ...$config,
                    'max_relation_depth' => ($config['max_relation_depth'] ?? 2) - 1,
                ];

                // Create a temporary model instance for the related model
                $tempModel = new class($relatedModel) {
                    private $model;

                    public function __construct($model)
                    {
                        $this->model = $model;
                    }

                    public function applySearchConditions($query, $search, $columns, $config)
                    {
                        $table = $this->model->getTable();
                        $searchableColumns = empty($columns)
                            ? $this->getSearchableColumns($this->model)
                            : $columns;

                        foreach ($searchableColumns as $column) {
                            $searchTerm = $this->formatSearchTerm($search, $config['mode']);
                            $query->orWhere("{$table}.{$column}", 'LIKE', $searchTerm);
                        }
                    }

                    protected function getSearchableColumns($model): array
                    {
                        $table = $model->getTable();
                        $allColumns = Schema::getColumnListing($table);
                        $excluded = config('smart-search.columns.excluded', []);

                        return array_filter($allColumns, function ($column) use ($excluded) {
                            return !in_array($column, $excluded) &&
                                !Str::endsWith($column, ['_token', 'password']);
                        });
                    }

                    protected function formatSearchTerm(string $search, string $mode): string
                    {
                        return match ($mode) {
                            'starts_with' => "{$search}%",
                            'ends_with' => "%{$search}",
                            'exact' => $search,
                            default => "%{$search}%",
                        };
                    }
                };

                $tempModel->applySearchConditions($relQuery, $search, [], $nestedConfig);
            });
        }
    }

    protected function getSearchableRelations($model, array $config): array
    {
        $relations = [];
        $columns = Schema::getColumnListing($model->getTable());

        foreach ($columns as $column) {
            if (Str::endsWith($column, '_id')) {
                $relationName = Str::camel(str_replace('_id', '', $column));

                if ($this->isValidRelation($model, $relationName)) {
                    $relations[$relationName] = $model->{$relationName}()->getRelated();
                }
            }
        }

        return $relations;
    }

    protected function isValidRelation($model, string $relationName): bool
    {
        try {
            return method_exists($model, $relationName) &&
                is_object($model->{$relationName}()) &&
                method_exists($model->{$relationName}(), 'getRelated');
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function getSearchableColumns($model): array
    {
        $table = $model->getTable();
        $allColumns = Schema::getColumnListing($table);
        $excluded = config('smart-search.columns.excluded', []);

        return array_filter($allColumns, function ($column) use ($excluded) {
            return !in_array($column, $excluded) &&
                !Str::endsWith($column, ['_token', 'password']);
        });
    }

    protected function prioritizeColumns(array $columns): array
    {
        $prioritized = config('smart-search.columns.prioritized', []);

        usort($columns, function ($a, $b) use ($prioritized) {
            $aPriority = array_search($a, $prioritized);
            $bPriority = array_search($b, $prioritized);

            if ($aPriority === false && $bPriority === false) return 0;
            if ($aPriority === false) return 1;
            if ($bPriority === false) return -1;

            return $aPriority - $bPriority;
        });

        return $columns;
    }

    protected function formatSearchTerm(string $search, string $mode): string
    {
        return match ($mode) {
            'starts_with' => "{$search}%",
            'ends_with' => "%{$search}",
            'exact' => $search,
            default => "%{$search}%",
        };
    }
}
