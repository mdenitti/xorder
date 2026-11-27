<?php

declare(strict_types=1);

// Instellingen
require __DIR__ . '/_bootstrap.php';

xorder_page(
    get: fn () => $settings->index($request),
    post: fn () => $settings->update($request),
);
