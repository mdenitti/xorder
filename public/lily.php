<?php

declare(strict_types=1);

/**
 * Standalone Business Intelligence dashboard voor XOrder (/lily.php).
 */

use App\Core\App;
use App\Core\Response;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\DemoSeederService;

if (!defined('XORDER_PUBLIC_DIR')) {
    define('XORDER_PUBLIC_DIR', __DIR__);
}

$rootDir = dirname(__DIR__);
if (is_file(__DIR__ . '/xorder-path.php')) {
    $rootDir = (string) require __DIR__ . '/xorder-path.php';
}

require_once $rootDir . '/src/autoload.php';

$app = new App($rootDir);
$request = $app->request;
$userRepo = new UserRepository($app->db);
$seeder = new DemoSeederService(
    new ProductRepository($app->db),
    new OrderRepository($app->db),
    $userRepo,
    $app->i18n
);
$authService = new AuthService($userRepo, $app->session, $app->i18n, $seeder);
$user = $authService->currentUser();

$e = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$url = static fn (string $path = '/', array $query = []): string => $request->url($path, $query);
$asset = static fn (string $file): string => $url('/features/lily/' . ltrim($file, '/'));

ob_start();
?>
<link rel="stylesheet" href="<?= $e($asset('lily.css')) ?>">
<?php if ($user === null): ?>
    <div class="card">
        <h1>Business Intelligence</h1>
        <p class="lily-muted">Log eerst in om je omzet, topgerechten en prognose voor volgend jaar te bekijken.</p>
        <p><a href="<?= $e($url('/login')) ?>" class="btn btn-primary btn-sm">Inloggen</a></p>
    </div>
