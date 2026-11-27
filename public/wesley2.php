<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Database;
use App\Core\Response;
use App\Repositories\UserRepository;

define('XORDER_PUBLIC_DIR', __DIR__);

$rootDir = dirname(__DIR__);
if (is_file(__DIR__ . '/xorder-path.php')) {
    $rootDir = (string) require __DIR__ . '/xorder-path.php';
}

require_once $rootDir . '/src/autoload.php';

/**
 * Seed or restore 6 realistic Belgian customer reviews for a user.
 */
function wesley2_seed_dummy_reviews(Database $db, int $userId, string $locale, bool $onlyIfNeverSeeded): int
{
    if ($onlyIfNeverSeeded) {
        $existing = $db->fetchOne(
            'SELECT COUNT(*) AS cnt FROM wesley2_reviews WHERE user_id = ?',
            [$userId]
        );
        if ((int) ($existing['cnt'] ?? 0) > 0) {
            return 0;
        }
    } else {
        $activeDemo = $db->fetchOne(
            'SELECT COUNT(*) AS cnt FROM wesley2_reviews WHERE user_id = ? AND is_demo = 1 AND deleted_at IS NULL',
            [$userId]
        );
        if ((int) ($activeDemo['cnt'] ?? 0) > 0) {
            return 0;
        }

        $now = date('Y-m-d H:i:s');
        $restored = $db->execute(
            'UPDATE wesley2_reviews
             SET deleted_at = NULL, updated_at = ?
             WHERE user_id = ? AND is_demo = 1 AND deleted_at IS NOT NULL',
            [$now, $userId]
        );
        if ($restored > 0) {
            return $restored;
        }
    }

    $isFr = $locale === 'fr';
    $samples = $isFr ? [
        [
            'reviewer_name' => 'Sofie Peeters',
            'title' => 'Menu de fete impeccable',
            'rating' => 5,
            'comment' => 'Commande prete a l\'heure convenue et emballage tres soigne. Nos invites ont adore la bisque de homard et le filet de biche.',
            'days_ago' => 1,
        ],
        [
            'reviewer_name' => 'Marc De Smet',
            'title' => 'Organisation parfaite pour le reveillon',
            'rating' => 5,
            'comment' => 'Retrait fluide sans file d\'attente malgre l\'affluence. Les fiches de rechauffage etaient claires et precises.',
            'days_ago' => 3,
        ],
        [
            'reviewer_name' => 'Annelies Claes',
            'title' => 'Tres bons plateaux fromage et charcuterie',
            'rating' => 4,
            'comment' => 'Produits bien frais et belle presentation sur plateau. Une petite etiquette avec le nom de chaque fromage serait un plus.',
            'days_ago' => 5,
        ],
        [
            'reviewer_name' => 'Bram Vermeulen',
            'title' => 'Buche pralinee tres appreciee',
            'rating' => 4,
            'comment' => 'Saveurs equilibrees et pas trop sucrees. Le creneau horaire choisi le matin a ete respecte a la minute.',
            'days_ago' => 8,
        ],
        [
            'reviewer_name' => 'Katrien Janssens',
            'title' => 'Bonne qualite, un peu d\'attente',
            'rating' => 3,
            'comment' => 'Plats savoureux, surtout la sauce aux champignons. Il a fallu patienter dix minutes au comptoir vers midi.',
            'days_ago' => 11,
        ],
        [
            'reviewer_name' => 'Pieter Maes',
            'title' => 'Portion d\'accompagnement un peu juste',
            'rating' => 2,
            'comment' => 'La viande etait tendre mais il manquait quelques croquettes par rapport au nombre de personnes annonce.',
            'days_ago' => 13,
        ],
    ] : [
        [
            'reviewer_name' => 'Sofie Peeters',
            'title' => 'Feestmenu tot in de puntjes verzorgd',
            'rating' => 5,
            'comment' => 'Alles stond op het afgesproken uur klaar en heel netjes verpakt. Onze gasten waren vol lof over de kreeftensoep en het hertenfilet.',
            'days_ago' => 1,
        ],
        [
            'reviewer_name' => 'Marc De Smet',
            'title' => 'Vlotte afhaling op kerstavond',
            'rating' => 5,
            'comment' => 'Geen wachttijd aan de toog ondanks de drukte. De opwarminstructies op het kaartje waren duidelijk en handig.',
            'days_ago' => 3,
        ],
        [
            'reviewer_name' => 'Annelies Claes',
            'title' => 'Lekkere kaas- en charcuterieschotel',
            'rating' => 4,
            'comment' => 'Mooie variatie en alles kraakvers. Een klein kaartje met de namen van de kazen erbij zou het helemaal af maken.',
            'days_ago' => 5,
        ],
        [
            'reviewer_name' => 'Bram Vermeulen',
            'title' => 'Kerststronk viel enorm in de smaak',
            'rating' => 4,
            'comment' => 'Luchtige praline en zeker niet te zoet. Het gekozen tijdslot in de voormiddag werd mooi gerespecteerd.',
            'days_ago' => 8,
        ],
        [
            'reviewer_name' => 'Katrien Janssens',
            'title' => 'Lekker gegeten, even moeten wachten',
            'rating' => 3,
            'comment' => 'De gerechten waren heerlijk, vooral de wildsaus. Rond de middag was er wel een tiental minuten vertraging bij afhaling.',
            'days_ago' => 11,
        ],
        [
            'reviewer_name' => 'Pieter Maes',
            'title' => 'Bijgerechten mochten royaler',
            'rating' => 2,
            'comment' => 'Het vlees was heel mals, maar de portie kroketten en groenten was aan de krappe kant voor zes personen.',
            'days_ago' => 13,
        ],
    ];

    $nowTs = time();
    $inserted = 0;

    $db->beginTransaction();
    try {
        foreach ($samples as $idx => $sample) {
            $ts = date('Y-m-d H:i:s', $nowTs - ($sample['days_ago'] * 86400) - ($idx * 1800));
            $db->execute(
                'INSERT INTO wesley2_reviews (
                    user_id, reviewer_name, title, rating, comment, is_demo, created_at, updated_at, deleted_at
                ) VALUES (?, ?, ?, ?, ?, 1, ?, ?, NULL)',
                [
                    $userId,
                    $sample['reviewer_name'],
                    $sample['title'],
                    $sample['rating'],
                    $sample['comment'],
                    $ts,
                    $ts,
                ]
            );
            $inserted++;
        }
        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }

    return $inserted;
}

