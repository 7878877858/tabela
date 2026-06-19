/**
 * ERP Sidebar — collapsible groups, scroll persistence, mobile drawer
 */
(function () {
    'use strict';

    const SCROLL_KEY = 'erpSidebarScroll';
    const GROUPS_KEY = 'erpSidebarGroups';

    const sidebar = document.getElementById('sidebar');
    const nav = document.getElementById('sidebarNav');
    const backdrop = document.getElementById('sidebarBackdrop');

    if (!sidebar || !nav) return;

    function loadGroupsState() {
        try {
            return JSON.parse(localStorage.getItem(GROUPS_KEY) || '{}');
        } catch (e) {
            return {};
        }
    }

    function saveGroupsState(state) {
        localStorage.setItem(GROUPS_KEY, JSON.stringify(state));
    }

    function saveScroll() {
        localStorage.setItem(SCROLL_KEY, String(nav.scrollTop));
    }

    function restoreScroll() {
        const saved = localStorage.getItem(SCROLL_KEY);
        if (saved === null || saved === '') return;
        const top = parseInt(saved, 10);
        if (Number.isNaN(top)) return;
        nav.scrollTop = top;
    }

    function setGroupOpen(groupEl, open, persist) {
        if (!groupEl) return;
        const id = groupEl.dataset.group;
        groupEl.classList.toggle('is-open', open);
        const toggle = groupEl.querySelector('.erp-sidebar__group-toggle');
        if (toggle) toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (persist && id) {
            const state = loadGroupsState();
            state[id] = open;
            saveGroupsState(state);
        }
    }

    function initGroups() {
        const state = loadGroupsState();
        nav.querySelectorAll('.erp-sidebar__group').forEach(function (group) {
            const id = group.dataset.group;
            const hasActive = group.classList.contains('is-active');
            const saved = id && Object.prototype.hasOwnProperty.call(state, id) ? state[id] : null;
            const open = hasActive || saved === true;
            setGroupOpen(group, open, false);
        });
    }

    nav.querySelectorAll('.erp-sidebar__group-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const group = btn.closest('.erp-sidebar__group');
            if (!group) return;
            const willOpen = !group.classList.contains('is-open');
            setGroupOpen(group, willOpen, true);
        });
    });

    nav.addEventListener('click', function (e) {
        const link = e.target.closest('a.erp-sidebar__link, a.erp-sidebar__sublink');
        if (link) {
            saveScroll();
            if (window.innerWidth <= 768) closeSidebar();
        }
    });

    var scrollTimer;
    nav.addEventListener('scroll', function () {
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(saveScroll, 80);
    });

    window.addEventListener('beforeunload', saveScroll);

    initGroups();
    restoreScroll();
    requestAnimationFrame(restoreScroll);
    window.addEventListener('load', restoreScroll);

    window.toggleSidebar = function () {
        const open = sidebar.classList.toggle('is-open');
        if (backdrop) backdrop.classList.toggle('is-open', open);
        document.body.style.overflow = open ? 'hidden' : '';
    };

    window.closeSidebar = function () {
        sidebar.classList.remove('is-open');
        if (backdrop) backdrop.classList.remove('is-open');
        document.body.style.overflow = '';
    };

    if (backdrop) {
        backdrop.addEventListener('click', closeSidebar);
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSidebar();
    });
})();
