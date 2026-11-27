<?php
session_start();

// Initialiseer de inkooplijst in de sessie als deze nog niet bestaat
if (!isset($_SESSION['inkooplijst'])) {
    $_SESSION['inkooplijst'] = [];
}

// Voorbeeldbestellingen en promoties voor de klantweergave.
$klantBestellingen = [
    ['klant' => 'Emma', 'producten' => 'Volkorenbrood × 1, Jonge kaas × 2', 'datum' => 'Vandaag', 'tijd' => '09:20', 'artikelen' => [['product' => 'Volkorenbrood', 'aantal' => 1, 'prijs' => 3.25], ['product' => 'Jonge kaas', 'aantal' => 2, 'prijs' => 4.75]]],
    ['klant' => 'Noah', 'producten' => 'Croissants × 4, Sinaasappelsap × 1', 'datum' => 'Vandaag', 'tijd' => '10:15', 'artikelen' => [['product' => 'Croissants', 'aantal' => 4, 'prijs' => 1.25], ['product' => 'Sinaasappelsap', 'aantal' => 1, 'prijs' => 3.49]]],
    ['klant' => 'Mila', 'producten' => 'Appeltaart × 1, Koffiebonen × 1', 'datum' => 'Gisteren', 'tijd' => '10:42', 'artikelen' => [['product' => 'Appeltaart', 'aantal' => 1, 'prijs' => 12.50], ['product' => 'Koffiebonen', 'aantal' => 1, 'prijs' => 8.95]]],
    ['klant' => 'Liam', 'producten' => 'Volkorenbrood × 2', 'datum' => 'Gisteren', 'tijd' => '11:05', 'artikelen' => [['product' => 'Volkorenbrood', 'aantal' => 2, 'prijs' => 3.25]]],
    ['klant' => 'Louise', 'producten' => 'Croissants × 6', 'datum' => 'Gisteren', 'tijd' => '11:35', 'artikelen' => [['product' => 'Croissants', 'aantal' => 6, 'prijs' => 1.25]]],
    ['klant' => 'Adam', 'producten' => 'Jonge kaas × 1', 'datum' => 'Gisteren', 'tijd' => '13:10', 'artikelen' => [['product' => 'Jonge kaas', 'aantal' => 1, 'prijs' => 4.75]]],
];
$bestellingenPerUur = [];
foreach ($klantBestellingen as $bestelling) {
    $uur = (int) substr($bestelling['tijd'], 0, 2);
    $bestellingenPerUur[$uur] = ($bestellingenPerUur[$uur] ?? 0) + 1;
}
$druksteUur = array_search(max($bestellingenPerUur), $bestellingenPerUur, true);
$promotieItems = [
    ['product' => 'Croissants', 'prijs' => 1.25, 'label' => '2e aan halve prijs'],
    ['product' => 'Appeltaart', 'prijs' => 12.50, 'label' => 'Deze week in promotie'],
    ['product' => 'Sinaasappelsap', 'prijs' => 3.49, 'label' => 'Van € 4,25 voor € 3,49'],
];
$nietBeschikbaar = [
    ['product' => 'Aardbeientaart', 'terug' => date('Y-m-d', strtotime('+5 days'))],
    ['product' => 'Verse frambozen', 'terug' => date('Y-m-d', strtotime('+3 days'))],
];

