<?php

declare(strict_types=1);

// Eén feature van een cursist: feature.php?naam=<voornaam> (toont features/<voornaam>.php)
require __DIR__ . '/_bootstrap.php';

xorder_page(
    get: fn () => $features->show($request, ['name' => $params['naam'] ?? '']),
    post: fn () => $features->show($request, ['name' => $params['naam'] ?? '']),
);
