# Deploy naar assurio.be/xorder

## Opbouw op de server (hellocodebe@ssh.hellocode.be)

| Pad | Inhoud |
| --- | --- |
| `~/xorder-app/` | app-code, `config/config.php`, `storage/` (buiten de webroot) |
| `~/subsites/assurio.be/xorder/` | enkel de inhoud van `public/` |
| `~/subsites/assurio.be/xorder/xorder-path.php` | `return '/data/sites/web/hellocodebe/xorder-app';` |

Database: MySQL bij Combell (`ID324796_xorder`). Tabellen worden automatisch aangemaakt bij het eerste verzoek.

## Eerste keer

1. `config/config.production.example.php` als `~/xorder-app/config/config.php` op de server zetten en de MySQL-gegevens invullen. Nooit in git.
2. Controleer dat `noreply@assurio.be` mag versturen (SPF van assurio.be), anders komen reset-mails in spam.

## Elke deploy

```bash
bash bin/deploy-assurio.sh --dry-run   # bekijk wat er verandert
bash bin/deploy-assurio.sh             # echt deployen (vraagt bevestiging)
```

Wordt nooit overschreven: `config/config.php`, `storage/`, `xorder-path.php`.
Gaat nooit mee: `features/`, `public/features/`, `.git`, `mcp-crashcourse/`.

## Rooktest na deploy

- [ ] `https://assurio.be/xorder/` laadt in NL, `?lang=fr` in FR
- [ ] Registreren met testaccount, voorbeelddata verschijnt, dashboard laadt
- [ ] Bestelling aanmaken, keukenlijst klopt, printknop werkt
- [ ] Excel-backup downloaden en openen (4 tabbladen)
- [ ] Wachtwoord vergeten: mail komt aan, link begint met `https://assurio.be/xorder/reset.php?token=`
- [ ] `/xorder/features.php` geeft 404 (features staan uit op productie)
- [ ] `/xorder/widget/assurio-xorder-widget.js` is bereikbaar
- [ ] Browserconsole: geen CSP-fouten
- [ ] Testaccount verwijderen

## Seizoensknop op assurio.be

Pas als de rooktest klopt: snippet uit `widget/README.md` in de statische pagina's van assurio.be plakken.

## Terugdraaien

De vorige versie staat in git: `git checkout <vorige-commit>` en opnieuw `bash bin/deploy-assurio.sh`.
De database blijft onaangeroerd.
