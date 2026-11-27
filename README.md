# XOrder by AssurIO

Lichte, modulaire OOP PHP + MySQL webapplicatie voor traiteurs, slagers, bakkers en speciaalzaken om feestbestellingen zonder gedoe en in alle zekerheid te beheren, inclusief 1-klik lokale Excel-backup (`.xlsx`).

Live: https://assurio.be/xorder/

## 1. Lokaal starten (SQLite, zonder extra installatie)

```bash
php -S 127.0.0.1:8787 -t public
```

- Landingspagina + registratie (NL/FR): `http://127.0.0.1:8787/`
- App dashboard (na registratie/login): `http://127.0.0.1:8787/dashboard.php`
- Features van de klas: `http://127.0.0.1:8787/features.php`

### Pagina's zonder rewrites

Er is geen `.htaccess`-routing: elke pagina is een gewoon `.php`-bestand in `public/` (`login.php`, `orders.php`, `order-edit.php?id=5`, ...).
Zo'n bestand laadt `public/_bootstrap.php` en roept één controller-methode aan via `xorder_page(get: ..., post: ...)`.
In de code schrijf je links nog altijd als logisch pad, bv. `$url('/app/orders/5/edit')`. De vertaling naar het bestand staat op één plek: `src/Core/Routes.php`.
Nieuwe pagina? Maak het bestand in `public/` en voeg één regel toe aan `Routes`.

Zonder `config/config.php` gebruikt de app `config/config.example.php` (SQLite in `storage/xorder.sqlite`, debug aan). Tabellen worden automatisch aangemaakt bij het eerste verzoek.

### Dummydata laden

```bash
php bin/seed.php
```

Vult je lokale database met [database/seed.sql](database/seed.sql): drie zaken, 20 producten en 22 bestellingen verspreid over alle feestdagen, tijdsloten, statussen en betaalstatussen. Werkt met SQLite en MySQL, zonder extra tools.

| Account | Zaak | Taal |
| --- | --- | --- |
| `traiteur@voorbeeld.test` | Traiteur De Gouden Lepel (12 bestellingen) | NL |
| `slager@voorbeeld.test` | Slagerij Van Damme (6 bestellingen) | NL |
| `bakker@voorbeeld.test` | Boulangerie Le Petit Four (4 bestellingen) | FR |

Wachtwoord voor alle drie: `xorder123`. Let op: het script wist eerst alle bestaande rijen, zodat iedereen vanaf dezelfde beginsituatie vertrekt. Draai het opnieuw wanneer je je data wil resetten. Op productie weigert het script (`app.env = production`) en de bestanden gaan niet mee met de deploy.

## 2. Features van cursisten

Elke cursist maakt `features/<voornaam>.php` met eigen assets in `public/features/<voornaam>/`.
De pagina verschijnt vanzelf op `/feature.php?naam=<voornaam>` en in het menu. Lees [CONTRIBUTING.md](CONTRIBUTING.md).
Op productie staan features uit (`features.enabled = false`) en worden ze niet mee gedeployed.

## 3. Productie

Zie [DEPLOY.md](DEPLOY.md). Kort: `bash bin/deploy-assurio.sh --dry-run`, daarna zonder `--dry-run`.

## 4. Seizoensknop op assurio.be

Zie [widget/README.md](widget/README.md). De bestanden staan in `public/widget/`.

## 5. Extra's

- **Wachtwoord vergeten:** `/forgot.php` en `/reset.php?token=<token>` (token 1 uur geldig, eenmalig, enkel gehasht opgeslagen). Links in mails komen uit `app.url`, nooit uit de Host-header.
- **Brute-force bescherming:** max 8 loginpogingen per e-mail en IP per 15 minuten, max 5 resetaanvragen per IP per uur.
- **Juridische pagina's:** `/privacy` en `/cookies` (NL/FR).
- **Statistieken seizoensknop:** `php bin/widget-stats.php`.
- **Engels later activeren:** kopieer `lang/en.php.example` naar `lang/en.php`, vertaal, en voeg `'en'` toe aan `supported_locales`.
