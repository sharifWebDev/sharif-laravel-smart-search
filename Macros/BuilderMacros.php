<?php

namespace Sharifuddin\LaravelSmartSearch\Macros;

use Illuminate\Database\Eloquent\Builder;
use Sharifuddin\LaravelSmartSearch\Facades\SmartSearch;

class BuilderMacros
{

    /**
     * Register query builder macros.
     */
    public static function register(): void
    {
        Builder::macro('smartSearch', function (
            ?string $search = null,
            array $columns = [],
            array $options = []
        ) {
            return SmartSearch::apply(static::class, $search, $columns, $options);
        });

        Builder::macro('search', function (
            ?string $search = null,
            array $columns = [],
            array $options = []
        ) {
            return SmartSearch::apply(static::class, $search, $columns, $options);
        });

        Builder::macro('searchOr', function (
            ?string $search = null,
            array $columns = [],
            array $options = []
        ) {
            return SmartSearch::apply(static::class, $search, $columns, array_merge($options, [
                'search_operator' => 'or',
            ]));
        });

        Builder::macro('searchAnd', function (
            ?string $search = null,
            array $columns = [],
            array $options = []
        ) {
            return SmartSearch::apply(static::class, $search, $columns, array_merge($options, [
                'search_operator' => 'and',
            ]));
        });
    }
}