/**
 * Return localized UI copy for nl and fr.
 *
 * @return array<string, string>
 */
function wesley2_texts(string $locale): array
{
    $nl = [
        'page_title' => 'Klantbeoordelingen',
        'eyebrow' => 'Kwaliteit & Feedback',
        'subtitle' => 'Beheer de klantbeoordelingen voor {business}.',
        'back_dashboard' => 'Terug naar overzicht',
        'reload_demo' => 'Voorbeeldreviews laden',
        'summary_title' => 'Gemiddelde score',
        'summary_based_on' => 'Gebaseerd op {count} beoordelingen',
        'summary_based_on_one' => 'Gebaseerd op 1 beoordeling',
        'summary_empty' => 'Nog geen beoordelingen ontvangen',
        'star_label_plural' => '{n} sterren',
        'star_label_one' => '1 ster',
        'filter_star_clear' => 'Klik nogmaals om filter te wissen',
        'form_add_title' => 'Beoordeling toevoegen',
        'form_edit_title' => 'Beoordeling bewerken',
        'form_cancel_edit' => 'Annuleren',
        'field_rating' => 'Score',
        'field_name' => 'Naam klant',
        'field_name_ph' => 'bv. Sofie Peeters',
        'field_title' => 'Korte titel (optioneel)',
        'field_title_ph' => 'bv. Feestmenu tot in de puntjes verzorgd',
        'field_comment' => 'Reactie',
        'field_comment_ph' => 'Deel de ervaring van je klant...',
        'submit_add' => 'Beoordeling opslaan',
        'submit_edit' => 'Wijzigingen opslaan',
        'filter_search_label' => 'Zoeken',
        'filter_search_ph' => 'Zoek op naam, titel of reactie...',
        'filter_rating_label' => 'Filter op score',
        'filter_rating_all' => 'Alle scores',
        'filter_sort_label' => 'Sorteren op',
        'sort_newest' => 'Nieuwste eerst',
        'sort_oldest' => 'Oudste eerst',
        'sort_highest' => 'Hoogste score eerst',
        'sort_lowest' => 'Laagste score eerst',
        'filter_apply' => 'Toepassen',
        'filter_reset' => 'Reset',
        'showing_count' => '{shown} van {total} beoordelingen getoond',
        'demo_badge' => 'Voorbeeld',
        'edit_btn' => 'Bewerken',
        'delete_btn' => 'Verwijderen',
        'confirm_delete' => 'Wil je deze beoordeling verwijderen?',
        'empty_filtered_title' => 'Geen beoordelingen gevonden voor deze filter',
        'empty_filtered_desc' => 'Pas je zoekopdracht of sterrenfilter aan om opnieuw resultaten te zien.',
        'empty_none_title' => 'Nog geen klantbeoordelingen',
        'empty_none_desc' => 'Voeg je eerste beoordeling toe via het formulier of laad de 6 voorbeeldreviews in.',
        'flash_created' => 'De beoordeling is toegevoegd.',
        'flash_updated' => 'De beoordeling is bijgewerkt.',
        'flash_deleted' => 'De beoordeling is verwijderd.',
        'flash_demo_loaded' => '{count} voorbeeldreviews zijn klaargezet.',
        'flash_demo_already' => 'De voorbeeldreviews zijn al actief in je lijst.',
        'err_name' => 'Geef een klantnaam in (1 tot 120 tekens).',
        'err_title' => 'De titel mag maximaal 160 tekens bevatten.',
        'err_rating' => 'Kies een geldige score van 1 tot 5 sterren.',
        'err_comment' => 'Geef een reactie in (1 tot 1500 tekens).',
        'err_unknown_action' => 'Ongeldige actie.',
    ];

    $fr = [
        'page_title' => 'Avis clients',
        'eyebrow' => 'Qualite & Retours',
        'subtitle' => 'Gerez les avis clients pour {business}.',
        'back_dashboard' => 'Retour au tableau de bord',
        'reload_demo' => 'Charger les avis exemple',
        'summary_title' => 'Note moyenne',
        'summary_based_on' => 'Base sur {count} avis',
        'summary_based_on_one' => 'Base sur 1 avis',
        'summary_empty' => 'Aucun avis recu pour le moment',
        'star_label_plural' => '{n} etoiles',
        'star_label_one' => '1 etoile',
        'filter_star_clear' => 'Cliquez a nouveau pour effacer le filtre',
        'form_add_title' => 'Ajouter un avis',
        'form_edit_title' => 'Modifier l\'avis',
        'form_cancel_edit' => 'Annuler',
        'field_rating' => 'Note',
        'field_name' => 'Nom du client',
        'field_name_ph' => 'ex. Sofie Peeters',
        'field_title' => 'Titre court (optionnel)',
        'field_title_ph' => 'ex. Menu de fete impeccable',
        'field_comment' => 'Commentaire',
        'field_comment_ph' => 'Partagez l\'experience de votre client...',
        'submit_add' => 'Enregistrer l\'avis',
        'submit_edit' => 'Enregistrer les modifications',
        'filter_search_label' => 'Rechercher',
        'filter_search_ph' => 'Rechercher par nom, titre ou texte...',
        'filter_rating_label' => 'Filtrer par note',
        'filter_rating_all' => 'Toutes les notes',
        'filter_sort_label' => 'Trier par',
        'sort_newest' => 'Plus recents d\'abord',
        'sort_oldest' => 'Plus anciens d\'abord',
        'sort_highest' => 'Meilleures notes d\'abord',
        'sort_lowest' => 'Notes les plus basses d\'abord',
        'filter_apply' => 'Appliquer',
        'filter_reset' => 'Reinitialiser',
        'showing_count' => '{shown} sur {total} avis affiches',
        'demo_badge' => 'Exemple',
        'edit_btn' => 'Modifier',
        'delete_btn' => 'Supprimer',
        'confirm_delete' => 'Voulez-vous vraiment supprimer cet avis ?',
        'empty_filtered_title' => 'Aucun avis trouve pour ce filtre',
        'empty_filtered_desc' => 'Modifiez votre recherche ou votre filtre d\'etoiles pour afficher des resultats.',
        'empty_none_title' => 'Aucun avis client pour le moment',
        'empty_none_desc' => 'Ajoutez votre premier avis via le formulaire ou chargez les 6 avis exemple.',
        'flash_created' => 'L\'avis a bien ete ajoute.',
        'flash_updated' => 'L\'avis a bien ete mis a jour.',
        'flash_deleted' => 'L\'avis a bien ete supprime.',
        'flash_demo_loaded' => '{count} avis exemple ont ete charges.',
        'flash_demo_already' => 'Les avis exemple sont deja actifs dans votre liste.',
        'err_name' => 'Veuillez saisir un nom de client (1 a 120 caracteres).',
        'err_title' => 'Le titre ne peut pas depasser 160 caracteres.',
        'err_rating' => 'Choisissez une note valide entre 1 et 5 etoiles.',
        'err_comment' => 'Veuillez saisir un commentaire (1 a 1500 caracteres).',
        'err_unknown_action' => 'Action invalide.',
    ];

    return $locale === 'fr' ? $fr : $nl;
}

