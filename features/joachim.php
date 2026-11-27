<?php

declare(strict_types=1);

use App\Core\Response;

$joachimLocale = $app->i18n->locale() === 'fr' ? 'fr' : 'nl';
$joachimMessages = [
    'nl' => [
        'login_title' => 'Nieuwsbrief maken',
        'login_message' => 'Log in om je nieuwsbrieven te beheren.',
        'login_link' => 'Inloggen',
        'hero_eyebrow' => 'JOUW VERHAAL, MOOI VERSTUURD',
        'hero_title' => 'Nieuwsbriefstudio',
        'hero_description' => 'Maak een verzorgd nieuwsbriefconcept voor je klanten.',
        'status' => 'Concepten',
        'editor_tab' => 'Nieuwsbrieven',
        'recipients_tab' => 'Testmailinglijst',
        'manager_eyebrow' => 'TESTONTVANGERS BEHEREN',
        'manager_title' => 'Testmailinglijst',
        'manager_status' => 'TESTMODUS',
        'manager_description' => 'Beheer je fictieve testontvangers apart van je nieuwsbriefconcepten.',
        'manager_nav_label' => 'Nieuwsbriefonderdelen',
        'list_title' => 'Demo-ontvangers',
        'test_data_notice' => 'Alleen testgegevens. Adressen eindigen op @example.test en er wordt niets verstuurd.',
        'recipient_count_one' => 'contact',
        'recipient_count_many' => 'contacten',
        'active_count' => 'ingeschreven',
        'name_label' => 'Naam',
        'name_placeholder' => 'Bijvoorbeeld: Alex Martin',
        'email_label' => 'E-mailadres',
        'email_placeholder' => 'alex@example.test',
        'email_hint' => 'Gebruik alleen fictieve adressen op het domein @example.test.',
        'add_recipient' => 'Contact toevoegen',
        'save_recipient' => 'Wijzigingen bewaren',
        'cancel' => 'Annuleren',
        'load_samples' => 'Voorbeeldcontacten toevoegen',
        'samples_loaded' => 'Voorbeeldcontacten zijn toegevoegd of stonden al in de lijst.',
        'recipients_empty_title' => 'Je testlijst is nog leeg',
        'recipients_empty_message' => 'Voeg een fictief contact toe of laad de voorbeeldcontacten om de lijstweergave te testen.',
        'status_label' => 'Status',
        'status_subscribed' => 'Ingeschreven',
        'status_unsubscribed' => 'Uitgeschreven',
        'unsubscribe_action' => 'Uitschrijven',
        'resubscribe_action' => 'Opnieuw inschrijven',
        'edit_recipient' => 'Bewerken',
        'delete_recipient' => 'Verwijderen',
        'confirm_delete_recipient' => 'Dit testcontact verwijderen?',
        'error_recipient_type' => 'Vul een geldige naam en een geldig e-mailadres in.',
        'error_recipient_required' => 'Naam en e-mailadres zijn verplicht.',
        'error_recipient_name_length' => 'De naam mag maximaal 100 tekens bevatten.',
        'error_recipient_email' => 'Gebruik een geldig e-mailadres op het domein @example.test.',
        'error_recipient_duplicate' => 'Dit e-mailadres staat al in je testlijst.',
        'error_recipient_not_found' => 'Dit testcontact kon niet worden gevonden.',
        'error_recipient_status' => 'Ongeldige status voor het testcontact.',
        'recipient_added' => 'Testcontact toegevoegd.',
        'recipient_saved' => 'Testcontact bijgewerkt.',
        'recipient_deleted' => 'Testcontact verwijderd.',
        'recipient_status_updated' => 'Inschrijfstatus bijgewerkt.',
        'new_edition' => 'NIEUWE EDITIE',
        'editor_new' => 'Stel je nieuwsbrief samen',
        'editor_edit' => 'Bewerk je concept',
        'new_draft' => 'Nieuw concept',
        'error_delete' => 'Deze nieuwsbrief kon niet worden verwijderd.',
        'deleted' => 'Concept verwijderd.',
        'not_found' => 'Deze nieuwsbrief kon niet worden gevonden.',
        'unknown_action' => 'Onbekende actie voor de nieuwsbrief.',
        'error_edit' => 'Deze nieuwsbrief kan niet worden bewerkt.',
        'error_field_type' => 'Vul geldige tekst in alle velden in.',
        'error_required' => 'Vul minstens het onderwerp, de titel en de inhoud in.',
        'error_too_long' => 'Een of meer velden zijn te lang. Controleer de aangegeven limieten.',
        'error_cta_pair' => 'Vul voor de knop zowel een tekst als een link in, of laat beide leeg.',
        'error_cta_url' => 'De knoplink moet een volledige http- of https-link zijn.',
        'saved' => 'Concept bewaard.',
        'required' => 'Verplicht',
        'subject' => 'Onderwerp',
        'subject_placeholder' => 'Bijvoorbeeld: Een frisse start voor de lente',
        'subject_hint' => 'De onderwerpregel die je klant later in de inbox ziet.',
        'preheader' => 'Korte inboxtekst',
        'preheader_placeholder' => 'Een korte aanvulling op je onderwerp',
        'headline' => 'Titel in de nieuwsbrief',
        'headline_placeholder' => 'Wat wil je deze keer vertellen?',
        'body' => 'Bericht',
        'body_placeholder' => 'Schrijf je nieuwsbrief. Gebruik een lege regel voor een nieuwe alinea.',
        'body_count_suffix' => ' / 10.000 tekens. Je opmaak blijft eenvoudig en leesbaar op mobiel.',
        'cta_section' => 'Optionele knop',
        'cta_label' => 'Knoptekst',
        'cta_label_placeholder' => 'Bekijk het menu',
        'cta_url' => 'Knoplink',
        'cta_url_placeholder' => 'https://voorbeeld.be/menu',
        'cta_url_hint' => 'Gebruik een volledige link die met http:// of https:// begint.',
        'save_draft' => 'Bewaar concept',
        'publish' => 'Verzenden (binnenkort)',
        'publish_help' => 'Verzenden wordt beschikbaar zodra de klantenlijst en mailingkoppeling bestaan.',
        'preview_eyebrow' => 'ZO ZIET HET ERUIT',
        'preview_title' => 'Voorbeeld',
        'email' => 'E-mail',
        'preview_subject_default' => 'Je nieuwsbriefonderwerp',
        'preview_preheader_default' => 'Een persoonlijk bericht van jou',
        'preview_headline_default' => 'Jouw titel komt hier',
        'preview_body_default' => 'Je nieuwsbriefbericht verschijnt hier zodra je begint met schrijven.',
        'email_kicker' => 'EEN BERICHT VAN JOU',
        'email_brand_label' => 'NIEUWSBRIEF',
        'email_footer' => 'Met zorg samengesteld in XOrder',
        'preview_note' => 'Dit is een voorbeeld. Er wordt nog geen e-mail verstuurd.',
        'drafts_eyebrow' => 'BEWAARD VOOR LATER',
        'drafts_title' => 'Jouw concepten',
        'draft_count_one' => 'concept',
        'draft_count_many' => 'concepten',
        'empty_title' => 'Je eerste nieuwsbrief begint hier',
        'empty_message' => 'Vul de editor in en bewaar je eerste concept om het later verder aan te passen.',
        'draft_tag' => 'CONCEPT',
        'updated_at' => 'Laatst bewerkt',
        'edit' => 'Bewerken',
        'delete' => 'Verwijderen',
        'confirm_delete' => 'Dit concept verwijderen?',
    ],
    'fr' => [
        'login_title' => 'Créer une newsletter',
        'login_message' => 'Connectez-vous pour gérer vos newsletters.',
        'login_link' => 'Se connecter',
        'hero_eyebrow' => 'VOTRE HISTOIRE, BIEN PRÉSENTÉE',
        'hero_title' => 'Studio newsletter',
        'hero_description' => 'Créez un brouillon de newsletter soigné pour vos clients.',
        'status' => 'Brouillons',
        'editor_tab' => 'Newsletters',
        'recipients_tab' => 'Liste de test',
        'manager_eyebrow' => 'GESTION DES DESTINATAIRES DE TEST',
        'manager_title' => 'Liste de test',
        'manager_status' => 'MODE TEST',
        'manager_description' => 'Gérez vos destinataires fictifs séparément de vos brouillons de newsletter.',
        'manager_nav_label' => 'Sections de newsletter',
        'list_title' => 'Destinataires de démonstration',
        'test_data_notice' => 'Données de test uniquement. Les adresses se terminent par @example.test et aucun message ne sera envoyé.',
        'recipient_count_one' => 'contact',
        'recipient_count_many' => 'contacts',
        'active_count' => 'inscrits',
        'name_label' => 'Nom',
        'name_placeholder' => 'Par exemple : Alex Martin',
        'email_label' => 'Adresse e-mail',
        'email_placeholder' => 'alex@example.test',
        'email_hint' => 'Utilisez uniquement des adresses fictives sur le domaine @example.test.',
        'add_recipient' => 'Ajouter un contact',
        'save_recipient' => 'Enregistrer les modifications',
        'cancel' => 'Annuler',
        'load_samples' => 'Ajouter des contacts de démonstration',
        'samples_loaded' => 'Les contacts de démonstration ont été ajoutés ou figuraient déjà dans la liste.',
        'recipients_empty_title' => 'Votre liste de test est vide',
        'recipients_empty_message' => 'Ajoutez un contact fictif ou chargez les exemples pour tester la liste.',
        'status_label' => 'Statut',
        'status_subscribed' => 'Inscrit',
        'status_unsubscribed' => 'Désinscrit',
        'unsubscribe_action' => 'Désinscrire',
        'resubscribe_action' => 'Réinscrire',
        'edit_recipient' => 'Modifier',
        'delete_recipient' => 'Supprimer',
        'confirm_delete_recipient' => 'Supprimer ce contact de test ?',
        'error_recipient_type' => 'Saisissez un nom et une adresse e-mail valides.',
        'error_recipient_required' => 'Le nom et l’adresse e-mail sont obligatoires.',
        'error_recipient_name_length' => 'Le nom ne peut pas dépasser 100 caractères.',
        'error_recipient_email' => 'Utilisez une adresse e-mail valide sur le domaine @example.test.',
        'error_recipient_duplicate' => 'Cette adresse e-mail figure déjà dans votre liste de test.',
        'error_recipient_not_found' => 'Ce contact de test est introuvable.',
        'error_recipient_status' => 'Le statut de ce contact de test n’est pas valide.',
        'recipient_added' => 'Contact de test ajouté.',
        'recipient_saved' => 'Contact de test mis à jour.',
        'recipient_deleted' => 'Contact de test supprimé.',
        'recipient_status_updated' => 'Statut d’inscription mis à jour.',
        'new_edition' => 'NOUVELLE ÉDITION',
        'editor_new' => 'Composez votre newsletter',
        'editor_edit' => 'Modifier votre brouillon',
        'new_draft' => 'Nouveau brouillon',
        'error_delete' => 'Impossible de supprimer cette newsletter.',
        'deleted' => 'Brouillon supprimé.',
        'not_found' => 'Cette newsletter est introuvable.',
        'unknown_action' => 'Action inconnue pour la newsletter.',
        'error_edit' => 'Impossible de modifier cette newsletter.',
        'error_field_type' => 'Veuillez saisir du texte valide dans tous les champs.',
        'error_required' => 'Veuillez remplir au moins l’objet, le titre et le contenu.',
        'error_too_long' => 'Un ou plusieurs champs sont trop longs. Vérifiez les limites indiquées.',
        'error_cta_pair' => 'Pour ajouter un bouton, indiquez son texte et son lien, ou laissez les deux champs vides.',
        'error_cta_url' => 'Le lien du bouton doit être une adresse complète commençant par http:// ou https://.',
        'saved' => 'Brouillon enregistré.',
        'required' => 'Obligatoire',
        'subject' => 'Objet',
        'subject_placeholder' => 'Par exemple : Un nouveau souffle pour le printemps',
        'subject_hint' => 'L’objet que vos clients verront dans leur boîte de réception.',
        'preheader' => 'Texte d’aperçu',
        'preheader_placeholder' => 'Un court complément à votre objet',
        'headline' => 'Titre de la newsletter',
        'headline_placeholder' => 'Que souhaitez-vous partager cette fois-ci ?',
        'body' => 'Message',
        'body_placeholder' => 'Rédigez votre newsletter. Laissez une ligne vide pour créer un nouveau paragraphe.',
        'body_count_suffix' => ' / 10 000 caractères. Une mise en forme simple reste lisible sur mobile.',
        'cta_section' => 'Bouton facultatif',
        'cta_label' => 'Texte du bouton',
        'cta_label_placeholder' => 'Découvrir le menu',
        'cta_url' => 'Lien du bouton',
        'cta_url_placeholder' => 'https://exemple.be/menu',
        'cta_url_hint' => 'Utilisez une adresse complète commençant par http:// ou https://.',
        'save_draft' => 'Enregistrer le brouillon',
        'publish' => 'Envoyer (bientôt)',
        'publish_help' => 'L’envoi sera disponible lorsque la liste de clients et la connexion de mailing seront prêtes.',
        'preview_eyebrow' => 'APERÇU DU RÉSULTAT',
        'preview_title' => 'Aperçu',
        'email' => 'E-mail',
        'preview_subject_default' => 'Objet de votre newsletter',
        'preview_preheader_default' => 'Un message personnel de votre part',
        'preview_headline_default' => 'Votre titre s’affichera ici',
        'preview_body_default' => 'Le contenu de votre newsletter s’affichera ici lorsque vous commencerez à écrire.',
        'email_kicker' => 'UN MESSAGE DE VOTRE PART',
        'email_brand_label' => 'NEWSLETTER',
        'email_footer' => 'Préparé avec soin dans XOrder',
        'preview_note' => 'Ceci est un aperçu. Aucun e-mail ne sera envoyé pour le moment.',
        'drafts_eyebrow' => 'ENREGISTRÉS POUR PLUS TARD',
        'drafts_title' => 'Vos brouillons',
        'draft_count_one' => 'brouillon',
        'draft_count_many' => 'brouillons',
        'empty_title' => 'Votre première newsletter commence ici',
        'empty_message' => 'Remplissez l’éditeur et enregistrez votre premier brouillon pour le modifier plus tard.',
        'draft_tag' => 'BROUILLON',
        'updated_at' => 'Modifié le',
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
        'confirm_delete' => 'Supprimer ce brouillon ?',
    ],
];
$joachimText = static fn (string $key): string => $joachimMessages[$joachimLocale][$key] ?? $key;

