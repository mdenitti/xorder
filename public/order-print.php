<?php

declare(strict_types=1);

// Bestelbon afdrukken: order-print.php?id=...
require __DIR__ . '/_bootstrap.php';

xorder_page(
    get: fn () => $orders->printSlip($request, $params),
);
