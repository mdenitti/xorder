<?php

declare(strict_types=1);

// Bestelling verwijderen (enkel POST): order-delete.php?id=...
require __DIR__ . '/_bootstrap.php';

xorder_page(
    post: fn () => $orders->delete($request, $params),
);
