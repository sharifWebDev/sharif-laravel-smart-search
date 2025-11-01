<?php

namespace Sharifuddin\LaravelSmartSearch\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Sharifuddin\LaravelSmartSearch\Contracts\Searchable;

trait SmartSearch
{
    /**
     * Apply smart search to query.
     */
    public function scopeApplySmartSearch(
        Builder $query,
        ?string $search = null,
        array $columns = [],
        array $options = []
    ): Builder {
        $search = $this->resolveSearchTerm($search);

        if (!$this->shouldPerformSearch($search)) {
            return $query;
        }

        $config = $this->buildSearchConfig($options);
        $search = trim($search);

        if ($this->shouldUseFullTextSearch($config)) {
            return $this->applyFullTextSearch($query, $search, $columns, $config);
        }

        return $query->where(function ($q) use ($search, $columns, $config) {
            $this->applySearchConditions($q, $search, $columns, $config);
        });
    }

    /**
     * Resolve search term from various sources.
     */
    protected function resolveSearchTerm(?string $search): ?string
    {
        if ($search !== null) {
            return $search;
        }

        return request()?->input('query')
            ?? request()?->input('search')
            ?? request()?->input('q');
    }

    /**
     * Determine if search should be performed.
     */
    protected function shouldPerformSearch(?string $search): bool
    {
        return $search !== null
            && !empty(trim($search))
            && config('smart-search.enabled', true);
    }

    /**
     * Build search configuration.
     */
    protected function buildSearchConfig(array $options): array
    {
        $defaults = [
            'mode' => 'like',
            'deep' => true,
            'max_relation_depth' => 2,
            'search_operator' => 'or',
            'case_sensitive' => false,
            'full_text' => false,
            'min_length' => 2,
            'max_length' => 255,
        ];

        return array_merge(
            $defaults,
            config('smart-search.defaults', []),
            $this->getSearchConfig(),
            $options
        );
    }

    /**
     * Apply search conditions to query.
     */
    protected function applySearchConditions(
        $query,
        string $search,
        array $columns,
        array $config
    ): void {
        $model = $this;
        $table = $model->getTable();

        // Validate search term
        $search = $this->validateSearchTerm($search, $config);

        // Get searchable columns
        $searchableColumns = empty($columns)
            ? $this->getSearchableColumns()
            : $columns;

        // Apply priority sorting
        $searchableColumns = $this->prioritizeColumns($searchableColumns);

        // Apply local search
        $this->applyLocalSearch($query, $search, $searchableColumns, $config);

        // Apply relation search
        if ($this->shouldSearchRelations($config)) {
            $this->applyRelationSearch($query, $search, $config);
        }
    }

    /**
     * Validate and format search term.
     */
    protected function validateSearchTerm(string $search, array $config): string
    {
        $search = trim($search);

        // Check min/max length
        $length = mb_strlen($search);
        $minLength = $config['min_length'] ?? 2;
        $maxLength = $config['max_length'] ?? 255;

        if ($length < $minLength) {
            throw new \InvalidArgumentException(
                "Search term must be at least {$minLength} characters long."
            );
        }

        if ($length > $maxLength) {
            throw new \InvalidArgumentException(
                "Search term must not exceed {$maxLength} characters."
            );
        }

        // Escape special characters for LIKE searches
        if (($config['mode'] ?? 'like') === 'like') {
            $search = addcslashes($search, '%_\\');
        }

        return $search;
    }

    /**
     * Apply local column search.
     */
    protected function applyLocalSearch(
        $query,
        string $search,
        array $columns,
        array $config
    ): void {
        $table = $this->getTable();
        $operator = $config['search_operator'] === 'and' ? 'where' : 'orWhere';
        $searchTerm = $this->formatSearchTerm($search, $config);

        foreach ($columns as $column) {
            if ($this->isColumnSearchable($column)) {
                $query->{$operator}(
                    "{$table}.{$column}",
                    $this->getSearchOperator($config),
                    $searchTerm
                );
            }
        }
    }

    /**
     * Apply relation search.
     */
    protected function applyRelationSearch($query, string $search, array $config): void
    {
        $relations = $this->getSearchableRelations();
        $maxDepth = $config['max_relation_depth'] ?? 2;

        foreach ($relations as $relationName => $relationConfig) {
            if ($this->shouldSearchRelation($relationName, $relationConfig, $config)) {
                $this->applySingleRelationSearch(
                    $query,
                    $search,
                    $relationName,
                    $relationConfig,
                    $config,
                    $maxDepth
                );
            }
        }
    }

    /**
     * Apply search for a single relation.
     */
    protected function applySingleRelationSearch(
        $query,
        string $search,
        string $relationName,
        array $relationConfig,
        array $config,
        int $maxDepth
    ): void {
        $operator = $config['search_operator'] === 'and' ? 'whereHas' : 'orWhereHas';

        $query->{$operator}($relationName, function ($relQuery) use (
            $search,
            $relationConfig,
            $config,
            $maxDepth
        ) {
            $relatedModel = $relQuery->getModel();
            $columns = $relationConfig['columns'] ?? [];
            $nestedConfig = [
                ...$config,
                'max_relation_depth' => $maxDepth - 1,
            ];

            if (method_exists($relatedModel, 'applySearchConditions')) {
                $relatedModel->applySearchConditions($relQuery, $search, $columns, $nestedConfig);
            } else {
                // Fallback for non-searchable related models
                $this->applyFallbackRelationSearch($relQuery, $search, $columns, $nestedConfig);
            }
        });
    }

