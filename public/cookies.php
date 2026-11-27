<?php

declare(strict_types=1);

// Cookiebeleid
require __DIR__ . '/_bootstrap.php';

xorder_page(
    get: fn () => $pages->cookies($request),
);
