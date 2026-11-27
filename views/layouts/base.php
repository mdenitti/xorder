<?php

declare(strict_types=1);

/**
 * @var string $pageTitle
 * @var string $content
 * @var \App\Models\User|null $user
 * @var string $locale
 * @var array<int, string> $supportedLocales
 * @var array<int, array{type: string, message: string}> $flashes
 * @var string $currentPath
 * @var callable $e
 * @var callable $t
 * @var callable $url
 * @var callable $csrfField
 * @var array<int, string> $featureNames
 */
?>
<!DOCTYPE html>
<html lang="<?= $e($locale) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($pageTitle) ?> | <?= $e($t('brand.full')) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= $e($assetUrl('/assets/img/xorder-mark.svg')) ?>">
    <link rel="stylesheet" href="<?= $e($assetUrl('/assets/css/app.css')) ?>">
</head>
<body>
<header class="site-header no-print">
    <div class="container header-inner">
        <a href="<?= $e($url($user !== null ? '/app' : '/')) ?>" class="brand-mark">
            <svg class="brand-logo-svg" width="62" height="50" viewBox="3 7 42 34" aria-hidden="true" focusable="false">
                <defs>
                  <linearGradient id="xo-hdr-pine" x1="6" y1="6" x2="42" y2="42" gradientUnits="userSpaceOnUse">
                    <stop offset="0" stop-color="#1f4d41"/><stop offset=".55" stop-color="#14332b"/><stop offset="1" stop-color="#0c211b"/>
                  </linearGradient>
                  <linearGradient id="xo-hdr-gold" x1="13" y1="30" x2="25" y2="18" gradientUnits="userSpaceOnUse">
                    <stop offset="0" stop-color="#b2771f"/><stop offset=".55" stop-color="#d9a046"/><stop offset="1" stop-color="#f2d38c"/>
                  </linearGradient>
                </defs>
                <path d="M8 8 H40 a4 4 0 0 1 4 4 V20.5 a3.5 3.5 0 0 0 0 7 V36 a4 4 0 0 1 -4 4 H8 a4 4 0 0 1 -4 -4 V27.5 a3.5 3.5 0 0 0 0 -7 V12 a4 4 0 0 1 4 -4 Z" fill="url(#xo-hdr-pine)"/>
                <path d="M33 12 V36" stroke="#f6efe1" stroke-opacity=".45" stroke-width="1.2" stroke-dasharray="1.6 2.2" stroke-linecap="round"/>
                <path d="M24.4 18.4 L13.6 29.6" stroke="#f6efe1" stroke-width="3.4" stroke-linecap="round" fill="none"/>
                <path d="M13.6 18.4 L24.4 29.6" stroke="url(#xo-hdr-gold)" stroke-width="3.4" stroke-linecap="round" fill="none"/>
                <path d="M38.5 20.6 Q38.85 23.65 41.9 24 Q38.85 24.35 38.5 27.4 Q38.15 24.35 35.1 24 Q38.15 23.65 38.5 20.6 Z" fill="#e9be6c"/>
            </svg>
            <span class="brand-wordmark">
                <span class="brand-name"><?= $e($t('brand.name')) ?></span>
                <span class="brand-sub"><?= $e($t('brand.by')) ?></span>
            </span>
        </a>

        <nav class="main-nav">
            
            <?php if ($featureNames !== []): ?>
                <a class="nav-link nav-link-features <?= str_starts_with($currentPath, '/features') ? 'is-active' : '' ?>" href="<?= $e($url('/features')) ?>">Features</a>
            <?php endif; ?>
          
            <?php if ($user !== null): ?>
                <a class="nav-link <?= $currentPath === '/app' ? 'is-active' : '' ?>" href="<?= $e($url('/app')) ?>"><?= $e($t('nav.dashboard')) ?></a>
                <a class="nav-link <?= str_starts_with($currentPath, '/app/order') ? 'is-active' : '' ?>" href="<?= $e($url('/app/orders')) ?>"><?= $e($t('nav.orders')) ?></a>
                <a class="nav-link <?= $currentPath === '/app/kitchen' ? 'is-active' : '' ?>" href="<?= $e($url('/app/kitchen')) ?>"><?= $e($t('nav.kitchen')) ?></a>
                <a class="nav-link <?= $currentPath === '/app/products' ? 'is-active' : '' ?>" href="<?= $e($url('/app/products')) ?>"><?= $e($t('nav.products')) ?></a>
                <a class="nav-link <?= $currentPath === '/app/settings' ? 'is-active' : '' ?>" href="<?= $e($url('/app/settings')) ?>"><?= $e($t('nav.settings')) ?></a>
                <a class="nav-link" href="<?= $e($url('/jeroen.php')) ?>">Order Health Check</a>
                <a class="nav-link" href="<?= $e($url('/wesley1.php')) ?>">Wesley1</a>
                <a class="nav-link" href="<?= $e($url('/robin.php')) ?>">Robin</a>
                <a class="nav-link" href="<?= $e($url('/wesley2.php')) ?>">Wesley2</a>
                <a class="nav-link" href="<?= $e($url('/Lily.php')) ?>">Lily</a>
        
 <?php else: ?>
                <a class="nav-link" href="<?= $e($url('/#voordelen')) ?>"><?= $e($t('nav.features')) ?></a>
                <a class="nav-link" href="<?= $e($url('/#excel-backup')) ?>"><?= $e($t('nav.excel')) ?></a>
                <a class="nav-link" href="<?= $e($url('/#waarom-gratis')) ?>"><?= $e($t('nav.why_free')) ?></a>
            <?php endif; ?>
        </nav>

        <div class="header-actions">
            <div class="lang-switch" aria-label="Language">
                <?php foreach ($supportedLocales as $loc): ?>
                    <a href="<?= $e($langUrl($loc)) ?>" class="<?= $locale === $loc ? 'is-active' : '' ?>"><?= $e(strtoupper($loc)) ?></a>
                <?php endforeach; ?>
            </div>

            <?php if ($user !== null): ?>
                <a href="<?= $e($url('/app/export')) ?>" class="btn btn-gold btn-sm"><?= $e($t('nav.backup_now')) ?></a>
                <form method="post" action="<?= $e($url('/logout')) ?>" style="margin:0;">
                    <?= $csrfField() ?>
                    <button type="submit" class="btn btn-outline btn-sm"><?= $e($t('nav.logout')) ?></button>
                </form>
            <?php else: ?>
                <a href="<?= $e($url('/login')) ?>" class="btn btn-outline btn-sm"><?= $e($t('nav.login')) ?></a>
                <a href="<?= $e($url('/#registreren')) ?>" class="btn btn-primary btn-sm"><?= $e($t('nav.register')) ?></a>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php if ($flashes !== []): ?>
    <div class="container flash-wrap no-print">
        <?php foreach ($flashes as $flash): ?>
            <div class="alert alert-<?= $e($flash['type'] === 'error' ? 'error' : ($flash['type'] === 'info' ? 'info' : 'success')) ?>">
                <?= $e($flash['message']) ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<main>
    <?= $content ?>
</main>

<footer class="site-footer no-print">
    <div class="container footer-inner">
        <div class="footer-brand">
            <img src="<?= $e($assetUrl('/assets/img/xorder-mark.svg')) ?>" alt="" width="26" height="26" class="footer-logo-mark">
            <span><strong><?= $e($t('brand.full')) ?></strong> · <?= $e($t('footer.tagline')) ?></span>
        </div>
        <div class="footer-links">
            <a href="<?= $e($url('/privacy')) ?>"><?= $e($t('footer.privacy')) ?></a>
            <a href="<?= $e($url('/cookies')) ?>"><?= $e($t('footer.cookies')) ?></a>
            <a href="<?= $e($url('/')) ?>"><?= $e($t('nav.back_to_site')) ?></a>
            <span><?= $e($t('footer.rights')) ?></span>
        </div>
    </div>
</footer>

<script src="<?= $e($assetUrl('/assets/js/app.js')) ?>"></script>
</body>
</html>
