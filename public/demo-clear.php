<?php

declare(strict_types=1);

// Demogegevens wissen (enkel POST)
require __DIR__ . '/_bootstrap.php';

xorder_page(
    post: fn () => $dashboard->clearDemo($request),
);
