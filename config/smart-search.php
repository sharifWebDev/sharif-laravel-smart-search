<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Search Configuration
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'mode' => 'like',
        'deep' => true,
        'max_relation_depth' => 2,
        'search_operator' => 'or',
    ],

    /*
    |--------------------------------------------------------------------------
    | Column Configuration
    |--------------------------------------------------------------------------
    */
    'columns' => [
        // Columns to exclude from automatic search
        'excluded' => [
            'id',
            'created_at',
            'updated_at',
            'deleted_at',
            'password',
            'remember_token',
        ],

        // Columns to prioritize in search order
        'prioritized' => ['name', 'title', 'code', 'email'],

        // Maximum number of columns to search per table
        'max_per_table' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Relation Configuration
    |--------------------------------------------------------------------------
    */
    'relations' => [
        'auto_discover' => true,
        'max_depth' => 2,

        // Relations to exclude from automatic discovery
        'excluded' => [
            'password',
            'secret',
            'tokens',
        ],

        // Custom relation configurations
        'custom' => [
            // 'user' => ['name', 'email'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'max_join_tables' => 5,
        'chunk_search' => false,
        'chunk_size' => 1000,
    ],
];
