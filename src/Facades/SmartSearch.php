<?php

namespace Sharifuddin\LaravelSmartSearch\Facades;

use Illuminate\Support\Facades\Facade;
use Sharifuddin\LaravelSmartSearch\SmartSearchManager;

/**
 * @method static \Illuminate\Database\Eloquent\Builder apply(\Illuminate\Database\Eloquent\Builder $query, string $search, array $options = [])
 * @method static mixed config(string $key = null, mixed $default = null)
 * @method static bool isEnabled()
 * @method static void macro(string $name, object|callable $macro)
 * @method static void mixin(object $mixin, bool $replace = true)
 *
 * @see \Sharifuddin\LaravelSmartSearch\SmartSearchManager
 */
class SmartSearch extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'smart-search';
    }
}