// Seizoenskleuren en maandkalender.
$maandInput = (string) ($_GET['maand'] ?? date('Y-m'));
$kalenderMaand = DateTimeImmutable::createFromFormat('!Y-m', $maandInput) ?: new DateTimeImmutable('first day of this month');
$datumInput = (string) ($_GET['datum'] ?? '');
$geselecteerdeDatum = null;
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $datumInput) === 1) {
    $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $datumInput);
    if ($parsedDate !== false && $parsedDate->format('Y-m-d') === $datumInput) {
        $geselecteerdeDatum = $parsedDate;
    }
}
$maandNummer = (int) $kalenderMaand->format('n');
$seizoenen = [
    'winter' => ['label' => 'Winter', 'icoon' => '❄️'],
    'lente' => ['label' => 'Lente', 'icoon' => '🌷'],
    'zomer' => ['label' => 'Zomer', 'icoon' => '☀️'],
    'herfst' => ['label' => 'Herfst', 'icoon' => '🍂'],
];
$seizoenKey = match (true) {
    in_array($maandNummer, [12, 1, 2], true) => 'winter',
    in_array($maandNummer, [3, 4, 5], true) => 'lente',
    in_array($maandNummer, [6, 7, 8], true) => 'zomer',
    default => 'herfst',
};
$seizoen = $seizoenen[$seizoenKey];
$seizoensAssortiment = [
    'winter' => [
        ['product' => 'Winterse pompoensoep', 'prijs' => 5.95],
        ['product' => 'Kruidige speculaas', 'prijs' => 4.50],
    ],
    'lente' => [
        ['product' => 'Lentesalade met geitenkaas', 'prijs' => 8.95],
        ['product' => 'Aspergesoep', 'prijs' => 6.25],
    ],
    'zomer' => [
        ['product' => 'Zomerse pastasalade', 'prijs' => 7.95],
        ['product' => 'Aardbeienpavlova', 'prijs' => 6.50],
    ],
    'herfst' => [
        ['product' => 'Pompoenquiche', 'prijs' => 9.50],
        ['product' => 'Appel-kaneelcake', 'prijs' => 5.75],
    ],
];
$actueleSeizoensItems = $seizoensAssortiment[$seizoenKey];
$beschikbareProducten = array_values(array_unique(array_merge(
    ['Volkorenbrood', 'Jonge kaas', 'Koffiebonen'],
    array_column($promotieItems, 'product'),
    array_column($actueleSeizoensItems, 'product')
)));
$beschikbareProducten = array_values(array_filter($beschikbareProducten, static fn (string $product): bool =>
    !in_array($product, array_column($nietBeschikbaar, 'product'), true)
));
$prijzenPerProduct = [
    'Volkorenbrood' => 3.25,
    'Jonge kaas' => 4.75,
    'Koffiebonen' => 8.95,
];
foreach (array_merge($promotieItems, $actueleSeizoensItems) as $productItem) {
    $prijzenPerProduct[$productItem['product']] = $productItem['prijs'];
}
$eersteDag = $kalenderMaand->modify('first day of this month');
$dagenInMaand = (int) $kalenderMaand->format('t');
$offsetEersteDag = ((int) $eersteDag->format('N')) - 1;
$vandaag = new DateTimeImmutable('today');
$vorigeMaand = $kalenderMaand->modify('-1 month')->format('Y-m');
$volgendeMaand = $kalenderMaand->modify('+1 month')->format('Y-m');

