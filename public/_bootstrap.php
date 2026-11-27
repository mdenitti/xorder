<?php

declare(strict_types=1);

/**
 * Elke pagina in deze map begint met: require __DIR__ . '/_bootstrap.php';
 * Dit bestand zoekt de map met de app-code en laadt de gedeelde opstart (src/bootstrap.php).
 */

// Rechtstreeks openen heeft geen zin
if (realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    http_response_code(404);
    exit;
}

// Standaard staat de app-code naast public/. Op een server waar enkel public/ in de webroot
// staat (bv. assurio.be/xorder), wijst public/xorder-path.php naar de map met de app-code.
$rootDir = dirname(__DIR__);
if (is_file(__DIR__ . '/xorder-path.php')) {
    $rootDir = (string) require __DIR__ . '/xorder-path.php';
}

// Webroot (public/) voor cache-busting van css/js, ook als de app-code elders staat
define('XORDER_PUBLIC_DIR', __DIR__);

require $rootDir . '/src/bootstrap.php';
