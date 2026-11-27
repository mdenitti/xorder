<?php

declare(strict_types=1);

// Bestellingen: lijst (GET) en nieuwe bestelling opslaan (POST)
require __DIR__ . '/_bootstrap.php';

xorder_page(
    get: fn () => $orders->index($request),
    post: fn () => $orders->store($request),
);
