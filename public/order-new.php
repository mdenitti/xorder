<?php

declare(strict_types=1);

// Formulier voor een nieuwe bestelling
require __DIR__ . '/_bootstrap.php';

xorder_page(
    get: fn () => $orders->create($request),
);
