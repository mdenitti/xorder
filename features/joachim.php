<?php

declare(strict_types=1);

use App\Core\Response;

if ($user === null) {
    echo '<h1>Nieuwsbrief maken</h1><p>Log in om je nieuwsbrieven te beheren. <a href="' . $e($url('/login')) . '">Inloggen</a></p>';
    return;
}

$app->db->pdo()->exec(
    'CREATE TABLE IF NOT EXISTS joachim_newsletters (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        subject TEXT NOT NULL,
        preheader TEXT NOT NULL,
        headline TEXT NOT NULL,
        body TEXT NOT NULL,
        cta_label TEXT NOT NULL,
        cta_url TEXT NOT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )'
);

$emptyDraft = [
    'subject' => '',
    'preheader' => '',
    'headline' => '',
    'body' => '',
    'cta_label' => '',
    'cta_url' => '',
];
$formDraft = $emptyDraft;
$editingId = null;
$formError = null;

if ($request->isPost()) {
    $actionValue = $request->post('action', 'save');
    $action = is_string($actionValue) ? $actionValue : '';

    if ($action === 'delete') {
        $rawId = $request->post('id');
        $id = is_string($rawId) || is_int($rawId)
            ? filter_var((string) $rawId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            : false;
        if ($id === false) {
            $app->session->flash('error', 'Deze nieuwsbrief kon niet worden verwijderd.');
        } else {
            $deleted = $app->db->execute(
                'DELETE FROM joachim_newsletters WHERE id = ? AND user_id = ?',
                [$id, $user->id]
            );
            $app->session->flash(
                $deleted > 0 ? 'success' : 'error',
                $deleted > 0 ? 'Concept verwijderd.' : 'Deze nieuwsbrief kon niet worden gevonden.'
            );
        }

        return Response::redirect($featureUrl);
    }

    if ($action !== 'save') {
        $app->session->flash('error', 'Onbekende actie voor de nieuwsbrief.');
        return Response::redirect($featureUrl);
    }

    $rawPostedId = $request->post('id', '');
    $postedId = is_string($rawPostedId) || is_int($rawPostedId) ? trim((string) $rawPostedId) : '';
    if (!is_string($rawPostedId) && !is_int($rawPostedId)) {
        $formError = 'Deze nieuwsbrief kan niet worden bewerkt.';
    }
    if ($postedId !== '') {
        $id = filter_var($postedId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false || $formError !== null) {
            $formError = 'Deze nieuwsbrief kan niet worden bewerkt.';
        } else {
            $existingDraft = $app->db->fetchOne(
                'SELECT id FROM joachim_newsletters WHERE id = ? AND user_id = ?',
                [$id, $user->id]
            );
            if ($existingDraft === null) {
                $app->session->flash('error', 'Deze nieuwsbrief kon niet worden gevonden.');
                return Response::redirect($featureUrl);
            }
            $editingId = (int) $id;
        }
    }

    foreach ($emptyDraft as $field => $_) {
        $value = $request->post($field, '');
        if (!is_string($value)) {
            $formError ??= 'Vul geldige tekst in alle velden in.';
            $formDraft[$field] = '';
        } else {
            $formDraft[$field] = trim($value);
        }
    }

    $length = static fn (string $value): int => mb_strlen($value, 'UTF-8');
    if ($formError === null) {
        if ($formDraft['subject'] === '' || $formDraft['headline'] === '' || $formDraft['body'] === '') {
            $formError = 'Vul minstens het onderwerp, de titel en de inhoud in.';
        } elseif (
            $length($formDraft['subject']) > 150
            || $length($formDraft['preheader']) > 200
            || $length($formDraft['headline']) > 120
            || $length($formDraft['body']) > 10000
            || $length($formDraft['cta_label']) > 60
            || $length($formDraft['cta_url']) > 2048
        ) {
            $formError = 'Een of meer velden zijn te lang. Controleer de aangegeven limieten.';
        } elseif (($formDraft['cta_label'] === '') !== ($formDraft['cta_url'] === '')) {
            $formError = 'Vul voor de knop zowel een tekst als een link in, of laat beide leeg.';
        } elseif ($formDraft['cta_url'] !== '') {
            $parts = filter_var($formDraft['cta_url'], FILTER_VALIDATE_URL) !== false
                ? parse_url($formDraft['cta_url'])
                : false;
            $scheme = is_array($parts) ? strtolower((string) ($parts['scheme'] ?? '')) : '';
            if (!in_array($scheme, ['http', 'https'], true)) {
                $formError = 'De knoplink moet een volledige http- of https-link zijn.';
            }
        }
    }

    if ($formError === null) {
        $now = date('Y-m-d H:i:s');
        if ($editingId === null) {
            $app->db->execute(
                'INSERT INTO joachim_newsletters
                    (user_id, subject, preheader, headline, body, cta_label, cta_url, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $user->id,
                    $formDraft['subject'],
                    $formDraft['preheader'],
                    $formDraft['headline'],
                    $formDraft['body'],
                    $formDraft['cta_label'],
                    $formDraft['cta_url'],
                    $now,
                    $now,
                ]
            );
            $editingId = $app->db->lastInsertId();
        } else {
            $app->db->execute(
                'UPDATE joachim_newsletters
                 SET subject = ?, preheader = ?, headline = ?, body = ?, cta_label = ?, cta_url = ?, updated_at = ?
                 WHERE id = ? AND user_id = ?',
                [
                    $formDraft['subject'],
                    $formDraft['preheader'],
                    $formDraft['headline'],
                    $formDraft['body'],
                    $formDraft['cta_label'],
                    $formDraft['cta_url'],
                    $now,
                    $editingId,
                    $user->id,
                ]
            );
        }

        $app->session->flash('success', 'Concept bewaard.');
        return Response::redirect($url('/features', ['name' => $featureName, 'id' => $editingId]));
    }
} else {
    $queryId = $request->query('id');
    if ($queryId !== null && $queryId !== '') {
        $id = is_string($queryId) || is_int($queryId)
            ? filter_var((string) $queryId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            : false;
        if ($id === false) {
            $app->session->flash('error', 'Deze nieuwsbrief kan niet worden bewerkt.');
            return Response::redirect($featureUrl);
        }

        $draft = $app->db->fetchOne(
            'SELECT id, subject, preheader, headline, body, cta_label, cta_url
             FROM joachim_newsletters WHERE id = ? AND user_id = ?',
            [$id, $user->id]
        );
        if ($draft === null) {
            $app->session->flash('error', 'Deze nieuwsbrief kon niet worden gevonden.');
            return Response::redirect($featureUrl);
        }

        $editingId = (int) $draft['id'];
        foreach ($emptyDraft as $field => $_) {
            $formDraft[$field] = (string) $draft[$field];
        }
    }
}

$drafts = $app->db->fetchAll(
    'SELECT id, subject, headline, updated_at
     FROM joachim_newsletters WHERE user_id = ? ORDER BY updated_at DESC, id DESC',
    [$user->id]
);
?>
<link rel="stylesheet" href="<?= $e($asset('style.css')) ?>">

<div class="joachim-newsletter">
    <header class="joachim-intro">
        <div>
            <p class="joachim-eyebrow">JOUW VERHAAL, MOOI VERSTUURD</p>
            <h1>Nieuwsbriefstudio</h1>
            <p>Maak een verzorgd nieuwsbriefconcept voor je klanten.</p>
        </div>
        <span class="joachim-status">Concepten</span>
    </header>

    <div class="joachim-workspace">
        <section class="joachim-editor card" aria-labelledby="joachim-editor-title">
            <div class="joachim-section-heading">
                <div>
                    <p class="joachim-eyebrow">NIEUWE EDITIE</p>
                    <h2 id="joachim-editor-title"><?= $editingId === null ? 'Stel je nieuwsbrief samen' : 'Bewerk je concept' ?></h2>
                </div>
                <?php if ($editingId !== null): ?>
                    <a class="joachim-text-link" href="<?= $e($featureUrl) ?>">Nieuw concept</a>
                <?php endif; ?>
            </div>

            <?php if ($formError !== null): ?>
                <p class="joachim-error" role="alert"><?= $e($formError) ?></p>
            <?php endif; ?>

            <form method="post" action="<?= $e($featureUrl) ?>" class="joachim-form">
                <?= $csrfField() ?>
                <input type="hidden" name="action" value="save">
                <?php if ($editingId !== null): ?>
                    <input type="hidden" name="id" value="<?= $e($editingId) ?>">
                <?php endif; ?>

                <div class="joachim-field">
                    <label class="form-label" for="joachim-subject">Onderwerp <span>Verplicht</span></label>
                    <input class="form-control" id="joachim-subject" name="subject" maxlength="150" value="<?= $e($formDraft['subject']) ?>" placeholder="Bijvoorbeeld: Een frisse start voor de lente" required data-joachim-preview="subject">
                    <small class="joachim-hint">De onderwerpregel die je klant later in de inbox ziet.</small>
                </div>

                <div class="joachim-field">
                    <label class="form-label" for="joachim-preheader">Korte inboxtekst</label>
                    <input class="form-control" id="joachim-preheader" name="preheader" maxlength="200" value="<?= $e($formDraft['preheader']) ?>" placeholder="Een korte aanvulling op je onderwerp" data-joachim-preview="preheader">
                </div>

                <div class="joachim-field">
                    <label class="form-label" for="joachim-headline">Titel in de nieuwsbrief <span>Verplicht</span></label>
                    <input class="form-control" id="joachim-headline" name="headline" maxlength="120" value="<?= $e($formDraft['headline']) ?>" placeholder="Wat wil je deze keer vertellen?" required data-joachim-preview="headline">
                </div>

                <div class="joachim-field">
                    <label class="form-label" for="joachim-body">Bericht <span>Verplicht</span></label>
                    <textarea class="form-textarea" id="joachim-body" name="body" rows="8" maxlength="10000" placeholder="Schrijf je nieuwsbrief. Gebruik een lege regel voor een nieuwe alinea." required data-joachim-preview="body"><?= $e($formDraft['body']) ?></textarea>
                    <small class="joachim-hint"><span id="joachim-body-count"><?= $e(mb_strlen($formDraft['body'], 'UTF-8')) ?></span> / 10.000 tekens. Je opmaak blijft eenvoudig en leesbaar op mobiel.</small>
                </div>

                <div class="joachim-optional">
                    <p class="joachim-optional-title">Optionele knop</p>
                    <div class="joachim-field">
                        <label class="form-label" for="joachim-cta-label">Knoptekst</label>
                        <input class="form-control" id="joachim-cta-label" name="cta_label" maxlength="60" value="<?= $e($formDraft['cta_label']) ?>" placeholder="Bekijk het menu" data-joachim-preview="cta-label">
                    </div>
                    <div class="joachim-field">
                        <label class="form-label" for="joachim-cta-url">Knoplink</label>
                        <input class="form-control" id="joachim-cta-url" name="cta_url" maxlength="2048" type="url" value="<?= $e($formDraft['cta_url']) ?>" placeholder="https://voorbeeld.be/menu">
                        <small class="joachim-hint">Gebruik een volledige link die met http:// of https:// begint.</small>
                    </div>
                </div>

                <div class="joachim-form-actions">
                    <button class="btn btn-primary" type="submit">Bewaar concept</button>
                    <button class="btn btn-secondary" type="button" disabled aria-describedby="joachim-publish-help">Verzenden (binnenkort)</button>
                </div>
                <p class="joachim-publish-help" id="joachim-publish-help">Verzenden wordt beschikbaar zodra de klantenlijst en mailingkoppeling bestaan.</p>
            </form>
        </section>

        <aside class="joachim-preview-column" aria-labelledby="joachim-preview-title">
            <div class="joachim-preview-heading">
                <div>
                    <p class="joachim-eyebrow">ZO ZIET HET ERUIT</p>
                    <h2 id="joachim-preview-title">Voorbeeld</h2>
                </div>
                <span class="joachim-preview-device">E-mail</span>
            </div>
            <div class="joachim-email-shell">
                <div class="joachim-email-meta">
                    <span>Onderwerp</span>
                    <strong id="joachim-preview-subject"><?= $formDraft['subject'] !== '' ? $e($formDraft['subject']) : 'Je nieuwsbriefonderwerp' ?></strong>
                </div>
                <div class="joachim-email-card">
                    <div class="joachim-email-brand">
                        <span class="joachim-brand-mark" aria-hidden="true">X</span>
                        <span>XOrder</span>
                        <span class="joachim-brand-label">NIEUWSBRIEF</span>
                    </div>
                    <div class="joachim-email-content">
                        <p class="joachim-email-preheader" id="joachim-preview-preheader"><?= $formDraft['preheader'] !== '' ? $e($formDraft['preheader']) : 'Een persoonlijk bericht van jou' ?></p>
                        <p class="joachim-email-kicker">EEN BERICHT VAN JOU</p>
                        <h3 id="joachim-preview-headline"><?= $formDraft['headline'] !== '' ? $e($formDraft['headline']) : 'Jouw titel komt hier' ?></h3>
                        <p class="joachim-email-body" id="joachim-preview-body"><?= $formDraft['body'] !== '' ? $e($formDraft['body']) : 'Je nieuwsbriefbericht verschijnt hier zodra je begint met schrijven.' ?></p>
                        <div class="joachim-preview-cta-wrap" id="joachim-preview-cta-wrap"<?= $formDraft['cta_label'] === '' ? ' hidden' : '' ?>>
                            <span class="joachim-preview-cta" id="joachim-preview-cta"><?= $formDraft['cta_label'] !== '' ? $e($formDraft['cta_label']) : '' ?></span>
                        </div>
                    </div>
                    <div class="joachim-email-footer">Met zorg samengesteld in XOrder</div>
                </div>
                <p class="joachim-preview-note">Dit is een voorbeeld. Er wordt nog geen e-mail verstuurd.</p>
            </div>
        </aside>
    </div>

    <section class="joachim-drafts" aria-labelledby="joachim-drafts-title">
        <div class="joachim-section-heading">
            <div>
                <p class="joachim-eyebrow">BEWAARD VOOR LATER</p>
                <h2 id="joachim-drafts-title">Jouw concepten</h2>
            </div>
            <span class="joachim-draft-count"><?= count($drafts) ?> <?= count($drafts) === 1 ? 'concept' : 'concepten' ?></span>
        </div>

        <?php if ($drafts === []): ?>
            <div class="joachim-empty card">
                <span class="joachim-empty-icon" aria-hidden="true">✉</span>
                <h3>Je eerste nieuwsbrief begint hier</h3>
                <p>Vul de editor in en bewaar je eerste concept om het later verder aan te passen.</p>
            </div>
        <?php else: ?>
            <ul class="joachim-draft-list">
                <?php foreach ($drafts as $draft): ?>
                    <li class="joachim-draft card">
                        <div class="joachim-draft-copy">
                            <span class="joachim-draft-tag">CONCEPT</span>
                            <h3><?= $e($draft['subject']) ?></h3>
                            <p><?= $e($draft['headline']) ?></p>
                            <small>Laatst bewerkt <?= $e($draft['updated_at']) ?></small>
                        </div>
                        <div class="joachim-draft-actions">
                            <a class="btn btn-secondary" href="<?= $e($url('/features', ['name' => $featureName, 'id' => (int) $draft['id']])) ?>">Bewerken</a>
                            <form method="post" action="<?= $e($featureUrl) ?>" data-joachim-confirm="Dit concept verwijderen?">
                                <?= $csrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $e($draft['id']) ?>">
                                <button class="joachim-delete" type="submit">Verwijderen</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>

<script src="<?= $e($asset('app.js')) ?>"></script>
