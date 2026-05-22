<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;

class RailwayPostgres
{
    public static function apply(): void
    {
        static::sanitizePlaceholderEnv();

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

    public static function isPlaceholder(string $value): bool
    {
        return str_contains($value, '${{') || str_contains($value, '${');
    }

    public static function resolveAppUrl(): ?string
    {
        $url = env('APP_URL');

        if (is_string($url) && $url !== '' && ! static::isPlaceholder($url) && filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        $domain = env('RAILWAY_PUBLIC_DOMAIN');

        if (is_string($domain) && $domain !== '' && ! static::isPlaceholder($domain)) {
            return 'https://'.$domain;
        }

        return null;
    }

    private static function sanitizePlaceholderEnv(): void
    {
        foreach (['APP_URL', 'DATABASE_URL', 'DB_URL'] as $key) {
            $value = env($key);

            if (! is_string($value) || $value === '' || ! static::isPlaceholder($value)) {
                continue;
            }

            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);
        }

        $appUrl = static::resolveAppUrl();

        if ($appUrl !== null) {
            putenv('APP_URL='.$appUrl);
            $_ENV['APP_URL'] = $appUrl;
            $_SERVER['APP_URL'] = $appUrl;
        }
    }

    private static function mirrorDatabaseUrl(): void
    {
        $url = env('DATABASE_URL') ?: env('DB_URL');

        if (! is_string($url) || $url === '' || static::isPlaceholder($url)) {
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

        $pgsql = [
            'database.connections.pgsql.host' => env('PGHOST', env('DB_HOST', '127.0.0.1')),
            'database.connections.pgsql.port' => env('PGPORT', env('DB_PORT', '5432')),
            'database.connections.pgsql.database' => env('PGDATABASE', env('DB_DATABASE', 'railway')),
            'database.connections.pgsql.username' => env('PGUSER', env('DB_USERNAME', 'postgres')),
            'database.connections.pgsql.password' => env('PGPASSWORD', env('DB_PASSWORD', '')),
            'database.connections.pgsql.sslmode' => $sslmode,
        ];

        if (is_string($url) && $url !== '' && ! static::isPlaceholder($url)) {
            $pgsql['database.connections.pgsql.url'] = $url;
        }

        config($pgsql);
    }

    private static function forceHttps(): void
    {
        $appUrl = static::resolveAppUrl();

        if ($appUrl === null) {
            return;
        }

        config(['app.url' => $appUrl]);
        URL::forceRootUrl($appUrl);
        URL::forceScheme('https');
    }
}
