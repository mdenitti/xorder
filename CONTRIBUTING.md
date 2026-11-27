# Meewerken aan XOrder (klasproject)

Iedereen bouwt een eigen feature. Om merge-conflicten te vermijden raakt niemand gezamenlijke bestanden aan.

## Wat mag je aanpassen?

| Mag | Mag niet |
| --- | --- |
| `features/<voornaam>.php` | alles in `src/`, `views/`, `lang/`, `config/`, `database/` |
| `public/features/<voornaam>/` (css, js, afbeeldingen) | `public/index.php`, `public/assets/`, `.htaccess` |
| | `README.md`, `.github/`, bestanden van een andere cursist |

`<voornaam>` is in kleine letters, zonder spaties of accenten (bv. `jan`, `anne-sophie`).
Je pagina verschijnt vanzelf op `/feature.php?naam=<voornaam>` en in het menu **Features**. Je hoeft geen route of menu toe te voegen.

Een automatische controle (GitHub Actions) keurt je pull request af als je een ander bestand aanraakt.

## Zo werk je (in VS Code)

1. **Clonen:** `Ctrl+Shift+P` (Mac: `Cmd+Shift+P`), kies **Git: Clone** en plak `https://github.com/mdenitti/xorder.git`.
2. **Branch:** klik linksonder op `main`, kies **Create new branch...** en typ `feature/<voornaam>`.
3. **Starten:** open **Terminal > New Terminal**, typ eenmalig `php bin/seed.php` voor testdata en start dan de server met `php -S 127.0.0.1:8787 -t public`. Meld je aan met `traiteur@voorbeeld.test` en wachtwoord `xorder123` (meer accounts in de [README](README.md#dummydata-laden)).
4. **Voorbeeld kopiëren:** kopieer `features/voorbeeld.php` naar `features/<voornaam>.php` en de map `public/features/voorbeeld` naar `public/features/<voornaam>`. Bouw je feature.
5. **Stagen:** in **Source Control** klik je op de **+** naast elk van jouw eigen bestanden. Nooit **Stage All Changes**.
6. **Committen:** bericht `feat(<voornaam>): korte beschrijving`, dan **Commit**. Vraagt VS Code om alles te stagen? Kies **No**.
7. **Publiceren:** klik op **Publish Branch**. Je hebt geen schrijfrechten op deze repo, dus VS Code stelt voor een fork te maken: kies **Create Fork**.
8. **Pull request:** klik op **Create PR** in de melding van VS Code. Met **Open on GitHub** bekijk je je PR op github.com.
9. **Feedback verwerken:** aanpassen, stagen, committen en **Sync Changes**. Je PR werkt zichzelf bij.
10. **Na de merge:** ga naar `main`, kies **Git: Pull from...**, dan de remote `upstream` (de repo van de docent) en `main`.

## Afspraken in je code

- Tabellen beginnen met je naam: `jan_allergenen`.
- CSS-klassen beginnen met je naam: `.jan-kaart`.
- Gegevens altijd filteren op `$user->id`.
- Tekst die je toont altijd escapen met `$e(...)`.
- Geen inline JavaScript (`<script>...</script>` of `onclick`): de beveiliging (CSP) blokkeert dat. Zet je code in `public/features/<voornaam>/app.js`.
- Elk POST-formulier krijgt `<?= $csrfField() ?>`.

Bekijk `features/voorbeeld.php`: alles wat je ter beschikking hebt staat bovenaan uitgelegd.
