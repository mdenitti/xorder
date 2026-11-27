<?php

declare(strict_types=1);

// Nieuw wachtwoord kiezen: reset.php?token=...
require __DIR__ . '/_bootstrap.php';

xorder_page(
    get: fn () => $password->showReset($request, $params),
    post: fn () => $password->reset($request, $params),
);
