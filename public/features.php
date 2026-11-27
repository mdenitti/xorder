<?php

declare(strict_types=1);

// Overzicht van de features van de klas
require __DIR__ . '/_bootstrap.php';

xorder_page(
    get: fn () => $features->index($request),
);
