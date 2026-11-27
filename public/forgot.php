<?php

declare(strict_types=1);

// Wachtwoord vergeten: formulier (GET) en link aanvragen (POST)
require __DIR__ . '/_bootstrap.php';

xorder_page(
    get: fn () => $password->showForgot($request),
    post: fn () => $password->forgot($request),
);
