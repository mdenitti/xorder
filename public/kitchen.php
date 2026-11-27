<?php

declare(strict_types=1);

// Keukenoverzicht
require __DIR__ . '/_bootstrap.php';

xorder_page(
    get: fn () => $kitchen->index($request),
);
