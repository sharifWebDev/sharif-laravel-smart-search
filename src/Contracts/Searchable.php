<?php

namespace Sharif\LaravelSmartSearch\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Searchable
{
    /**
     * Apply smart search to the query.
     */
    public function scopeApplySmartSearch(
        Builder $query,
        ?string $search = null,
        array $columns = [],
        array $options = []
    ): Builder;

    /**
     * Get searchable columns for the model.
     */
    public function getSearchableColumns(): array;

    /**
     * Get searchable relations for the model.
     */
    public function getSearchableRelations(): array;

    /**
     * Get search configuration for the model.
     */
    public function getSearchConfig(): array;
}
