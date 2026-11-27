(function () {
    'use strict';

    var previewTargets = {
        subject: document.getElementById('joachim-preview-subject'),
        preheader: document.getElementById('joachim-preview-preheader'),
        headline: document.getElementById('joachim-preview-headline'),
        body: document.getElementById('joachim-preview-body'),
        'cta-label': document.getElementById('joachim-preview-cta')
    };
    var previewDefaults = {
        subject: 'Je nieuwsbriefonderwerp',
        preheader: 'Een persoonlijk bericht van jou',
        headline: 'Jouw titel komt hier',
        body: 'Je nieuwsbriefbericht verschijnt hier zodra je begint met schrijven.'
    };

    document.querySelectorAll('[data-joachim-preview]').forEach(function (field) {
        var target = previewTargets[field.dataset.joachimPreview];
        if (!target) {
            return;
        }

        var updatePreview = function () {
            var value = field.value.trim();
            target.textContent = value || previewDefaults[field.dataset.joachimPreview] || '';

            if (field.dataset.joachimPreview === 'cta-label') {
                var wrapper = document.getElementById('joachim-preview-cta-wrap');
                if (wrapper) {
                    wrapper.hidden = value === '';
                }
            }
        };

        field.addEventListener('input', updatePreview);
    });

    var body = document.getElementById('joachim-body');
    var bodyCount = document.getElementById('joachim-body-count');
    if (body && bodyCount) {
        body.addEventListener('input', function () {
            bodyCount.textContent = Array.from(body.value).length;
        });
    }

    document.querySelectorAll('form[data-joachim-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.joachimConfirm || 'Weet je het zeker?')) {
                event.preventDefault();
            }
        });
    });
})();
