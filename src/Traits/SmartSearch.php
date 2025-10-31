<?php

namespace LaravelSmartSearch\Traits;

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

        $config = array_merge(config('smart-search.defaults', []), $options);
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
            $query->orWhere("{$table}.{$column}", 'LIKE', "%{$search}%");
        }

        // Relation search if enabled
        if ($config['deep'] ?? true) {
            $this->applyRelationSearch($query, $search, $model, $config);
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
}
