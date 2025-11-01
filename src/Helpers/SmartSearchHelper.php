<?php

namespace Sharifuddin\LaravelSmartSearch\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class SmartSearchHelper
{
    public function apply(Builder $query, string $term, array $columns = [], array $options = []): Builder
    {
        $mode = $options['mode'] ?? 'like';
        $deep = $options['deep'] ?? true;
        $table = $query->getModel()->getTable();

        $columns = $columns ?: Schema::getColumnListing($table);

        $query->where(function ($q) use ($columns, $term, $mode, $table) {
            foreach ($columns as $column) {
                $operator = $mode === 'like' ? 'like' : '=';
                $value = $mode === 'like' ? "%{$term}%" : $term;

                $q->orWhere("{$table}.{$column}", $operator, $value);
            }
        });

        return $query;
    }
}