// Verwerk formulieracties (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actie = $_POST['actie'] ?? '';

    // 1. Nieuw product toevoegen
    if ($actie === 'toevoegen') {
        $product = trim($_POST['product'] ?? '');
        $aantal = (int)($_POST['aantal'] ?? 1);
        $prijs = (float)str_replace(',', '.', $_POST['prijs'] ?? 0);

        if ($product !== '' && $aantal > 0) {
            $_SESSION['inkooplijst'][] = [
                'id' => uniqid(),
                'product' => $product,
                'aantal' => $aantal,
                'prijs' => $prijs,
                'gekocht' => false
            ];
        }
    }

    // Promotieartikel rechtstreeks aan de inkooplijst toevoegen.
    if ($actie === 'promotie' && isset($_POST['promotie'])) {
        $promotieIndex = (int) $_POST['promotie'];
        if (isset($promotieItems[$promotieIndex])) {
            $promo = $promotieItems[$promotieIndex];
            $_SESSION['inkooplijst'][] = [
                'id' => uniqid(),
                'product' => $promo['product'],
                'aantal' => 1,
                'prijs' => $promo['prijs'],
                'gekocht' => false
            ];
        }
    }

    // Seizoensartikel toevoegen; product en prijs komen uit de servercatalogus.
    if ($actie === 'seizoensitem' && isset($_POST['seizoensitem'])) {
        $seizoensIndex = (int) $_POST['seizoensitem'];
        if (isset($actueleSeizoensItems[$seizoensIndex])) {
            $seizoensItem = $actueleSeizoensItems[$seizoensIndex];
            $_SESSION['inkooplijst'][] = [
                'id' => uniqid(),
                'product' => $seizoensItem['product'],
                'aantal' => 1,
                'prijs' => $seizoensItem['prijs'],
                'gekocht' => false
            ];
        }
    }

    // Een eerdere bestelling in één keer opnieuw aan de inkooplijst toevoegen.
    if ($actie === 'bestel_opnieuw' && isset($_POST['bestelling'])) {
        $bestellingIndex = filter_var($_POST['bestelling'], FILTER_VALIDATE_INT);
        if ($bestellingIndex !== false && isset($klantBestellingen[$bestellingIndex])) {
            foreach ($klantBestellingen[$bestellingIndex]['artikelen'] as $besteldArtikel) {
                $_SESSION['inkooplijst'][] = [
                    'id' => uniqid(),
                    'product' => $besteldArtikel['product'],
                    'aantal' => $besteldArtikel['aantal'],
                    'prijs' => $besteldArtikel['prijs'],
                    'gekocht' => false
                ];
            }
        }
    }

    // Aantal van een artikel in de lijst aanpassen.
    if ($actie === 'aantal_wijzigen' && isset($_POST['id'])) {
        $nieuwAantal = filter_var($_POST['aantal'] ?? null, FILTER_VALIDATE_INT);
        if ($nieuwAantal !== false && $nieuwAantal >= 1) {
            foreach ($_SESSION['inkooplijst'] as &$item) {
                if ($item['id'] === $_POST['id']) {
                    $item['aantal'] = $nieuwAantal;
                    break;
                }
            }
            unset($item);
        }
    }

    // 2. Status wisselen (afvinken / terugzetten)
    if ($actie === 'toggle' && isset($_POST['id'])) {
        foreach ($_SESSION['inkooplijst'] as &$item) {
            if ($item['id'] === $_POST['id']) {
                $item['gekocht'] = !$item['gekocht'];
                break;
            }
        }
        unset($item); // Referentie opruimen
    }

    // 3. Eén product verwijderen
    if ($actie === 'verwijderen' && isset($_POST['id'])) {
        $_SESSION['inkooplijst'] = array_filter($_SESSION['inkooplijst'], function ($item) {
            return $item['id'] !== $_POST['id'];
        });
    }

    // 4. Gehele lijst leegmaken
    if ($actie === 'legen') {
        $_SESSION['inkooplijst'] = [];
    }

    // AJAX-acties sturen dezelfde pagina terug; gewone formulierposts blijven PRG gebruiken.
    $isAjaxRequest = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
    if ($isAjaxRequest) {
        // De client vervangt alleen de inkooplijst met de nieuwe serverweergave.
    } else {
    // Redirect om te voorkomen dat het formulier opnieuw wordt verzonden bij vernieuwen (F5)
    $redirectUrl = $_SERVER['PHP_SELF'];
    if (isset($_GET['maand']) && is_string($_GET['maand'])) {
        $redirectUrl .= '?maand=' . rawurlencode($_GET['maand']);
    }
    header('Location: ' . $redirectUrl);
    exit;
    }
}

