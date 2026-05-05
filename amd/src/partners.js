define(['core/ajax'], function(Ajax) {
    var started = false;
    var observer = null;

    function escapeAttribute(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function renderLogo(target, data) {
        target.innerHTML = '';
        target.insertAdjacentHTML('beforeend', `
            <img src="${escapeAttribute(data.logo)}" class="rbt-partner-logo" title="${escapeAttribute(data.name)}" alt="${escapeAttribute(data.name)}">
        `);
    }

    function renderPartner(card, data) {
        if (!data || !data.found || !data.logo) {
            return;
        }

        var target = card.querySelector('.partner-card, .partner-list, .partner-summary');

        if (target) {
            renderLogo(target, data);
        }
    }

    function scanCards() {
        var cards = [];

        document.querySelectorAll('.partner-card, .partner-list, .partner-summary').forEach(function(target) {
            var card = target.closest('[data-course-id]');

            if (card && cards.indexOf(card) === -1) {
                cards.push(card);
            }
        });

        cards.forEach(function(card) {

            if (card.dataset.loaded === '1') {
                return;
            }

            var courseid = parseInt(card.dataset.courseId, 10);

            if (!courseid) {
                return;
            }

            card.dataset.loaded = '1';

            Ajax.call([{
                methodname: 'local_edukav_get_course_partner',
                args: {courseid: courseid}
            }])[0].done(function(response) {
                renderPartner(card, response);
            });
        });
    }

    function init() {
        if (started) {
            scanCards();
            return;
        }

        started = true;
        scanCards();

        if (window.MutationObserver) {
            observer = new MutationObserver(function() {
                scanCards();
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    }

    return {
        init: init
    };
});
