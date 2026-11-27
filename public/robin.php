<?php

declare(strict_types=1);

/**
 * Bestellingen per tijdslot (Robin)
 *
 * Overzicht per tijdslot van alle bestellingen voor een gekozen dag.
 * Inclusief een interactief selectievak om bestellingen van andere afhaaldagen op te halen.
 */

define('XORDER_PUBLIC_DIR', __DIR__);
$rootDir = dirname(__DIR__);

require_once $rootDir . '/src/autoload.php';

use App\Core\App;
use App\Core\Response;
use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\DemoSeederService;

// 1. Initialiseer applicatie en services
$app = new App($rootDir);
$userRepo = new UserRepository($app->db);
$productRepo = new ProductRepository($app->db);
$orderRepo = new OrderRepository($app->db);
$seeder = new DemoSeederService($productRepo, $orderRepo, $userRepo, $app->i18n);
$authService = new AuthService($userRepo, $app->session, $app->i18n, $seeder);

$user = $authService->currentUser();

// Helpers
$e = static fn (mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$url = static fn (string $path = '/', array $query = []): string => $app->request->url($path, $query);

// Helper voor Nederlandse datumweergave (bv. "Donderdag 24 december 2026")
$formatDutchDate = static function (string $dateStr): string {
    $ts = strtotime($dateStr);
    if ($ts === false) {
        return $dateStr;
    }
    $days = ['Zondag', 'Maandag', 'Dinsdag', 'Woensdag', 'Donderdag', 'Vrijdag', 'Zaterdag'];
    $months = [
        1 => 'januari', 2 => 'februari', 3 => 'maart', 4 => 'april',
        5 => 'mei', 6 => 'juni', 7 => 'juli', 8 => 'augustus',
        9 => 'september', 10 => 'oktober', 11 => 'november', 12 => 'december'
    ];
    $dayName = $days[(int) date('w', $ts)];
    $dayNum = date('j', $ts);
    $monthName = $months[(int) date('n', $ts)];
    $year = date('Y', $ts);

    return "{$dayName} {$dayNum} {$monthName} {$year}";
};

ob_start();

// 2. Toegangscontrole: handelaar moet ingelogd zijn
if ($user === null) {
    ?>
    <div class="card" style="padding: 40px; text-align: center; max-width: 580px; margin: 40px auto;">
        <div style="font-size: 2.5rem; margin-bottom: 12px;">⏰</div>
        <h1 style="margin: 0 0 12px; font-size: 1.5rem; color: var(--pine-900);">Bestellingen per tijdslot</h1>
        <p style="color: var(--ink-600); margin: 0 0 24px;">
            Meld je eerst aan met je handelaarsaccount om het overzicht per tijdslot te bekijken.
        </p>
        <a href="<?= $e($url('/login')) ?>" class="btn btn-primary">Aanmelden bij XOrder</a>
        <p style="font-size: 0.85rem; color: var(--ink-500); margin-top: 18px; margin-bottom: 0;">
            Tip voor lokale test: account <code>traiteur@voorbeeld.test</code> &bull; wachtwoord <code>xorder123</code>
        </p>
    </div>
    <?php
    $contentHtml = ob_get_clean();

    $html = $app->view->renderPartial('layouts/base', [
        'pageTitle' => 'Bestellingen per tijdslot - Robin',
        'user' => null,
        'content' => '<section class="section"><div class="container">' . $contentHtml . '</div></section>',
    ]);

    Response::html($html)->send();
    exit;
}

// 3. Beschikbare datums verzamelen
$datesFromOrders = $orderRepo->distinctPickupDates($user->id);
$availableDates = array_values(array_unique(array_merge($datesFromOrders, $user->pickupDates)));
sort($availableDates);

// Aantal bestellingen per datum tellen voor de dropdown
$dateCounts = [];
$countsRaw = $app->db->fetchAll(
    'SELECT pickup_date, COUNT(*) as cnt FROM orders WHERE user_id = ? GROUP BY pickup_date',
    [$user->id]
);
foreach ($countsRaw as $cr) {
    $dateCounts[(string) $cr['pickup_date']] = (int) $cr['cnt'];
}

// 4. Geselecteerde datum bepalen uit query parameter 'date'
$selectedDate = trim((string) ($app->request->query('date') ?? ''));

if ($selectedDate === '' || !in_array($selectedDate, $availableDates, true)) {
    $today = date('Y-m-d');
    if (in_array($today, $availableDates, true)) {
        $selectedDate = $today;
    } elseif ($availableDates !== []) {
        $selectedDate = $availableDates[0];
    } else {
        $selectedDate = $today;
    }
}

// 5. Bestellingen ophalen voor de gekozen datum
$orders = $orderRepo->listForUser($user->id, ['pickup_date' => $selectedDate]);

// 6. Groeperen per tijdslot
$slots = [];

// Eerst de door de handelaar geconfigureerde tijdsloten toevoegen
foreach ($user->timeSlots as $slotName) {
    $cleanSlot = trim((string) $slotName);
    if ($cleanSlot !== '') {
        $slots[$cleanSlot] = [];
    }
}

// Bestellingen verdelen over de tijdsloten
foreach ($orders as $order) {
    $slot = trim($order->pickupSlot);
    if ($slot === '') {
        $slot = 'Zonder specifiek tijdslot';
    }
    if (!isset($slots[$slot])) {
        $slots[$slot] = [];
    }
    $slots[$slot][] = $order;
}

// Dagstatistieken berekenen
$totalOrders = count($orders);
$totalRevenue = array_sum(array_map(static fn (Order $o): float => $o->totalAmount, $orders));
$totalBalance = array_sum(array_map(static fn (Order $o): float => $o->remainingBalance(), $orders));
$activeSlotsCount = count(array_filter($slots, static fn (array $ordersInSlot): bool => $ordersInSlot !== []));

// Status & Betaalstatus badge definities
$statusBadges = [
    'new' => ['label' => 'Nieuw', 'class' => 'badge badge-info'],
    'confirmed' => ['label' => 'Bevestigd', 'class' => 'badge badge-gold'],
    'ready' => ['label' => 'Klaar voor afhaal', 'class' => 'badge badge-success'],
    'picked_up' => ['label' => 'Afgehaald', 'class' => 'badge badge-pine'],
    'cancelled' => ['label' => 'Geannuleerd', 'class' => 'badge badge-danger'],
];

$paymentBadges = [
    'paid' => ['label' => 'Betaald', 'class' => 'badge badge-success'],
    'deposit' => ['label' => 'Voorschot voldaan', 'class' => 'badge badge-warn'],
    'unpaid' => ['label' => 'Onbetaald', 'class' => 'badge badge-danger'],
];

// Vorige & volgende dag bepalen
$currentIndex = array_search($selectedDate, $availableDates, true);
$prevDate = ($currentIndex !== false && $currentIndex > 0) ? $availableDates[$currentIndex - 1] : null;
$nextDate = ($currentIndex !== false && $currentIndex < count($availableDates) - 1) ? $availableDates[$currentIndex + 1] : null;
?>

<!-- Kopgedeelte met titel en knoppen -->
<div class="page-top" style="margin-bottom: 20px;">
    <div>
        <h1 style="margin: 0; color: var(--pine-900);">⏰ Bestellingen per tijdslot</h1>
        <p style="margin: 4px 0 0; color: var(--ink-600); font-size: 1.05rem;">
            <?= $e($formatDutchDate($selectedDate)) ?> &mdash; <strong><?= $totalOrders ?></strong> <?= $totalOrders === 1 ? 'bestelling' : 'bestellingen' ?>
        </p>
    </div>
    <div class="no-print" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
        <button type="button" data-print class="btn btn-outline">
            🖨️ Druk overzicht af
        </button>
        <a href="<?= $e($url('/app/order-new')) ?>" class="btn btn-primary">
            + Nieuwe bestelling
        </a>
    </div>
</div>

<!-- Selectievak om orders van een andere dag op te halen -->
<div class="card filter-bar no-print" style="padding: 18px 20px; margin-bottom: 24px;">
    <form method="get" action="<?= $e($url('/robin.php')) ?>" style="display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap; width: 100%;">
        <div style="flex: 1; min-width: 280px;">
            <label class="form-label" for="select-date" style="font-weight: 600; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                <span>📅 Selecteer een afhaaldag:</span>
            </label>
            <select id="select-date" class="form-select" name="date" data-autosubmit style="font-size: 0.95rem; font-weight: 500;">
                <?php if ($availableDates === []): ?>
                    <option value="<?= $e($selectedDate) ?>"><?= $e($formatDutchDate($selectedDate)) ?> (0 bestellingen)</option>
                <?php else: ?>
                    <?php foreach ($availableDates as $date): ?>
                        <?php
                        $cnt = $dateCounts[$date] ?? 0;
                        $label = $formatDutchDate($date) . ' (' . $cnt . ' ' . ($cnt === 1 ? 'bestelling' : 'bestellingen') . ')';
                        ?>
                        <option value="<?= $e($date) ?>" <?= $selectedDate === $date ? 'selected' : '' ?>>
                            <?= $e($label) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div style="display: flex; gap: 8px; align-items: center;">
            <?php if ($prevDate !== null): ?>
                <a href="<?= $e($url('/robin.php', ['date' => $prevDate])) ?>" class="btn btn-outline btn-sm" title="Ga naar <?= $e($prevDate) ?>">
                    &larr; Vorige dag
                </a>
            <?php else: ?>
                <button type="button" class="btn btn-outline btn-sm" disabled style="opacity: 0.45; cursor: not-allowed;" title="Geen eerdere afhaaldag">
                    &larr; Vorige dag
                </button>
            <?php endif; ?>

            <?php if ($nextDate !== null): ?>
                <a href="<?= $e($url('/robin.php', ['date' => $nextDate])) ?>" class="btn btn-outline btn-sm" title="Ga naar <?= $e($nextDate) ?>">
                    Volgende dag &rarr;
                </a>
            <?php else: ?>
                <button type="button" class="btn btn-outline btn-sm" disabled style="opacity: 0.45; cursor: not-allowed;" title="Geen volgende afhaaldag">
                    Volgende dag &rarr;
                </button>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Samenvattende KPI statistieken -->
<div class="dash-kpis" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 28px;">
    <div class="card stat-card" style="padding: 16px 20px;">
        <span class="stat-label">Bestellingen vandaag</span>
        <strong class="stat-val"><?= $totalOrders ?></strong>
        <span style="font-size: 0.8rem; color: var(--ink-500);"><?= $activeSlotsCount ?> actieve tijdsloten</span>
    </div>

    <div class="card stat-card" style="padding: 16px 20px;">
        <span class="stat-label">Totale omzet</span>
        <strong class="stat-val">€ <?= $e(number_format($totalRevenue, 2, ',', '.')) ?></strong>
        <span style="font-size: 0.8rem; color: var(--ink-500);">Voor <?= $e($selectedDate) ?></span>
    </div>

    <div class="card stat-card" style="padding: 16px 20px;">
        <span class="stat-label">Nog te ontvangen saldo</span>
        <strong class="stat-val" style="color: <?= $totalBalance > 0 ? 'var(--danger-ink, #b91c1c)' : 'var(--success-ink, #15803d)' ?>;">
            € <?= $e(number_format($totalBalance, 2, ',', '.')) ?>
        </strong>
        <span style="font-size: 0.8rem; color: var(--ink-500);">Openstaand bij afhaling</span>
    </div>
</div>

<!-- Lijst van bestellingen gegroepeerd per tijdslot -->
<?php if ($orders === []): ?>
    <div class="card" style="padding: 40px; text-align: center; margin-bottom: 30px;">
        <div style="font-size: 2.2rem; margin-bottom: 10px;">📦</div>
        <h2 style="margin: 0 0 8px; font-size: 1.25rem; color: var(--pine-900);">Geen bestellingen gevonden</h2>
        <p style="color: var(--ink-600); margin: 0 0 20px;">
            Er zijn nog geen bestellingen geplaatst voor <strong><?= $e($formatDutchDate($selectedDate)) ?></strong>.
        </p>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <a href="<?= $e($url('/app/order-new')) ?>" class="btn btn-primary btn-sm">+ Bestelling toevoegen</a>
            <a href="<?= $e($url('/app/orders')) ?>" class="btn btn-outline btn-sm">Alle bestellingen bekijken</a>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($slots as $slotLabel => $slotOrders): ?>
        <?php
        $slotOrderCount = count($slotOrders);
        $slotTotal = array_sum(array_map(static fn (Order $o): float => $o->totalAmount, $slotOrders));
        ?>

        <div class="card" style="margin-bottom: 24px; padding: 0; overflow: hidden; border: 1px solid var(--border, #e2e8f0);">
            <!-- Tijdslot kop -->
            <div style="background: <?= $slotOrderCount > 0 ? 'var(--pine-50, #f0fdf4)' : 'var(--bg-subtle, #f8fafc)' ?>; padding: 14px 20px; border-bottom: 1px solid var(--border, #e2e8f0); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.3rem;">🕒</span>
                    <h2 style="margin: 0; font-size: 1.15rem; color: var(--pine-900); font-weight: 700;">
                        <?= $e($slotLabel) ?>
                    </h2>
                    <span class="<?= $slotOrderCount > 0 ? 'badge badge-gold' : 'badge' ?>" style="font-size: 0.8rem;">
                        <?= $slotOrderCount ?> <?= $slotOrderCount === 1 ? 'bestelling' : 'bestellingen' ?>
                    </span>
                </div>

                <?php if ($slotOrderCount > 0): ?>
                    <div style="font-size: 0.95rem; color: var(--ink-700);">
                        Subtotaal slot: <strong>€ <?= $e(number_format($slotTotal, 2, ',', '.')) ?></strong>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Bestellingen in dit specifieke tijdslot -->
            <?php if ($slotOrderCount === 0): ?>
                <div style="padding: 16px 20px; color: var(--ink-400); font-style: italic; font-size: 0.9rem;">
                    Geen bestellingen gepland in dit tijdslot.
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data-table" style="margin: 0;">
                        <thead>
                            <tr>
                                <th style="width: 110px;">Nummer</th>
                                <th style="width: 220px;">Klant</th>
                                <th>Bestelde gerechten / artikelen</th>
                                <th style="width: 140px;">Totaal &amp; Saldo</th>
                                <th style="width: 160px;">Status</th>
                                <th style="width: 130px;" class="no-print">Acties</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($slotOrders as $order): ?>
                                <?php
                                $statusInfo = $statusBadges[$order->status] ?? ['label' => $order->status, 'class' => 'badge'];
                                $paymentInfo = $paymentBadges[$order->paymentStatus] ?? ['label' => $order->paymentStatus, 'class' => 'badge'];
                                $balance = $order->remainingBalance();
                                ?>
                                <tr>
                                    <!-- Bestelnummer -->
                                    <td>
                                        <a href="<?= $e($url('/app/order-edit', ['id' => $order->id])) ?>" style="font-weight: 700; color: var(--pine-800); text-decoration: none;">
                                            <?= $e($order->orderNumber) ?>
                                        </a>
                                        <?php if ($order->isDemo): ?>
                                            <div><span class="badge badge-gold" style="font-size: 0.7rem; margin-top: 3px;">Demo</span></div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Klantgegevens -->
                                    <td>
                                        <strong style="color: var(--ink-900); font-size: 0.95rem;"><?= $e($order->customerName) ?></strong>
                                        <?php if ($order->customerPhone !== ''): ?>
                                            <div>
                                                <a href="tel:<?= $e(preg_replace('/\s+/', '', $order->customerPhone)) ?>" style="color: var(--ink-600); font-size: 0.85rem; text-decoration: none;">
                                                    📞 <?= $e($order->customerPhone) ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($order->customerEmail !== ''): ?>
                                            <div>
                                                <a href="mailto:<?= $e($order->customerEmail) ?>" style="color: var(--ink-500); font-size: 0.8rem; text-decoration: none;">
                                                    ✉️ <?= $e($order->customerEmail) ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Artikelen en opmerkingen -->
                                    <td>
                                        <?php if ($order->items !== []): ?>
                                            <ul style="margin: 0; padding-left: 18px; font-size: 0.9rem; line-height: 1.45;">
                                                <?php foreach ($order->items as $item): ?>
                                                    <?php
                                                    $qty = fmod($item->quantity, 1.0) === 0.0
                                                        ? (string) (int) $item->quantity
                                                        : number_format($item->quantity, 2, ',', '');
                                                    ?>
                                                    <li>
                                                        <strong><?= $e($qty) ?>x</strong> <?= $e($item->productName) ?>
                                                        <span style="color: var(--ink-500); font-size: 0.8rem;">(€ <?= $e(number_format($item->lineTotal, 2, ',', '.')) ?>)</span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <span style="color: var(--ink-500); font-style: italic;">Geen gerechten geregistreerd</span>
                                        <?php endif; ?>

                                        <?php if ($order->notes !== ''): ?>
                                            <div style="margin-top: 6px; padding: 6px 10px; background: #fef3c7; border-left: 3px solid #d97706; border-radius: 4px; font-size: 0.82rem; color: #92400e;">
                                                <strong>Notitie:</strong> <?= $e($order->notes) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Bedragen -->
                                    <td>
                                        <div style="font-weight: 700; font-size: 1rem; color: var(--ink-900);">
                                            € <?= $e(number_format($order->totalAmount, 2, ',', '.')) ?>
                                        </div>
                                        <?php if ($balance > 0): ?>
                                            <div style="color: var(--danger-ink, #b91c1c); font-size: 0.82rem; margin-top: 2px;">
                                                Nog: € <?= $e(number_format($balance, 2, ',', '.')) ?>
                                            </div>
                                        <?php else: ?>
                                            <div style="color: var(--success-ink, #15803d); font-size: 0.82rem; margin-top: 2px;">
                                                ✓ Volledig voldaan
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Statussen -->
                                    <td>
                                        <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                            <span class="<?= $e($statusInfo['class']) ?>" style="font-size: 0.78rem;">
                                                <?= $e($statusInfo['label']) ?>
                                            </span>
                                            <span class="<?= $e($paymentInfo['class']) ?>" style="font-size: 0.78rem;">
                                                <?= $e($paymentInfo['label']) ?>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Acties -->
                                    <td class="no-print">
                                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                            <a href="<?= $e($url('/app/order-print', ['id' => $order->id])) ?>" target="_blank" class="btn btn-outline btn-sm" title="Bestelbon afdrukken" style="padding: 4px 8px; font-size: 0.82rem;">
                                                🖨️ Bon
                                            </a>
                                            <a href="<?= $e($url('/app/order-edit', ['id' => $order->id])) ?>" class="btn btn-outline btn-sm" title="Bestelling bewerken" style="padding: 4px 8px; font-size: 0.82rem;">
                                                ✏️ Bewerk
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php
$contentHtml = ob_get_clean();

// 7. Render binnen de overkoepelende XOrder-layout
$html = $app->view->renderPartial('layouts/base', [
    'pageTitle' => 'Bestellingen per tijdslot - Robin',
    'user' => $user,
    'content' => '<section class="section"><div class="container">' . $contentHtml . '</div></section>',
]);

Response::html($html)->send();