<?php else:
    $eur = static fn (float $value): string => '€ ' . number_format($value, 2, ',', '.');
    $qtyFmt = static fn (float $value): string => fmod($value, 1.0) === 0.0
        ? (string) (int) $value
        : number_format($value, 2, ',', '.');

    $yearRows = $app->db->fetchAll(
        "SELECT DISTINCT SUBSTR(pickup_date, 1, 4) AS y
         FROM orders
         WHERE user_id = ? AND pickup_date != ''
         ORDER BY y DESC",
        [$user->id]
    );
    $availableYears = array_values(array_filter(
        array_map(static fn (array $row): string => (string) ($row['y'] ?? ''), $yearRows),
        static fn (string $y): bool => $y !== ''
    ));

    $year = (string) $request->query('year', 'all');
    if ($year !== 'all' && !in_array($year, $availableYears, true)) {
        $year = 'all';
    }

    $growth = 10;
    $growthNotice = '';
    $rawGrowth = $request->query('growth', null);
    if ($rawGrowth !== null && trim((string) $rawGrowth) !== '') {
        $normalizedGrowth = str_replace(',', '.', trim((string) $rawGrowth));
        if (!is_numeric($normalizedGrowth)) {
            $growth = 10;
            $growthNotice = 'Groeipercentage moet een getal zijn groter dan 1% en kleiner dan 100% (2% – 99%). Standaard 10% toegepast.';
        } else {
            $growthVal = (float) $normalizedGrowth;
            if ($growthVal <= 1.0) {
                $growth = 2;
                $growthNotice = 'Groeipercentage moet groter zijn dan 1% en kleiner dan 100%. Automatisch aangepast naar 2%.';
            } elseif ($growthVal >= 100.0) {
                $growth = 99;
                $growthNotice = 'Groeipercentage moet groter zijn dan 1% en kleiner dan 100%. Automatisch aangepast naar 99%.';
            } else {
                $growth = max(2, min(99, (int) round($growthVal)));
            }
        }
    }

    $yearSql = $year !== 'all' ? ' AND SUBSTR(o.pickup_date, 1, 4) = ?' : '';
    $baseParams = $year !== 'all' ? [$user->id, $year] : [$user->id];

    $summary = $app->db->fetchOne(
        "SELECT
            COUNT(*) AS total_orders,
            SUM(CASE WHEN o.status != 'cancelled' THEN 1 ELSE 0 END) AS active_orders,
            SUM(CASE WHEN o.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_orders,
            COALESCE(SUM(CASE WHEN o.status != 'cancelled' THEN o.total_amount ELSE 0 END), 0) AS revenue,
            COALESCE(SUM(
                CASE
                    WHEN o.status != 'cancelled' AND o.payment_status != 'paid'
                        THEN CASE WHEN o.total_amount > o.deposit_amount THEN o.total_amount - o.deposit_amount ELSE 0 END
                    ELSE 0
                END
            ), 0) AS open_balance,
            SUM(CASE WHEN o.status != 'cancelled' AND o.payment_status = 'paid' THEN 1 ELSE 0 END) AS paid_orders
         FROM orders o
         WHERE o.user_id = ?{$yearSql}",
        $baseParams
    ) ?? [];

    $topDishes = $app->db->fetchAll(
        "SELECT
            oi.product_name,
            oi.unit,
            SUM(oi.quantity) AS total_qty,
            SUM(oi.line_total) AS total_revenue,
            COUNT(DISTINCT o.id) AS order_count
         FROM order_items oi
         INNER JOIN orders o ON o.id = oi.order_id
         WHERE o.user_id = ? AND o.status != 'cancelled'{$yearSql}
         GROUP BY oi.product_name, oi.unit
         ORDER BY total_revenue DESC, total_qty DESC",
        $baseParams
    );

    $topCustomers = $app->db->fetchAll(
        "SELECT
            MAX(TRIM(o.customer_name)) AS customer_name,
            MAX(NULLIF(TRIM(o.customer_email), '')) AS customer_email,
            MAX(NULLIF(TRIM(o.customer_phone), '')) AS customer_phone,
            COUNT(*) AS order_count,
            COALESCE(SUM(o.total_amount), 0) AS total_spent,
            COALESCE(AVG(o.total_amount), 0) AS avg_order,
            MAX(o.pickup_date) AS last_pickup_date,
            COALESCE(SUM(item_agg.total_items), 0) AS total_items
         FROM orders o
         LEFT JOIN (
             SELECT order_id, SUM(quantity) AS total_items
             FROM order_items
             GROUP BY order_id
         ) item_agg ON item_agg.order_id = o.id
         WHERE o.user_id = ?
           AND o.status != 'cancelled'
           AND TRIM(o.customer_name) != ''{$yearSql}
         GROUP BY LOWER(TRIM(o.customer_name))
         ORDER BY order_count DESC, total_spent DESC, customer_name ASC
         LIMIT 10",
        $baseParams
    );

    $customerStats = $app->db->fetchOne(
        "SELECT
            COUNT(*) AS unique_customers,
            SUM(CASE WHEN c_cnt >= 2 THEN 1 ELSE 0 END) AS repeat_customers
         FROM (
             SELECT COUNT(*) AS c_cnt
             FROM orders o
             WHERE o.user_id = ?
               AND o.status != 'cancelled'
               AND TRIM(o.customer_name) != ''{$yearSql}
             GROUP BY LOWER(TRIM(o.customer_name))
         ) sub",
        $baseParams
    ) ?? [];

    $peakDays = $app->db->fetchAll(
        "SELECT
            o.pickup_date,
            COUNT(*) AS order_count,
            COALESCE(SUM(o.total_amount), 0) AS revenue
         FROM orders o
         WHERE o.user_id = ? AND o.status != 'cancelled'{$yearSql}
         GROUP BY o.pickup_date
         ORDER BY order_count DESC, revenue DESC",
        $baseParams
    );

    $peakSlots = $app->db->fetchAll(
        "SELECT
            COALESCE(NULLIF(TRIM(o.pickup_slot), ''), 'Geen tijdslot') AS slot,
            COUNT(*) AS order_count,
            COALESCE(SUM(o.total_amount), 0) AS revenue
         FROM orders o
         WHERE o.user_id = ? AND o.status != 'cancelled'{$yearSql}
         GROUP BY slot
         ORDER BY order_count DESC, revenue DESC",
        $baseParams
    );

    $unsoldProducts = $app->db->fetchAll(
        "SELECT p.name
         FROM products p
         WHERE p.user_id = ?
           AND p.is_active = 1
           AND NOT EXISTS (
               SELECT 1
               FROM order_items oi
               INNER JOIN orders o ON o.id = oi.order_id
               WHERE o.user_id = p.user_id
                 AND o.status != 'cancelled'{$yearSql}
                 AND (oi.product_id = p.id OR LOWER(oi.product_name) = LOWER(p.name))
           )
         ORDER BY p.name ASC",
        $baseParams
    );

    $totalOrders = (int) ($summary['total_orders'] ?? 0);
    $activeOrders = (int) ($summary['active_orders'] ?? 0);
    $cancelledOrders = (int) ($summary['cancelled_orders'] ?? 0);
    $revenue = (float) ($summary['revenue'] ?? 0.0);
    $openBalance = (float) ($summary['open_balance'] ?? 0.0);
    $paidOrders = (int) ($summary['paid_orders'] ?? 0);

    $uniqueCustomers = (int) ($customerStats['unique_customers'] ?? 0);
    $repeatCustomers = (int) ($customerStats['repeat_customers'] ?? 0);

    $avgOrder = $activeOrders > 0 ? $revenue / $activeOrders : 0.0;
    $paidRate = $activeOrders > 0 ? ($paidOrders / $activeOrders) * 100 : 0.0;
    $cancelRate = $totalOrders > 0 ? ($cancelledOrders / $totalOrders) * 100 : 0.0;
    $successRate = max(0.0, min(100.0, 100.0 - $cancelRate));

    $factor = 1.0 + ($growth / 100.0);
    $projectedRevenue = $revenue * $factor;
    $revenueDelta = max(0.0, $projectedRevenue - $revenue);
    $projectedOrders = (int) ceil($activeOrders * $factor);

    $maxDayOrders = $peakDays !== [] ? max(1, (int) $peakDays[0]['order_count']) : 1;
    $maxSlotOrders = $peakSlots !== [] ? max(1, (int) $peakSlots[0]['order_count']) : 1;

    // Infographic geometry: 1. Donut slices for revenue mix
    $donutRadius = 66.0;
    $donutCirc = 2.0 * M_PI * $donutRadius;
    $donutPalette = ['#e9be6c', '#c88a2e', '#4da388', '#2d7a65', '#6c837c'];
    $donutSlices = [];
    $dishTotalRev = 0.0;
    foreach ($topDishes as $dish) {
        $dishTotalRev += max(0.0, (float) $dish['total_revenue']);
    }
    if ($dishTotalRev > 0.0) {
        $top4 = array_slice($topDishes, 0, 4);
        $rest = array_slice($topDishes, 4);
        $rawBuckets = [];
        foreach ($top4 as $idx => $dish) {
            $rawBuckets[] = [
                'label' => (string) $dish['product_name'],
                'revenue' => max(0.0, (float) $dish['total_revenue']),
                'color' => $donutPalette[$idx % count($donutPalette)],
            ];
        }
        if ($rest !== []) {
            $otherRev = 0.0;
            foreach ($rest as $dish) {
                $otherRev += max(0.0, (float) $dish['total_revenue']);
            }
            if ($otherRev > 0.0) {
                $rawBuckets[] = [
                    'label' => 'Overige gerechten',
                    'revenue' => $otherRev,
                    'color' => $donutPalette[4],
                ];
            }
        }
        $accumOffset = 0.0;
        foreach ($rawBuckets as $bucket) {
            $fraction = $bucket['revenue'] / $dishTotalRev;
            $dashLen = $fraction * $donutCirc;
            $donutSlices[] = [
                'label' => $bucket['label'],
                'revenue' => $bucket['revenue'],
                'pct' => (int) round($fraction * 100),
                'color' => $bucket['color'],
                'dash' => number_format($dashLen, 2, '.', '') . ' ' . number_format($donutCirc, 2, '.', ''),
                'offset' => number_format(-$accumOffset, 2, '.', ''),
            ];
            $accumOffset += $dashLen;
        }
    }

    // Infographic geometry: 2. Dual radial gauges
    $gaugeRadius = 44.0;
    $gaugeCirc = 2.0 * M_PI * $gaugeRadius;
    $paidDash = number_format((max(0.0, min(100.0, $paidRate)) / 100.0) * $gaugeCirc, 2, '.', '')
        . ' ' . number_format($gaugeCirc, 2, '.', '');
    $successDash = number_format(($successRate / 100.0) * $gaugeCirc, 2, '.', '')
        . ' ' . number_format($gaugeCirc, 2, '.', '');

    // Infographic geometry: 3. Peak days timeline chart (up to 6 days sorted chronologically)
    $chartDays = array_slice($peakDays, 0, 6);
    usort($chartDays, static fn (array $a, array $b): int => strcmp((string) $a['pickup_date'], (string) $b['pickup_date']));
    $maxProjDay = max(1, (int) ceil($maxDayOrders * $factor));

    // Current vs projected revenue bar widths
    $currentBarPct = $projectedRevenue > 0 ? round(($revenue / $projectedRevenue) * 100, 1) : 100.0;

    $actions = [];
    if ($peakDays !== []) {
        $busiestDay = $peakDays[0];
        $dayCount = (int) $busiestDay['order_count'];
        $actions[] = sprintf(
            'Extra personeel & mise-en-place voorzien rond %s: piek van %d bestellingen (prognose +%d%%: %d).',
            (string) $busiestDay['pickup_date'],
            $dayCount,
            $growth,
            (int) ceil($dayCount * $factor)
        );
    }

    if ($topCustomers !== []) {
        $bestCustomer = $topCustomers[0];
        $actions[] = sprintf(
            'Topklant %s bestelde %d keer (%s totaal): stuur volgend jaar als eerste een persoonlijke uitnodiging of vroegboekkorting.',
            (string) $bestCustomer['customer_name'],
            (int) $bestCustomer['order_count'],
            $eur((float) $bestCustomer['total_spent'])
        );
    }

    if ($peakSlots !== [] && $activeOrders > 0) {
        $busiestSlot = $peakSlots[0];
        $slotCount = (int) $busiestSlot['order_count'];
        $actions[] = sprintf(
            'Tijdslot "%s" vangt %d%% van alle afhalingen op (%d bestellingen): overweeg een extra afhaalbalie of limiet per slot.',
            (string) $busiestSlot['slot'],
            (int) round(($slotCount / $activeOrders) * 100),
            $slotCount
        );
    }

    if ($topDishes !== [] && $revenue > 0) {
        $top3Revenue = 0.0;
        $top3Names = [];
        foreach (array_slice($topDishes, 0, 3) as $dish) {
            $top3Revenue += (float) $dish['total_revenue'];
            $top3Names[] = (string) $dish['product_name'];
        }
        $actions[] = sprintf(
            'Top %d gerechten (%s) leveren %d%% van je omzet op: bestel hiervoor tijdig grootverpakkingen en maak vooraf klaar.',
            count($top3Names),
            implode(', ', $top3Names),
            (int) round(($top3Revenue / $revenue) * 100)
        );
    }

    if ($openBalance > 0 || $paidRate < 80.0) {
        $actions[] = sprintf(
            'Er staat nog %s open (%d%% volledig betaald): vraag volgend jaar standaard een voorschot bij bestelling.',
            $eur($openBalance),
            (int) round($paidRate)
        );
    } else {
        $actions[] = sprintf(
            'Sterke betalingsopvolging (%d%% volledig betaald): behoud je huidige afrekenflow voor volgend jaar.',
            (int) round($paidRate)
        );
    }

    if ($cancelRate >= 10.0) {
        $actions[] = sprintf(
            'Annulatiegraad ligt op %d%% (%d bestellingen): voer een vroegere bevestigingsdeadline of voorschot in.',
            (int) round($cancelRate),
            $cancelledOrders
        );
    }

    if ($unsoldProducts !== []) {
        $unsoldNames = array_map(static fn (array $row): string => (string) $row['name'], $unsoldProducts);
        $preview = implode(', ', array_slice($unsoldNames, 0, 6));
        if (count($unsoldNames) > 6) {
            $preview .= ', ...';
        }
        $actions[] = sprintf(
            '%d actieve gerechten kregen 0 bestellingen (%s): overweeg deze te schrappen voor een strakkere kaart.',
            count($unsoldNames),
            $preview
        );
    }
?>
    <div class="page-top">
        <div>
            <span class="badge badge-gold">Business Intelligence</span>
            <h1>Overzicht &amp; voorbereiding volgend jaar</h1>
            <p id="lily-period-meta" class="lily-muted"><?= $e($user->businessName) ?> · periode: <?= $e($year === 'all' ? 'Alle jaren' : $year) ?> · groei: +<?= $e((string) $growth) ?>%</p>
        </div>

        <form id="lily-filter-form" method="get" action="<?= $e($url('/lily')) ?>" class="lily-filters no-print" novalidate>
            <?php if ($availableYears !== []): ?>
                <div class="lily-filter-field">
                    <label class="form-label" for="lily-year">Jaar</label>
                    <select id="lily-year" name="year" class="form-select">
                        <option value="all"<?= $year === 'all' ? ' selected' : '' ?>>Alle jaren</option>
                        <?php foreach ($availableYears as $y): ?>
                            <option value="<?= $e($y) ?>"<?= $year === $y ? ' selected' : '' ?>><?= $e($y) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <div class="lily-filter-field lily-filter-field--growth">
                <label class="form-label" for="lily-growth">Groei volgend jaar</label>
                <div class="lily-percent-wrap">
                    <input
                        id="lily-growth"
                        name="growth"
                        type="number"
                        min="2"
                        max="99"
                        step="1"
                        inputmode="numeric"
                        required
                        value="<?= $e((string) $growth) ?>"
                        class="form-control lily-growth-input"
                        aria-describedby="lily-growth-hint"
                    >
                    <span class="lily-percent-suffix" aria-hidden="true">%</span>
                </div>
                <div id="lily-growth-hint" class="lily-field-hint" aria-live="polite">Tussen 2% en 99%</div>
            </div>
            <div class="lily-filter-field lily-filter-field--submit">
                <button type="submit" class="btn btn-primary btn-sm lily-filter-btn">
                    <span class="lily-btn-spinner" aria-hidden="true"></span>
                    <span class="lily-btn-label">Toepassen</span>
                </button>
            </div>
        </form>
    </div>

    <div id="lily-dashboard-root" class="lily-dashboard-root" data-growth="<?= $e((string) $growth) ?>" data-year="<?= $e($year) ?>">
        <?php if ($growthNotice !== ''): ?>
            <div class="alert alert-info lily-notice" role="status"><?= $e($growthNotice) ?></div>
        <?php endif; ?>

        <?php if ($totalOrders === 0): ?>
            <div class="card">
                <h2>Nog geen bestellingen om te analyseren</h2>
                <p class="lily-muted">Zodra er bestellingen zijn geregistreerd, verschijnen hier je omzetcijfers, topklanten, topgerechten, infografiek en het actieplan voor volgend jaar.</p>
                <p><a href="<?= $e($url('/app/order-new')) ?>" class="btn btn-primary btn-sm">Eerste bestelling toevoegen</a></p>
            </div>
        <?php else: ?>
            <div class="grid-4 lily-section">
                <div class="stat-card">
                    <div class="stat-label">Totale omzet</div>
                    <div class="stat-value"><?= $e($eur($revenue)) ?></div>
                    <div class="lily-stat-sub">Prognose (+<?= $e((string) $growth) ?>%): <?= $e($eur($projectedRevenue)) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Actieve bestellingen</div>
                    <div class="stat-value"><?= $e((string) $activeOrders) ?></div>
                    <div class="lily-stat-sub">Gem. <?= $e($eur($avgOrder)) ?> · prognose: <?= $e((string) $projectedOrders) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Volledig betaald</div>
                    <div class="stat-value"><?= $e(number_format($paidRate, 0)) ?>%</div>
                    <div class="lily-stat-sub">Openstaand saldo: <?= $e($eur($openBalance)) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Annulaties</div>
                    <div class="stat-value"><?= $e(number_format($cancelRate, 0)) ?>%</div>
                    <div class="lily-stat-sub"><?= $e((string) $cancelledOrders) ?> van <?= $e((string) $totalOrders) ?> bestellingen</div>
                </div>
            </div>

            <section class="lily-infographic lily-section" aria-label="Visuele Business Intelligence infografiek">
                <div class="lily-info-hero">
                    <div class="lily-info-hero-copy">
                        <span class="lily-info-eyebrow">Visuele Infografiek</span>
                        <h2>Jaaroverzicht &amp; groeiprognose (+<?= $e((string) $growth) ?>%)</h2>
                        <p class="lily-info-sub">Omzetmix, ordergezondheid en piekdrukte in één visueel overzicht voor <?= $e($year === 'all' ? 'alle jaren' : $year) ?>.</p>
                    </div>
                    <div class="lily-info-delta">
                        <div class="lily-info-delta-pill">+<?= $e($eur($revenueDelta)) ?></div>
                        <div class="lily-info-delta-cap">Verwachte extra omzet bij +<?= $e((string) $growth) ?>% groei</div>
                    </div>
                </div>

                <div class="lily-trajectory">
                    <div class="lily-traj-bars">
                        <div class="lily-traj-row">
                            <div class="lily-traj-label">
                                <span>Huidige omzet</span>
                                <strong><?= $e($eur($revenue)) ?></strong>
                            </div>
                            <div class="lily-traj-track">
                                <div class="lily-traj-fill lily-traj-fill--current" style="width: <?= $e((string) $currentBarPct) ?>%"></div>
                            </div>
                        </div>
                        <div class="lily-traj-row">
                            <div class="lily-traj-label">
                                <span>Prognose volgend jaar (+<?= $e((string) $growth) ?>%)</span>
                                <strong><?= $e($eur($projectedRevenue)) ?></strong>
                            </div>
                            <div class="lily-traj-track">
                                <div class="lily-traj-fill lily-traj-fill--projected" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="lily-traj-spark" aria-hidden="true">
                        <svg viewBox="0 0 180 64" class="lily-spark-svg">
                            <defs>
                                <linearGradient id="lilySparkGrad" x1="0" y1="0" x2="1" y2="0">
                                    <stop offset="0%" stop-color="#4da388"/>
                                    <stop offset="100%" stop-color="#e9be6c"/>
                                </linearGradient>
                                <linearGradient id="lilySparkFill" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#e9be6c" stop-opacity="0.28"/>
                                    <stop offset="100%" stop-color="#e9be6c" stop-opacity="0"/>
                                </linearGradient>
                            </defs>
                            <path d="M8,52 C46,50 68,36 102,30 C130,25 148,14 170,10 L170,58 L8,58 Z" fill="url(#lilySparkFill)"/>
                            <path d="M8,52 C46,50 68,36 102,30 C130,25 148,14 170,10" fill="none" stroke="url(#lilySparkGrad)" stroke-width="3" stroke-linecap="round"/>
                            <circle cx="8" cy="52" r="4" fill="#4da388"/>
                            <circle cx="170" cy="10" r="5" fill="#e9be6c"/>
                        </svg>
                    </div>
                </div>

                <div class="lily-info-grid">
                    <div class="lily-info-panel">
                        <div class="lily-info-panel-head">
                            <h3>Omzetmix per gerecht</h3>
                            <span class="lily-info-tag">Top <?= $e((string) min(4, count($topDishes))) ?></span>
                        </div>
                        <?php if ($donutSlices === []): ?>
                            <p class="lily-info-empty">Nog geen gerechtregels met omzet geregistreerd.</p>
                        <?php else: ?>
                            <div class="lily-donut-wrap">
                                <div class="lily-donut-box">
                                    <svg viewBox="0 0 180 180" class="lily-donut-svg" role="img" aria-label="Omzetverdeling topgerechten">
                                        <circle cx="90" cy="90" r="66" class="lily-donut-track" />
                                        <?php foreach ($donutSlices as $slice): ?>
                                            <circle
                                                cx="90"
                                                cy="90"
                                                r="66"
                                                class="lily-donut-slice"
                                                stroke="<?= $e($slice['color']) ?>"
                                                stroke-dasharray="<?= $e($slice['dash']) ?>"
                                                stroke-dashoffset="<?= $e($slice['offset']) ?>"
                                            />
                                        <?php endforeach; ?>
                                    </svg>
                                    <div class="lily-donut-center">
                                        <strong><?= $e($eur($revenue)) ?></strong>
                                        <span><?= $e((string) $activeOrders) ?> best.</span>
                                    </div>
                                </div>
                                <ul class="lily-donut-legend">
                                    <?php foreach ($donutSlices as $slice): ?>
                                        <li>
                                            <span class="lily-legend-dot" style="background: <?= $e($slice['color']) ?>"></span>
                                            <span class="lily-legend-name"><?= $e($slice['label']) ?></span>
                                            <strong class="lily-legend-pct"><?= $e((string) $slice['pct']) ?>%</strong>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="lily-info-panel">
                        <div class="lily-info-panel-head">
                            <h3>Order- &amp; betaalgezondheid</h3>
                            <span class="lily-info-tag"><?= $e((string) $activeOrders) ?> actief</span>
                        </div>
                        <div class="lily-gauges-row">
                            <div class="lily-gauge-card">
                                <div class="lily-gauge-ring">
                                    <svg viewBox="0 0 110 110" class="lily-gauge-svg" role="img" aria-label="Betaalratio <?= $e((string) round($paidRate)) ?> procent">
                                        <circle cx="55" cy="55" r="44" class="lily-gauge-track" />
                                        <circle
                                            cx="55"
                                            cy="55"
                                            r="44"
                                            class="lily-gauge-fill lily-gauge-fill--gold"
                                            stroke-dasharray="<?= $e($paidDash) ?>"
                                        />
                                    </svg>
                                    <div class="lily-gauge-val"><?= $e((string) round($paidRate)) ?>%</div>
                                </div>
                                <div class="lily-gauge-label">Betaalratio</div>
                            </div>
                            <div class="lily-gauge-card">
                                <div class="lily-gauge-ring">
                                    <svg viewBox="0 0 110 110" class="lily-gauge-svg" role="img" aria-label="Succesratio <?= $e((string) round($successRate)) ?> procent">
                                        <circle cx="55" cy="55" r="44" class="lily-gauge-track" />
                                        <circle
                                            cx="55"
                                            cy="55"
                                            r="44"
                                            class="lily-gauge-fill lily-gauge-fill--mint"
                                            stroke-dasharray="<?= $e($successDash) ?>"
                                        />
                                    </svg>
                                    <div class="lily-gauge-val"><?= $e((string) round($successRate)) ?>%</div>
                                </div>
                                <div class="lily-gauge-label">Succesratio</div>
                            </div>
                        </div>
                        <div class="lily-health-pills">
                            <span>Betaald: <strong><?= $e((string) $paidOrders) ?></strong></span>
                            <span>Open: <strong><?= $e($eur($openBalance)) ?></strong></span>
                            <span>Geannuleerd: <strong><?= $e((string) $cancelledOrders) ?></strong></span>
                        </div>
                    </div>

                    <div class="lily-info-panel">
                        <div class="lily-info-panel-head">
                            <h3>Piekdagen &amp; Topklant</h3>
                            <span class="lily-info-tag">+<?= $e((string) $growth) ?>% prog.</span>
                        </div>
                        <?php if ($chartDays === []): ?>
                            <p class="lily-info-empty">Geen ophaaldagen beschikbaar.</p>
                        <?php else: ?>
                            <?php
                            $barCount = count($chartDays);
                            $svgWidth = 300;
                            $svgHeight = 148;
                            $plotTop = 20;
                            $plotBottom = 116;
                            $plotHeight = $plotBottom - $plotTop;
                            $slotWidth = $svgWidth / max(1, $barCount);
                            $barWidth = min(28, max(14, (int) floor($slotWidth * 0.46)));
                            ?>
                            <svg viewBox="0 0 <?= $e((string) $svgWidth) ?> <?= $e((string) $svgHeight) ?>" class="lily-bars-svg" role="img" aria-label="Piekdagen en groeiprognose">
                                <line x1="8" y1="<?= $e((string) $plotBottom) ?>" x2="<?= $e((string) ($svgWidth - 8)) ?>" y2="<?= $e((string) $plotBottom) ?>" class="lily-bars-axis" />
                                <?php foreach ($chartDays as $idx => $day): ?>
                                    <?php
                                    $cnt = (int) $day['order_count'];
                                    $projCnt = (int) ceil($cnt * $factor);
                                    $cx = ($idx * $slotWidth) + ($slotWidth / 2);
                                    $x = $cx - ($barWidth / 2);
                                    $curH = max(4.0, ($cnt / $maxProjDay) * $plotHeight);
                                    $projH = max($curH, ($projCnt / $maxProjDay) * $plotHeight);
                                    $curY = $plotBottom - $curH;
                                    $projY = $plotBottom - $projH;
                                    $rawDate = (string) $day['pickup_date'];
                                    $shortDate = strlen($rawDate) >= 10 ? substr($rawDate, 8, 2) . '/' . substr($rawDate, 5, 2) : $rawDate;
                                    ?>
                                    <rect
                                        x="<?= $e(number_format($x, 1, '.', '')) ?>"
                                        y="<?= $e(number_format($projY, 1, '.', '')) ?>"
                                        width="<?= $e((string) $barWidth) ?>"
                                        height="<?= $e(number_format($projH, 1, '.', '')) ?>"
                                        rx="5"
                                        class="lily-bar-proj"
                                    />
                                    <rect
                                        x="<?= $e(number_format($x, 1, '.', '')) ?>"
                                        y="<?= $e(number_format($curY, 1, '.', '')) ?>"
                                        width="<?= $e((string) $barWidth) ?>"
                                        height="<?= $e(number_format($curH, 1, '.', '')) ?>"
                                        rx="5"
                                        class="lily-bar-cur"
                                    />
                                    <text x="<?= $e(number_format($cx, 1, '.', '')) ?>" y="<?= $e(number_format($projY - 5, 1, '.', '')) ?>" text-anchor="middle" class="lily-bar-val"><?= $e((string) $cnt) ?>→<?= $e((string) $projCnt) ?></text>
                                    <text x="<?= $e(number_format($cx, 1, '.', '')) ?>" y="<?= $e((string) ($plotBottom + 16)) ?>" text-anchor="middle" class="lily-bar-lbl"><?= $e($shortDate) ?></text>
                                <?php endforeach; ?>
                            </svg>
                        <?php endif; ?>

                        <div class="lily-spotlight">
                            <div class="lily-spotlight-item">
                                <span class="lily-spotlight-kicker">#1 Topklant</span>
                                <?php if ($topCustomers !== []): ?>
                                    <strong><?= $e((string) $topCustomers[0]['customer_name']) ?></strong>
                                    <small><?= $e((string) (int) $topCustomers[0]['order_count']) ?> best. · <?= $e($eur((float) $topCustomers[0]['total_spent'])) ?></small>
                                <?php else: ?>
                                    <strong>—</strong>
                                    <small>Nog geen klantdata</small>
                                <?php endif; ?>
                            </div>
                            <div class="lily-spotlight-item">
                                <span class="lily-spotlight-kicker">#1 Topgerecht</span>
                                <?php if ($topDishes !== []): ?>
                                    <strong><?= $e((string) $topDishes[0]['product_name']) ?></strong>
                                    <small><?= $e($qtyFmt((float) $topDishes[0]['total_qty'])) ?> <?= $e((string) $topDishes[0]['unit']) ?> · <?= $e($eur((float) $topDishes[0]['total_revenue'])) ?></small>
                                <?php else: ?>
                                    <strong>—</strong>
                                    <small>Nog geen gerechten</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="card card-pine lily-section">
                <div class="lily-card-head">
                    <h2>Actieplan voor volgend jaar</h2>
                    <span class="badge badge-gold">Prognose +<?= $e((string) $growth) ?>%</span>
                </div>
                <?php if ($actions === []): ?>
                    <p>Voeg bestellingen toe om automatische aanbevelingen voor volgend jaar te ontvangen.</p>
                <?php else: ?>
                    <ul class="sheet-list">
                        <?php foreach ($actions as $action): ?>
                            <li><?= $e($action) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php if ($unsoldProducts !== []): ?>
                    <p class="lily-unsold">
                        <strong>Niet verkocht in deze periode:</strong>
                        <?= $e(implode(', ', array_map(static fn (array $r): string => (string) $r['name'], $unsoldProducts))) ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="card lily-section">
                <div class="lily-card-head">
                    <h2>Topklanten · wie het meeste bestelt</h2>
                    <span class="badge badge-pine">
                        <?= $e((string) $uniqueCustomers) ?> unieke klanten · <?= $e((string) $repeatCustomers) ?> terugkerend
                    </span>
                </div>

                <?php if ($topCustomers === []): ?>
                    <p class="lily-muted">Geen actieve klantbestellingen gevonden voor deze periode.</p>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Klant</th>
                                    <th class="lily-num">Bestellingen</th>
                                    <th class="lily-num">Totale omzet &amp; aandeel</th>
                                    <th class="lily-num">Gem. bestelling</th>
                                    <th class="lily-num">Laatste afhaling</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topCustomers as $idx => $cust): ?>
                                    <?php
                                    $rank = $idx + 1;
                                    $rankClass = match ($rank) {
                                        1 => 'lily-rank-badge--1',
                                        2 => 'lily-rank-badge--2',
                                        3 => 'lily-rank-badge--3',
                                        default => 'lily-rank-badge--n',
                                    };
                                    $custSpent = (float) $cust['total_spent'];
                                    $custShare = $revenue > 0 ? min(100, (int) round(($custSpent / $revenue) * 100)) : 0;
                                    $custOrders = (int) $cust['order_count'];
                                    $custItems = (float) $cust['total_items'];
                                    $contactParts = array_filter([
                                        (string) ($cust['customer_email'] ?? ''),
                                        (string) ($cust['customer_phone'] ?? ''),
                                    ], static fn (string $v): bool => $v !== '');
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="lily-rank-badge <?= $e($rankClass) ?>">#<?= $e((string) $rank) ?></span>
                                        </td>
                                        <td>
                                            <strong><?= $e((string) $cust['customer_name']) ?></strong>
                                            <?php if ($contactParts !== []): ?>
                                                <div class="lily-customer-meta"><?= $e(implode(' · ', $contactParts)) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="lily-num">
                                            <strong><?= $e((string) $custOrders) ?> <?= $e($custOrders === 1 ? 'bestelling' : 'bestellingen') ?></strong>
                                            <div class="lily-customer-meta"><?= $e($qtyFmt($custItems)) ?> items</div>
                                        </td>
                                        <td class="lily-num">
                                            <div><?= $e($eur($custSpent)) ?> (<?= $e((string) $custShare) ?>%)</div>
                                            <progress class="lily-progress lily-progress-gold" value="<?= $e((string) $custShare) ?>" max="100"></progress>
                                        </td>
                                        <td class="lily-num"><?= $e($eur((float) $cust['avg_order'])) ?></td>
                                        <td class="lily-num"><?= $e((string) $cust['last_pickup_date']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card lily-section">
                <div class="lily-card-head">
                    <h2>Topgerechten &amp; voorbereiding</h2>
                    <span class="badge badge-pine">Prognose +<?= $e((string) $growth) ?>%</span>
                </div>

                <?php if ($topDishes === []): ?>
                    <p class="lily-muted">Geen actieve gerechtregels gevonden voor deze periode.</p>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Gerecht</th>
                                    <th class="lily-num">Verkocht</th>
                                    <th class="lily-num">Bestellingen</th>
                                    <th class="lily-num">Omzet &amp; aandeel</th>
                                    <th class="lily-num">Voorbereiden volgend jaar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topDishes as $dish): ?>
                                    <?php
                                    $qty = (float) $dish['total_qty'];
                                    $dishRev = (float) $dish['total_revenue'];
                                    $share = $revenue > 0 ? min(100, (int) round(($dishRev / $revenue) * 100)) : 0;
                                    $prepQty = (int) ceil($qty * $factor);
                                    $unit = (string) $dish['unit'];
                                    ?>
                                    <tr>
                                        <td><strong><?= $e((string) $dish['product_name']) ?></strong></td>
                                        <td class="lily-num"><?= $e($qtyFmt($qty)) ?> <?= $e($unit) ?></td>
                                        <td class="lily-num"><?= $e((string) (int) $dish['order_count']) ?></td>
                                        <td class="lily-num">
                                            <div><?= $e($eur($dishRev)) ?> (<?= $e((string) $share) ?>%)</div>
                                            <progress class="lily-progress lily-progress-gold" value="<?= $e((string) $share) ?>" max="100"></progress>
                                        </td>
                                        <td class="lily-num"><strong><?= $e((string) $prepQty) ?> <?= $e($unit) ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="grid-2">
                <div class="card">
                    <div class="lily-card-head">
                        <h2>Drukste ophaaldagen</h2>
                    </div>
                    <?php if ($peakDays === []): ?>
                        <p class="lily-muted">Geen actieve ophaaldagen.</p>
                    <?php else: ?>
                        <div class="lily-bar-list">
                            <?php foreach ($peakDays as $day): ?>
                                <?php
                                $cnt = (int) $day['order_count'];
                                $projCnt = (int) ceil($cnt * $factor);
                                ?>
                                <div>
                                    <div class="lily-bar-meta">
                                        <strong><?= $e((string) $day['pickup_date']) ?></strong>
                                        <span><?= $e((string) $cnt) ?> best. (volgend jaar: <?= $e((string) $projCnt) ?>) · <?= $e($eur((float) $day['revenue'])) ?></span>
                                    </div>
                                    <progress class="lily-progress" value="<?= $e((string) $cnt) ?>" max="<?= $e((string) $maxDayOrders) ?>"></progress>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="lily-card-head">
                        <h2>Drukste tijdslots</h2>
                    </div>
                    <?php if ($peakSlots === []): ?>
                        <p class="lily-muted">Geen actieve tijdslots.</p>
                    <?php else: ?>
                        <div class="lily-bar-list">
                            <?php foreach ($peakSlots as $slot): ?>
                                <?php
                                $cnt = (int) $slot['order_count'];
                                $slotPct = $activeOrders > 0 ? (int) round(($cnt / $activeOrders) * 100) : 0;
                                ?>
                                <div>
                                    <div class="lily-bar-meta">
                                        <strong><?= $e((string) $slot['slot']) ?></strong>
                                        <span><?= $e((string) $cnt) ?> best. (<?= $e((string) $slotPct) ?>%) · <?= $e($eur((float) $slot['revenue'])) ?></span>
                                    </div>
                                    <progress class="lily-progress lily-progress-gold" value="<?= $e((string) $cnt) ?>" max="<?= $e((string) $maxSlotOrders) ?>"></progress>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <script src="<?= $e($asset('lily.js')) ?>" defer></script>
<?php endif; ?>
<?php
$content = (string) ob_get_clean();

$html = $app->view->renderPartial('layouts/base', [
    'pageTitle' => 'Business Intelligence',
    'user' => $user,
    'content' => '<section class="section feature-page feature-lily"><div class="container">' . $content . '</div></section>',
]);

Response::html($html)->send();
