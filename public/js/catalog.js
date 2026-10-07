(function () {
    'use strict';

    var siteNavbar = document.getElementById('site-navbar');
    var filterNavbar = document.getElementById('catalog-filter-navbar');

    function updateNavbarHeights() {
        if (!siteNavbar || !filterNavbar) return;

        document.documentElement.style.setProperty('--site-navbar-height', siteNavbar.getBoundingClientRect().height + 'px');
        document.documentElement.style.setProperty('--catalog-filter-height', filterNavbar.getBoundingClientRect().height + 'px');
    }

    window.addEventListener('DOMContentLoaded', updateNavbarHeights);
    window.addEventListener('load', updateNavbarHeights);
    window.addEventListener('resize', updateNavbarHeights);

    if (window.ResizeObserver && filterNavbar) {
        new ResizeObserver(updateNavbarHeights).observe(filterNavbar);
    }

    // ------------------------------------------------------------------
    // Global lightbox — works on any [data-lightbox-src] element, on
    // every page (not just the catalog list/detail pages).
    // ------------------------------------------------------------------
    (function () {
        var overlay = document.getElementById('catalog-lightbox');
        var img = document.getElementById('catalog-lightbox-img');
        var closeBtn = overlay ? overlay.querySelector('.catalog-lightbox-close') : null;
        if (!overlay || !img) return;

        function open(src, alt) {
            img.src = src;
            img.alt = alt || '';
            overlay.hidden = false;
            document.body.classList.add('catalog-lightbox-open');
        }

        function close() {
            overlay.hidden = true;
            img.src = '';
            document.body.classList.remove('catalog-lightbox-open');
        }

        document.addEventListener('click', function (e) {
            var trigger = e.target.closest('[data-lightbox-src]');
            if (trigger) {
                e.preventDefault();
                open(trigger.getAttribute('data-lightbox-src'), trigger.getAttribute('data-lightbox-alt'));
            }
        });

        closeBtn && closeBtn.addEventListener('click', close);
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) close();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !overlay.hidden) close();
        });
    })();

    // ------------------------------------------------------------------
    // Sticky back-to-top button.
    // ------------------------------------------------------------------
    (function () {
        var btn = document.getElementById('catalog-back-to-top');
        if (!btn) return;

        var THRESHOLD = 480;

        window.addEventListener('scroll', function () {
            btn.hidden = window.scrollY < THRESHOLD;
        }, { passive: true });

        btn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    })();

    // ------------------------------------------------------------------
    // Prev/next coin navigation via left/right arrow keys on the detail page.
    // ------------------------------------------------------------------
    (function () {
        var prevLink = document.querySelector('.catalog-neighbor-prev');
        var nextLink = document.querySelector('.catalog-neighbor-next');
        if (!prevLink && !nextLink) return;

        document.addEventListener('keydown', function (e) {
            // Don't hijack arrow keys while the user is typing in a field.
            var tag = (e.target.tagName || '').toLowerCase();
            if (tag === 'input' || tag === 'textarea' || tag === 'select') return;

            if (e.key === 'ArrowLeft' && prevLink) window.location.href = prevLink.href;
            if (e.key === 'ArrowRight' && nextLink) window.location.href = nextLink.href;
        });
    })();

    // ------------------------------------------------------------------
    // PDF export button — carries the current (possibly unsubmitted)
    // filter values so the download always matches what's on screen.
    // ------------------------------------------------------------------
    (function () {
        var exportBtn = document.getElementById('catalog-export-pdf-btn');
        var filterForm = document.getElementById('coin-filter-form');
        if (!exportBtn || !filterForm) return;

        var baseHref = exportBtn.getAttribute('href');

        exportBtn.addEventListener('click', function () {
            var params = new URLSearchParams(new FormData(filterForm)).toString();
            exportBtn.setAttribute('href', baseHref + (params ? '?' + params : ''));
        });
    })();

    var form = document.getElementById('coin-filter-form');
    var list = document.getElementById('coin-list');

    // ------------------------------------------------------------------
    // Active-filter count badge on the funnel button.
    // ------------------------------------------------------------------
    function updateActiveFilterBadge() {
        if (!form) return;

        var badge = document.getElementById('active-filter-badge');
        if (!badge) return;

        var count = 0;
        Array.prototype.forEach.call(form.elements, function (el) {
            if (!el.name) return;
            if (el.type === 'submit' || el.type === 'button') return;
            if (el.value !== '') count++;
        });

        if (count > 0) {
            badge.textContent = count;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    }

    if (form) {
        updateActiveFilterBadge();
        form.addEventListener('change', updateActiveFilterBadge);
    }

    if (!form || !list) return;

    // ------------------------------------------------------------------
    // AJAX-based filtering / pagination / chip removal.
    // ------------------------------------------------------------------
    function loadResults(url) {
        list.innerHTML = '<p class="text-center mt-4">Loading…</p>';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) { return res.text(); })
            .then(function (html) {
                list.innerHTML = html;
                window.history.pushState({}, '', url);
                updateActiveFilterBadge();
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

    // Only intercept links that are meant to change the filtered result set
    // in place: paginator links, and the removable filter chips. Everything
    // else inside #coin-list (coin titles, "recently added" thumbnails, …)
    // must navigate normally — they are real pages, not list fragments.
    list.addEventListener('click', function (e) {
        var link = e.target.closest('a');
        if (!link || !list.contains(link)) return;

        var isPaginationLink = !!link.closest('.catalog-pagination');
        var isAjaxNavLink = link.matches('[data-chip-remove], [data-ajax-nav]');

        if (!isPaginationLink && !isAjaxNavLink) return;

        e.preventDefault();
        loadResults(link.href);
    });

    // ------------------------------------------------------------------
    // Tap-to-flip on touch devices (hover doesn't exist there).
    // ------------------------------------------------------------------
    var supportsHover = window.matchMedia('(hover: hover)').matches;

    if (!supportsHover) {
        list.addEventListener('click', function (e) {
            var toggle = e.target.closest('[data-flip-toggle]');
            if (!toggle) return;

            e.preventDefault();
            e.stopPropagation();
            var container = toggle.closest('[data-flip-container]');
            container && container.classList.toggle('is-flipped');
        });
    }
})();
