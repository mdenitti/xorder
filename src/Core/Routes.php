<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Vertaaltabel tussen de "logische" paden die de code gebruikt (bv. /app/orders/5/edit)
 * en de gewone .php-bestanden in public/ (bv. order-edit.php?id=5).
 *
 * Zo werkt de app zonder .htaccess-rewrites: elke pagina is een echt bestand,
 * en views/controllers blijven gewoon $url('/app/orders') schrijven.
 *
 * Een :param in het pad wordt een ?param= in de query string.
 * Volgorde telt: vaste paden (bv. /app/orders/new) staan voor paden met een :param.
 */
final class Routes
{
    /** @var array<string, string> logisch pad => bestand in public/ */
    private const MAP = [
        '/' => 'index.php',
        '/privacy' => 'privacy.php',
        '/cookies' => 'cookies.php',
        '/forgot' => 'forgot.php',
        '/reset/:token' => 'reset.php',
        '/track' => 'track.php',
        '/login' => 'login.php',
        '/register' => 'register.php',
        '/logout' => 'logout.php',
        '/app' => 'dashboard.php',
        '/app/demo/clear' => 'demo-clear.php',
        '/app/products' => 'products.php',
        '/app/products/:id/update' => 'product-update.php',
        '/app/products/:id/delete' => 'product-delete.php',
        '/app/orders' => 'orders.php',
        '/app/orders/new' => 'order-new.php',
        '/app/orders/:id/edit' => 'order-edit.php',
        '/app/orders/:id' => 'order-edit.php', // POST = bestelling opslaan, zelfde bestand
        '/app/orders/:id/status' => 'order-status.php',
        '/app/orders/:id/delete' => 'order-delete.php',
        '/app/orders/:id/print' => 'order-print.php',
        '/app/kitchen' => 'kitchen.php',
        '/app/export/xlsx' => 'export.php',
        '/app/settings' => 'settings.php',
        '/features' => 'features.php',
        '/features/:naam' => 'feature.php',
    ];

    /**
     * Logisch pad => bestand met query string, bv. "/app/orders/5/edit" => "/order-edit.php?id=5".
     * Paden die geen pagina zijn (css, js, afbeeldingen) komen ongewijzigd terug.
     */
    public static function toFile(string $path): string
    {
        foreach (self::MAP as $pattern => $file) {
            $params = self::match($pattern, $path);
            if ($params === null) {
                continue;
            }
            if ($file === 'index.php') {
                return '/';
            }

            return '/' . $file . ($params === [] ? '' : '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986));
        }

        return $path;
    }

    /**
     * Omgekeerd: welk logisch pad hoort bij dit bestand + deze query? Bv. ("order-edit.php", ["id" => "5"]) => "/app/orders/5/edit".
     *
     * @param array<string, mixed> $query
     */
    public static function fromFile(string $file, array $query): string
    {
        foreach (self::MAP as $pattern => $mapped) {
            if ($mapped !== $file) {
                continue;
            }

            $segments = [];
            foreach (explode('/', trim($pattern, '/')) as $segment) {
                if (str_starts_with($segment, ':')) {
                    $value = $query[substr($segment, 1)] ?? null;
                    if (!is_string($value) || $value === '' || str_contains($value, '/')) {
                        continue 2;
                    }
                    $segment = $value;
                }
                $segments[] = $segment;
            }

            return '/' . implode('/', array_filter($segments, static fn (string $s): bool => $s !== ''));
        }

        return '/' . $file;
    }

    /**
     * De :params uit een logisch pad, bv. "/app/orders/5/edit" => ["id" => "5"].
     *
     * @return array<string, string>
     */
    public static function params(string $path): array
    {
        foreach (array_keys(self::MAP) as $pattern) {
            $params = self::match($pattern, $path);
            if ($params !== null) {
                return $params;
            }
        }

        return [];
    }

    /**
     * @return array<string, string>|null
     */
    private static function match(string $pattern, string $path): ?array
    {
        $path = '/' . trim($path, '/');
        if ($pattern === $path) {
            return [];
        }

        $patternParts = explode('/', trim($pattern, '/'));
        $pathParts = explode('/', trim($path, '/'));
        if (count($patternParts) !== count($pathParts)) {
            return null;
        }

        $params = [];
        foreach ($patternParts as $index => $segment) {
            $value = $pathParts[$index];
            if (str_starts_with($segment, ':')) {
                if ($value === '') {
                    return null;
                }
                $params[substr($segment, 1)] = rawurldecode($value);
                continue;
            }
            if ($segment !== $value) {
                return null;
            }
        }

        return $params;
    }
}
