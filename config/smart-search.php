<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Package Enabled
    |--------------------------------------------------------------------------
    |
    | This option determines if the smart search functionality is enabled.
    | You can use this to disable search globally in certain environments.
    |
    */
    'enabled' => env('SMART_SEARCH_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Default Search Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can define the default search behavior for your application.
    | These settings can be overridden per model or per search call.
    |
    */
    'defaults' => [
        'mode' => 'like',
        'deep' => true,
        'max_relation_depth' => 2,
        'search_operator' => 'or',
        'case_sensitive' => false,
        'full_text' => false,
        'min_length' => 2,
        'max_length' => 255,
        'cache' => [
            'enabled' => false,
            'ttl' => 3600, // 1 hour
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Column Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which columns should be included or excluded from searches,
    | and define priority for certain columns.
    |
    */
    'columns' => [
        'excluded' => [
            'id',
            'uuid',
            'created_at',
            'updated_at',
            'deleted_at',
            'password',
            'remember_token',
            'email_verified_at',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ],

        'prioritized' => [],

        'max_per_table' => 15,

        'types' => [
            'searchable' => ['string', 'text', 'varchar', 'char'],
            'non_searchable' => ['binary', 'blob'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Relation Configuration
    |--------------------------------------------------------------------------
    |
    | Configure relation search behavior including auto-discovery and
    | relation-specific settings.
    |
    */
    'relations' => [
        'auto_discover' => true,

        'max_depth' => 3,

        'excluded' => [
            'password',
            'secret',
            'tokens',
            'oauth_providers',
        ],

        'nested' => [
            'enabled' => true,
            'max_level' => 2,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Optimize search performance with these settings.
    |
    */
    'performance' => [
        'max_join_tables' => 5,
        'chunk_search' => false,
        'chunk_size' => 1000,
        'query_timeout' => 30, // seconds
        'max_results' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Full-Text Search Configuration
    |--------------------------------------------------------------------------
    |
    | Configure full-text search settings for supported databases.
    |
    */
    'full_text' => [
        'enabled' => false,
        'mode' => 'boolean', // natural, boolean
        'min_word_length' => 4,
        'stopwords' => [
            'the',
            'and',
            'or',
            'in',
            'on',
            'at',
            'to',
            'for',
            'of',
            'a',
            'an'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure search result caching.
    |
    */
    'cache' => [
        'enabled' => env('SMART_SEARCH_CACHE', false),
        'store' => env('SMART_SEARCH_CACHE_STORE', 'file'),
        'prefix' => 'smart_search',
        'ttl' => 3600, // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Configuration
    |--------------------------------------------------------------------------
    |
    | Enable debug mode for development.
    |
    */
    'debug' => [
        'enabled' => env('SMART_SEARCH_DEBUG', false),
        'log_queries' => false,
        'log_performance' => false,
    ],
];
