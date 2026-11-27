<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Config;
use App\Repositories\UserRepository;
use App\Repositories\OrderRepository;

// ==========================================
// 1. APPLICATIE EN BEVEILIGING
// ==========================================

$rootDir = dirname(__DIR__);

require_once $rootDir . '/src/autoload.php';

$config = Config::load($rootDir);

// Klasfeature uitsluitend lokaal beschikbaar.
if (
    $config->get('app.env') !== 'local' ||
    !$config->get('features.enabled', false)
) {
    http_response_code(404);
    exit('Feature niet beschikbaar.');
}

define('XORDER_PUBLIC_DIR', __DIR__);

$app = new App($rootDir);

header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

$nonce = base64_encode(random_bytes(18));

header("Content-Security-Policy: default-src 'self'; "
    . "style-src 'self' 'nonce-$nonce'; "
    . "script-src 'none'; object-src 'none'; "
    . "base-uri 'none'; frame-ancestors 'none'");

// Veilige weergave van tekst.
$e = static fn($value): string =>
    htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );

// ==========================================
// 2. AUTHENTICATIE
// ==========================================

$userRepo = new UserRepository($app->db);

$userId = $app->session->userId();

$user = $userId !== null
    ? $userRepo->findById($userId)
    : null;

if ($user === null) {
    header('Location: ' . $app->request->url('/login'));
    exit;
}

// ==========================================
// 3. BESTELLINGEN OPHALEN
// ==========================================

$orderRepo = new OrderRepository($app->db);

// Alleen bestellingen van de ingelogde handelaar.
$allOrders = $orderRepo->listForUser($user->id);

// Afgehaalde en geannuleerde orders overslaan.
$orders = array_values(array_filter(
    $allOrders,
    static fn($order) => !in_array(
        $order->status,
        ['cancelled', 'picked_up'],
        true
    )
));

$now = new DateTimeImmutable('now');
$today = $now->format('Y-m-d');

$results = [];

$counts = [
    'healthy' => 0,
    'warning' => 0,
    'problem' => 0
];

$labels = [
    'healthy' => 'In orde',
    'warning' => 'Waarschuwing',
    'problem' => 'Probleem'
];

// ==========================================
// 4. AUTOMATISCHE BESTELCONTROLE
// ==========================================

foreach ($orders as $order) {

    $errors = [];
    $warnings = [];

    // Klantnaam controleren.
    if (trim($order->customerName) === '') {
        $errors[] = 'Klantnaam ontbreekt';
    }

    // Contactgegevens controleren.
    if (
        trim($order->customerPhone) === '' &&
        trim($order->customerEmail) === ''
    ) {
        $errors[] = 'Geen contactgegevens';
    }

    if (
        trim($order->customerEmail) !== '' &&
        !filter_var(
            $order->customerEmail,
            FILTER_VALIDATE_EMAIL
        )
    ) {
        $warnings[] = 'E-mailadres lijkt ongeldig';
    }

    // Afhaalinformatie controleren.
    $date = DateTimeImmutable::createFromFormat(
        '!Y-m-d',
        $order->pickupDate
    );

    $validDate = $date !== false &&
        $date->format('Y-m-d') === $order->pickupDate;

    if (!$validDate) {
        $errors[] = 'Afhaaldatum ontbreekt of is ongeldig';
    }

    if (trim($order->pickupSlot) === '') {
        $errors[] = 'Afhaaltijdslot ontbreekt';
    }

    // Producten controleren.
    if (count($order->items) === 0) {
        $errors[] = 'Bestelling zonder producten';
    }

    foreach ($order->items as $item) {
        if ($item->quantity <= 0) {
            $errors[] = 'Ongeldige producthoeveelheid';
            break;
        }
    }

    // Verstreken afhaaldag.
    if ($validDate && $order->pickupDate < $today) {
        $errors[] = 'Afhaaldag verstreken';
    }

    // Verstreken tijdslot op de huidige dag.
    if (
        $validDate &&
        $order->pickupDate === $today &&
        preg_match(
            '/(\d{2}:\d{2})\s*[-–]\s*(\d{2}:\d{2})/u',
            $order->pickupSlot,
            $matches
        )
    ) {
        $end = DateTimeImmutable::createFromFormat(
            '!Y-m-d H:i',
            $today . ' ' . $matches[2]
        );

        if (
            $end !== false &&
            $end->format('H:i') === $matches[2] &&
            $end < $now
        ) {
            $errors[] = 'Afhaaltijdslot verstreken';
        }
    }

    // Mogelijke dubbele bestellingen.
    foreach ($orders as $other) {

        if ($order->id === $other->id) {
            continue;
        }

        $phone = preg_replace(
            '/\D+/',
            '',
            $order->customerPhone
        );

        $otherPhone = preg_replace(
            '/\D+/',
            '',
            $other->customerPhone
        );

        $email = strtolower(trim($order->customerEmail));
        $otherEmail = strtolower(trim($other->customerEmail));

        $samePhone = $phone !== '' &&
            $phone === $otherPhone;

        $sameEmail = $email !== '' &&
            $email === $otherEmail;

        if (
            ($samePhone || $sameEmail) &&
            $validDate &&
            trim($order->pickupSlot) !== '' &&
            $order->pickupDate === $other->pickupDate &&
            $order->pickupSlot === $other->pickupSlot
        ) {
            $warnings[] = 'Mogelijke dubbele bestelling';
            break;
        }
    }

    // Eindstatus bepalen.
    if ($errors !== []) {
        $status = 'problem';
    } elseif ($warnings !== []) {
        $status = 'warning';
    } else {
        $status = 'healthy';
    }

    $counts[$status]++;

    $results[] = [
        'order' => $order,
        'status' => $status,
        'messages' => array_merge($errors, $warnings)
    ];
}

