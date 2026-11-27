<?php

declare(strict_types=1);

// Afmelden (enkel POST)
require __DIR__ . '/_bootstrap.php';

xorder_page(
    post: fn () => $auth->logout($request),
);
