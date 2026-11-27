<?php

declare(strict_types=1);

// Status van een bestelling wijzigen (enkel POST): order-status.php?id=...
require __DIR__ . '/_bootstrap.php';

xorder_page(
    post: fn () => $orders->updateStatus($request, $params),
);
