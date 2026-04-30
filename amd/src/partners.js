define(['core/ajax'], function(Ajax) {

    function renderCard(target, data) {
        target.insertAdjacentHTML('beforeend', `
            <img src="${data.logo}" class="rbt-partner-logo" title="${data.name}">
        `);
    }

    function renderList(target, data) {
        target.insertAdjacentHTML('beforeend', `
            <img src="${data.logo}" class="rbt-partner-logo" title="${data.name}">
        `);
    }

    function renderSummary(target, data) {
        target.insertAdjacentHTML('beforeend', `
            <img src="${data.logo}" class="rbt-partner-logo" title="${data.name}">
        `);
    }

   function renderPartner(card, data) {
        if (!data || !data.found) {
            return;
        }

        const cardtarget = card.querySelector('.partner-card');

        if (cardtarget) {
            renderCard(cardtarget, data);
            return;
        }

        const listtarget = card.querySelector('.partner-list');

        if (listtarget) {
            renderList(listtarget, data);
            return;
        }

        const summarytarget = card.querySelector('.partner-summary');

        if (summarytarget) {
            renderSummary(summarytarget, data);
        }
    }

    function scanCards() {

        const cards = document.querySelectorAll('[data-course-id]');

        cards.forEach(function(card) {

            if (card.dataset.loaded === '1') {
                return;
            }

            const courseid = parseInt(card.dataset.courseId, 10);

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

        scanCards();

        new MutationObserver(scanCards).observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    return {
        init: init
    };
});