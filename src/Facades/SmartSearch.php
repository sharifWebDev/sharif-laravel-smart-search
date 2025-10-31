<?php

namespace LaravelSmartSearch\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

trait SmartSearch
{
    /**
     * Apply smart search on query
     */
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

        $search = trim($search);

        return $query->where(function ($q) use ($search, $columns) {
            $table = $this->getTable();

            // If no columns specified, use fillable or all columns
            if (empty($columns)) {
                $columns = $this->getFillable() ?: Schema::getColumnListing($table);
            }

            foreach ($columns as $column) {
                $q->orWhere("{$table}.{$column}", 'LIKE', "%{$search}%");
            }
        });
    }
}
