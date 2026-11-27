<?php

declare(strict_types=1);

// Staat het volledige project in de webroot? Dan draait de app in public/.
header('Location: public/', true, 302);
