<?php

declare(strict_types=1);

// Aanmelden
require __DIR__ . '/_bootstrap.php';

xorder_page(
    get: fn () => $auth->showLogin($request),
    post: fn () => $auth->login($request),
);
