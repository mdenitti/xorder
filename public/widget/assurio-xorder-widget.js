(function () {
    'use strict';

    var STORAGE_KEY = 'assurio_xorder_dismissed_until';
    var ROOT_ID = 'assurio-xorder-sticky-root';

    // Config komt uit data-attributen op de script-tag (werkt met een strikte CSP, geen inline script nodig):
    // <script src="/xorder/widget/assurio-xorder-widget.js" data-start="2026-10-15" data-end="2027-01-05" defer></script>
    var CURRENT_SCRIPT = document.currentScript;

    var COPY = {
        nl: {
            badge: 'Feestseizoen 2026 · Gratis',
            pill: 'Traiteur? Beheer je feestbestellingen gratis',
            title: 'Feestbestellingen zonder gedoe, en in alle zekerheid',
            desc: 'Vanaf nu voor traiteurs, slagers en bakkers: 100% gratis dit seizoen met 1-klik lokale Excel-backup op je eigen pc.',
            cta: 'Ontdek XOrder by AssurIO',
            closeLabel: 'Verberg melding'
        },
        fr: {
            badge: 'Fêtes 2026 · Gratuit',
            pill: 'Traiteur ? Gérez vos commandes de fêtes gratuitement',
            title: 'Commandes de fêtes sans tracas, en toute sérénité',
            desc: 'Pour traiteurs, bouchers et boulangers : 100% gratuit cette saison avec backup Excel local en 1 clic sur votre PC.',
            cta: 'Découvrir XOrder by AssurIO',
            closeLabel: 'Masquer'
        }
    };

    function readDataConfig() {
        var d = (CURRENT_SCRIPT && CURRENT_SCRIPT.dataset) ? CURRENT_SCRIPT.dataset : {};
        var cfg = {};
        if (d.start) cfg.startDate = d.start;
        if (d.end) cfg.endDate = d.end;
        if (d.target) cfg.targetUrl = d.target;
        if (d.lang) cfg.lang = d.lang;
        if (d.position) cfg.position = d.position;
        if (d.dismissDays) cfg.dismissDays = parseInt(d.dismissDays, 10);
        if (d.track === 'false') cfg.track = false;
        return cfg;
    }

    // Testen op de live site buiten het seizoen of na wegklikken: voeg ?xorder-knop=toon toe aan de url
    function forcedByUrl() {
        try {
            return /[?&]xorder-knop=toon(&|$)/.test(window.location.search);
        } catch (e) {
            return false;
        }
    }

    function getDefaultConfig() {
        var userCfg = Object.assign({}, window.AssurioXOrderConfig || {}, readDataConfig());
        return {
            startDate: userCfg.startDate || '2026-10-15',
            endDate: userCfg.endDate || '2027-01-05',
            targetUrl: userCfg.targetUrl || 'https://assurio.be/xorder/',
            lang: userCfg.lang || 'auto',
            position: userCfg.position || 'bottom-right',
            dismissDays: typeof userCfg.dismissDays === 'number' && !isNaN(userCfg.dismissDays) ? userCfg.dismissDays : 14,
            forceShow: Boolean(userCfg.forceShow) || forcedByUrl(),
            track: userCfg.track !== false,
            trackUrl: userCfg.trackUrl || ''
        };
    }

    // Anonymous counters: no cookies, no IP, no personal data (see privacy page).
    function sendEvent(cfg, eventName, lang) {
        if (!cfg.track) return;
        try {
            var base = cfg.targetUrl.replace(/\?.*$/, '').replace(/\/?$/, '/');
            var url = cfg.trackUrl || (base + 'track.php');
            var payload = JSON.stringify({
                event: eventName,
                lang: lang,
                host: window.location.hostname
            });
            if (navigator.sendBeacon) {
                navigator.sendBeacon(url, payload);
            } else if (window.fetch) {
                window.fetch(url, { method: 'POST', body: payload, keepalive: true, mode: 'no-cors' });
            }
        } catch (e) {
            // Tracking must never break the host page
        }
    }

    function resolveLang(langCfg) {
        if (langCfg === 'nl' || langCfg === 'fr') {
            return langCfg;
        }
        var docLang = (document.documentElement.getAttribute('lang') || 'nl').toLowerCase();
        return docLang.indexOf('fr') === 0 ? 'fr' : 'nl';
    }

    function isWithinSeason(startStr, endStr) {
        // Lokale datum van de bezoeker (toISOString zou UTC geven en rond middernacht een dag verschuiven)
        var now = new Date();
        var today = now.getFullYear() + '-'
            + String(now.getMonth() + 1).padStart(2, '0') + '-'
            + String(now.getDate()).padStart(2, '0');
        return today >= startStr && today <= endStr;
    }

    function isDismissed() {
        try {
            var raw = window.localStorage.getItem(STORAGE_KEY);
            if (!raw) return false;
            var until = parseInt(raw, 10);
            return !isNaN(until) && Date.now() < until;
        } catch (e) {
            return false;
        }
    }

    function dismissFor(days) {
        try {
            var until = Date.now() + (Math.max(1, days) * 86400 * 1000);
            window.localStorage.setItem(STORAGE_KEY, String(until));
        } catch (e) {
            // Ignore storage restrictions
        }
    }

    function unmount() {
        var existing = document.getElementById(ROOT_ID);
        if (existing && existing.parentNode) {
            existing.parentNode.removeChild(existing);
        }
    }

    function mount(overrideConfig) {
        unmount();

        var cfg = Object.assign({}, getDefaultConfig(), overrideConfig || {});
        if (!cfg.forceShow && !isWithinSeason(cfg.startDate, cfg.endDate)) {
            return false;
        }
        if (!cfg.forceShow && isDismissed()) {
            return false;
        }

        var lang = resolveLang(cfg.lang);
        var text = COPY[lang] || COPY.nl;

        var wrap = document.createElement('div');
        wrap.id = ROOT_ID;
        wrap.className = 'assurio-xorder-wrap' + (cfg.position === 'bottom-left' ? ' assurio-xorder-pos-left' : '');
        wrap.setAttribute('role', 'region');
        wrap.setAttribute('aria-label', 'XOrder by AssurIO');

        var card = document.createElement('div');
        card.className = 'assurio-xorder-card';

        var cardHead = document.createElement('div');
        cardHead.className = 'assurio-xorder-card-head';

        var badge = document.createElement('span');
        badge.className = 'assurio-xorder-badge';
        badge.textContent = text.badge;

        var closeCardBtn = document.createElement('button');
        closeCardBtn.type = 'button';
        closeCardBtn.className = 'assurio-xorder-close-card';
        closeCardBtn.setAttribute('aria-label', text.closeLabel);
        closeCardBtn.textContent = '×';
        closeCardBtn.addEventListener('click', function () {
            wrap.classList.remove('is-expanded');
        });

        cardHead.appendChild(badge);
        cardHead.appendChild(closeCardBtn);

        var title = document.createElement('h3');
        title.className = 'assurio-xorder-card-title';
        title.textContent = text.title;

        var desc = document.createElement('p');
        desc.className = 'assurio-xorder-card-desc';
        desc.textContent = text.desc;

        var cta = document.createElement('a');
        cta.className = 'assurio-xorder-card-cta';
        cta.href = cfg.targetUrl + (cfg.targetUrl.indexOf('?') === -1 ? '?lang=' + lang : '&lang=' + lang)
            + '&utm_source=assurio-site&utm_medium=sticky-button&utm_campaign=feestseizoen-2026';
        cta.textContent = text.cta + ' ->';
        cta.addEventListener('click', function () {
            sendEvent(cfg, 'click', lang);
        });

        card.appendChild(cardHead);
        card.appendChild(title);
        card.appendChild(desc);
        card.appendChild(cta);

        var pillRow = document.createElement('div');
        pillRow.className = 'assurio-xorder-pill-row';

        var pillBtn = document.createElement('button');
        pillBtn.type = 'button';
        pillBtn.className = 'assurio-xorder-pill-btn';

        var icon = document.createElement('span');
        icon.className = 'assurio-xorder-pill-icon';
        var svgNS = 'http://www.w3.org/2000/svg';
        var iconSvg = document.createElementNS(svgNS, 'svg');
        iconSvg.setAttribute('width', '15');
        iconSvg.setAttribute('height', '15');
        iconSvg.setAttribute('viewBox', '0 0 24 24');
        iconSvg.setAttribute('aria-hidden', 'true');
        var starPath = document.createElementNS(svgNS, 'path');
        starPath.setAttribute('d', 'M12 2.5 Q13.1 10.9 21.5 12 Q13.1 13.1 12 21.5 Q10.9 13.1 2.5 12 Q10.9 10.9 12 2.5 Z');
        starPath.setAttribute('fill', '#ffffff');
        iconSvg.appendChild(starPath);
        icon.appendChild(iconSvg);

        var label = document.createElement('span');
        label.textContent = text.pill;

        pillBtn.appendChild(icon);
        pillBtn.appendChild(label);
        pillBtn.addEventListener('click', function () {
            var expanded = wrap.classList.toggle('is-expanded');
            if (expanded) {
                sendEvent(cfg, 'expand', lang);
            }
        });

        var dismissBtn = document.createElement('button');
        dismissBtn.type = 'button';
        dismissBtn.className = 'assurio-xorder-dismiss';
        dismissBtn.setAttribute('aria-label', text.closeLabel);
        dismissBtn.setAttribute('title', text.closeLabel);
        dismissBtn.textContent = '×';
        dismissBtn.addEventListener('click', function (ev) {
            ev.stopPropagation();
            sendEvent(cfg, 'dismiss', lang);
            dismissFor(cfg.dismissDays);
            unmount();
        });

        pillRow.appendChild(pillBtn);
        pillRow.appendChild(dismissBtn);

        wrap.appendChild(card);
        wrap.appendChild(pillRow);
        document.body.appendChild(wrap);

        // Count one impression per browser session
        try {
            if (!window.sessionStorage.getItem('assurio_xorder_seen')) {
                window.sessionStorage.setItem('assurio_xorder_seen', '1');
                sendEvent(cfg, 'impression', lang);
            }
        } catch (e) {
            sendEvent(cfg, 'impression', lang);
        }
        return true;
    }

    window.AssurioXOrderWidget = {
        mount: mount,
        unmount: unmount,
        resetDismiss: function () {
            try {
                window.localStorage.removeItem(STORAGE_KEY);
            } catch (e) {
                // Ignore
            }
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            mount();
        });
    } else {
        mount();
    }
})();