// ==========================================
// 5. DASHBOARDGEGEVENS
// ==========================================

$total = count($orders);

$percentage = $total > 0
    ? round(($counts['healthy'] / $total) * 100)
    : 0;

// Problemen altijd bovenaan.
$priority = [
    'problem' => 0,
    'warning' => 1,
    'healthy' => 2
];

usort(
    $results,
    static fn($a, $b) =>
        $priority[$a['status']] <=>
        $priority[$b['status']]
);

// Zoekfunctie en filters.
$filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

if (!is_string($filter) || !in_array(
    $filter,
    ['all', 'healthy', 'warning', 'problem'],
    true
)) {
    $filter = 'all';
}

$search = is_string($search)
    ? trim($search)
    : '';

$visible = array_filter(
    $results,
    static function ($result) use ($filter, $search) {

        if (
            $filter !== 'all' &&
            $result['status'] !== $filter
        ) {
            return false;
        }

        $order = $result['order'];

        return $search === '' ||
            stripos($order->customerName, $search) !== false ||
            stripos($order->orderNumber, $search) !== false;
    }
);

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Order Health Check | XOrder</title>

    <style nonce="<?= $e($nonce) ?>">

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f7f6f2;
            color: #17372f;
        }

        .jeroen-header {
            background: #12372e;
            color: white;
            padding: 20px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .jeroen-header strong {
            font-size: 23px;
        }

        .jeroen-header a {
            color: white;
            text-decoration: none;
        }

        .jeroen-container {
            max-width: 1250px;
            margin: auto;
            padding: 35px 24px;
        }

        .jeroen-intro {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        h1 {
            font-size: 32px;
            margin: 0 0 10px;
        }

        h2 {
            font-size: 20px;
        }

        .jeroen-muted {
            color: #64756d;
            line-height: 1.6;
        }

        .jeroen-button {
            display: inline-block;
            padding: 12px 18px;
            border-radius: 8px;
            background: #12372e;
            color: white;
            text-decoration: none;
            border: 0;
            cursor: pointer;
            font-size: 14px;
        }

        .jeroen-grid {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(190px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .jeroen-card,
        .jeroen-panel {
            padding: 22px;
            border: 1px solid #e0e5df;
            border-radius: 12px;
            background: white;
        }

        .jeroen-card strong {
            display: block;
            font-size: 38px;
            margin-bottom: 8px;
        }

        .jeroen-card span {
            font-size: 14px;
        }

        .jeroen-healthy {
            color: #17643b;
            background: #e6f4eb;
        }

        .jeroen-warning {
            color: #946000;
            background: #fff1d5;
        }

        .jeroen-problem {
            color: #b52b2b;
            background: #fce8e8;
        }

        .jeroen-panel {
            margin-bottom: 24px;
        }

        .jeroen-progress {
            display: block;
            width: 100%;
            height: 14px;
            accent-color: #23794b;
            margin: 15px 0;
        }

        .jeroen-filters {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin: 20px 0;
        }

        .jeroen-filter {
            border: 1px solid #dce3df;
            padding: 10px 14px;
            text-decoration: none;
            color: #12372e;
            border-radius: 7px;
            background: white;
            font-size: 14px;
        }

        .jeroen-active {
            background: #12372e;
            color: white;
        }

        .jeroen-search {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .jeroen-search input {
            border: 1px solid #dce3df;
            border-radius: 7px;
            padding: 12px;
            font-size: 14px;
            max-width: 100%;
        }

        .jeroen-table-wrap {
            overflow-x: auto;
        }

        .jeroen-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        .jeroen-table th {
            padding: 14px;
            background: #12372e;
            color: white;
        }

        .jeroen-table td {
            padding: 15px 14px;
            border-bottom: 1px solid #e8ebe7;
            vertical-align: top;
        }

        .jeroen-table tr:hover {
            background: #f7f9f7;
        }

        .jeroen-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
        }

        .jeroen-messages {
            padding-left: 17px;
            margin: 8px 0 0;
            line-height: 1.6;
        }

        .jeroen-link {
            color: #17643b;
            font-weight: bold;
        }

        .jeroen-empty {
            text-align: center;
            padding: 40px;
            color: #64756d;
        }

        .jeroen-footer {
            font-size: 13px;
            color: #64756d;
            text-align: center;
            padding: 15px;
        }

        @media (max-width: 600px) {
            h1 {
                font-size: 26px;
            }

            .jeroen-container {
                padding: 20px 12px;
            }
        }

    </style>
</head>

<body>

<header class="jeroen-header">

    <strong>XOrder | Health Check</strong>

    <a href="<?= $e($app->request->url('/app')) ?>">
        Terug naar dashboard
    </a>

</header>

<main class="jeroen-container">

    <div class="jeroen-intro">

        <div>
            <h1>Order Health Check</h1>

            <div class="jeroen-muted">
                Detecteer mogelijke bestelproblemen
                voordat ze voor verrassingen zorgen.
            </div>
        </div>

        <a class="jeroen-button" href="jeroen.php">
            Opnieuw controleren
        </a>

    </div>

    <!-- Statistieken -->

    <div class="jeroen-grid">

        <div class="jeroen-card">
            <strong><?= $e($total) ?></strong>
            <span>Bestellingen gecontroleerd</span>
        </div>

        <div class="jeroen-card jeroen-healthy">
            <strong><?= $e($counts['healthy']) ?></strong>
            <span>Bestellingen in orde</span>
        </div>

        <div class="jeroen-card jeroen-problem">
            <strong><?= $e($counts['problem']) ?></strong>
            <span>Bestellingen met problemen</span>
        </div>

        <div class="jeroen-card jeroen-warning">
            <strong><?= $e($counts['warning']) ?></strong>
            <span>Bestellingen met waarschuwingen</span>
        </div>

    </div>

    <!-- Algemene gezondheidsscore -->

    <section class="jeroen-panel">

        <h2>Gezondheid van je bestellingen</h2>

        <?php if ($total > 0): ?>

            <strong>
                <?= $e($percentage) ?>% zonder aandachtspunten
            </strong>

            <progress class="jeroen-progress"
                      value="<?= $e($percentage) ?>"
                      max="100">
            </progress>

            <p class="jeroen-muted">
                <?= $e($counts['healthy']) ?> van
                <?= $e($total) ?> actieve bestellingen
                hebben geen vastgestelde aandachtspunten.
            </p>

        <?php else: ?>

            <p>Geen actieve bestellingen beschikbaar.</p>

        <?php endif; ?>

        <small class="jeroen-muted">
            Laatste controle:
            <?= $e($now->format('d/m/Y H:i:s')) ?>
        </small>

    </section>

    <!-- Bestellingenoverzicht -->

    <section class="jeroen-panel">

        <h2>Bestellingenoverzicht</h2>

        <form method="get"
              action="jeroen.php"
              class="jeroen-search">

            <input type="hidden"
                   name="status"
                   value="<?= $e($filter) ?>">

            <input type="search"
                   name="search"
                   aria-label="Zoek bestelling"
                   placeholder="Zoek klant of bestelnummer..."
                   value="<?= $e($search) ?>">

            <button class="jeroen-button" type="submit">
                Zoeken
            </button>

        </form>

        <nav class="jeroen-filters">

            <?php

            $filters = [
                'all' => 'Alles (' . $total . ')',
                'problem' => 'Problemen (' .
                    $counts['problem'] . ')',
                'warning' => 'Waarschuwingen (' .
                    $counts['warning'] . ')',
                'healthy' => 'In orde (' .
                    $counts['healthy'] . ')'
            ];

            ?>

            <?php foreach ($filters as $key => $label): ?>

                <a class="jeroen-filter
                    <?= $filter === $key
                        ? 'jeroen-active' : '' ?>"
                   href="?<?= $e(http_build_query([
                       'status' => $key,
                       'search' => $search
                   ])) ?>">

                    <?= $e($label) ?>

                </a>

            <?php endforeach; ?>

        </nav>

        <?php if ($visible === []): ?>

            <div class="jeroen-empty">
                Geen bestellingen gevonden voor deze selectie.
            </div>

        <?php else: ?>

            <div class="jeroen-table-wrap">

                <table class="jeroen-table">

                    <thead>
                        <tr>
                            <th>Bestelling</th>
                            <th>Klant</th>
                            <th>Afhaling</th>
                            <th>Controle</th>
                            <th>Actie</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach ($visible as $result): ?>

                        <?php $order = $result['order']; ?>

                        <tr>

                            <td>
                                <strong>
                                    <?= $e($order->orderNumber) ?>
                                </strong>
                            </td>

                            <td>
                                <?= $e($order->customerName) ?>
                            </td>

                            <td>
                                <?= $e($order->pickupDate) ?>
                                <br>
                                <small>
                                    <?= $e($order->pickupSlot) ?>
                                </small>
                            </td>

                            <td>

                                <span class="jeroen-badge
                                    jeroen-<?= $e($result['status']) ?>">

                                    <?= $e($labels[
                                        $result['status']
                                    ]) ?>

                                </span>

                                <?php if ($result['messages'] !== []): ?>

                                    <ul class="jeroen-messages">

                                        <?php foreach (
                                            $result['messages'] as $message
                                        ): ?>

                                            <li><?= $e($message) ?></li>

                                        <?php endforeach; ?>

                                    </ul>

                                <?php endif; ?>

                            </td>

                            <td>

                                <a class="jeroen-link"
                                   href="<?= $e(
                                       $app->request->url(
                                           '/app/order-edit',
                                           ['id' => $order->id]
                                       )
                                   ) ?>">

                                    Bekijken

                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </section>

    <!-- Uitleg over de controleregels -->

    <section class="jeroen-panel">

        <h2>Wat wordt gecontroleerd?</h2>

        <ul class="jeroen-muted">
            <li>Ontbrekende klantnaam of contactgegevens.</li>
            <li>Ongeldig e-mailadres.</li>
            <li>Ontbrekende of ongeldige afhaaldatum.</li>
            <li>Ontbrekend afhaaltijdslot.</li>
            <li>Bestellingen zonder producten.</li>
            <li>Ongeldige producthoeveelheden.</li>
            <li>Verstreken afhaalmomenten.</li>
            <li>Mogelijke dubbele bestellingen.</li>
        </ul>

        <p class="jeroen-muted">
            Een waarschuwing is een aandachtspunt,
            niet noodzakelijk een fout.
            Afgehaalde en geannuleerde bestellingen
            worden niet gecontroleerd.
        </p>

    </section>

    <footer class="jeroen-footer">
        Order Health Check · Klasproject<br>
        Deze feature wijzigt zelf geen bestellingen.
    </footer>

</main>

</body>
</html>