if ($user === null) {
    echo '<h1>' . $e($joachimText('login_title')) . '</h1><p>' . $e($joachimText('login_message'))
        . ' <a href="' . $e($url('/login')) . '">' . $e($joachimText('login_link')) . '</a></p>';
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

$app->db->pdo()->exec(
    'CREATE TABLE IF NOT EXISTS joachim_test_recipients (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT \'subscribed\' CHECK (status IN (\'subscribed\', \'unsubscribed\')),
        created_at TEXT NOT NULL,
        UNIQUE (user_id, email)
    )'
);

$requestedView = $request->query('view');
$activeView = $requestedView === 'recipients' ? 'recipients' : 'editor';
$viewUrl = static function (string $view, array $query = []) use ($url, $featureName): string {
    return $url('/features', array_merge(['name' => $featureName, 'view' => $view], $query));
};

if ($activeView === 'recipients') {
    $recipientForm = ['name' => '', 'email' => ''];
    $editingRecipientId = null;
    $recipientError = null;

    if ($request->isPost()) {
        $actionValue = $request->post('action', '');
        $recipientAction = is_string($actionValue) ? $actionValue : '';

        if ($recipientAction === 'seed_recipients') {
            $sampleRecipients = [
                ['Alex Martin', 'alex.martin@example.test'],
                ['Noor Janssens', 'noor.janssens@example.test'],
                ['Lou Dupont', 'lou.dupont@example.test'],
            ];
            foreach ($sampleRecipients as [$name, $email]) {
                $existingRecipient = $app->db->fetchOne(
                    'SELECT id FROM joachim_test_recipients WHERE user_id = ? AND email = ?',
                    [$user->id, $email]
                );
                if ($existingRecipient === null) {
                    $app->db->execute(
                        'INSERT INTO joachim_test_recipients (user_id, name, email, status, created_at)
                         VALUES (?, ?, ?, ?, ?)',
                        [$user->id, $name, $email, 'subscribed', date('Y-m-d H:i:s')]
                    );
                }
            }
            $app->session->flash('success', $joachimText('samples_loaded'));
            return Response::redirect($viewUrl('recipients'));
        }

        if ($recipientAction === 'delete_recipient' || $recipientAction === 'toggle_recipient') {
            $rawRecipientId = $request->post('recipient_id');
            $recipientId = is_string($rawRecipientId) || is_int($rawRecipientId)
                ? filter_var((string) $rawRecipientId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
                : false;

            if ($recipientId === false) {
                $app->session->flash('error', $joachimText('error_recipient_not_found'));
                return Response::redirect($viewUrl('recipients'));
            }

            if ($recipientAction === 'delete_recipient') {
                $deleted = $app->db->execute(
                    'DELETE FROM joachim_test_recipients WHERE id = ? AND user_id = ?',
                    [$recipientId, $user->id]
                );
                $app->session->flash(
                    $deleted > 0 ? 'success' : 'error',
                    $deleted > 0 ? $joachimText('recipient_deleted') : $joachimText('error_recipient_not_found')
                );
                return Response::redirect($viewUrl('recipients'));
            }

            $nextStatus = $request->post('next_status');
            if (!is_string($nextStatus) || !in_array($nextStatus, ['subscribed', 'unsubscribed'], true)) {
                $app->session->flash('error', $joachimText('error_recipient_status'));
                return Response::redirect($viewUrl('recipients'));
            }
            $updated = $app->db->execute(
                'UPDATE joachim_test_recipients SET status = ? WHERE id = ? AND user_id = ?',
                [$nextStatus, $recipientId, $user->id]
            );
            $app->session->flash(
                $updated > 0 ? 'success' : 'error',
                $updated > 0 ? $joachimText('recipient_status_updated') : $joachimText('error_recipient_not_found')
            );
            return Response::redirect($viewUrl('recipients'));
        }

        if ($recipientAction !== 'save_recipient') {
            $app->session->flash('error', $joachimText('unknown_action'));
            return Response::redirect($viewUrl('recipients'));
        }

        $rawRecipientId = $request->post('recipient_id', '');
        if (!is_string($rawRecipientId) && !is_int($rawRecipientId)) {
            $recipientError = $joachimText('error_recipient_type');
        } elseif (trim((string) $rawRecipientId) !== '') {
            $recipientId = filter_var(trim((string) $rawRecipientId), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($recipientId === false) {
                $recipientError = $joachimText('error_recipient_not_found');
            } else {
                $existingRecipient = $app->db->fetchOne(
                    'SELECT id FROM joachim_test_recipients WHERE id = ? AND user_id = ?',
                    [$recipientId, $user->id]
                );
                if ($existingRecipient === null) {
                    $app->session->flash('error', $joachimText('error_recipient_not_found'));
                    return Response::redirect($viewUrl('recipients'));
                }
                $editingRecipientId = (int) $recipientId;
            }
        }

        $rawName = $request->post('recipient_name', '');
        $rawEmail = $request->post('recipient_email', '');
        if (!is_string($rawName) || !is_string($rawEmail)) {
            $recipientError ??= $joachimText('error_recipient_type');
        } else {
            $recipientForm['name'] = trim($rawName);
            $recipientForm['email'] = mb_strtolower(trim($rawEmail), 'UTF-8');
        }

        if ($recipientError === null) {
            if ($recipientForm['name'] === '' || $recipientForm['email'] === '') {
                $recipientError = $joachimText('error_recipient_required');
            } elseif (mb_strlen($recipientForm['name'], 'UTF-8') > 100) {
                $recipientError = $joachimText('error_recipient_name_length');
            } elseif (
                filter_var($recipientForm['email'], FILTER_VALIDATE_EMAIL) === false
                || !str_ends_with($recipientForm['email'], '@example.test')
            ) {
                $recipientError = $joachimText('error_recipient_email');
            } else {
                $duplicateRecipient = $editingRecipientId === null
                    ? $app->db->fetchOne(
                        'SELECT id FROM joachim_test_recipients WHERE user_id = ? AND email = ?',
                        [$user->id, $recipientForm['email']]
                    )
                    : $app->db->fetchOne(
                        'SELECT id FROM joachim_test_recipients WHERE user_id = ? AND email = ? AND id != ?',
                        [$user->id, $recipientForm['email'], $editingRecipientId]
                    );
                if ($duplicateRecipient !== null) {
                    $recipientError = $joachimText('error_recipient_duplicate');
                }
            }
        }

        if ($recipientError === null) {
            if ($editingRecipientId === null) {
                $app->db->execute(
                    'INSERT INTO joachim_test_recipients (user_id, name, email, status, created_at)
                     VALUES (?, ?, ?, ?, ?)',
                    [$user->id, $recipientForm['name'], $recipientForm['email'], 'subscribed', date('Y-m-d H:i:s')]
                );
                $message = 'recipient_added';
            } else {
                $app->db->execute(
                    'UPDATE joachim_test_recipients SET name = ?, email = ? WHERE id = ? AND user_id = ?',
                    [$recipientForm['name'], $recipientForm['email'], $editingRecipientId, $user->id]
                );
                $message = 'recipient_saved';
            }
            $app->session->flash('success', $joachimText($message));
            return Response::redirect($viewUrl('recipients'));
        }
    } else {
        $queryRecipientId = $request->query('recipient_id');
        if ($queryRecipientId !== null && $queryRecipientId !== '') {
            $recipientId = is_string($queryRecipientId) || is_int($queryRecipientId)
                ? filter_var((string) $queryRecipientId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
                : false;
            if ($recipientId === false) {
                $app->session->flash('error', $joachimText('error_recipient_not_found'));
                return Response::redirect($viewUrl('recipients'));
            }
            $recipient = $app->db->fetchOne(
                'SELECT id, name, email FROM joachim_test_recipients WHERE id = ? AND user_id = ?',
                [$recipientId, $user->id]
            );
            if ($recipient === null) {
                $app->session->flash('error', $joachimText('error_recipient_not_found'));
                return Response::redirect($viewUrl('recipients'));
            }
            $editingRecipientId = (int) $recipient['id'];
            $recipientForm = ['name' => (string) $recipient['name'], 'email' => (string) $recipient['email']];
        }
    }

    $testRecipients = $app->db->fetchAll(
        'SELECT id, name, email, status, created_at
         FROM joachim_test_recipients WHERE user_id = ? ORDER BY name COLLATE NOCASE, id',
        [$user->id]
    );
    $recipientStats = $app->db->fetchOne(
        'SELECT COUNT(*) AS total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS subscribed
         FROM joachim_test_recipients WHERE user_id = ?',
        ['subscribed', $user->id]
    ) ?? ['total' => 0, 'subscribed' => 0];
} else {
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
            $app->session->flash('error', $joachimText('error_delete'));
        } else {
            $deleted = $app->db->execute(
                'DELETE FROM joachim_newsletters WHERE id = ? AND user_id = ?',
                [$id, $user->id]
            );
            $app->session->flash(
                $deleted > 0 ? 'success' : 'error',
                $deleted > 0 ? $joachimText('deleted') : $joachimText('not_found')
            );
        }

        return Response::redirect($featureUrl);
    }

    if ($action !== 'save') {
        $app->session->flash('error', $joachimText('unknown_action'));
        return Response::redirect($featureUrl);
    }

    $rawPostedId = $request->post('id', '');
    $postedId = is_string($rawPostedId) || is_int($rawPostedId) ? trim((string) $rawPostedId) : '';
    if (!is_string($rawPostedId) && !is_int($rawPostedId)) {
        $formError = $joachimText('error_edit');
    }
    if ($postedId !== '') {
        $id = filter_var($postedId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false || $formError !== null) {
            $formError = $joachimText('error_edit');
        } else {
            $existingDraft = $app->db->fetchOne(
                'SELECT id FROM joachim_newsletters WHERE id = ? AND user_id = ?',
                [$id, $user->id]
            );
            if ($existingDraft === null) {
                $app->session->flash('error', $joachimText('not_found'));
                return Response::redirect($featureUrl);
            }
            $editingId = (int) $id;
        }
    }

    foreach ($emptyDraft as $field => $_) {
        $value = $request->post($field, '');
        if (!is_string($value)) {
            $formError ??= $joachimText('error_field_type');
            $formDraft[$field] = '';
        } else {
            $formDraft[$field] = trim($value);
        }
    }

    $length = static fn (string $value): int => mb_strlen($value, 'UTF-8');
    if ($formError === null) {
        if ($formDraft['subject'] === '' || $formDraft['headline'] === '' || $formDraft['body'] === '') {
            $formError = $joachimText('error_required');
        } elseif (
            $length($formDraft['subject']) > 150
            || $length($formDraft['preheader']) > 200
            || $length($formDraft['headline']) > 120
            || $length($formDraft['body']) > 10000
            || $length($formDraft['cta_label']) > 60
            || $length($formDraft['cta_url']) > 2048
        ) {
            $formError = $joachimText('error_too_long');
        } elseif (($formDraft['cta_label'] === '') !== ($formDraft['cta_url'] === '')) {
            $formError = $joachimText('error_cta_pair');
        } elseif ($formDraft['cta_url'] !== '') {
            $parts = filter_var($formDraft['cta_url'], FILTER_VALIDATE_URL) !== false
                ? parse_url($formDraft['cta_url'])
                : false;
            $scheme = is_array($parts) ? strtolower((string) ($parts['scheme'] ?? '')) : '';
            if (!in_array($scheme, ['http', 'https'], true)) {
                $formError = $joachimText('error_cta_url');
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

        $app->session->flash('success', $joachimText('saved'));
        return Response::redirect($url('/features', ['name' => $featureName, 'id' => $editingId]));
    }
} else {
    $queryId = $request->query('id');
    if ($queryId !== null && $queryId !== '') {
        $id = is_string($queryId) || is_int($queryId)
            ? filter_var((string) $queryId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            : false;
        if ($id === false) {
            $app->session->flash('error', $joachimText('error_edit'));
            return Response::redirect($featureUrl);
        }

        $draft = $app->db->fetchOne(
            'SELECT id, subject, preheader, headline, body, cta_label, cta_url
             FROM joachim_newsletters WHERE id = ? AND user_id = ?',
            [$id, $user->id]
        );
        if ($draft === null) {
            $app->session->flash('error', $joachimText('not_found'));
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
}
?>
<link rel="stylesheet" href="<?= $e($asset('style.css')) ?>">

<div class="joachim-newsletter">
    <header class="joachim-intro">
        <div>
            <p class="joachim-eyebrow"><?= $e($joachimText($activeView === 'editor' ? 'hero_eyebrow' : 'manager_eyebrow')) ?></p>
            <h1><?= $e($joachimText($activeView === 'editor' ? 'hero_title' : 'manager_title')) ?></h1>
            <p><?= $e($joachimText($activeView === 'editor' ? 'hero_description' : 'manager_description')) ?></p>
        </div>
        <span class="joachim-status"><?= $e($joachimText($activeView === 'editor' ? 'status' : 'manager_status')) ?></span>
    </header>

    <nav class="joachim-tabs" aria-label="<?= $e($joachimText('manager_nav_label')) ?>">
        <a href="<?= $e($viewUrl('editor')) ?>" <?= $activeView === 'editor' ? 'aria-current="page"' : '' ?>><?= $e($joachimText('editor_tab')) ?></a>
        <a href="<?= $e($viewUrl('recipients')) ?>" <?= $activeView === 'recipients' ? 'aria-current="page"' : '' ?>><?= $e($joachimText('recipients_tab')) ?></a>
    </nav>

    <?php if ($activeView === 'editor'): ?>
    <div class="joachim-workspace">
        <section class="joachim-editor card" aria-labelledby="joachim-editor-title">
            <div class="joachim-section-heading">
                <div>
                    <p class="joachim-eyebrow"><?= $e($joachimText('new_edition')) ?></p>
                    <h2 id="joachim-editor-title"><?= $e($joachimText($editingId === null ? 'editor_new' : 'editor_edit')) ?></h2>
                </div>
                <?php if ($editingId !== null): ?>
                    <a class="joachim-text-link" href="<?= $e($featureUrl) ?>"><?= $e($joachimText('new_draft')) ?></a>
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
                    <label class="form-label" for="joachim-subject"><?= $e($joachimText('subject')) ?> <span><?= $e($joachimText('required')) ?></span></label>
                    <input class="form-control" id="joachim-subject" name="subject" maxlength="150" value="<?= $e($formDraft['subject']) ?>" placeholder="<?= $e($joachimText('subject_placeholder')) ?>" required data-joachim-preview="subject" data-joachim-default="<?= $e($joachimText('preview_subject_default')) ?>">
                    <small class="joachim-hint"><?= $e($joachimText('subject_hint')) ?></small>
                </div>

                <div class="joachim-field">
                    <label class="form-label" for="joachim-preheader"><?= $e($joachimText('preheader')) ?></label>
                    <input class="form-control" id="joachim-preheader" name="preheader" maxlength="200" value="<?= $e($formDraft['preheader']) ?>" placeholder="<?= $e($joachimText('preheader_placeholder')) ?>" data-joachim-preview="preheader" data-joachim-default="<?= $e($joachimText('preview_preheader_default')) ?>">
                </div>

                <div class="joachim-field">
                    <label class="form-label" for="joachim-headline"><?= $e($joachimText('headline')) ?> <span><?= $e($joachimText('required')) ?></span></label>
                    <input class="form-control" id="joachim-headline" name="headline" maxlength="120" value="<?= $e($formDraft['headline']) ?>" placeholder="<?= $e($joachimText('headline_placeholder')) ?>" required data-joachim-preview="headline" data-joachim-default="<?= $e($joachimText('preview_headline_default')) ?>">
                </div>

                <div class="joachim-field">
                    <label class="form-label" for="joachim-body"><?= $e($joachimText('body')) ?> <span><?= $e($joachimText('required')) ?></span></label>
                    <textarea class="form-textarea" id="joachim-body" name="body" rows="8" maxlength="10000" placeholder="<?= $e($joachimText('body_placeholder')) ?>" required data-joachim-preview="body" data-joachim-default="<?= $e($joachimText('preview_body_default')) ?>"><?= $e($formDraft['body']) ?></textarea>
                    <small class="joachim-hint"><span id="joachim-body-count"><?= $e(mb_strlen($formDraft['body'], 'UTF-8')) ?></span><?= $e($joachimText('body_count_suffix')) ?></small>
                </div>

                <div class="joachim-optional">
                    <p class="joachim-optional-title"><?= $e($joachimText('cta_section')) ?></p>
                    <div class="joachim-field">
                        <label class="form-label" for="joachim-cta-label"><?= $e($joachimText('cta_label')) ?></label>
                        <input class="form-control" id="joachim-cta-label" name="cta_label" maxlength="60" value="<?= $e($formDraft['cta_label']) ?>" placeholder="<?= $e($joachimText('cta_label_placeholder')) ?>" data-joachim-preview="cta-label">
                    </div>
                    <div class="joachim-field">
                        <label class="form-label" for="joachim-cta-url"><?= $e($joachimText('cta_url')) ?></label>
                        <input class="form-control" id="joachim-cta-url" name="cta_url" maxlength="2048" type="url" value="<?= $e($formDraft['cta_url']) ?>" placeholder="<?= $e($joachimText('cta_url_placeholder')) ?>">
                        <small class="joachim-hint"><?= $e($joachimText('cta_url_hint')) ?></small>
                    </div>
                </div>

                <div class="joachim-form-actions">
                    <button class="btn btn-primary" type="submit"><?= $e($joachimText('save_draft')) ?></button>
                    <button class="btn btn-secondary" type="button" disabled aria-describedby="joachim-publish-help"><?= $e($joachimText('publish')) ?></button>
                </div>
                <p class="joachim-publish-help" id="joachim-publish-help"><?= $e($joachimText('publish_help')) ?></p>
            </form>
        </section>

        <aside class="joachim-preview-column" aria-labelledby="joachim-preview-title">
            <div class="joachim-preview-heading">
                <div>
                    <p class="joachim-eyebrow"><?= $e($joachimText('preview_eyebrow')) ?></p>
                    <h2 id="joachim-preview-title"><?= $e($joachimText('preview_title')) ?></h2>
                </div>
                <span class="joachim-preview-device"><?= $e($joachimText('email')) ?></span>
            </div>
            <div class="joachim-email-shell">
                <div class="joachim-email-meta">
                    <span><?= $e($joachimText('subject')) ?></span>
                    <strong id="joachim-preview-subject"><?= $formDraft['subject'] !== '' ? $e($formDraft['subject']) : $e($joachimText('preview_subject_default')) ?></strong>
                </div>
                <div class="joachim-email-card">
                    <div class="joachim-email-brand">
                        <span class="joachim-brand-mark" aria-hidden="true">X</span>
                        <span>XOrder</span>
                        <span class="joachim-brand-label"><?= $e($joachimText('email_brand_label')) ?></span>
                    </div>
                    <div class="joachim-email-content">
                        <p class="joachim-email-preheader" id="joachim-preview-preheader"><?= $formDraft['preheader'] !== '' ? $e($formDraft['preheader']) : $e($joachimText('preview_preheader_default')) ?></p>
                        <p class="joachim-email-kicker"><?= $e($joachimText('email_kicker')) ?></p>
                        <h3 id="joachim-preview-headline"><?= $formDraft['headline'] !== '' ? $e($formDraft['headline']) : $e($joachimText('preview_headline_default')) ?></h3>
                        <p class="joachim-email-body" id="joachim-preview-body"><?= $formDraft['body'] !== '' ? $e($formDraft['body']) : $e($joachimText('preview_body_default')) ?></p>
                        <div class="joachim-preview-cta-wrap" id="joachim-preview-cta-wrap"<?= $formDraft['cta_label'] === '' ? ' hidden' : '' ?>>
                            <span class="joachim-preview-cta" id="joachim-preview-cta"><?= $formDraft['cta_label'] !== '' ? $e($formDraft['cta_label']) : '' ?></span>
                        </div>
                    </div>
                    <div class="joachim-email-footer"><?= $e($joachimText('email_footer')) ?></div>
                </div>
                <p class="joachim-preview-note"><?= $e($joachimText('preview_note')) ?></p>
            </div>
        </aside>
    </div>

    <section class="joachim-drafts" aria-labelledby="joachim-drafts-title">
        <div class="joachim-section-heading">
            <div>
                <p class="joachim-eyebrow"><?= $e($joachimText('drafts_eyebrow')) ?></p>
                <h2 id="joachim-drafts-title"><?= $e($joachimText('drafts_title')) ?></h2>
            </div>
            <span class="joachim-draft-count"><?= count($drafts) ?> <?= $e($joachimText(count($drafts) === 1 ? 'draft_count_one' : 'draft_count_many')) ?></span>
        </div>

        <?php if ($drafts === []): ?>
            <div class="joachim-empty card">
                <span class="joachim-empty-icon" aria-hidden="true">✉</span>
                <h3><?= $e($joachimText('empty_title')) ?></h3>
                <p><?= $e($joachimText('empty_message')) ?></p>
            </div>
        <?php else: ?>
            <ul class="joachim-draft-list">
                <?php foreach ($drafts as $draft): ?>
                    <?php $updatedAt = new DateTimeImmutable((string) $draft['updated_at']); ?>
                    <li class="joachim-draft card">
                        <div class="joachim-draft-copy">
                            <span class="joachim-draft-tag"><?= $e($joachimText('draft_tag')) ?></span>
                            <h3><?= $e($draft['subject']) ?></h3>
                            <p><?= $e($draft['headline']) ?></p>
                            <small><?= $e($joachimText('updated_at')) ?> <?= $e($updatedAt->format($joachimLocale === 'fr' ? 'd/m/Y H:i' : 'd-m-Y H:i')) ?></small>
                        </div>
                        <div class="joachim-draft-actions">
                            <a class="btn btn-secondary" href="<?= $e($url('/features', ['name' => $featureName, 'id' => (int) $draft['id']])) ?>"><?= $e($joachimText('edit')) ?></a>
                            <form method="post" action="<?= $e($featureUrl) ?>" data-joachim-confirm="<?= $e($joachimText('confirm_delete')) ?>">
                                <?= $csrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $e($draft['id']) ?>">
                                <button class="joachim-delete" type="submit"><?= $e($joachimText('delete')) ?></button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
    <?php else: ?>
    <section class="joachim-recipient-manager">
        <div class="joachim-recipient-toolbar">
            <div>
                <p class="joachim-eyebrow"><?= $e($joachimText('list_title')) ?></p>
                <h2><?= (int) $recipientStats['total'] ?> <?= $e($joachimText((int) $recipientStats['total'] === 1 ? 'recipient_count_one' : 'recipient_count_many')) ?></h2>
                <p class="joachim-recipient-summary"><?= (int) $recipientStats['subscribed'] ?> <?= $e($joachimText('active_count')) ?></p>
            </div>
            <form method="post" action="<?= $e($viewUrl('recipients')) ?>">
                <?= $csrfField() ?>
                <input type="hidden" name="action" value="seed_recipients">
                <button class="btn btn-secondary" type="submit"><?= $e($joachimText('load_samples')) ?></button>
            </form>
        </div>

        <p class="joachim-test-notice" role="note"><?= $e($joachimText('test_data_notice')) ?></p>

        <div class="joachim-recipient-workspace">
            <section class="joachim-recipient-editor card" aria-labelledby="joachim-recipient-editor-title">
                <div class="joachim-section-heading">
                    <div>
                        <p class="joachim-eyebrow"><?= $e($joachimText('manager_eyebrow')) ?></p>
                        <h2 id="joachim-recipient-editor-title"><?= $e($joachimText($editingRecipientId === null ? 'add_recipient' : 'edit_recipient')) ?></h2>
                    </div>
                </div>

                <?php if ($recipientError !== null): ?>
                    <p class="joachim-error" role="alert"><?= $e($recipientError) ?></p>
                <?php endif; ?>

                <form method="post" action="<?= $e($viewUrl('recipients')) ?>" class="joachim-form">
                    <?= $csrfField() ?>
                    <input type="hidden" name="action" value="save_recipient">
                    <?php if ($editingRecipientId !== null): ?>
                        <input type="hidden" name="recipient_id" value="<?= $e($editingRecipientId) ?>">
                    <?php endif; ?>

                    <div class="joachim-field">
                        <label class="form-label" for="joachim-recipient-name"><?= $e($joachimText('name_label')) ?></label>
                        <input class="form-control" id="joachim-recipient-name" name="recipient_name" maxlength="100" value="<?= $e($recipientForm['name']) ?>" placeholder="<?= $e($joachimText('name_placeholder')) ?>" required>
                    </div>
                    <div class="joachim-field">
                        <label class="form-label" for="joachim-recipient-email"><?= $e($joachimText('email_label')) ?></label>
                        <input class="form-control" id="joachim-recipient-email" name="recipient_email" type="email" maxlength="254" value="<?= $e($recipientForm['email']) ?>" placeholder="<?= $e($joachimText('email_placeholder')) ?>" required>
                        <small class="joachim-hint"><?= $e($joachimText('email_hint')) ?></small>
                    </div>

                    <div class="joachim-form-actions">
                        <button class="btn btn-primary" type="submit"><?= $e($joachimText($editingRecipientId === null ? 'add_recipient' : 'save_recipient')) ?></button>
                        <?php if ($editingRecipientId !== null): ?>
                            <a class="btn btn-secondary" href="<?= $e($viewUrl('recipients')) ?>"><?= $e($joachimText('cancel')) ?></a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>

            <section class="joachim-recipient-list-panel card" aria-labelledby="joachim-recipient-list-title">
                <div class="joachim-section-heading">
                    <div>
                        <p class="joachim-eyebrow"><?= $e($joachimText('list_title')) ?></p>
                        <h2 id="joachim-recipient-list-title"><?= $e($joachimText('recipients_tab')) ?></h2>
                    </div>
                    <span class="joachim-draft-count"><?= (int) $recipientStats['total'] ?> <?= $e($joachimText((int) $recipientStats['total'] === 1 ? 'recipient_count_one' : 'recipient_count_many')) ?></span>
                </div>

                <?php if ($testRecipients === []): ?>
                    <div class="joachim-empty">
                        <span class="joachim-empty-icon" aria-hidden="true">+</span>
                        <h3><?= $e($joachimText('recipients_empty_title')) ?></h3>
                        <p><?= $e($joachimText('recipients_empty_message')) ?></p>
                    </div>
                <?php else: ?>
                    <ul class="joachim-recipient-list">
                        <?php foreach ($testRecipients as $recipient): ?>
                            <?php $isSubscribed = $recipient['status'] === 'subscribed'; ?>
                            <li class="joachim-recipient">
                                <div class="joachim-recipient-copy">
                                    <h3><?= $e($recipient['name']) ?></h3>
                                    <p><?= $e($recipient['email']) ?></p>
                                    <span class="joachim-recipient-status <?= $isSubscribed ? 'joachim-recipient-status--subscribed' : 'joachim-recipient-status--unsubscribed' ?>">
                                        <?= $e($joachimText($isSubscribed ? 'status_subscribed' : 'status_unsubscribed')) ?>
                                    </span>
                                </div>
                                <div class="joachim-recipient-actions">
                                    <a class="joachim-text-link" href="<?= $e($viewUrl('recipients', ['recipient_id' => (int) $recipient['id']])) ?>"><?= $e($joachimText('edit_recipient')) ?></a>
                                    <form method="post" action="<?= $e($viewUrl('recipients')) ?>">
                                        <?= $csrfField() ?>
                                        <input type="hidden" name="action" value="toggle_recipient">
                                        <input type="hidden" name="recipient_id" value="<?= $e($recipient['id']) ?>">
                                        <input type="hidden" name="next_status" value="<?= $isSubscribed ? 'unsubscribed' : 'subscribed' ?>">
                                        <button class="joachim-text-button" type="submit"><?= $e($joachimText($isSubscribed ? 'unsubscribe_action' : 'resubscribe_action')) ?></button>
                                    </form>
                                    <form method="post" action="<?= $e($viewUrl('recipients')) ?>" data-joachim-confirm="<?= $e($joachimText('confirm_delete_recipient')) ?>">
                                        <?= $csrfField() ?>
                                        <input type="hidden" name="action" value="delete_recipient">
                                        <input type="hidden" name="recipient_id" value="<?= $e($recipient['id']) ?>">
                                        <button class="joachim-delete" type="submit"><?= $e($joachimText('delete_recipient')) ?></button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </div>
    </section>
    <?php endif; ?>
</div>

<script src="<?= $e($asset('app.js')) ?>"></script>