/**
 * Translate a local UI key with optional placeholder interpolation.
 *
 * @param array<string, string> $texts
 * @param array<string, string|int|float> $params
 */
function wesley2_t(array $texts, string $key, array $params = []): string
{
    $value = $texts[$key] ?? $key;
    if ($params === []) {
        return $value;
    }

    $replacements = [];
    foreach ($params as $paramKey => $paramVal) {
        $replacements['{' . $paramKey . '}'] = (string) $paramVal;
    }

    return strtr($value, $replacements);
}

/**
 * Measure UTF-8 string length safely.
 */
function wesley2_strlen(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

/**
 * Validate review form fields and return sanitized values or an error message.
 *
 * @param array<string, string> $texts
 * @return array{ok: bool, error: string, data: array{reviewer_name: string, title: string, rating: int, comment: string}}
 */
function wesley2_validate_review_input(
    string $reviewerName,
    string $title,
    mixed $ratingRaw,
    string $comment,
    array $texts
): array {
    $name = trim($reviewerName);
    $cleanTitle = trim($title);
    $cleanComment = trim($comment);
    $rating = filter_var($ratingRaw, FILTER_VALIDATE_INT);

    $data = [
        'reviewer_name' => $name,
        'title' => $cleanTitle,
        'rating' => is_int($rating) ? $rating : 0,
        'comment' => $cleanComment,
    ];

    $nameLen = wesley2_strlen($name);
    if ($nameLen < 1 || $nameLen > 120) {
        return ['ok' => false, 'error' => $texts['err_name'], 'data' => $data];
    }

    if (wesley2_strlen($cleanTitle) > 160) {
        return ['ok' => false, 'error' => $texts['err_title'], 'data' => $data];
    }

    if (!is_int($rating) || $rating < 1 || $rating > 5) {
        return ['ok' => false, 'error' => $texts['err_rating'], 'data' => $data];
    }

    $commentLen = wesley2_strlen($cleanComment);
    if ($commentLen < 1 || $commentLen > 1500) {
        return ['ok' => false, 'error' => $texts['err_comment'], 'data' => $data];
    }

    return ['ok' => true, 'error' => '', 'data' => $data];
}

/**
 * Normalize filter and sort parameters from a query array.
 *
 * @return array{rating: ?int, sort: string, q: string}
 */
function wesley2_parse_filters(App $app): array
{
    $ratingRaw = (string) $app->request->query('rating', '');
    $rating = in_array($ratingRaw, ['1', '2', '3', '4', '5'], true) ? (int) $ratingRaw : null;

    $sortRaw = (string) $app->request->query('sort', 'newest');
    $sort = in_array($sortRaw, ['newest', 'oldest', 'highest', 'lowest'], true) ? $sortRaw : 'newest';

    $q = trim((string) $app->request->query('q', ''));
    if (wesley2_strlen($q) > 120) {
        $q = function_exists('mb_substr') ? mb_substr($q, 0, 120, 'UTF-8') : substr($q, 0, 120);
    }

    return [
        'rating' => $rating,
        'sort' => $sort,
        'q' => $q,
    ];
}

/**
 * Build compact query array preserving active filter and sort parameters.
 *
 * @param array{rating: ?int, sort: string, q: string} $filters
 * @return array<string, string>
 */
function wesley2_filter_query(array $filters): array
{
    $params = [];
    if ($filters['rating'] !== null) {
        $params['rating'] = (string) $filters['rating'];
    }
    if ($filters['sort'] !== 'newest') {
        $params['sort'] = $filters['sort'];
    }
    if ($filters['q'] !== '') {
        $params['q'] = $filters['q'];
    }

    return $params;
}

/**
 * Escape HTML characters for template output.
 */
function wesley2_e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Render a reusable inline SVG star icon.
 */
function wesley2_star_svg(string $className = 'wesley2-star-svg'): string
{
    return '<svg class="' . wesley2_e($className) . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
        . '<path d="M12 2.4l2.92 5.92 6.53.95-4.72 4.6 1.11 6.5L12 17.3l-5.84 3.07 1.11-6.5-4.72-4.6 6.53-.95L12 2.4z"/>'
        . '</svg>';
}

/**
 * Render 5 SVG stars supporting fractional fill (0.0 to 5.0).
 */
function wesley2_render_stars_visual(float $score, string $sizeClass = ''): string
{
    $clamped = max(0.0, min(5.0, $score));
    $pct = round(($clamped / 5.0) * 100, 1);

    $stars = '';
    for ($i = 0; $i < 5; $i++) {
        $stars .= wesley2_star_svg();
    }

    return '<span class="wesley2-stars ' . wesley2_e($sizeClass) . '" aria-hidden="true">'
        . '<span class="wesley2-stars-base">' . $stars . '</span>'
        . '<span class="wesley2-stars-fill" style="width:' . $pct . '%">' . $stars . '</span>'
        . '</span>';
}

try {
    $app = new App($rootDir);
    $userRepo = new UserRepository($app->db);

    $userId = $app->session->userId();
    $user = $userId !== null ? $userRepo->findById($userId) : null;

    if ($user === null) {
        Response::redirect($app->request->url('/login'))->send();
        exit;
    }

    $locale = $app->i18n->locale();
    $texts = wesley2_texts($locale);
    $pageUrl = $app->request->url('/wesley2');

    // Seed initial demo reviews on first visit if user has zero rows (including soft-deleted).
    if (!$app->request->isPost()) {
        wesley2_seed_dummy_reviews($app->db, $user->id, $locale, true);
    }

    if ($app->request->isPost()) {
        $csrfRaw = $app->request->post('_csrf');
        $csrfToken = is_string($csrfRaw) ? $csrfRaw : null;

        if (!$app->csrf->verify($csrfToken)) {
            $app->session->flash('error', $app->i18n->t('error.csrf'));
            Response::redirect($pageUrl)->send();
            exit;
        }

        $action = (string) $app->request->post('action', '');
        $now = date('Y-m-d H:i:s');

        if ($action === 'create') {
            $validation = wesley2_validate_review_input(
                (string) $app->request->post('reviewer_name', ''),
                (string) $app->request->post('title', ''),
                $app->request->post('rating'),
                (string) $app->request->post('comment', ''),
                $texts
            );

            if (!$validation['ok']) {
                $app->session->set('_wesley2_old', array_merge(['mode' => 'create'], $validation['data']));
                $app->session->flash('error', $validation['error']);
                Response::redirect($pageUrl)->send();
                exit;
            }

            $data = $validation['data'];
            $app->db->execute(
                'INSERT INTO wesley2_reviews (
                    user_id, reviewer_name, title, rating, comment, is_demo, created_at, updated_at, deleted_at
                ) VALUES (?, ?, ?, ?, ?, 0, ?, ?, NULL)',
                [
                    $user->id,
                    $data['reviewer_name'],
                    $data['title'],
                    $data['rating'],
                    $data['comment'],
                    $now,
                    $now,
                ]
            );

            $app->session->flash('success', $texts['flash_created']);
            Response::redirect($pageUrl)->send();
            exit;
        }

        if ($action === 'update') {
            $id = (int) $app->request->post('id', 0);
            $existing = $id > 0 ? $app->db->fetchOne(
                'SELECT id FROM wesley2_reviews WHERE id = ? AND user_id = ? AND deleted_at IS NULL LIMIT 1',
                [$id, $user->id]
            ) : null;

            if ($existing === null) {
                $app->session->flash('error', $app->i18n->t('error.not_found'));
                Response::redirect($pageUrl)->send();
                exit;
            }

            $validation = wesley2_validate_review_input(
                (string) $app->request->post('reviewer_name', ''),
                (string) $app->request->post('title', ''),
                $app->request->post('rating'),
                (string) $app->request->post('comment', ''),
                $texts
            );

            if (!$validation['ok']) {
                $app->session->set('_wesley2_old', array_merge(['mode' => 'edit', 'id' => $id], $validation['data']));
                $app->session->flash('error', $validation['error']);
                Response::redirect($app->request->url('/wesley2', ['edit' => $id]))->send();
                exit;
            }

            $data = $validation['data'];
            $app->db->execute(
                'UPDATE wesley2_reviews
                 SET reviewer_name = ?, title = ?, rating = ?, comment = ?, updated_at = ?
                 WHERE id = ? AND user_id = ? AND deleted_at IS NULL',
                [
                    $data['reviewer_name'],
                    $data['title'],
                    $data['rating'],
                    $data['comment'],
                    $now,
                    $id,
                    $user->id,
                ]
            );

            $app->session->flash('success', $texts['flash_updated']);
            Response::redirect($pageUrl)->send();
            exit;
        }

        if ($action === 'delete') {
            $id = (int) $app->request->post('id', 0);
            $filters = wesley2_parse_filters($app);
            $redirectUrl = $app->request->url('/wesley2', wesley2_filter_query($filters));

            $existing = $id > 0 ? $app->db->fetchOne(
                'SELECT id FROM wesley2_reviews WHERE id = ? AND user_id = ? AND deleted_at IS NULL LIMIT 1',
                [$id, $user->id]
            ) : null;

            if ($existing === null) {
                $app->session->flash('error', $app->i18n->t('error.not_found'));
                Response::redirect($redirectUrl)->send();
                exit;
            }

            $app->db->execute(
                'UPDATE wesley2_reviews
                 SET deleted_at = ?, updated_at = ?
                 WHERE id = ? AND user_id = ? AND deleted_at IS NULL',
                [$now, $now, $id, $user->id]
            );

            $app->session->flash('success', $texts['flash_deleted']);
            Response::redirect($redirectUrl)->send();
            exit;
        }

        if ($action === 'seed_demo') {
            $seeded = wesley2_seed_dummy_reviews($app->db, $user->id, $locale, false);
            if ($seeded > 0) {
                $app->session->flash('success', wesley2_t($texts, 'flash_demo_loaded', ['count' => $seeded]));
            } else {
                $app->session->flash('info', $texts['flash_demo_already']);
            }
            Response::redirect($pageUrl)->send();
            exit;
        }

        $app->session->flash('error', $texts['err_unknown_action']);
        Response::redirect($pageUrl)->send();
        exit;
    }

    // Consume old input from validation errors if present.
    $oldInputRaw = $app->session->get('_wesley2_old');
    $app->session->remove('_wesley2_old');
    $oldInput = is_array($oldInputRaw) ? $oldInputRaw : null;

    $filters = wesley2_parse_filters($app);
    $activeFilterQuery = wesley2_filter_query($filters);
    $editId = (int) $app->request->query('edit', 0);

    // Aggregate statistics across all active reviews for this merchant.
    $statRows = $app->db->fetchAll(
        'SELECT rating, COUNT(*) AS cnt
         FROM wesley2_reviews
         WHERE user_id = ? AND deleted_at IS NULL
         GROUP BY rating',
        [$user->id]
    );

    $starCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
    $totalCount = 0;
    $ratingSum = 0;
    foreach ($statRows as $statRow) {
        $r = (int) ($statRow['rating'] ?? 0);
        $cnt = (int) ($statRow['cnt'] ?? 0);
        if ($r >= 1 && $r <= 5 && $cnt > 0) {
            $starCounts[$r] = $cnt;
            $totalCount += $cnt;
            $ratingSum += ($r * $cnt);
        }
    }
    $averageRating = $totalCount > 0 ? round($ratingSum / $totalCount, 1) : 0.0;

    // Build parameterized filtered query with strict sort allowlist.
    $where = ['user_id = ?', 'deleted_at IS NULL'];
    $params = [$user->id];

    if ($filters['rating'] !== null) {
        $where[] = 'rating = ?';
        $params[] = $filters['rating'];
    }

    if ($filters['q'] !== '') {
        $escapedQ = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['q']);
        $like = '%' . $escapedQ . '%';
        $where[] = '(LOWER(reviewer_name) LIKE LOWER(?) ESCAPE \'!\' OR LOWER(title) LIKE LOWER(?) ESCAPE \'!\' OR LOWER(comment) LIKE LOWER(?) ESCAPE \'!\')';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    $orderBy = match ($filters['sort']) {
        'oldest' => 'created_at ASC, id ASC',
        'highest' => 'rating DESC, created_at DESC, id DESC',
        'lowest' => 'rating ASC, created_at DESC, id DESC',
        default => 'created_at DESC, id DESC',
    };

    $reviews = $app->db->fetchAll(
        'SELECT id, reviewer_name, title, rating, comment, is_demo, created_at, updated_at
         FROM wesley2_reviews
         WHERE ' . implode(' AND ', $where) . '
         ORDER BY ' . $orderBy,
        $params
    );

    $editingReview = null;
    if ($editId > 0) {
        $editingReview = $app->db->fetchOne(
            'SELECT id, reviewer_name, title, rating, comment
             FROM wesley2_reviews
             WHERE id = ? AND user_id = ? AND deleted_at IS NULL
             LIMIT 1',
            [$editId, $user->id]
        );
    }

    // Prepare form values from editing row or failed POST old input.
    $isEditMode = $editingReview !== null;
    $formValues = [
        'reviewer_name' => $isEditMode ? (string) $editingReview['reviewer_name'] : '',
        'title' => $isEditMode ? (string) $editingReview['title'] : '',
        'rating' => $isEditMode ? (int) $editingReview['rating'] : 5,
        'comment' => $isEditMode ? (string) $editingReview['comment'] : '',
    ];

    if ($oldInput !== null) {
        $oldMode = (string) ($oldInput['mode'] ?? '');
        $matchesCreate = !$isEditMode && $oldMode === 'create';
        $matchesEdit = $isEditMode && $oldMode === 'edit' && (int) ($oldInput['id'] ?? 0) === (int) $editingReview['id'];
        if ($matchesCreate || $matchesEdit) {
            $oldRating = (int) ($oldInput['rating'] ?? 5);
            $formValues = [
                'reviewer_name' => (string) ($oldInput['reviewer_name'] ?? ''),
                'title' => (string) ($oldInput['title'] ?? ''),
                'rating' => ($oldRating >= 1 && $oldRating <= 5) ? $oldRating : 5,
                'comment' => (string) ($oldInput['comment'] ?? ''),
            ];
        }
    }

    $hasActiveFilters = $filters['rating'] !== null || $filters['q'] !== '' || $filters['sort'] !== 'newest';
    $csrfHtml = $app->csrf->field();

    ob_start();
    ?>
<style>
.wesley2-top-grid {
    display: grid;
    grid-template-columns: 0.95fr 1.05fr;
    gap: 24px;
    align-items: stretch;
    margin-bottom: 24px;
}
.wesley2-left-col {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.wesley2-eyebrow {
    margin-bottom: 8px;
}
.wesley2-subtitle {
    margin: 0;
    color: var(--ink-600);
}
.wesley2-header-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}
.wesley2-summary-head {
    display: flex;
    align-items: center;
    gap: 18px;
    padding-bottom: 18px;
    margin-bottom: 18px;
    border-bottom: 1px solid var(--border);
}
.wesley2-avg-score {
    font-size: 2.75rem;
    font-weight: 800;
    line-height: 1;
    color: var(--pine-950);
    letter-spacing: -0.03em;
}
.wesley2-avg-denom {
    font-size: 1rem;
    font-weight: 600;
    color: var(--ink-400);
    margin-left: 2px;
}
.wesley2-avg-meta {
    display: grid;
    gap: 6px;
}
.wesley2-avg-count {
    font-size: 0.9rem;
    color: var(--ink-600);
    font-weight: 600;
}
.wesley2-stars {
    position: relative;
    display: inline-flex;
    width: max-content;
    justify-self: start;
    line-height: 0;
    vertical-align: middle;
}
.wesley2-stars-base,
.wesley2-stars-fill {
    display: inline-flex;
    gap: 3px;
}
.wesley2-stars-base .wesley2-star-svg {
    fill: #ded8cb;
}
.wesley2-stars-fill {
    position: absolute;
    top: 0;
    left: 0;
    overflow: hidden;
    white-space: nowrap;
}
.wesley2-stars-fill .wesley2-star-svg {
    fill: var(--gold-500);
    flex-shrink: 0;
}
.wesley2-star-svg {
    width: 18px;
    height: 18px;
    display: block;
    flex-shrink: 0;
}
.wesley2-stars-lg .wesley2-star-svg {
    width: 22px;
    height: 22px;
}
.wesley2-breakdown {
    display: grid;
    gap: 8px;
}
.wesley2-bar-row {
    display: grid;
    grid-template-columns: 74px 1fr 34px;
    gap: 10px;
    align-items: center;
    padding: 6px 8px;
    border-radius: 9px;
    color: var(--ink-900);
    text-decoration: none;
    transition: background-color 0.15s ease;
}
.wesley2-bar-row:hover {
    background: var(--cream-50);
    text-decoration: none;
}
.wesley2-bar-row.is-active {
    background: var(--gold-100);
    outline: 1px solid #e6c993;
}
.wesley2-bar-label {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--pine-900);
    white-space: nowrap;
}
.wesley2-bar-track {
    height: 10px;
    background: var(--cream-100);
    border-radius: 999px;
    overflow: hidden;
}
.wesley2-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--gold-500), var(--gold-600));
    border-radius: 999px;
}
.wesley2-bar-count {
    text-align: right;
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--ink-600);
}
.wesley2-card-title-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}
.wesley2-card-title-row h2 {
    margin: 0;
    font-size: 1.2rem;
    color: var(--pine-950);
}
.wesley2-star-input {
    position: relative;
    display: inline-flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 6px;
    padding: 4px 0;
}
.wesley2-star-input input {
    position: absolute;
    opacity: 0;
    width: 1px;
    height: 1px;
    pointer-events: none;
}
.wesley2-star-input label {
    cursor: pointer;
    display: inline-flex;
    padding: 4px;
    border-radius: 8px;
    color: #d8d1c2;
    transition: color 0.12s ease, transform 0.12s ease;
}
.wesley2-star-input label .wesley2-star-svg {
    width: 28px;
    height: 28px;
    fill: currentColor;
}
.wesley2-star-input input:checked ~ label,
.wesley2-star-input label:hover,
.wesley2-star-input label:hover ~ label {
    color: var(--gold-500);
}
.wesley2-star-input label:hover {
    transform: scale(1.08);
}
.wesley2-star-input input:focus-visible + label {
    outline: 2px solid var(--pine-900);
    outline-offset: 2px;
}
.wesley2-form-card {
    display: flex;
    flex-direction: column;
}
.wesley2-form-card form {
    display: flex;
    flex-direction: column;
    flex: 1;
}
.wesley2-comment-group {
    display: flex;
    flex-direction: column;
    flex: 1;
}
.wesley2-comment-group .form-textarea {
    flex: 1;
    min-height: 120px;
    resize: vertical;
}
.wesley2-form-submit {
    align-self: flex-start;
    margin-top: 4px;
}
.wesley2-toolbar {
    padding: 20px 22px;
    margin-bottom: 0;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.wesley2-filter-form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    align-items: end;
    flex: 1;
}
.wesley2-filter-search {
    grid-column: 1 / -1;
}
.wesley2-filter-actions {
    grid-column: 1 / -1;
    display: flex;
    gap: 8px;
    align-items: center;
    margin-top: 2px;
}
.wesley2-results-meta {
    margin: 0 0 14px;
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--ink-600);
}
.wesley2-list {
    display: grid;
    gap: 14px;
}
.wesley2-review-card {
    padding: 20px 22px;
    transition: border-color 0.15s ease;
}
.wesley2-review-card.is-editing {
    border-color: var(--gold-500);
    background: #fffdf9;
}
.wesley2-review-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 10px;
}
.wesley2-rating-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.wesley2-score-text {
    font-weight: 800;
    font-size: 0.88rem;
    color: var(--pine-900);
}
.wesley2-review-date {
    font-size: 0.84rem;
    color: var(--ink-400);
    font-weight: 600;
}
.wesley2-review-title {
    margin: 0 0 6px;
    font-size: 1.04rem;
    color: var(--pine-950);
}
.wesley2-review-comment {
    margin: 0 0 14px;
    color: var(--ink-900);
    font-size: 0.95rem;
    line-height: 1.6;
}
.wesley2-review-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    padding-top: 12px;
    border-top: 1px solid var(--cream-100);
}
.wesley2-author {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    font-weight: 700;
    font-size: 0.92rem;
    color: var(--pine-900);
}
.wesley2-avatar {
    width: 30px;
    height: 30px;
    border-radius: 999px;
    background: var(--pine-100);
    color: var(--pine-900);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    font-weight: 800;
}
.wesley2-review-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}
.wesley2-empty {
    text-align: center;
    padding: 36px 24px;
}
.wesley2-empty h3 {
    margin: 0 0 8px;
    color: var(--pine-950);
}
.wesley2-empty p {
    margin: 0 0 18px;
    color: var(--ink-600);
}
@media (max-width: 960px) {
    .wesley2-top-grid,
    .wesley2-filter-form {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="container app-shell wesley2-page">
    <div class="page-top">
        <div>
            <span class="badge badge-pine wesley2-eyebrow"><?= wesley2_e($texts['eyebrow']) ?></span>
            <h1><?= wesley2_e($texts['page_title']) ?></h1>
            <p class="wesley2-subtitle"><?= wesley2_e(wesley2_t($texts, 'subtitle', ['business' => $user->businessName])) ?></p>
        </div>
        <div class="wesley2-header-actions">
            <form method="post" action="<?= wesley2_e($pageUrl) ?>" style="margin:0;">
                <?= $csrfHtml ?>
                <input type="hidden" name="action" value="seed_demo">
                <button type="submit" class="btn btn-outline btn-sm"><?= wesley2_e($texts['reload_demo']) ?></button>
            </form>
            <a href="<?= wesley2_e($app->request->url('/app')) ?>" class="btn btn-outline btn-sm"><?= wesley2_e($texts['back_dashboard']) ?></a>
        </div>
    </div>

    <div class="wesley2-top-grid">
        <div class="wesley2-left-col">
            <section class="card wesley2-summary-card" aria-labelledby="wesley2-summary-heading">
                <div class="wesley2-card-title-row">
                    <h2 id="wesley2-summary-heading"><?= wesley2_e($texts['summary_title']) ?></h2>
                    <?php if ($filters['rating'] !== null): ?>
                        <a href="<?= wesley2_e($app->request->url('/wesley2', wesley2_filter_query(['rating' => null, 'sort' => $filters['sort'], 'q' => $filters['q']]))) ?>" class="badge badge-gold">
                            <?= wesley2_e($filters['rating'] === 1 ? $texts['star_label_one'] : wesley2_t($texts, 'star_label_plural', ['n' => $filters['rating']])) ?> &times;
                        </a>
                    <?php endif; ?>
                </div>

                <div class="wesley2-summary-head">
                    <div class="wesley2-avg-score">
                        <?= wesley2_e(number_format($averageRating, 1, '.', '')) ?><span class="wesley2-avg-denom">/5</span>
                    </div>
                    <div class="wesley2-avg-meta">
                        <?= wesley2_render_stars_visual($averageRating, 'wesley2-stars-lg') ?>
                        <div class="wesley2-avg-count">
                            <?php if ($totalCount === 0): ?>
                                <?= wesley2_e($texts['summary_empty']) ?>
                            <?php elseif ($totalCount === 1): ?>
                                <?= wesley2_e($texts['summary_based_on_one']) ?>
                            <?php else: ?>
                                <?= wesley2_e(wesley2_t($texts, 'summary_based_on', ['count' => $totalCount])) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="wesley2-breakdown">
                    <?php for ($star = 5; $star >= 1; $star--): ?>
                        <?php
                        $cnt = $starCounts[$star];
                        $barPct = $totalCount > 0 ? round(($cnt / $totalCount) * 100, 1) : 0.0;
                        $isCurrentStar = $filters['rating'] === $star;
                        $targetQuery = wesley2_filter_query([
                            'rating' => $isCurrentStar ? null : $star,
                            'sort' => $filters['sort'],
                            'q' => $filters['q'],
                        ]);
                        $starLabel = $star === 1
                            ? $texts['star_label_one']
                            : wesley2_t($texts, 'star_label_plural', ['n' => $star]);
                        ?>
                        <a href="<?= wesley2_e($app->request->url('/wesley2', $targetQuery)) ?>"
                           class="wesley2-bar-row <?= $isCurrentStar ? 'is-active' : '' ?>"
                           title="<?= wesley2_e($isCurrentStar ? $texts['filter_star_clear'] : $starLabel) ?>">
                            <span class="wesley2-bar-label"><?= wesley2_e($starLabel) ?></span>
                            <span class="wesley2-bar-track">
                                <span class="wesley2-bar-fill" style="display:block;width:<?= $barPct ?>%"></span>
                            </span>
                            <span class="wesley2-bar-count"><?= wesley2_e((string) $cnt) ?></span>
                        </a>
                    <?php endfor; ?>
                </div>
            </section>

            <section class="card wesley2-toolbar">
                <form method="get" action="<?= wesley2_e($pageUrl) ?>" class="wesley2-filter-form">
                    <div class="wesley2-filter-search">
                        <label class="form-label" for="wesley2-q"><?= wesley2_e($texts['filter_search_label']) ?></label>
                        <input id="wesley2-q"
                               class="form-control"
                               type="search"
                               name="q"
                               value="<?= wesley2_e($filters['q']) ?>"
                               placeholder="<?= wesley2_e($texts['filter_search_ph']) ?>">
                    </div>
                    <div>
                        <label class="form-label" for="wesley2-filter-rating"><?= wesley2_e($texts['filter_rating_label']) ?></label>
                        <select id="wesley2-filter-rating" class="form-select" name="rating" data-autosubmit>
                            <option value=""><?= wesley2_e($texts['filter_rating_all']) ?></option>
                            <?php for ($s = 5; $s >= 1; $s--): ?>
                                <?php $optLabel = $s === 1 ? $texts['star_label_one'] : wesley2_t($texts, 'star_label_plural', ['n' => $s]); ?>
                                <option value="<?= $s ?>" <?= $filters['rating'] === $s ? 'selected' : '' ?>><?= wesley2_e($optLabel) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="wesley2-sort"><?= wesley2_e($texts['filter_sort_label']) ?></label>
                        <select id="wesley2-sort" class="form-select" name="sort" data-autosubmit>
                            <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>><?= wesley2_e($texts['sort_newest']) ?></option>
                            <option value="oldest" <?= $filters['sort'] === 'oldest' ? 'selected' : '' ?>><?= wesley2_e($texts['sort_oldest']) ?></option>
                            <option value="highest" <?= $filters['sort'] === 'highest' ? 'selected' : '' ?>><?= wesley2_e($texts['sort_highest']) ?></option>
                            <option value="lowest" <?= $filters['sort'] === 'lowest' ? 'selected' : '' ?>><?= wesley2_e($texts['sort_lowest']) ?></option>
                        </select>
                    </div>
                    <div class="wesley2-filter-actions">
                        <button type="submit" class="btn btn-primary btn-sm"><?= wesley2_e($texts['filter_apply']) ?></button>
                        <?php if ($hasActiveFilters): ?>
                            <a href="<?= wesley2_e($pageUrl) ?>" class="btn btn-outline btn-sm"><?= wesley2_e($texts['filter_reset']) ?></a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>
        </div>

        <section class="card wesley2-form-card" aria-labelledby="wesley2-form-heading">
            <div class="wesley2-card-title-row">
                <h2 id="wesley2-form-heading">
                    <?= wesley2_e($isEditMode ? $texts['form_edit_title'] : $texts['form_add_title']) ?>
                </h2>
                <?php if ($isEditMode): ?>
                    <a href="<?= wesley2_e($app->request->url('/wesley2', $activeFilterQuery)) ?>" class="btn btn-outline btn-sm">
                        <?= wesley2_e($texts['form_cancel_edit']) ?>
                    </a>
                <?php endif; ?>
            </div>

            <form method="post" action="<?= wesley2_e($pageUrl) ?>">
                <?= $csrfHtml ?>
                <input type="hidden" name="action" value="<?= $isEditMode ? 'update' : 'create' ?>">
                <?php if ($isEditMode): ?>
                    <input type="hidden" name="id" value="<?= wesley2_e((string) $editingReview['id']) ?>">
                <?php endif; ?>

                <div class="form-group">
                    <span class="form-label"><?= wesley2_e($texts['field_rating']) ?></span>
                    <div class="wesley2-star-input" role="radiogroup" aria-label="<?= wesley2_e($texts['field_rating']) ?>">
                        <?php for ($r = 5; $r >= 1; $r--): ?>
                            <?php $inputId = 'wesley2-rating-' . $r; ?>
                            <input type="radio"
                                   id="<?= wesley2_e($inputId) ?>"
                                   name="rating"
                                   value="<?= $r ?>"
                                   <?= (int) $formValues['rating'] === $r ? 'checked' : '' ?>
                                   required>
                            <label for="<?= wesley2_e($inputId) ?>" title="<?= $r ?> / 5">
                                <?= wesley2_star_svg() ?>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="wesley2-reviewer-name"><?= wesley2_e($texts['field_name']) ?></label>
                    <input id="wesley2-reviewer-name"
                           class="form-control"
                           type="text"
                           name="reviewer_name"
                           maxlength="120"
                           required
                           value="<?= wesley2_e($formValues['reviewer_name']) ?>"
                           placeholder="<?= wesley2_e($texts['field_name_ph']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="wesley2-title"><?= wesley2_e($texts['field_title']) ?></label>
                    <input id="wesley2-title"
                           class="form-control"
                           type="text"
                           name="title"
                           maxlength="160"
                           value="<?= wesley2_e($formValues['title']) ?>"
                           placeholder="<?= wesley2_e($texts['field_title_ph']) ?>">
                </div>

                <div class="form-group wesley2-comment-group">
                    <label class="form-label" for="wesley2-comment"><?= wesley2_e($texts['field_comment']) ?></label>
                    <textarea id="wesley2-comment"
                              class="form-textarea"
                              name="comment"
                              rows="3"
                              maxlength="1500"
                              required
                              placeholder="<?= wesley2_e($texts['field_comment_ph']) ?>"><?= wesley2_e($formValues['comment']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary wesley2-form-submit">
                    <?= wesley2_e($isEditMode ? $texts['submit_edit'] : $texts['submit_add']) ?>
                </button>
            </form>
        </section>
    </div>

    <?php if ($totalCount > 0): ?>
        <p class="wesley2-results-meta">
            <?= wesley2_e(wesley2_t($texts, 'showing_count', ['shown' => count($reviews), 'total' => $totalCount])) ?>
        </p>
    <?php endif; ?>

    <?php if ($reviews === []): ?>
        <div class="card wesley2-empty">
            <?php if ($totalCount > 0 && $hasActiveFilters): ?>
                <h3><?= wesley2_e($texts['empty_filtered_title']) ?></h3>
                <p><?= wesley2_e($texts['empty_filtered_desc']) ?></p>
                <a href="<?= wesley2_e($pageUrl) ?>" class="btn btn-outline btn-sm"><?= wesley2_e($texts['filter_reset']) ?></a>
            <?php else: ?>
                <h3><?= wesley2_e($texts['empty_none_title']) ?></h3>
                <p><?= wesley2_e($texts['empty_none_desc']) ?></p>
                <form method="post" action="<?= wesley2_e($pageUrl) ?>" style="display:inline-block;margin:0;">
                    <?= $csrfHtml ?>
                    <input type="hidden" name="action" value="seed_demo">
                    <button type="submit" class="btn btn-gold btn-sm"><?= wesley2_e($texts['reload_demo']) ?></button>
                </form>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="wesley2-list">
            <?php foreach ($reviews as $review): ?>
                <?php
                $revId = (int) $review['id'];
                $revRating = (int) $review['rating'];
                $revName = (string) $review['reviewer_name'];
                $revTitle = trim((string) ($review['title'] ?? ''));
                $revComment = (string) $review['comment'];
                $revIsDemo = (int) ($review['is_demo'] ?? 0) === 1;
                $createdRaw = (string) $review['created_at'];
                $createdTs = strtotime($createdRaw);
                $formattedDate = $createdTs !== false ? date('d/m/Y H:i', $createdTs) : $createdRaw;
                $initial = $revName !== ''
                    ? (function_exists('mb_substr')
                        ? mb_strtoupper(mb_substr($revName, 0, 1, 'UTF-8'), 'UTF-8')
                        : strtoupper(substr($revName, 0, 1)))
                    : '?';
                $editHref = $app->request->url('/wesley2', array_merge($activeFilterQuery, ['edit' => $revId]));
                $deleteHref = $app->request->url('/wesley2', $activeFilterQuery);
                ?>
                <article class="card wesley2-review-card <?= ($editingReview !== null && (int) $editingReview['id'] === $revId) ? 'is-editing' : '' ?>">
                    <div class="wesley2-review-top">
                        <div class="wesley2-rating-pill">
                            <?= wesley2_render_stars_visual((float) $revRating) ?>
                            <span class="wesley2-score-text"><?= $revRating ?> / 5</span>
                            <?php if ($revIsDemo): ?>
                                <span class="badge badge-gold"><?= wesley2_e($texts['demo_badge']) ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="wesley2-review-date"><?= wesley2_e($formattedDate) ?></span>
                    </div>

                    <?php if ($revTitle !== ''): ?>
                        <h3 class="wesley2-review-title"><?= wesley2_e($revTitle) ?></h3>
                    <?php endif; ?>

                    <p class="wesley2-review-comment"><?= nl2br(wesley2_e($revComment)) ?></p>

                    <div class="wesley2-review-bottom">
                        <div class="wesley2-author">
                            <span class="wesley2-avatar" aria-hidden="true"><?= wesley2_e($initial) ?></span>
                            <span><?= wesley2_e($revName) ?></span>
                        </div>
                        <div class="wesley2-review-actions">
                            <a href="<?= wesley2_e($editHref) ?>" class="btn btn-outline btn-sm"><?= wesley2_e($texts['edit_btn']) ?></a>
                            <form method="post" action="<?= wesley2_e($deleteHref) ?>" style="margin:0;">
                                <?= $csrfHtml ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $revId ?>">
                                <button type="submit"
                                        class="btn btn-danger btn-sm"
                                        data-confirm="<?= wesley2_e($texts['confirm_delete']) ?>">
                                    <?= wesley2_e($texts['delete_btn']) ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
    <?php
    $content = (string) ob_get_clean();

    $html = $app->view->renderPartial('layouts/base', [
        'pageTitle' => $texts['page_title'],
        'user' => $user,
        'content' => $content,
    ]);

    Response::html($html)->send();
} catch (Throwable $e) {
    http_response_code(500);
    $logDir = $rootDir . '/storage/logs';
    if (is_dir($logDir) || @mkdir($logDir, 0775, true)) {
        @error_log(
            '[' . date('Y-m-d H:i:s') . '] ' . get_class($e) . ': ' . $e->getMessage()
            . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n",
            3,
            $logDir . '/app.log'
        );
    }
    $isLocal = isset($app) && $app->config->isDebug();
    if ($isLocal) {
        echo '<pre style="font-family:monospace;padding:24px;">'
            . htmlspecialchars($e->getMessage() . "\n\n" . $e->getTraceAsString(), ENT_QUOTES, 'UTF-8')
            . '</pre>';
    } else {
        echo '<h1>Er ging iets mis</h1><p>Probeer het later opnieuw.</p>';
    }
}
