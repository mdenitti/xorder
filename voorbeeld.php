<?php

declare(strict_types=1);

/**
 * VOORBEELD-FEATURE van de docent. Kopieer dit bestand naar features/<jouwvoornaam>.php
 * en je map met assets naar public/features/<jouwvoornaam>/.
 *
 * Wat je hier ter beschikking hebt (je hoeft niets te importeren):
 *
 * @var \App\Core\App $app              database: $app->db, sessie: $app->session
 * @var \App\Core\Request $request      $request->isPost(), $request->post('veld'), $request->query('veld')
 * @var \App\Models\User|null $user     de ingelogde handelaar, of null
 * @var string $featureName             jouw naam, bv. "voorbeeld"
 * @var string $featureUrl              de url van jouw pagina, bv. /features/voorbeeld
 * @var callable $asset                 $asset('style.css') geeft de url naar public/features/<naam>/style.css
 * @var callable $e                     escapen van tekst: altijd gebruiken bij output!
 * @var callable $url                   $url('/app/orders') geeft een correcte link binnen de app
 * @var callable $csrfField             verborgen veld voor elk POST-formulier
 *
 * Regels:
 * - Je eigen tabellen beginnen met jouw naam: voorbeeld_notes, jan_allergenen, ...
 * - Filter altijd op $user->id: een handelaar ziet nooit gegevens van een andere handelaar.
 * - Geen inline <script> of onclick: JavaScript komt in public/features/<naam>/app.js.
 */

use App\Core\Response;

// 1. Niet ingelogd? Dan tonen we een korte uitleg en stoppen we.
if ($user === null) {
    echo '<h1>Notities</h1><p>Log eerst in om je notities te zien. <a href="' . $e($url('/login')) . '">Inloggen</a></p>';
    return;
}

// 2. Eigen tabel aanmaken (gebeurt maar 1 keer, daarna bestaat ze al).
$app->db->pdo()->exec(
    'CREATE TABLE IF NOT EXISTS voorbeeld_notes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        body TEXT NOT NULL,
        created_at TEXT NOT NULL
    )'
);

// 3. Formulier verwerken (POST). De CSRF-controle gebeurt al voor je.
if ($request->isPost()) {
    $body = trim((string) $request->post('body', ''));
    if ($body !== '') {
        $app->db->execute(
            'INSERT INTO voorbeeld_notes (user_id, body, created_at) VALUES (?, ?, ?)',
            [$user->id, mb_substr($body, 0, 500), date('Y-m-d H:i:s')]
        );
        $app->session->flash('success', 'Notitie bewaard.');
    }

    // Na een POST altijd doorsturen, zo wordt het formulier niet dubbel verstuurd bij vernieuwen.
    return Response::redirect($featureUrl);
}

// 4. Gegevens ophalen, enkel van deze handelaar.
$notes = $app->db->fetchAll(
    'SELECT body, created_at FROM voorbeeld_notes WHERE user_id = ? ORDER BY id DESC',
    [$user->id]
);
?>
<link rel="stylesheet" href="<?= $e($asset('style.css')) ?>">

<h1>Notities voor de keuken</h1>
<p>Een eenvoudig voorbeeld: kleine notities voor jezelf of je personeel.</p>

<form method="post" action="<?= $e($featureUrl) ?>" class="card voorbeeld-form">
    <?= $csrfField() ?>
    <label class="form-label" for="voorbeeld-body">Nieuwe notitie</label>
    <textarea id="voorbeeld-body" name="body" class="form-textarea" rows="3" maxlength="500" required></textarea>
    <p class="voorbeeld-counter"><span id="voorbeeld-count">0</span> / 500 tekens</p>
    <button type="submit" class="btn btn-primary">Bewaar</button>
</form>

<?php if ($notes === []): ?>
    <p>Nog geen notities.</p>
<?php else: ?>
    <ul class="voorbeeld-list">
        <?php foreach ($notes as $note): ?>
            <li class="card">
                <?= $e($note['body']) ?>
                <small><?= $e($note['created_at']) ?></small>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<script src="<?= $e($asset('app.js')) ?>"></script>
