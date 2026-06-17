/**
 * Enterprise Daily Report grids — tabs, search, client pagination (25/page).
 * All form values persist in hidden named inputs; visible grid is lazy-rendered.
 */
(function (global) {
    'use strict';

    const PAGE_SIZE = 25;

    const TABS = [
        { key: 'buffalo', label: 'Buffalo', labelGu: 'ભેંસ' },
        { key: 'cow', label: 'Cow', labelGu: 'ગાય' },
        { key: 'buffalo_calf', label: 'Buffalo Calf', labelGu: 'ભેંસ બચ્ચું' },
        { key: 'cow_calf', label: 'Cow Calf', labelGu: 'ગાય બચ્ચું' },
    ];

    function parseNum(val) {
        const n = parseFloat(val);
        return Number.isFinite(n) ? n : 0;
    }

    function normalizeType(type) {
        const t = (type || 'buffalo').toLowerCase();
        if (['buffalo', 'cow', 'buffalo_calf', 'cow_calf'].includes(t)) return t;
        if (t.includes('calf') && t.includes('buffalo')) return 'buffalo_calf';
        if (t.includes('calf')) return 'cow_calf';
        return t === 'cow' ? 'cow' : 'buffalo';
    }

    function haystack(animal) {
        return ((animal.tag || '') + ' ' + (animal.name || '')).toLowerCase();
    }

    function syncVisibleToStore(body, store) {
        if (!body || !store) return;
        body.querySelectorAll('[data-sync-field]').forEach((input) => {
            const key = input.dataset.syncField;
            const hidden = store.querySelector(`[data-sync-key="${key}"]`);
            if (hidden) hidden.value = input.value;
        });
    }

    function bindRowInputs(row, store, onChange) {
        row.querySelectorAll('[data-sync-field]').forEach((input) => {
            const key = input.dataset.syncField;
            const hidden = store.querySelector(`[data-sync-key="${key}"]`);
            if (hidden && hidden.value !== '' && input.value === '') {
                input.value = hidden.value;
            }
            input.addEventListener('input', () => {
                if (hidden) hidden.value = input.value;
                if (typeof onChange === 'function') onChange();
            });
        });
    }

    function renderPagination(el, currentPage, totalPages, totalItems, pageSize, typeLabel, onPage) {
        if (!el) return;
        el.innerHTML = '';
        if (totalItems <= pageSize) {
            el.style.display = 'none';
            return;
        }
        el.style.display = 'flex';

        const start = (currentPage - 1) * pageSize + 1;
        const end = Math.min(currentPage * pageSize, totalItems);

        const info = document.createElement('span');
        info.className = 'dr-grid-page-info';
        info.textContent = `Showing ${start}-${end} of ${totalItems} ${typeLabel}`;
        el.appendChild(info);

        const btns = document.createElement('div');
        btns.className = 'dr-grid-page-btns';

        const mkBtn = (label, page, disabled, active) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'dr-grid-page-btn' + (active ? ' is-active' : '');
            b.textContent = label;
            b.disabled = !!disabled;
            if (!disabled) b.addEventListener('click', () => onPage(page));
            return b;
        };

        btns.appendChild(mkBtn('Previous', currentPage - 1, currentPage <= 1, false));

        const maxBtns = 5;
        let s = Math.max(1, currentPage - 2);
        let e = Math.min(totalPages, s + maxBtns - 1);
        s = Math.max(1, e - maxBtns + 1);
        for (let p = s; p <= e; p++) {
            btns.appendChild(mkBtn(String(p), p, false, p === currentPage));
        }

        btns.appendChild(mkBtn('Next', currentPage + 1, currentPage >= totalPages, false));
        el.appendChild(btns);
    }

    function loadServerTypeCounts() {
        const el = document.getElementById('dailyReportAnimalTypeCounts');
        if (!el) return null;
        try {
            const data = JSON.parse(el.textContent || '{}');
            return {
                buffalo: Number(data.buffalo) || 0,
                cow: Number(data.cow) || 0,
                buffalo_calf: Number(data.buffalo_calf) || 0,
                cow_calf: Number(data.cow_calf) || 0,
            };
        } catch (e) {
            return null;
        }
    }

    function initTabbedGrid(options) {
        const {
            animals = [],
            storeId,
            bodyId,
            paginationId,
            tabsId,
            searchId,
            countsId,
            activeTab: initialTab = 'buffalo',
            renderRow,
            onRecalc,
            typeLabel = 'animals',
            typeCounts = null,
        } = options;

        const store = document.getElementById(storeId);
        const body = document.getElementById(bodyId);
        const paginationEl = document.getElementById(paginationId);
        const tabsEl = document.getElementById(tabsId);
        const searchEl = document.getElementById(searchId);
        const countsEl = document.getElementById(countsId);

        if (!store || !body) return null;

        let activeTab = initialTab;
        let searchQuery = '';
        let currentPage = 1;

        function countByTab() {
            if (typeCounts) {
                return { ...typeCounts };
            }
            const counts = { buffalo: 0, cow: 0, buffalo_calf: 0, cow_calf: 0 };
            animals.forEach((a) => {
                const t = normalizeType(a.animal_type);
                if (counts[t] !== undefined) counts[t]++;
            });
            return counts;
        }

        function renderTabs() {
            if (!tabsEl) return;
            const counts = countByTab();
            tabsEl.innerHTML = '';
            TABS.forEach((tab) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'dr-animal-tab' + (activeTab === tab.key ? ' is-active' : '');
                btn.dataset.tab = tab.key;
                btn.innerHTML = `<span class="dr-animal-tab__label">${tab.labelGu}</span><span class="dr-animal-tab__count">${counts[tab.key] || 0}</span>`;
                btn.addEventListener('click', () => {
                    if (activeTab === tab.key) return;
                    syncVisibleToStore(body, store);
                    activeTab = tab.key;
                    currentPage = 1;
                    renderTabs();
                    renderPage();
                });
                tabsEl.appendChild(btn);
            });
        }

        function getFiltered() {
            return animals.filter((a) => {
                if (normalizeType(a.animal_type) !== activeTab) return false;
                if (!searchQuery) return true;
                return haystack(a).includes(searchQuery);
            });
        }

        function renderPage() {
            syncVisibleToStore(body, store);
            const filtered = getFiltered();
            const pages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE) || 1);
            currentPage = Math.min(currentPage, pages);
            const start = (currentPage - 1) * PAGE_SIZE;
            const slice = filtered.slice(start, start + PAGE_SIZE);

            body.innerHTML = '';
            if (!slice.length) {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="20" class="text-center text-muted" style="padding:24px;">કોઈ પશુ મળ્યું નથી</td>`;
                body.appendChild(tr);
            } else {
                slice.forEach((animal) => {
                    const row = renderRow(animal);
                    if (row) {
                        body.appendChild(row);
                        bindRowInputs(row, store, onRecalc);
                    }
                });
            }

            if (countsEl) {
                const c = countByTab();
                const headTotal = Object.values(c).reduce((sum, n) => sum + n, 0);
                countsEl.textContent = `${headTotal} total · ${c[activeTab] || 0} in ${TABS.find((t) => t.key === activeTab)?.labelGu || activeTab}`;
            }

            renderPagination(paginationEl, currentPage, pages, filtered.length, PAGE_SIZE, typeLabel, (p) => {
                syncVisibleToStore(body, store);
                currentPage = p;
                renderPage();
            });

            if (typeof onRecalc === 'function') onRecalc();
        }

        if (searchEl) {
            searchEl.addEventListener('input', () => {
                syncVisibleToStore(body, store);
                searchQuery = (searchEl.value || '').toLowerCase().trim();
                currentPage = 1;
                renderPage();
            });
        }

        renderTabs();
        renderPage();

        return {
            sync: () => syncVisibleToStore(body, store),
            refresh: renderPage,
            getStore: () => store,
        };
    }

    function initMilkGrid() {
        const jsonEl = document.getElementById('milkAnimalsJson');
        if (!jsonEl) return null;

        let animals = [];
        try {
            animals = JSON.parse(jsonEl.textContent || '[]');
        } catch (e) {
            animals = [];
        }

        const firstTab = TABS.find((t) => animals.some((a) => normalizeType(a.animal_type) === t.key))?.key || 'buffalo';
        const typeCounts = loadServerTypeCounts();

        return initTabbedGrid({
            animals,
            storeId: 'milkGridHiddenStore',
            bodyId: 'milkGridBody',
            paginationId: 'milkGridPagination',
            tabsId: 'milkAnimalTabs',
            searchId: 'milkAnimalSearch',
            countsId: 'milkGridCounts',
            activeTab: firstTab,
            typeCounts,
            typeLabel: TABS.find((t) => t.key === firstTab)?.labelGu || 'પશુ',
            renderRow(animal) {
                const tr = document.createElement('tr');
                tr.className = 'milk-animal-row';
                tr.dataset.buffaloId = animal.id;
                tr.innerHTML = `
                    <td><strong>${animal.tag}</strong></td>
                    <td>${animal.name || '—'}</td>
                    <td class="dr-grid-input-cell">
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm milk-qty-input milk-qty milk-morning"
                            data-period="morning" data-buffalo-id="${animal.id}" data-sync-field="milk-${animal.id}-morning"
                            placeholder="0.00" inputmode="decimal">
                    </td>
                    <td class="dr-grid-input-cell">
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm milk-qty-input milk-qty milk-evening"
                            data-period="evening" data-buffalo-id="${animal.id}" data-sync-field="milk-${animal.id}-evening"
                            placeholder="0.00" inputmode="decimal">
                    </td>
                    <td class="milk-row-total-wrap">
                        <span class="milk-row-total row-total-display">0.00</span>
                    </td>`;
                return tr;
            },
            onRecalc: recalcMilkTotals,
        });
    }

    function recalcMilkTotals() {
        const store = document.getElementById('milkGridHiddenStore');
        if (!store) return;

        let totalMorning = 0;
        let totalEvening = 0;
        let withData = 0;

        store.querySelectorAll('[data-period="morning"]').forEach((input) => {
            const m = parseNum(input.value);
            const id = input.dataset.buffaloId;
            const eve = store.querySelector(`[data-sync-key="milk-${id}-evening"]`);
            const e = parseNum(eve?.value);
            totalMorning += m;
            totalEvening += e;
            if (m > 0 || e > 0) withData++;
        });

        const grand = totalMorning + totalEvening;
        const set = (id, val, dec = 2) => {
            const el = document.getElementById(id);
            if (el) el.textContent = typeof val === 'number' ? val.toFixed(dec) : String(val);
        };

        set('milkSummaryMorning', totalMorning);
        set('milkSummaryEvening', totalEvening);
        set('milkSummaryGrand', grand);
        set('milkStickyMorning', totalMorning);
        set('milkStickyEvening', totalEvening);
        set('milkStickyTotal', grand);
        set('milkStickyAnimals', withData, 0);

        document.querySelectorAll('#milkGridBody tr.milk-animal-row').forEach((row) => {
            const id = row.dataset.buffaloId;
            const m = parseNum(store.querySelector(`[data-sync-key="milk-${id}-morning"]`)?.value);
            const e = parseNum(store.querySelector(`[data-sync-key="milk-${id}-evening"]`)?.value);
            const d = row.querySelector('.row-total-display');
            if (d) d.textContent = (m + e).toFixed(2);
        });
    }

    function initFeedGrid() {
        const jsonEl = document.getElementById('feedAnimalsJson');
        const feedsEl = document.getElementById('feedTypesJson');
        if (!jsonEl || !feedsEl) return null;

        let animals = [];
        let feeds = [];
        try {
            animals = JSON.parse(jsonEl.textContent || '[]');
            feeds = JSON.parse(feedsEl.textContent || '[]');
        } catch (e) {
            return null;
        }

        const firstTab = TABS.find((t) => animals.some((a) => normalizeType(a.animal_type) === t.key))?.key || 'buffalo';
        const typeCounts = loadServerTypeCounts();

        return initTabbedGrid({
            animals,
            storeId: 'feedGridHiddenStore',
            bodyId: 'feedGridBody',
            paginationId: 'feedGridPagination',
            tabsId: 'feedAnimalTabs',
            searchId: 'feedAnimalSearch',
            countsId: 'feedGridCounts',
            activeTab: firstTab,
            typeCounts,
            typeLabel: TABS.find((t) => t.key === firstTab)?.labelGu || 'પશુ',
            renderRow(animal) {
                const tr = document.createElement('tr');
                tr.className = 'feed-animal-row';
                tr.dataset.buffaloId = animal.id;
                let html = `<td><strong>${animal.tag}</strong></td><td>${animal.name || '—'}</td>`;
                feeds.forEach((feed) => {
                    html += `<td class="dr-grid-input-cell"><input type="number" step="0.01" min="0" class="form-control feed-qty-input feed-qty"
                        data-period="morning" data-feed-id="${feed.id}" data-buffalo-id="${animal.id}"
                        data-sync-field="feed-${animal.id}-morning-${feed.id}" placeholder="0"></td>`;
                });
                feeds.forEach((feed) => {
                    html += `<td class="dr-grid-input-cell"><input type="number" step="0.01" min="0" class="form-control feed-qty-input feed-qty"
                        data-period="evening" data-feed-id="${feed.id}" data-buffalo-id="${animal.id}"
                        data-sync-field="feed-${animal.id}-evening-${feed.id}" placeholder="0"></td>`;
                });
                html += `<td class="feed-row-total"><span class="row-total-display">0.00</span></td>`;
                tr.innerHTML = html;
                return tr;
            },
            onRecalc: recalcFeedTotals,
        });
    }

    function recalcFeedTotals() {
        const store = document.getElementById('feedGridHiddenStore');
        if (!store) return;

        let grandTotal = 0;
        let totalMorning = 0;
        let totalEvening = 0;
        const morningByFeed = {};
        const eveningByFeed = {};

        store.querySelectorAll('.feed-qty-store').forEach((input) => {
            const qty = parseNum(input.value);
            const feedId = input.dataset.feedId;
            const period = input.dataset.period;
            if (period === 'morning') {
                totalMorning += qty;
                morningByFeed[feedId] = (morningByFeed[feedId] || 0) + qty;
            } else {
                totalEvening += qty;
                eveningByFeed[feedId] = (eveningByFeed[feedId] || 0) + qty;
            }
            grandTotal += qty;
        });

        document.querySelectorAll('.summary-morning').forEach((el) => {
            el.textContent = (morningByFeed[el.dataset.feedId] || 0).toFixed(2);
        });
        document.querySelectorAll('.summary-evening').forEach((el) => {
            el.textContent = (eveningByFeed[el.dataset.feedId] || 0).toFixed(2);
        });

        const setText = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val.toFixed(2);
        };
        setText('summaryGrandTotal', grandTotal);
        setText('summaryTotalMorning', totalMorning);
        setText('summaryTotalEvening', totalEvening);
        setText('summaryTotalFeed', grandTotal);

        document.querySelectorAll('#feedGridBody tr.feed-animal-row').forEach((row) => {
            let rowTotal = 0;
            row.querySelectorAll('.feed-qty').forEach((input) => {
                rowTotal += parseNum(input.value);
            });
            const d = row.querySelector('.row-total-display');
            if (d) d.textContent = rowTotal.toFixed(2);
        });
    }

    global.DailyReportGrids = {
        PAGE_SIZE,
        initMilkGrid,
        initFeedGrid,
        recalcMilkTotals,
        recalcFeedTotals,
        parseNum,
        syncVisibleToStore,
    };

    function boot() {
        global.DailyReportMilkPager = initMilkGrid();
        global.DailyReportFeedPager = initFeedGrid();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})(window);
