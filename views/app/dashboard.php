<?php

declare(strict_types=1);

/**
 * @var \App\Models\User $user
 * @var array{active_orders: int, total_revenue: float, open_balance: float, upcoming_dates: array<int, array{pickup_date: string, order_count: int, total_amount: float}>} $stats
 * @var int $productCount
 * @var array<int, \App\Models\Order> $recentOrders
 * @var callable $e
 * @var callable $t
 * @var callable $url
 * @var callable $csrfField
 */
?>
<div class="container app-shell">
    <div class="page-top">
        <div>
            <span class="badge badge-gold"><?= $e($t('dash.founding_badge')) ?></span>
            <h1 style="margin-top:8px;"><?= $e($user->businessName) ?></h1>
            <p style="margin:0;color:var(--ink-600);"><?= $e($t('dash.title')) ?></p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="<?= $e($url('/app/orders/new')) ?>" class="btn btn-primary"><?= $e($t('dash.new_order')) ?></a>
            <a href="<?= $e($url('/app/export/xlsx')) ?>" class="btn btn-gold"><?= $e($t('dash.backup_btn')) ?></a>
        </div>
    </div>

    <?php if ($user->hasDemoData): ?>
        <div class="alert alert-info" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
            <div>
                <strong style="display:block;"><?= $e($t('dash.demo_banner_title')) ?></strong>
                <span style="font-weight:500;"><?= $e($t('dash.demo_banner_desc')) ?></span>
            </div>
            <form method="post" action="<?= $e($url('/app/demo/clear')) ?>" style="margin:0;">
                <?= $csrfField() ?>
                <button type="submit" class="btn btn-outline btn-sm"><?= $e($t('dash.demo_clear_btn')) ?></button>
            </form>
        </div>
    <?php endif; ?>

    <div class="grid-4" style="margin-bottom:24px;">
        <div class="stat-card">
            <div class="stat-label"><?= $e($t('dash.stat_orders')) ?></div>
            <div class="stat-value"><?= $e($stats['active_orders']) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label"><?= $e($t('dash.stat_revenue')) ?></div>
            <div class="stat-value">€ <?= $e(number_format($stats['total_revenue'], 2, ',', '.')) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label"><?= $e($t('dash.stat_open_balance')) ?></div>
            <div class="stat-value">€ <?= $e(number_format($stats['open_balance'], 2, ',', '.')) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label"><?= $e($t('dash.stat_products')) ?></div>
            <div class="stat-value"><?= $e($productCount) ?></div>
        </div>
    </div>

    <div class="grid-2" style="margin-bottom:24px;">
        <div class="card card-pine">
            <h2 style="margin-top:0;font-size:1.3rem;"><?= $e($t('dash.backup_card_title')) ?></h2>
            <p style="opacity:0.9;margin-bottom:14px;"><?= $e($t('dash.backup_card_desc')) ?></p>
            <p style="font-size:0.88rem;opacity:0.85;margin-bottom:18px;">
                <?php if ($user->lastBackupAt !== null): ?>
                    <?= $e($t('dash.backup_last', ['date' => $user->lastBackupAt])) ?>
                <?php else: ?>
                    <?= $e($t('dash.backup_never')) ?>
                <?php endif; ?>
            </p>
            <a href="<?= $e($url('/app/export/xlsx')) ?>" class="btn btn-gold"><?= $e($t('dash.backup_btn')) ?></a>
        </div>

        <div class="card">
            <h2 style="margin-top:0;font-size:1.25rem;"><?= $e($t('dash.upcoming_title')) ?></h2>
            <?php if ($stats['upcoming_dates'] === []): ?>
                <p style="color:var(--ink-600);"><?= $e($t('dash.no_orders_yet')) ?></p>
            <?php else: ?>
                <div style="display:grid;gap:10px;">
                    <?php foreach ($stats['upcoming_dates'] as $day): ?>
                        <a href="<?= $e($url('/app/orders?pickup_date=' . rawurlencode((string) $day['pickup_date']))) ?>" style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border:1px solid var(--border);border-radius:10px;background:var(--cream-50);text-decoration:none;">
                            <span><strong><?= $e($day['pickup_date']) ?></strong> · <?= $e($t('dash.upcoming_orders', ['count' => $day['order_count']])) ?></span>
                            <strong>€ <?= $e(number_format($day['total_amount'], 2, ',', '.')) ?></strong>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px;">
                <a href="<?= $e($url('/app/kitchen')) ?>" class="btn btn-outline btn-sm"><?= $e($t('dash.view_kitchen')) ?></a>
                <a href="<?= $e($url('/app/products')) ?>" class="btn btn-outline btn-sm"><?= $e($t('dash.manage_products')) ?></a>
            </div>
        </div>
    </div>

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <h2 style="margin:0;font-size:1.2rem;"><?= $e($t('dash.recent_orders')) ?></h2>
            <a href="<?= $e($url('/app/orders')) ?>" class="btn btn-outline btn-sm"><?= $e($t('nav.orders')) ?></a>
        </div>

        <?php if ($recentOrders === []): ?>
            <p style="color:var(--ink-600);margin:0;"><?= $e($t('dash.no_orders_yet')) ?></p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><?= $e($t('orders.col_number')) ?></th>
                            <th><?= $e($t('orders.col_customer')) ?></th>
                            <th><?= $e($t('orders.col_pickup')) ?></th>
                            <th><?= $e($t('orders.col_items')) ?></th>
                            <th><?= $e($t('orders.col_total')) ?></th>
                            <th><?= $e($t('orders.col_status')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>
                                    <a href="<?= $e($url('/app/orders/' . $order->id . '/edit')) ?>"><strong><?= $e($order->orderNumber) ?></strong></a>
                                    <?php if ($order->isDemo): ?>
                                        <div><span class="badge badge-gold" style="margin-top:4px;"><?= $e($t('orders.demo_tag')) ?></span></div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= $e($order->customerName) ?></strong><br><small style="color:var(--ink-600);"><?= $e($order->customerPhone) ?></small></td>
                                <td><?= $e($order->pickupDate) ?><br><small style="color:var(--ink-600);"><?= $e($order->pickupSlot) ?></small></td>
                                <td><?= $e($order->itemsSummary()) ?></td>
                                <td><strong>€ <?= $e(number_format($order->totalAmount, 2, ',', '.')) ?></strong></td>
                                <td><span class="badge badge-pine"><?= $e($t('status.' . $order->status)) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