    /**
     * Fallback relation search implementation.
     */
    protected function applyFallbackRelationSearch($query, string $search, array $columns, array $config): void
    {
        $table = $query->getModel()->getTable();
        $searchTerm = $this->formatSearchTerm($search, $config);
        $operator = $config['search_operator'] === 'and' ? 'where' : 'orWhere';

        foreach ($columns as $column) {
            $query->{$operator}("{$table}.{$column}", $this->getSearchOperator($config), $searchTerm);
        }
    }

    /**
     * Get searchable columns for the model.
     */
    public function getSearchableColumns(): array
    {
        if (method_exists($this, 'searchableColumns')) {
            return $this->searchableColumns();
        }

        $table = $this->getTable();
        $allColumns = Schema::getColumnListing($table);
        $excluded = config('smart-search.columns.excluded', []);

        return array_filter($allColumns, function ($column) use ($excluded) {
            return !in_array($column, $excluded) &&
                !Str::endsWith($column, ['_token', 'password', 'secret']);
        });
    }

    /**
     * Get searchable relations for the model.
     */
    public function getSearchableRelations(): array
    {
        if (method_exists($this, 'searchableRelations')) {
            return $this->searchableRelations();
        }

        $relations = [];
        $columns = Schema::getColumnListing($this->getTable());

        foreach ($columns as $column) {
            if (Str::endsWith($column, '_id')) {
                $relationName = Str::camel(str_replace('_id', '', $column));

                if ($this->isValidRelation($relationName)) {
                    $relations[$relationName] = [
                        'columns' => $this->getRelationSearchableColumns($relationName),
                        'max_depth' => 1,
                    ];
                }
            }
        }

        return $relations;
    }

    /**
     * Get search configuration for the model.
     */
    public function getSearchConfig(): array
    {
        if (method_exists($this, 'searchConfig')) {
            return $this->searchConfig();
        }

        return [];
    }

    /**
     * Check if a column is searchable.
     */
    protected function isColumnSearchable(string $column): bool
    {
        $excluded = config('smart-search.columns.excluded', []);

        return !in_array($column, $excluded) &&
            !Str::endsWith($column, ['_token', 'password', 'secret']);
    }

    /**
     * Check if relation should be searched.
     */
    protected function shouldSearchRelation(
        string $relationName,
        array $relationConfig,
        array $config
    ): bool {
        $excludedRelations = config('smart-search.relations.excluded', []);

        return !in_array($relationName, $excludedRelations) &&
            ($relationConfig['enabled'] ?? true) &&
            ($config['max_relation_depth'] ?? 2) > 0;
    }

    /**
     * Check if relations should be searched.
     */
    protected function shouldSearchRelations(array $config): bool
    {
        return ($config['deep'] ?? true) && ($config['max_relation_depth'] ?? 2) > 0;
    }

    /**
     * Check if full-text search should be used.
     */
    protected function shouldUseFullTextSearch(array $config): bool
    {
        return ($config['full_text'] ?? false) && $this->supportsFullTextSearch();
    }

    /**
     * Check if database supports full-text search.
     */
    protected function supportsFullTextSearch(): bool
    {
        $connection = $this->getConnection();
        $driver = $connection->getDriverName();

        return in_array($driver, ['mysql', 'pgsql']);
    }

    /**
     * Apply full-text search.
     */
    protected function applyFullTextSearch(
        Builder $query,
        string $search,
        array $columns,
        array $config
    ): Builder {
        $searchableColumns = empty($columns) ? $this->getSearchableColumns() : $columns;
        $table = $this->getTable();

        return $query->where(function ($q) use ($search, $searchableColumns, $table) {
            foreach ($searchableColumns as $column) {
                $q->orWhereRaw("MATCH({$table}.{$column}) AGAINST(? IN BOOLEAN MODE)", [$search]);
            }
        });
    }

    /**
     * Get search operator based on configuration.
     */
    protected function getSearchOperator(array $config): string
    {
        return match ($config['mode']) {
            'exact' => '=',
            'starts_with' => 'LIKE',
            'ends_with' => 'LIKE',
            default => 'LIKE',
        };
    }

    /**
     * Format search term based on search mode.
     */
    protected function formatSearchTerm(string $search, array $config): string
    {
        return match ($config['mode']) {
            'exact' => $search,
            'starts_with' => "{$search}%",
            'ends_with' => "%{$search}",
            default => "%{$search}%",
        };
    }

    /**
     * Prioritize columns based on configuration.
     */
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

    /**
     * Check if relation is valid.
     */
    protected function isValidRelation(string $relationName): bool
    {
        try {
            return method_exists($this, $relationName) &&
                is_object($this->{$relationName}()) &&
                method_exists($this->{$relationName}(), 'getRelated');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get searchable columns for a relation.
     */
    protected function getRelationSearchableColumns(string $relationName): array
    {
        try {
            $relatedModel = $this->{$relationName}()->getRelated();
            \Illuminate\Support\Facades\Log::info($relatedModel);
            return $relatedModel->getSearchableColumns();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
