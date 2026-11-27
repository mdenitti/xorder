<?php

declare(strict_types=1);

// Dashboard van de handelaar
require __DIR__ . '/_bootstrap.php';

xorder_page(
    get: fn () => $dashboard->index($request),
);
