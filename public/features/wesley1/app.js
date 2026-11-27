// JavaScript voor de feature van wesley1 (feestelijke menukaart). Geen inline JS in de pagina.
// Zonder JavaScript werkt alles ook: dan zijn alle formulieren gewoon zichtbaar.
(function () {
    'use strict';

    var app = document.querySelector('.wesley1-app');
    if (!app) {
        return;
    }
    // Vanaf nu mag de CSS formulieren verbergen (zie .wesley1-js in style.css)
    app.classList.add('wesley1-js');

    // ---------- Aanpassen: formulier onder een rij openen en sluiten ----------
    function zetBewerk(id, open) {
        var formulier = document.getElementById(id);
        var knop = app.querySelector('[data-wesley1-bewerk="' + id + '"]');
        if (!formulier) {
            return;
        }
        formulier.hidden = !open;
        if (knop) {
            knop.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        if (open) {
            var eersteVeld = formulier.querySelector('input[name="naam"]');
            if (eersteVeld) {
                eersteVeld.focus();
            }
        }
    }

    Array.prototype.forEach.call(app.querySelectorAll('[data-wesley1-bewerk]'), function (knop) {
        var id = knop.getAttribute('data-wesley1-bewerk');
        zetBewerk(id, false);
        knop.addEventListener('click', function () {
            zetBewerk(id, knop.getAttribute('aria-expanded') !== 'true');
        });
    });

    Array.prototype.forEach.call(app.querySelectorAll('[data-wesley1-sluit]'), function (knop) {
        knop.addEventListener('click', function () {
            var id = knop.getAttribute('data-wesley1-sluit');
            zetBewerk(id, false);
            var bewerkKnop = app.querySelector('[data-wesley1-bewerk="' + id + '"]');
            if (bewerkKnop) {
                bewerkKnop.focus();
            }
        });
    });

    // ---------- Tabbladen in het paneel "Toevoegen" ----------
    var tabs = app.querySelectorAll('[data-wesley1-tab]');

    function kiesTab(naam) {
        Array.prototype.forEach.call(tabs, function (tab) {
            tab.setAttribute('aria-selected', tab.getAttribute('data-wesley1-tab') === naam ? 'true' : 'false');
        });
        Array.prototype.forEach.call(app.querySelectorAll('[data-wesley1-paneel]'), function (paneel) {
            paneel.hidden = paneel.getAttribute('data-wesley1-paneel') !== naam;
        });
    }

    if (tabs.length > 0) {
        var gekozen = app.querySelector('[data-wesley1-tab][aria-selected="true"]') || tabs[0];
        kiesTab(gekozen.getAttribute('data-wesley1-tab'));
        Array.prototype.forEach.call(tabs, function (tab) {
            tab.addEventListener('click', function () {
                kiesTab(tab.getAttribute('data-wesley1-tab'));
            });
        });
    }

    // ---------- Bevestiging voor een item van de kaart verwijderd wordt ----------
    Array.prototype.forEach.call(app.querySelectorAll('form[data-wesley1-bevestig]'), function (formulier) {
        formulier.addEventListener('submit', function (gebeurtenis) {
            if (!window.confirm(formulier.getAttribute('data-wesley1-bevestig'))) {
                gebeurtenis.preventDefault();
            }
        });
    });

    // ---------- Afdrukken: de print-CSS in style.css toont enkel de kaart ----------
    var afdrukknop = document.getElementById('wesley1-afdrukken');
    if (afdrukknop) {
        afdrukknop.addEventListener('click', function () {
            window.print();
        });
    }
})();
