<?php

namespace App\Support;

use Illuminate\Http\Request;
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
        static::forceAppUrl();

        if (! app()->runningInConsole() && app()->bound('request')) {
            static::applyFromRequest(app('request'));
        }
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
        $domain = env('RAILWAY_PUBLIC_DOMAIN');

        if (is_string($domain) && $domain !== '' && ! static::isPlaceholder($domain)) {
            return 'https://'.$domain;
        }

        $url = env('APP_URL');

        if (is_string($url) && $url !== '' && ! static::isPlaceholder($url) && filter_var($url, FILTER_VALIDATE_URL)) {
            $host = parse_url($url, PHP_URL_HOST);

            if (! static::isRailway() || ! static::isLoopbackHost(is_string($host) ? $host : null)) {
                return $url;
            }
        }

        return null;
    }

    public static function applyFromRequest(Request $request): void
    {
        if (! static::isRailway()) {
            return;
        }

        $host = $request->header('X-Forwarded-Host') ?: $request->getHost();

        if (! is_string($host) || $host === '' || static::isLoopbackHost($host)) {
            return;
        }

        $proto = $request->header('X-Forwarded-Proto');
        $scheme = is_string($proto) && $proto !== '' ? $proto : $request->getScheme();
        $root = rtrim($scheme.'://'.$host, '/');

        static::setAppUrl($root, $scheme === 'https' || $request->isSecure());
    }

    /** @param  ?string  $host */
    public static function isLoopbackHost(?string $host): bool
    {
        if ($host === null || $host === '') {
            return false;
        }

        $host = strtolower($host);

        return in_array($host, ['localhost', '127.0.0.1', '0.0.0.0', '[::1]'], true);
    }

    private static function setAppUrl(string $root, bool $https): void
    {
        putenv('APP_URL='.$root);
        $_ENV['APP_URL'] = $root;
        $_SERVER['APP_URL'] = $root;
        config(['app.url' => $root]);
        URL::forceRootUrl($root);

        if ($https) {
            URL::forceScheme('https');
        }
    }

    private static function sanitizePlaceholderEnv(): void
    {
        foreach (['APP_URL', 'DATABASE_URL', 'DATABASE_PRIVATE_URL', 'DB_URL'] as $key) {
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
        $url = env('DATABASE_PRIVATE_URL')
            ?: env('DATABASE_URL')
            ?: env('DB_URL');

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

        $url = env('DATABASE_PRIVATE_URL')
            ?: env('DATABASE_URL')
            ?: env('DB_URL');

        if (is_string($url) && $url !== '' && ! static::isPlaceholder($url)) {
            config(['database.connections.pgsql.url' => $url]);

            return;
        }

        config([
            'database.connections.pgsql.host' => env('PGHOST', env('DB_HOST', '127.0.0.1')),
            'database.connections.pgsql.port' => env('PGPORT', env('DB_PORT', '5432')),
            'database.connections.pgsql.database' => env('PGDATABASE', env('DB_DATABASE', 'railway')),
            'database.connections.pgsql.username' => env('PGUSER', env('DB_USERNAME', 'postgres')),
            'database.connections.pgsql.password' => env('PGPASSWORD', env('DB_PASSWORD', '')),
            'database.connections.pgsql.sslmode' => env('PGSSLMODE', env('DB_SSLMODE', 'require')),
        ]);
    }

    private static function forceAppUrl(): void
    {
        $appUrl = static::resolveAppUrl();

        if ($appUrl === null) {
            return;
        }

        static::setAppUrl($appUrl, true);
    }
}
