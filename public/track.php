<?php

declare(strict_types=1);

// Anonieme tellers van de widget (enkel POST)
require __DIR__ . '/_bootstrap.php';

xorder_page(
    post: fn () => $tracker->track($request),
);