// Bereken het totaalbedrag
$totaalBedrag = 0;
foreach ($_SESSION['inkooplijst'] as $item) {
    $totaalBedrag += $item['aantal'] * $item['prijs'];
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inkooplijst Klant</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--season-bg);
            color: #333;
            max-width: 700px;
            margin: 40px auto;
            padding: 20px;
        }
        .container {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
        body.seizoen-winter { --season-bg: #edf4f8; --season-accent: #397a9b; --season-soft: #e0eef5; }
        body.seizoen-lente { --season-bg: #f2f7ed; --season-accent: #648b4f; --season-soft: #e8f1df; }
        body.seizoen-zomer { --season-bg: #fff8e8; --season-accent: #bf8125; --season-soft: #fff0cf; }
        body.seizoen-herfst { --season-bg: #f8f0e9; --season-accent: #a65d36; --season-soft: #f2e2d4; }
        .seizoen-banner {
            background: var(--season-soft);
            border-left: 5px solid var(--season-accent);
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 18px;
        }
        .kalender { margin: 18px 0 24px; }
        .kalender-kop { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
        .kalender-kop h2 { margin: 0; font-size: 18px; }
        .kalender-nav { color: var(--season-accent); text-decoration: none; font-weight: 700; padding: 6px 10px; }
        .kalender-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 5px; text-align: center; }
        .kalender-dag, .kalender-datum { padding: 8px 4px; border-radius: 5px; }
        .kalender-dag { color: #64716c; font-size: 12px; font-weight: 700; }
        .kalender-datum { background: #f8faf9; }
        .kalender-datum.is-vandaag { background: var(--season-accent); color: #fff; font-weight: 700; }
        a.kalender-datum { display: grid; place-items: center; min-height: 36px; text-decoration: none; color: inherit; transition: background-color .18s ease, box-shadow .18s ease; }
        a.kalender-datum:hover { box-shadow: inset 0 0 0 2px var(--season-accent); }
        a.kalender-datum.is-vandaag { color: #fff; }
        a.kalender-datum.is-selected { background: #263d34; color: #fff; box-shadow: inset 0 0 0 2px #fff, 0 0 0 2px #263d34; font-weight: 700; }
        h1 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 10px;
        }
        .form-groep {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        input[type="text"], input[type="number"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        input[name="product"] { flex: 2; min-width: 180px; }
        input[name="aantal"] { width: 70px; }
        input[name="prijs"] { width: 100px; }
        .aantal-formulier { display: flex; align-items: center; gap: 6px; margin: 0; }
        .aantal-formulier input { width: 64px; padding: 7px; }
        .btn-aantal { padding: 7px 9px; background: var(--season-soft); color: #333; }
        
        button {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }
        .btn-toevoegen { background-color: #27ae60; color: white; }
        .btn-toevoegen:hover { background-color: #219150; }
        .btn-actie { background-color: #ecf0f1; color: #333; padding: 5px 10px; }
        .btn-verwijder { background-color: #e74c3c; color: white; padding: 5px 10px; }
        .btn-legen { background-color: #95a5a6; color: white; margin-top: 15px; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th { background-color: #f9fafb; }
        .gekocht {
            text-decoration: line-through;
            color: #95a5a6;
        }
        .totaal-rij {
            font-weight: bold;
            font-size: 16px;
            background-color: #f9fafb;
        }
        .leeg-bericht {
            text-align: center;
            color: #7f8c8d;
            padding: 20px;
        }
        .overzicht-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin: 24px 0;
        }
        .overzicht-kaart {
            background: #fff;
            border: 1px solid #e7ecea;
            border-radius: 8px;
            padding: 16px;
        }
        .overzicht-kaart h2 { margin: 0 0 12px; font-size: 18px; }
        .bestelling { border-top: 1px solid #eee; padding: 10px 0; }
        .bestelling:first-of-type { border-top: 0; }
        .btn-herhaal { margin-top: 8px; padding: 7px 10px; background: var(--season-soft); color: #263d34; font-size: 13px; }
        .bestelling small, .promo-label { color: #687873; }
        .promo-item { border-top: 1px solid #eee; padding: 12px 0; }
        .promo-item:first-of-type { border-top: 0; }
        .promo-item form { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
        .promo-label { display: block; font-size: 13px; color: #18804b; }
        .btn-promo { background: #18804b; color: #fff; padding: 8px 10px; }
        .seizoensaanbod { margin: 18px 0; border: 1px solid #e7ecea; background: #fff; border-radius: 8px; padding: 16px; }
        .seizoensaanbod h2 { margin: 0 0 5px; font-size: 19px; }
        .seizoensaanbod-intro { margin: 0 0 12px; color: #687873; }
        .niet-beschikbaar { margin-top: 18px; border: 1px solid #f0d7c7; background: #fff8f3; border-radius: 8px; padding: 16px; }
        .niet-beschikbaar h2 { margin: 0 0 10px; font-size: 18px; color: #8d4c2b; }
        .niet-beschikbaar ul { margin: 0; padding-left: 20px; }
        .niet-beschikbaar li { padding: 5px 0; }
        .piekmoment { margin: 22px 0; padding: 18px; border-radius: 8px; background: var(--season-soft); }
        .piekmoment h2, .handleiding h2 { margin: 0 0 8px; font-size: 19px; }
        .piekmoment p { margin: 0 0 12px; }
        .uur-balken { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 8px; align-items: end; }
        .uur-kolom { text-align: center; font-size: 12px; }
        .uur-waarde { display: flex; align-items: end; justify-content: center; min-height: 76px; margin-bottom: 5px; }
        .uur-balk { width: min(38px, 70%); height: calc(14px + (var(--aantal) * 18px)); max-height: 72px; background: var(--season-accent); border-radius: 5px 5px 0 0; }
        .handleiding { margin: 22px 0; padding: 18px; border: 1px solid #e7ecea; border-radius: 8px; background: #fff; }
        .tutorial-nav { display: flex; gap: 8px; margin-top: 14px; overflow-x: auto; padding: 4px 2px 10px; scroll-snap-type: x proximity; }
        .tutorial-nav a { flex: 0 0 auto; display: inline-flex; align-items: center; gap: 7px; padding: 9px 13px; border: 1px solid #e0e7e3; border-radius: 999px; color: #47534e; text-decoration: none; background: #f8faf9; transition: color .22s ease, background-color .22s ease, border-color .22s ease, box-shadow .22s ease; scroll-snap-align: start; }
        .tutorial-nav a:hover { border-color: var(--season-accent); }
        .tutorial-nav a.is-active { color: #fff; background: var(--season-accent); border-color: var(--season-accent); box-shadow: 0 4px 12px color-mix(in srgb, var(--season-accent) 28%, transparent); }
        .tutorial-nav .stap-nummer { display: inline-grid; place-items: center; width: 22px; height: 22px; border-radius: 50%; background: rgba(255,255,255,.55); font-size: 12px; font-weight: 700; }
        .tutorial-doel { scroll-margin-top: 20px; }
        html { scroll-behavior: smooth; }
        @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } .tutorial-nav a { transition: none; } }
    </style>
</head>
<body class="seizoen-<?= htmlspecialchars($seizoenKey, ENT_QUOTES, 'UTF-8') ?>">

<div class="container">
    <h1>🛒 Mijn Inkooplijst</h1>

    <div class="seizoen-banner">
        <strong><?= $seizoen['icoon'] ?> <?= htmlspecialchars($seizoen['label'], ENT_QUOTES, 'UTF-8') ?>assortiment</strong>
        <span> — de pagina past zich automatisch aan het seizoen aan.</span>
    </div>

    <section class="handleiding" aria-labelledby="handleiding-titel">
        <h2 id="handleiding-titel">Nieuw hier? Zo gebruik je de pagina</h2>
        <p>Kies een stap om ernaartoe te gaan. De navigatie licht mee op terwijl je door de pagina bladert.</p>
        <nav class="tutorial-nav" aria-label="Handleiding navigatie">
            <a href="#kalender" data-tutorial-step><span class="stap-nummer">1</span> Kalender</a>
            <a href="#klantbestellingen" data-tutorial-step><span class="stap-nummer">2</span> Bestellingen</a>
            <a href="#promoties" data-tutorial-step><span class="stap-nummer">3</span> Promoties</a>
            <a href="#inkooplijst-formulier" data-tutorial-step><span class="stap-nummer">4</span> Artikel toevoegen</a>
            <a href="#mijn-inkooplijst" data-tutorial-step><span class="stap-nummer">5</span> Mijn lijst</a>
        </nav>
    </section>

    <section class="kalender tutorial-doel" id="kalender" aria-label="Maandkalender">
        <div class="kalender-kop">
            <a class="kalender-nav" href="?maand=<?= htmlspecialchars($vorigeMaand, ENT_QUOTES, 'UTF-8') ?>" aria-label="Vorige maand">←</a>
            <h2><?= htmlspecialchars($kalenderMaand->format('F Y'), ENT_QUOTES, 'UTF-8') ?></h2>
            <a class="kalender-nav" href="?maand=<?= htmlspecialchars($volgendeMaand, ENT_QUOTES, 'UTF-8') ?>" aria-label="Volgende maand">→</a>
        </div>
        <div class="kalender-grid">
            <?php foreach (['Ma', 'Di', 'Wo', 'Do', 'Vr', 'Za', 'Zo'] as $dag): ?>
                <div class="kalender-dag"><?= $dag ?></div>
            <?php endforeach; ?>
            <?php for ($leeg = 0; $leeg < $offsetEersteDag; $leeg++): ?>
                <div aria-hidden="true"></div>
            <?php endfor; ?>
            <?php for ($dag = 1; $dag <= $dagenInMaand; $dag++): ?>
                <?php $datum = $eersteDag->setDate((int) $kalenderMaand->format('Y'), $maandNummer, $dag); ?>
                <?php $datumFormat = $datum->format('Y-m-d'); ?>
                <a class="kalender-datum <?= $datumFormat === $vandaag->format('Y-m-d') ? 'is-vandaag' : '' ?> <?= $geselecteerdeDatum !== null && $datumFormat === $geselecteerdeDatum->format('Y-m-d') ? 'is-selected' : '' ?>"
                   href="?maand=<?= htmlspecialchars($kalenderMaand->format('Y-m'), ENT_QUOTES, 'UTF-8') ?>&amp;datum=<?= htmlspecialchars($datumFormat, ENT_QUOTES, 'UTF-8') ?>"
                   aria-label="Selecteer <?= htmlspecialchars($datum->format('d-m-Y'), ENT_QUOTES, 'UTF-8') ?>"
                   <?= $geselecteerdeDatum !== null && $datumFormat === $geselecteerdeDatum->format('Y-m-d') ? 'aria-current="date"' : '' ?>>
                    <?= $dag ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php if ($geselecteerdeDatum !== null): ?>
            <p aria-live="polite">Geselecteerde datum: <strong><?= htmlspecialchars($geselecteerdeDatum->format('d-m-Y'), ENT_QUOTES, 'UTF-8') ?></strong></p>
        <?php endif; ?>
    </section>

    <div class="overzicht-grid">
        <section class="overzicht-kaart tutorial-doel" id="klantbestellingen" aria-labelledby="klantbestellingen-titel">
            <h2 id="klantbestellingen-titel">Wat klanten bestelden</h2>
            <?php foreach ($klantBestellingen as $loopIndex => $bestelling): ?>
                <div class="bestelling">
                    <strong><?= htmlspecialchars($bestelling['klant'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <small> · <?= htmlspecialchars($bestelling['datum'], ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars($bestelling['tijd'], ENT_QUOTES, 'UTF-8') ?></small>
                    <div><?= htmlspecialchars($bestelling['producten'], ENT_QUOTES, 'UTF-8') ?></div>
                    <form method="POST">
                        <input type="hidden" name="actie" value="bestel_opnieuw">
                        <input type="hidden" name="bestelling" value="<?= (int) $loopIndex ?>">
                        <button type="submit" class="btn-herhaal">↻ Bestel opnieuw</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </section>

        <section class="overzicht-kaart tutorial-doel" id="promoties" aria-labelledby="promoties-titel">
            <h2 id="promoties-titel">In promotie</h2>
            <?php foreach ($promotieItems as $index => $promo): ?>
                <div class="promo-item">
                    <form method="POST">
                        <input type="hidden" name="actie" value="promotie">
                        <input type="hidden" name="promotie" value="<?= (int) $index ?>">
                        <span>
                            <strong><?= htmlspecialchars($promo['product'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <span class="promo-label"><?= htmlspecialchars($promo['label'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span>€ <?= number_format($promo['prijs'], 2, ',', '.') ?></span>
                        </span>
                        <button type="submit" class="btn-promo">+ Toevoegen</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </section>
    </div>

    <section class="seizoensaanbod" aria-labelledby="seizoensaanbod-titel">
        <h2 id="seizoensaanbod-titel"><?= $seizoen['icoon'] ?> Seizoensaanbod: <?= htmlspecialchars($seizoen['label'], ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="seizoensaanbod-intro">Deze artikelen zijn tijdelijk verkrijgbaar. Alle prijzen zijn per stuk.</p>
        <?php foreach ($actueleSeizoensItems as $index => $seizoensItem): ?>
            <div class="promo-item">
                <form method="POST">
                    <input type="hidden" name="actie" value="seizoensitem">
                    <input type="hidden" name="seizoensitem" value="<?= (int) $index ?>">
                    <span>
                        <strong><?= htmlspecialchars($seizoensItem['product'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                        <span>€ <?= number_format($seizoensItem['prijs'], 2, ',', '.') ?></span>
                    </span>
                    <button type="submit" class="btn-promo">+ Toevoegen</button>
                </form>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="piekmoment" aria-labelledby="piekmoment-titel">
        <h2 id="piekmoment-titel">Drukste bestelmoment</h2>
        <p>In deze voorbeeldbestellingen valt de piek tussen <strong><?= sprintf('%02d:00 en %02d:00 uur', (int) $druksteUur, (int) $druksteUur + 1) ?></strong> (<?= (int) $bestellingenPerUur[$druksteUur] ?> bestellingen).</p>
        <div class="uur-balken" aria-label="Bestellingen per uur">
            <?php foreach (range(9, 13) as $uur): ?>
                <?php $aantal = $bestellingenPerUur[$uur] ?? 0; ?>
                <div class="uur-kolom">
                    <div class="uur-waarde"><span class="uur-balk" style="--aantal: <?= (int) $aantal ?>" title="<?= (int) $aantal ?> bestellingen"></span></div>
                    <span><?= sprintf('%02d:00', $uur) ?></span><br>
                    <strong><?= (int) $aantal ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
        <small>Gebaseerd op de voorbeeldbestellingen hierboven.</small>
    </section>

    <section class="niet-beschikbaar" aria-labelledby="niet-beschikbaar-titel">
        <h2 id="niet-beschikbaar-titel">Tijdelijk niet beschikbaar</h2>
        <ul>
            <?php foreach ($nietBeschikbaar as $item): ?>
                <li>
                    <strong><?= htmlspecialchars($item['product'], ENT_QUOTES, 'UTF-8') ?></strong>
                    — weer beschikbaar vanaf <?= htmlspecialchars(date('d-m-Y', strtotime($item['terug'])), ENT_QUOTES, 'UTF-8') ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <!-- Formulier om product toe te voegen -->
    <form method="POST" class="form-groep" id="inkooplijst-formulier">
        <input type="hidden" name="actie" value="toevoegen">
        <input type="text" name="product" list="beschikbare-producten" placeholder="Kies of typ een product" autocomplete="off" required>
        <datalist id="beschikbare-producten">
            <?php foreach ($beschikbareProducten as $product): ?>
                <option value="<?= htmlspecialchars($product, ENT_QUOTES, 'UTF-8') ?>"></option>
            <?php endforeach; ?>
        </datalist>
        <input type="number" name="aantal" value="1" min="1" title="Aantal" required>
        <input type="number" name="prijs" id="product-prijs" step="0.01" min="0" placeholder="Prijs (€)" title="Prijs per stuk">
        <small id="prijs-hulp">De prijs wordt automatisch ingevuld voor een gekozen artikel en blijft aanpasbaar.</small>
        <button type="submit" class="btn-toevoegen">+ Toevoegen</button>
    </form>

    <!-- Overzicht van de lijst -->
    <div id="mijn-inkooplijst">
    <?php if (empty($_SESSION['inkooplijst'])): ?>
        <p class="leeg-bericht">Je inkooplijst is momenteel leeg. Voeg hierboven een product toe!</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Aantal</th>
                    <th>Prijs p/s</th>
                    <th>Subtotaal</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['inkooplijst'] as $item): ?>
                    <?php $subtotaal = $item['aantal'] * $item['prijs']; ?>
                    <tr class="<?= $item['gekocht'] ? 'gekocht' : '' ?>">
                        <td><?= htmlspecialchars($item['product'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <form method="POST" class="aantal-formulier" aria-label="Aantal wijzigen voor <?= htmlspecialchars($item['product'], ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="actie" value="aantal_wijzigen">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?>">
                                <input type="number" name="aantal" value="<?= (int) $item['aantal'] ?>" min="1" step="1" required aria-label="Nieuw aantal">
                                <button type="submit" class="btn-aantal" title="Aantal opslaan">Opslaan</button>
                            </form>
                        </td>
                        <td>€ <?= number_format($item['prijs'], 2, ',', '.') ?></td>
                        <td>€ <?= number_format($subtotaal, 2, ',', '.') ?></td>
                        <td style="display: flex; gap: 5px;">
                            <!-- Afvinken knop -->
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="actie" value="toggle">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn-actie" title="Afvinken">
                                    <?= $item['gekocht'] ? '↩️' : '✔️' ?>
                                </button>
                            </form>
                            <!-- Verwijderen knop -->
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="actie" value="verwijderen">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn-verwijder" title="Verwijderen">🗑️</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="totaal-rij">
                    <td colspan="3">Geschat totaalbedrag:</td>
                    <td colspan="2">€ <?= number_format($totaalBedrag, 2, ',', '.') ?></td>
                </tr>
            </tfoot>
        </table>

        <!-- Knop om hele lijst te wissen -->
        <form method="POST" onsubmit="return confirm('Weet je zeker dat je de hele lijst wilt wissen?');">
            <input type="hidden" name="actie" value="legen">
            <button type="submit" class="btn-legen">Lijst volledig wissen</button>
        </form>
    <?php endif; ?>
    </div>
</div>

<script>
    (function () {
        var scrollKey = 'timothy-page-scroll-y';
        if ('scrollRestoration' in window.history) {
            window.history.scrollRestoration = 'manual';
        }
        var saveScroll = function () {
            try {
                sessionStorage.setItem(scrollKey, String(window.scrollY));
            } catch (error) {
                // Scroll restoration is optional if browser storage is unavailable.
            }
        };
        var restoreScroll = function () {
            var savedPosition;
            try {
                savedPosition = sessionStorage.getItem(scrollKey);
                if (savedPosition === null) return;
                sessionStorage.removeItem(scrollKey);
            } catch (error) {
                return;
            }
            var scrollY = parseInt(savedPosition, 10);
            if (!Number.isFinite(scrollY)) return;
            requestAnimationFrame(function () {
                requestAnimationFrame(function () { window.scrollTo(0, scrollY); });
            });
        };

        window.addEventListener('pagehide', saveScroll);
        window.addEventListener('beforeunload', saveScroll);
        window.addEventListener('pageshow', restoreScroll);
        document.addEventListener('submit', function (event) {
            var form = event.target.closest('form[method="POST"], form[method="post"]');
            if (!form || event.defaultPrevented) return;

            event.preventDefault();
            var submitButton = event.submitter;
            if (submitButton) submitButton.disabled = true;

            fetch(window.location.href, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            }).then(function (response) {
                if (!response.ok) throw new Error('De lijst kon niet worden bijgewerkt.');
                return response.text();
            }).then(function (html) {
                var parsedPage = new DOMParser().parseFromString(html, 'text/html');
                var updatedList = parsedPage.getElementById('mijn-inkooplijst');
                var currentList = document.getElementById('mijn-inkooplijst');
                if (!updatedList || !currentList) throw new Error('De bijgewerkte lijst ontbreekt.');
                currentList.replaceWith(updatedList);
                if (form.id === 'inkooplijst-formulier') form.reset();
                if (submitButton && submitButton.isConnected) submitButton.disabled = false;
            }).catch(function () {
                saveScroll();
                window.location.reload();
            });
        });

        var productVeld = document.querySelector('input[name="product"]');
        var prijsVeld = document.getElementById('product-prijs');
        var prijzenPerProduct = <?= json_encode($prijzenPerProduct, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

        if (productVeld && prijsVeld) {
            productVeld.addEventListener('change', function () {
                var product = productVeld.value.trim();
                if (Object.prototype.hasOwnProperty.call(prijzenPerProduct, product)) {
                    prijsVeld.value = Number(prijzenPerProduct[product]).toFixed(2);
                } else {
                    prijsVeld.value = '';
                }
            });
        }

        var links = Array.from(document.querySelectorAll('[data-tutorial-step]'));
        var targets = links.map(function (link) {
            return document.querySelector(link.getAttribute('href'));
        }).filter(Boolean);

        if (!('IntersectionObserver' in window)) {
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            var visible = entries.filter(function (entry) { return entry.isIntersecting; });
            if (!visible.length) return;
            var current = visible.sort(function (a, b) {
                return b.intersectionRatio - a.intersectionRatio;
            })[0].target.id;
            links.forEach(function (link) {
                var active = link.getAttribute('href') === '#' + current;
                link.classList.toggle('is-active', active);
                if (active) link.setAttribute('aria-current', 'step');
                else link.removeAttribute('aria-current');
            });
        }, { rootMargin: '-15% 0px -60% 0px', threshold: [0, 0.2, 0.5] });

        targets.forEach(function (target) { observer.observe(target); });
    }());
</script>

</body>
</html>
