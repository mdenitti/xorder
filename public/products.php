<?php

declare(strict_types=1);

// Producten: lijst (GET) en nieuw product (POST)
require __DIR__ . '/_bootstrap.php';

xorder_page(
    get: fn () => $products->index($request),
    post: fn () => $products->store($request),
);
