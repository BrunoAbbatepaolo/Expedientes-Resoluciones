<?php

use Illuminate\Support\Str;

return [

    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [

        // Conexión principal (ajustá si usás otra como default)
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        // Si usás MariaDB separado del mysql principal (opcional)
        'mariadb' => [
            'driver' => 'mysql',
            'url' => env('MARIADB_URL'),
            'host' => env('MARIADB_HOST', '127.0.0.1'),
            'port' => env('MARIADB_PORT', '3306'),
            'database' => env('MARIADB_DATABASE', 'laravel'),
            'username' => env('MARIADB_USERNAME', 'root'),
            'password' => env('MARIADB_PASSWORD', ''),
            'unix_socket' => env('MARIADB_SOCKET', ''),
            'charset' => env('MARIADB_CHARSET', 'utf8mb4'),
            'collation' => env('MARIADB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ],

        // Postgres genérico
        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('PG_URL'),
            'host' => env('PG_HOST', '127.0.0.1'),
            'port' => env('PG_PORT', '5432'),
            'database' => env('PG_DATABASE', 'laravel'),
            'username' => env('PG_USERNAME', 'postgres'),
            'password' => env('PG_PASSWORD', ''),
            'charset' => env('PG_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => env('PG_SEARCH_PATH', 'public'),
            'sslmode' => env('PG_SSLMODE', 'prefer'),
        ],

        // Postgres mitiv (antes tenía credenciales hardcodeadas)
        'pgsql_mitiv' => [
            'driver' => 'pgsql',
            'url' => env('MITIV_PG_URL'),
            'host' => env('MITIV_PG_HOST', '127.0.0.1'),
            'port' => env('MITIV_PG_PORT', '5432'),
            'database' => env('MITIV_PG_DATABASE', 'mitivdb'),
            'username' => env('MITIV_PG_USERNAME', 'postgres'),
            'password' => env('MITIV_PG_PASSWORD', ''),
            'charset' => env('MITIV_PG_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => env('MITIV_PG_SEARCH_PATH', 'public'),
            'sslmode' => env('MITIV_PG_SSLMODE', 'prefer'),
        ],

        // MySQL legui (antes tenía host/usuario/pass hardcodeados)
        'mysql_legui' => [
            'driver' => 'mysql',
            'url' => env('LEGUI_DB_URL'),
            'host' => env('LEGUI_DB_HOST', '127.0.0.1'),
            'port' => env('LEGUI_DB_PORT', '3306'),
            'database' => env('LEGUI_DB_DATABASE', 'legui'),
            'username' => env('LEGUI_DB_USERNAME', 'root'),
            'password' => env('LEGUI_DB_PASSWORD', ''),
            'unix_socket' => env('LEGUI_DB_SOCKET', ''),
            'charset' => env('LEGUI_DB_CHARSET', 'utf8mb4'),
            'collation' => env('LEGUI_DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ],

        // MySQL admin (antes tenía host/usuario/pass hardcodeados)
        'mysql_admin' => [
            'driver' => 'mysql',
            'url' => env('ADMIN_DB_URL'),
            'host' => env('ADMIN_DB_HOST', '127.0.0.1'),
            'port' => env('ADMIN_DB_PORT', '3306'),
            'database' => env('ADMIN_DB_DATABASE', 'admin'),
            'username' => env('ADMIN_DB_USERNAME', 'root'),
            'password' => env('ADMIN_DB_PASSWORD', ''),
            'unix_socket' => env('ADMIN_DB_SOCKET', ''),
            'charset' => env('ADMIN_DB_CHARSET', 'utf8mb4'),
            'collation' => env('ADMIN_DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('MSSQL_URL'),
            'host' => env('MSSQL_HOST', 'localhost'),
            'port' => env('MSSQL_PORT', '1433'),
            'database' => env('MSSQL_DATABASE', 'laravel'),
            'username' => env('MSSQL_USERNAME', 'sa'),
            'password' => env('MSSQL_PASSWORD', ''),
            'charset' => env('MSSQL_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],
    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],
        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],
    ],
];
