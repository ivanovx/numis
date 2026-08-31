<?php

declare(strict_types=1);

return [
    'route' => [
        'uri' => '/graphql',
        'name' => 'graphql',
        'middleware' => [
            Nuwave\Lighthouse\Http\Middleware\AcceptJson::class,
            Nuwave\Lighthouse\Http\Middleware\AttemptAuthentication::class,
        ],
    ],

    'guards' => null,
    'schema_path' => base_path('graphql/schema.graphql'),

    'schema_cache' => [
        'enable' => env('LIGHTHOUSE_SCHEMA_CACHE_ENABLE', env('APP_ENV') !== 'local'),
        'path' => env('LIGHTHOUSE_SCHEMA_CACHE_PATH', base_path('bootstrap/cache/lighthouse-schema.php')),
    ],

    'cache_directive_tags' => false,

    'query_cache' => [
        'enable' => env('LIGHTHOUSE_QUERY_CACHE_ENABLE', true),
        'mode' => env('LIGHTHOUSE_QUERY_CACHE_MODE', 'store'),
        'opcache_path' => env('LIGHTHOUSE_QUERY_CACHE_OPCACHE_PATH', base_path('bootstrap/cache')),
        'store' => env('LIGHTHOUSE_QUERY_CACHE_STORE', null),
        'ttl' => env('LIGHTHOUSE_QUERY_CACHE_TTL', 24 * 60 * 60),
    ],

    'validation_cache' => [
        'enable' => env('LIGHTHOUSE_VALIDATION_CACHE_ENABLE', false),
        'store' => env('LIGHTHOUSE_VALIDATION_CACHE_STORE', null),
        'ttl' => env('LIGHTHOUSE_VALIDATION_CACHE_TTL', 24 * 60 * 60),
    ],

    'parse_source_location' => true,

    'namespaces' => [
        'models' => ['App', 'App\\Models'],
        'queries' => 'App\\GraphQL\\Queries',
        'mutations' => 'App\\GraphQL\\Mutations',
        'subscriptions' => 'App\\GraphQL\\Subscriptions',
        'types' => 'App\\GraphQL\\Types',
        'interfaces' => 'App\\GraphQL\\Interfaces',
        'unions' => 'App\\GraphQL\\Unions',
        'scalars' => 'App\\GraphQL\\Scalars',
        'directives' => 'App\\GraphQL\\Directives',
        'validators' => 'App\\GraphQL\\Validators',
    ],
];
