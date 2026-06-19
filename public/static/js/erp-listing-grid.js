/**
 * ERP Listing Grid — shared toolbar, footer, pagination for client-side lists.
 */
(function (global) {
    'use strict';

    const PAGE_SIZE_OPTIONS = [10, 25, 50, 100];
    const DEFAULT_PAGE_SIZE = 25;

    const defaultLabels = {
        show: 'બતાવો',
        recordsPerPage: 'રેકોર્ડ પ્રતિ પેજ',
        showingRecords: (from, to, total) => `દર્શાવી રહ્યા છીએ ${from}–${to} પૈકી ${total} રેકોર્ડ`,
        previous: 'પાછળ',
        next: 'આગળ',
        total: (n, meta) => meta || `${n} total`,
        searchPlaceholder: 'ટેગ / નામ શોધો...',
        srNo: 'અ.નં.',
    };

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderFooter(footerEl, labels, currentPage, totalPages, totalItems, pageSize, onPage) {
        if (!footerEl) return;

        footerEl.className = 'erp-listing__footer erp-listing__footer--js';
        footerEl.innerHTML = '';

        const info = document.createElement('div');
        info.className = 'erp-listing__footer-info';

        if (totalItems === 0) {
            info.textContent = labels.noRecords || 'કોઈ રેકોર્ડ મળ્યો નથી';
            footerEl.appendChild(info);
            return;
        }

        const from = (currentPage - 1) * pageSize + 1;
        const to = Math.min(currentPage * pageSize, totalItems);
        info.textContent = typeof labels.showingRecords === 'function'
            ? labels.showingRecords(from, to, totalItems)
            : `દર્શાવી રહ્યા છીએ ${from}–${to} પૈકી ${totalItems} રેકોર્ડ`;
        footerEl.appendChild(info);

        if (totalPages <= 1) return;

        const btns = document.createElement('div');
        btns.className = 'erp-listing__page-btns';

        const mkBtn = (label, page, disabled, active) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'erp-listing__page-btn' + (active ? ' is-active' : '');
            b.textContent = label;
            b.disabled = !!disabled;
            if (!disabled) b.addEventListener('click', () => onPage(page));
            return b;
        };

        btns.appendChild(mkBtn(labels.previous || 'પાછળ', currentPage - 1, currentPage <= 1, false));

        const maxBtns = 5;
        let s = Math.max(1, currentPage - 2);
        let e = Math.min(totalPages, s + maxBtns - 1);
        s = Math.max(1, e - maxBtns + 1);
        for (let p = s; p <= e; p++) {
            btns.appendChild(mkBtn(String(p), p, false, p === currentPage));
        }

        btns.appendChild(mkBtn(labels.next || 'આગળ', currentPage + 1, currentPage >= totalPages, false));
        footerEl.appendChild(btns);
    }

    function bindPageSize(pageSizeEl, onChange) {
        if (!pageSizeEl) return;
        pageSizeEl.addEventListener('change', () => {
            const val = parseInt(pageSizeEl.value, 10);
            onChange(PAGE_SIZE_OPTIONS.includes(val) ? val : DEFAULT_PAGE_SIZE);
        });
    }

    function updateTotalMeta(el, total, meta) {
        if (!el) return;
        el.textContent = meta || `${total} total`;
    }

    function initStaticTable(options) {
        const {
            tableId,
            listingId,
            searchInputId = null,
            labels = {},
        } = options;

        const table = document.getElementById(tableId);
        const footerEl = document.getElementById(`erp-listing-footer-${listingId}`);
        const totalEl = document.getElementById(`erp-listing-total-${listingId}`);
        const pageSizeEl = document.getElementById(`erp_js_per_page_${listingId}`);
        const searchEl = searchInputId ? document.getElementById(searchInputId) : null;
        const tbody = table?.querySelector('tbody');

        if (!table || !tbody) return;

        const L = { ...defaultLabels, ...labels };
        const allRows = Array.from(tbody.querySelectorAll('tr')).filter((tr) => !tr.querySelector('td[colspan]'));
        const emptyTemplate = tbody.querySelector('tr td[colspan]')?.closest('tr');
        const hasSrCol = table.querySelector('thead th')?.classList.contains('erp-listing__sr-col')
            || table.querySelector('thead th')?.textContent?.trim() === L.srNo;

        let pageSize = DEFAULT_PAGE_SIZE;
        let currentPage = 1;
        let filtered = allRows.map((tr) => tr.cloneNode(true));

        function render() {
            const pages = Math.max(1, Math.ceil(filtered.length / pageSize) || 1);
            currentPage = Math.min(currentPage, pages);
            const start = (currentPage - 1) * pageSize;
            const slice = filtered.slice(start, start + pageSize);

            tbody.innerHTML = '';
            if (!slice.length) {
                if (emptyTemplate) {
                    tbody.appendChild(emptyTemplate.cloneNode(true));
                } else {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td colspan="99" class="text-center text-muted" style="padding:24px;">${escapeHtml(L.noRecords || 'કોઈ રેકોર્ડ મળ્યો નથી')}</td>`;
                    tbody.appendChild(tr);
                }
            } else {
                slice.forEach((tr, idx) => {
                    const row = tr.cloneNode(true);
                    if (hasSrCol) {
                        const firstTd = row.querySelector('td');
                        if (firstTd) firstTd.textContent = String(start + idx + 1);
                    }
                    tbody.appendChild(row);
                });
            }

            updateTotalMeta(totalEl, filtered.length);
            renderFooter(footerEl, L, currentPage, pages, filtered.length, pageSize, (p) => {
                currentPage = p;
                render();
            });
        }

        function applySearch() {
            const q = (searchEl?.value || '').toLowerCase().trim();
            filtered = !q
                ? allRows.map((tr) => tr.cloneNode(true))
                : allRows
                    .filter((tr) => tr.textContent.toLowerCase().includes(q))
                    .map((tr) => tr.cloneNode(true));
            currentPage = 1;
            render();
        }

        bindPageSize(pageSizeEl, (size) => {
            pageSize = size;
            currentPage = 1;
            render();
        });

        searchEl?.addEventListener('input', applySearch);
        updateTotalMeta(totalEl, allRows.length);
        render();
    }

    global.ErpListingGrid = {
        PAGE_SIZE_OPTIONS,
        DEFAULT_PAGE_SIZE,
        defaultLabels,
        escapeHtml,
        renderFooter,
        bindPageSize,
        updateTotalMeta,
        initStaticTable,
    };
})(window);
