<?php

namespace Sharifuddin\LaravelSmartSearch\Exceptions;

use Exception;

class SmartSearchException extends Exception
{
    /**
     * Create a new exception for invalid model.
     */
    public static function invalidModel(string $modelClass): self
    {
        return new self(
            "Model [{$modelClass}] must implement Searchable contract to use smart search."
        );
    }

    /**
     * Create a new exception for invalid configuration.
     */
    public static function invalidConfiguration(string $key): self
    {
        return new self(
            "Invalid configuration key [{$key}] in smart-search config."
        );
    }

    /**
     * Create a new exception for relation error.
     */
    public static function relationError(string $relation, string $message): self
    {
        return new self(
            "Error searching relation [{$relation}]: {$message}"
        );
    }
}
