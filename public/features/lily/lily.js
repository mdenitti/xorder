(function () {
    'use strict';

    var form = document.getElementById('lily-filter-form');
    var growthInput = document.getElementById('lily-growth');
    var growthHint = document.getElementById('lily-growth-hint');
    var growthWrap = growthInput ? growthInput.closest('.lily-percent-wrap') : null;
    var submitBtn = form ? form.querySelector('.lily-filter-btn') : null;
    var btnLabel = submitBtn ? submitBtn.querySelector('.lily-btn-label') : null;
    var dashboardRoot = document.getElementById('lily-dashboard-root');
    var periodMeta = document.getElementById('lily-period-meta');

    if (!form || !growthInput || !dashboardRoot) {
        return;
    }

    var defaultHint = 'Tussen 2% en 99%';
    var defaultBtnText = btnLabel ? btnLabel.textContent : 'Toepassen';
    var isLoading = false;

    function validateGrowth() {
        var raw = String(growthInput.value || '').trim().replace(',', '.');
        var num = Number(raw);
        var valid = raw !== '' && Number.isFinite(num) && num > 1 && num < 100;

        if (growthWrap) {
            growthWrap.classList.toggle('is-invalid', !valid);
        }
        growthInput.setAttribute('aria-invalid', valid ? 'false' : 'true');

        if (growthHint) {
            growthHint.textContent = valid
                ? defaultHint
                : 'Moet groter zijn dan 1% en kleiner dan 100% (2–99%)';
        }

        return valid;
    }

    function setLoadingState(loading) {
        isLoading = loading;
        dashboardRoot.classList.toggle('lily-is-refreshing', loading);
        if (submitBtn) {
            submitBtn.disabled = loading;
            submitBtn.classList.toggle('is-loading', loading);
        }
        if (btnLabel) {
            btnLabel.textContent = loading ? 'Vernieuwen...' : defaultBtnText;
        }
    }

    function delay(ms) {
        return new Promise(function (resolve) {
            window.setTimeout(resolve, ms);
        });
    }

    function buildTargetUrl() {
        var action = form.getAttribute('action') || window.location.pathname;
        var url = new URL(action, window.location.origin);
        var params = new URLSearchParams(new FormData(form));
        url.search = params.toString();
        return url.toString();
    }

    function syncFormFromRoot(newRoot) {
        if (!newRoot) return;
        var serverGrowth = newRoot.getAttribute('data-growth');
        var serverYear = newRoot.getAttribute('data-year');
        var yearSelect = document.getElementById('lily-year');
        if (serverGrowth) {
            growthInput.value = serverGrowth;
            validateGrowth();
        }
        if (yearSelect && serverYear) {
            yearSelect.value = serverYear;
        }
    }

    function refreshDashboard(targetUrl, pushHistory) {
        if (isLoading) return;
        setLoadingState(true);

        var fetchPromise = fetch(targetUrl, {
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'fetch'
            }
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.text();
        });

        Promise.all([fetchPromise, delay(320)])
            .then(function (results) {
                var html = results[0];
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var nextRoot = doc.getElementById('lily-dashboard-root');
                var nextMeta = doc.getElementById('lily-period-meta');

                if (!nextRoot) {
                    window.location.assign(targetUrl);
                    return;
                }

                dashboardRoot.innerHTML = nextRoot.innerHTML;
                dashboardRoot.setAttribute('data-growth', nextRoot.getAttribute('data-growth') || '');
                dashboardRoot.setAttribute('data-year', nextRoot.getAttribute('data-year') || '');

                if (periodMeta && nextMeta) {
                    periodMeta.textContent = nextMeta.textContent;
                }

                syncFormFromRoot(nextRoot);

                if (pushHistory && window.history && window.history.pushState) {
                    window.history.pushState({ lilyUrl: targetUrl }, '', targetUrl);
                }

                setLoadingState(false);
            })
            .catch(function () {
                window.location.assign(targetUrl);
            });
    }

    growthInput.addEventListener('input', validateGrowth);
    growthInput.addEventListener('blur', validateGrowth);

    form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        if (!validateGrowth()) {
            growthInput.focus();
            return;
        }
        var parsed = Math.round(Number(String(growthInput.value).trim().replace(',', '.')));
        growthInput.value = String(Math.max(2, Math.min(99, parsed)));
        refreshDashboard(buildTargetUrl(), true);
    });

    window.addEventListener('popstate', function () {
        refreshDashboard(window.location.href, false);
    });
})();
