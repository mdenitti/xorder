<?php

declare(strict_types=1);

// Bestelling bewerken (GET) en opslaan (POST): order-edit.php?id=...
require __DIR__ . '/_bootstrap.php';

xorder_page(
    get: fn () => $orders->edit($request, $params),
    post: fn () => $orders->update($request, $params),
);
