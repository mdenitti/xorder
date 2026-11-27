// Enkel JavaScript voor de wesley1-feature: een teller onder het tekstvak.
(function () {
    'use strict';

    var textarea = document.getElementById('wesley1-body');
    var counter = document.getElementById('wesley1-count');

    if (!textarea || !counter) {
        return;
    }

    textarea.addEventListener('input', function () {
        counter.textContent = textarea.value.length;
    });
})();
