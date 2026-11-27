<?php

declare(strict_types=1);

// Excel-backup downloaden
require __DIR__ . '/_bootstrap.php';

xorder_page(
    get: fn () => $export->exportXlsx($request),
);
