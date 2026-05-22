<?php

namespace App\Support;

class RailwayPostgres
{
    public static function apply(): void
    {
        if (! static::isRailway()) {
            return;
        }

        static::mirrorDatabaseUrl();
        static::configureConnection();
        static::forceHttps();
    }

    public static function isRailway(): bool
    {
        return (bool) (env('RAILWAY_ENVIRONMENT')
            || env('RAILWAY_PUBLIC_DOMAIN')
            || env('RAILWAY_SERVICE_NAME'));
    }

    private static function mirrorDatabaseUrl(): void
    {
        $url = env('DATABASE_URL') ?: env('DB_URL');

        if (! $url) {
            return;
        }

        putenv('DATABASE_URL='.$url);
        putenv('DB_URL='.$url);
        $_ENV['DATABASE_URL'] = $url;
        $_ENV['DB_URL'] = $url;
        $_SERVER['DATABASE_URL'] = $url;
        $_SERVER['DB_URL'] = $url;
    }

    private static function configureConnection(): void
    {
        $default = env('DB_CONNECTION', 'pgsql');
        config(['database.default' => $default]);

        if ($default !== 'pgsql') {
            return;
        }

        $url = env('DATABASE_URL') ?: env('DB_URL');
        $sslmode = env('PGSSLMODE', env('DB_SSLMODE', 'require'));

        config([
            'database.connections.pgsql.url' => $url,
            'database.connections.pgsql.host' => env('PGHOST', env('DB_HOST', '127.0.0.1')),
            'database.connections.pgsql.port' => env('PGPORT', env('DB_PORT', '5432')),
            'database.connections.pgsql.database' => env('PGDATABASE', env('DB_DATABASE', 'railway')),
            'database.connections.pgsql.username' => env('PGUSER', env('DB_USERNAME', 'postgres')),
            'database.connections.pgsql.password' => env('PGPASSWORD', env('DB_PASSWORD', '')),
            'database.connections.pgsql.sslmode' => $sslmode,
        ]);
    }

    private static function forceHttps(): void
    {
        \Illuminate\Support\Facades\URL::forceScheme('https');

        if ($domain = env('RAILWAY_PUBLIC_DOMAIN')) {
            config(['app.url' => 'https://'.$domain]);
        }
    }
}
