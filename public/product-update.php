<?php

declare(strict_types=1);

// Product aanpassen (enkel POST): product-update.php?id=...
require __DIR__ . '/_bootstrap.php';

xorder_page(
    post: fn () => $products->update($request, $params),
);
