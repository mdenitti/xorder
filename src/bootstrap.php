<?php

declare(strict_types=1);

/**
 * Gedeelde opstart voor elke pagina in public/ (login.php, orders.php, ...).
 * Wordt geladen via public/_bootstrap.php, dat $rootDir al bepaald heeft.
 *
 * Na het laden heb je in elk paginabestand:
 *   $app      de app (database, sessie, views, ...)
 *   $request  het huidige verzoek
 *   $params   de waarden uit de url, bv. ['id' => '5'] bij order-edit.php?id=5
 *   $landing, $auth, $dashboard, $products, $orders, $kitchen, $export,
 *   $settings, $password, $pages, $tracker, $features   de controllers
 *
 * en de functie xorder_page(get: ..., post: ...) die de juiste controller-methode uitvoert.
 *
 * @var string $rootDir
 */

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ExportController;
use App\Controllers\FeatureController;
use App\Controllers\KitchenController;
use App\Controllers\LandingController;
use App\Controllers\OrderController;
use App\Controllers\PageController;
use App\Controllers\PasswordController;
use App\Controllers\ProductController;
use App\Controllers\SettingsController;
use App\Controllers\TrackController;
use App\Core\App;
use App\Core\Response;
use App\Core\Routes;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\DemoSeederService;
use App\Services\LoginThrottle;
use App\Services\PasswordResetService;
use App\Services\XlsxExporter;

require_once __DIR__ . '/autoload.php';

/**
 * Fouten altijd loggen (buiten de webroot), enkel tonen in debugmodus.
 */
function xorder_fail(Throwable $error): void
{
    global $app, $rootDir;

    http_response_code(500);
    $logDir = $rootDir . '/storage/logs';
    if (is_dir($logDir) || @mkdir($logDir, 0775, true)) {
        @error_log(
            '[' . date('Y-m-d H:i:s') . '] ' . get_class($error) . ': ' . $error->getMessage()
            . ' in ' . $error->getFile() . ':' . $error->getLine() . "\n",
            3,
            $logDir . '/app.log'
        );
    }

    $isLocal = isset($app) && $app instanceof App && $app->config->isDebug();
    if ($isLocal) {
        echo '<pre style="font-family:monospace;padding:24px;">'
            . htmlspecialchars($error->getMessage() . "\n\n" . $error->getTraceAsString(), ENT_QUOTES, 'UTF-8')
            . '</pre>';
    } else {
        echo '<h1>Er ging iets mis</h1><p>Probeer het later opnieuw.</p>';
    }
}

/**
 * Voert de juiste functie uit voor GET of POST en stuurt het antwoord naar de browser.
 * Geef enkel mee wat de pagina ondersteunt; een andere methode geeft een 404, net als vroeger.
 *
 * @param (Closure(): Response)|null $get
 * @param (Closure(): Response)|null $post
 */
function xorder_page(?Closure $get = null, ?Closure $post = null): void
{
    global $app;

    try {
        $method = $app->request->method();
        $handler = match ($method) {
            'GET' => $get,
            'POST' => $post,
            default => null,
        };

        $response = $handler !== null
            ? $handler()
            : Response::html('<h1>404 - Pagina niet gevonden</h1>', 404);
        $response->send();
    } catch (Throwable $error) {
        xorder_fail($error);
    }
}

try {
    $app = new App($rootDir);
    $request = $app->request;
    $params = Routes::params($request->path());

    $userRepo = new UserRepository($app->db);
    $productRepo = new ProductRepository($app->db);
    $orderRepo = new OrderRepository($app->db);

    $seeder = new DemoSeederService($productRepo, $orderRepo, $userRepo, $app->i18n);
    $authService = new AuthService($userRepo, $app->session, $app->i18n, $seeder);
    $xlsxExporter = new XlsxExporter($app->i18n);

    $landing = new LandingController($app, $authService);
    $auth = new AuthController($app, $authService);
    $dashboard = new DashboardController($app, $authService, $orderRepo, $productRepo, $seeder);
    $products = new ProductController($app, $authService, $productRepo);
    $orders = new OrderController($app, $authService, $orderRepo, $productRepo);
    $kitchen = new KitchenController($app, $authService, $orderRepo);
    $export = new ExportController($app, $authService, $orderRepo, $userRepo, $xlsxExporter);
    $settings = new SettingsController($app, $authService, $userRepo);
    $resetService = new PasswordResetService($app->db, $userRepo, $app->i18n, $app->config);
    $throttle = new LoginThrottle($app->db);
    $auth->setThrottle($throttle);
    $password = new PasswordController($app, $authService, $resetService, $throttle);
    $pages = new PageController($app, $authService);
    $tracker = new TrackController($app, $authService);
    $features = new FeatureController($app, $authService);
} catch (Throwable $error) {
    xorder_fail($error);
    exit;
}
