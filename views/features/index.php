<?php

declare(strict_types=1);

/**
 * @var array<int, string> $names
 * @var callable $e
 * @var callable $url
 */
?>
<section class="section">
    <div class="container">
        <h1>Features van de klas</h1>
        <p>Elke cursist bouwde een eigen feature. Klik op een naam om ze te bekijken.</p>

        <?php if ($names === []): ?>
            <div class="card"><p style="margin:0;">Nog geen features. Voeg een bestand toe in <code>features/</code>.</p></div>
        <?php else: ?>
            <div class="feature-grid">
                <?php foreach ($names as $name): ?>
                    <a class="card feature-card" href="<?= $e($url('/features/' . $name)) ?>">
                        <strong><?= $e(ucfirst($name)) ?></strong>
                        <span>feature.php?naam=<?= $e($name) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
