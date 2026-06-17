/**
 * Animal listing — tabs, search, client-side pagination (25/page).
 */
(function (global) {
    'use strict';

    const PAGE_SIZE = 25;

    const TABS = [
        { key: 'buffalo', labelGu: 'ભેંસ', listLabel: 'ભેંસ' },
        { key: 'cow', labelGu: 'ગાય', listLabel: 'ગાય' },
        { key: 'buffalo_calf', labelGu: 'ભેંસ બચ્ચું', listLabel: 'ભેંસ બચ્ચું' },
        { key: 'cow_calf', labelGu: 'ગાય બચ્ચું', listLabel: 'ગાય બચ્ચું' },
    ];

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
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

    function filterAnimals(animals, type) {
        return animals.filter((a) => normalizeType(a.animal_type) === type);
    }

    function searchAnimals(animals, keyword) {
        const q = (keyword || '').toLowerCase().trim();
        if (!q) return animals;
        return animals.filter((a) => haystack(a).includes(q));
    }

    function statusBadgeClass(status) {
        if (status === 'active') return 'badge-green';
        if (status === 'sold') return 'badge-blue';
        return 'badge-red';
    }

    function renderPagination(el, currentPage, totalPages, totalItems, pageSize, typeLabel, onPage) {
        if (!el) return;
        el.innerHTML = '';
        if (totalItems <= pageSize) {
            if (totalItems > 0) {
                const info = document.createElement('span');
                info.className = 'dr-grid-page-info';
                info.textContent = `Showing 1-${totalItems} of ${totalItems} ${typeLabel}`;
                el.appendChild(info);
            }
            el.style.display = totalItems > 0 ? 'flex' : 'none';
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

    function renderRow(animal, config) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td data-label="ટેગ"><strong>${escapeHtml(animal.tag)}</strong></td>
            <td data-label="પ્રકાર"><span class="badge badge-gray">${escapeHtml(animal.type_label)}</span></td>
            <td data-label="નામ">${escapeHtml(animal.name || '—')}</td>
            <td data-label="સ્થિતિ">
                <span class="badge ${statusBadgeClass(animal.status)}">${escapeHtml(animal.status_label)}</span>
                <span class="badge badge-gray">${escapeHtml(animal.lactation_label)}</span>
            </td>
            <td data-label="દૂધ">${animal.milk_entries_count} દિવસ</td>
            <td data-label="આ મહિને"><strong>${Number(animal.month_milk || 0).toFixed(1)}</strong></td>
            <td data-label="" class="animal-list-actions">
                <a href="${escapeHtml(animal.show_url)}" class="btn btn-outline btn-sm">👁</a>
                <a href="${escapeHtml(animal.edit_url)}" class="btn btn-ghost btn-sm">✏️</a>
                <form method="POST" action="${escapeHtml(animal.destroy_url)}" style="display:inline;" class="animal-delete-form">
                    <input type="hidden" name="_token" value="${escapeHtml(config.csrf)}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                </form>
            </td>`;
        const form = tr.querySelector('.animal-delete-form');
        form?.addEventListener('submit', (e) => {
            if (!global.confirm(config.deleteConfirm || 'Delete?')) {
                e.preventDefault();
            }
        });
        return tr;
    }

    function initBuffaloList() {
        const jsonEl = document.getElementById('buffaloListJson');
        const configEl = document.getElementById('buffaloListConfig');
        const body = document.getElementById('buffaloListBody');
        const tabsEl = document.getElementById('buffaloAnimalTabs');
        const searchEl = document.getElementById('buffaloAnimalSearch');
        const countsEl = document.getElementById('buffaloListCounts');
        const paginationEl = document.getElementById('buffaloListPagination');

        if (!jsonEl || !body) return;

        let animals = [];
        let config = { csrf: '', deleteConfirm: '', createUrl: '' };

        try {
            animals = JSON.parse(jsonEl.textContent || '[]');
        } catch (e) {
            animals = [];
        }

        try {
            config = { ...config, ...JSON.parse(configEl?.textContent || '{}') };
        } catch (e) {}

        let activeTab = TABS.find((t) => animals.some((a) => normalizeType(a.animal_type) === t.key))?.key || 'buffalo';
        const tabParam = new URLSearchParams(window.location.search).get('tab');
        if (tabParam && TABS.some((t) => t.key === tabParam)) {
            activeTab = tabParam;
        }
        let searchQuery = '';
        let currentPage = 1;

        function countByTab() {
            const counts = { buffalo: 0, cow: 0, buffalo_calf: 0, cow_calf: 0 };
            animals.forEach((a) => {
                const t = normalizeType(a.animal_type);
                if (counts[t] !== undefined) counts[t]++;
            });
            return counts;
        }

        function getFiltered() {
            const byType = filterAnimals(animals, activeTab);
            return searchAnimals(byType, searchQuery);
        }

        function renderTabs() {
            if (!tabsEl) return;
            const counts = countByTab();
            tabsEl.innerHTML = '';
            TABS.forEach((tab) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'dr-animal-tab' + (activeTab === tab.key ? ' is-active' : '');
                btn.innerHTML = `<span class="dr-animal-tab__label">${tab.labelGu}</span><span class="dr-animal-tab__count">${counts[tab.key] || 0}</span>`;
                btn.addEventListener('click', () => {
                    if (activeTab === tab.key) return;
                    activeTab = tab.key;
                    currentPage = 1;
                    renderTabs();
                    renderPage();
                });
                tabsEl.appendChild(btn);
            });
        }

        function renderPage() {
            const filtered = getFiltered();
            const tabMeta = TABS.find((t) => t.key === activeTab) || TABS[0];
            const pages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE) || 1);
            currentPage = Math.min(currentPage, pages);
            const start = (currentPage - 1) * PAGE_SIZE;
            const slice = filtered.slice(start, start + PAGE_SIZE);

            body.innerHTML = '';

            if (!slice.length) {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="7" class="text-center" style="padding:2rem;color:#94a3b8;">
                    ${animals.length ? 'કોઈ પશુ મળ્યું નથી' : `<a href="${escapeHtml(config.createUrl)}">➕ નવું પશુ ઉમેરો</a>`}
                </td>`;
                body.appendChild(tr);
            } else {
                slice.forEach((animal) => body.appendChild(renderRow(animal, config)));
            }

            if (countsEl) {
                const c = countByTab();
                const headTotal = Object.values(c).reduce((sum, n) => sum + n, 0);
                countsEl.textContent = `${headTotal} total · ${c[activeTab] || 0} ${tabMeta.labelGu}`;
            }

            updatePagination(paginationEl, currentPage, pages, filtered.length, tabMeta.listLabel, (p) => {
                currentPage = p;
                renderPage();
            });
        }

        function updatePagination(el, currentPage, totalPages, totalItems, typeLabel, onPage) {
            renderPagination(el, currentPage, totalPages, totalItems, PAGE_SIZE, typeLabel, onPage);
        }

        if (searchEl) {
            searchEl.addEventListener('input', () => {
                searchQuery = searchEl.value || '';
                currentPage = 1;
                renderPage();
            });
        }

        renderTabs();
        renderPage();

        global.BuffaloList = {
            filterAnimals,
            searchAnimals,
            renderPage,
            updatePagination,
            getFiltered,
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBuffaloList);
    } else {
        initBuffaloList();
    }
})(window);
