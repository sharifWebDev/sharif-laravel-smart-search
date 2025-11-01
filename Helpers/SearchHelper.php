<?php

namespace Sharif\LaravelSmartSearch\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SearchHelper
{
    /**
     * Parse search query for advanced patterns.
     */
    public static function parseQuery(string $query): array
    {
        $patterns = [
            'exact' => '/"([^"]+)"/',
            'exclude' => '/-(\w+)/',
            'or' => '/\bOR\b/i',
        ];

        $exactMatches = [];
        preg_match_all($patterns['exact'], $query, $exactMatches);
        $query = preg_replace($patterns['exact'], '', $query);

        $excludeMatches = [];
        preg_match_all($patterns['exclude'], $query, $excludeMatches);
        $query = preg_replace($patterns['exclude'], '', $query);

        $orMatches = preg_match($patterns['or'], $query);

        return [
            'terms' => array_filter(explode(' ', trim($query))),
            'exact' => $exactMatches[1] ?? [],
            'exclude' => $excludeMatches[1] ?? [],
            'use_or' => (bool) $orMatches,
        ];
    }

    /**
     * Build search condition for column.
     */
    public static function buildCondition(
        Builder $query,
        string $column,
        string $value,
        string $operator = 'like',
        string $boolean = 'or'
    ): void {
        $method = $boolean === 'or' ? 'orWhere' : 'where';

        if ($operator === 'like') {
            $query->{$method}($column, 'LIKE', "%{$value}%");
        } elseif ($operator === 'exact') {
            $query->{$method}($column, '=', $value);
        } elseif ($operator === 'starts_with') {
            $query->{$method}($column, 'LIKE', "{$value}%");
        } elseif ($operator === 'ends_with') {
            $query->{$method}($column, 'LIKE', "%{$value}");
        }
    }

    /**
     * Apply search with advanced parsing.
     */
    public static function applyAdvancedSearch(
        Builder $query,
        string $search,
        array $columns,
        array $options = []
    ): Builder {
        $parsed = self::parseQuery($search);
        $operator = $parsed['use_or'] ? 'or' : 'and';

        return $query->where(function ($q) use ($parsed, $columns, $operator, $options) {
            // Handle exact matches
            foreach ($parsed['exact'] as $exactTerm) {
                $q->where(function ($exactQuery) use ($exactTerm, $columns) {
                    foreach ($columns as $column) {
                        $exactQuery->orWhere($column, '=', $exactTerm);
                    }
                });
            }

            // Handle regular terms
            foreach ($parsed['terms'] as $term) {
                $q->where(function ($termQuery) use ($term, $columns, $operator) {
                    foreach ($columns as $column) {
                        self::buildCondition(
                            $termQuery,
                            $column,
                            $term,
                            'like',
                            $operator
                        );
                    }
                });
            }

            // Handle excluded terms
            foreach ($parsed['exclude'] as $excludeTerm) {
                $q->where(function ($excludeQuery) use ($excludeTerm, $columns) {
                    foreach ($columns as $column) {
                        $excludeQuery->where($column, 'NOT LIKE', "%{$excludeTerm}%");
                    }
                });
            }
        });
    }

    /**
     * Generate search index for a model.
     */
    public static function generateSearchIndex($model, array $columns): string
    {
        $indexParts = [];

        foreach ($columns as $column) {
            if (isset($model->{$column})) {
                $value = $model->{$column};
                if (is_string($value)) {
                    $indexParts[] = Str::lower(trim($value));
                }
            }
        }

        return implode(' ', array_filter($indexParts));
    }
}
