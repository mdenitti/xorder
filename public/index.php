<?php

declare(strict_types=1);

// Landingspagina. Elke andere pagina is een eigen bestand in deze map (login.php, orders.php, ...).
// De vertaling tussen de paden in de code (bv. /app/orders) en die bestanden staat in src/Core/Routes.php.
use App\Core\Response;

require __DIR__ . '/_bootstrap.php';

xorder_page(
    get: static function () use ($landing, $request): Response {
        // De ingebouwde PHP-server (php -S) toont index.php ook voor onbestaande adressen, bv. een oude /app/orders.
        $uriPath = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
        $base = $request->basePath();
        if (!in_array($uriPath, [$base . '/', $base . '/index.php', $base === '' ? '/' : $base], true)) {
            return Response::html('<h1>404 - Pagina niet gevonden</h1>', 404);
        }

        return $landing->index($request);
    },
);
