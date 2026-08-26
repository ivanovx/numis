(function () {
    'use strict';

    var form = document.getElementById('coin-filter-form');
    var list = document.getElementById('coin-list');

    if (!form || !list) return;

    function loadResults(url) {
        list.innerHTML = '<p class="text-center mt-4">Loading…</p>';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) { return res.text(); })
            .then(function (html) {
                list.innerHTML = html;
                window.history.pushState({}, '', url);
            })
            .catch(function () {
                list.innerHTML = '<p class="text-center mt-4">Something went wrong. Please try again.</p>';
            });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var params = new URLSearchParams(new FormData(form)).toString();
        loadResults(form.action + '?' + params);
    });

    // Pagination links rendered inside #coin-list are plain <a> tags from
    // Laravel's paginator — intercept clicks on them too.
    list.addEventListener('click', function (e) {
        var link = e.target.closest('a');
        if (!link || !list.contains(link)) return;

        e.preventDefault();
        loadResults(link.href);
    });
})();
