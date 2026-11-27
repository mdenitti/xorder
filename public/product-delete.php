<?php

declare(strict_types=1);

// Product verwijderen (enkel POST): product-delete.php?id=...
require __DIR__ . '/_bootstrap.php';

xorder_page(
    post: fn () => $products->delete($request, $params),
);
