# Seizoensknop voor assurio.be

De bestanden van de knop staan in `public/widget/` en worden mee gedeployed met de app.
Op productie zijn ze bereikbaar op:

- `https://assurio.be/xorder/widget/assurio-xorder-widget.css`
- `https://assurio.be/xorder/widget/assurio-xorder-widget.js`

## Op de statische site van assurio.be plaatsen

Plak dit net voor `</body>` op de pagina's waar de knop mag verschijnen (bv. `index.html`):

```html
<link rel="stylesheet" href="/xorder/widget/assurio-xorder-widget.css">
<script src="/xorder/widget/assurio-xorder-widget.js"
        data-start="2026-10-15"
        data-end="2027-01-05"
        data-target="/xorder/"
        data-position="bottom-right"
        defer></script>
```

Geen inline script nodig, dus het werkt met de strikte Content-Security-Policy van assurio.be.

- Buiten de periode `data-start` tot `data-end` verschijnt de knop niet.
- Taal volgt `<html lang>` (nl of fr). Forceren kan met `data-lang="fr"`.
- Wegklikken (x) verbergt de knop 14 dagen (`data-dismiss-days`).
- Testen buiten het seizoen of na wegklikken: zet `?xorder-knop=toon` achter de url.
- Anonieme tellers (getoond, uitgeklapt, aangeklikt, weggeklikt) gaan naar `/xorder/track.php`.
  Uitzetten met `data-track="false"`. Bekijken met `php bin/widget-stats.php` op de server.
