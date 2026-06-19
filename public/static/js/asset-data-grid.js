/**
 * Asset Management — client-side filter, sort, pagination (25/page).
 */
(function (global) {
    'use strict';

    const PAGE_SIZE_OPTIONS = [10, 25, 50, 100];
    const DEFAULT_PAGE_SIZE = 25;

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatMoney(n, currency) {
        const val = Number(n) || 0;
        return (currency || '₹') + val.toLocaleString('en-IN', { maximumFractionDigits: 0 });
    }

    function statusBadge(status) {
        const map = {
            active: 'badge-green',
            inactive: 'badge-gray',
            sold: 'badge-blue',
            scrap: 'badge-red',
        };
        return map[status] || 'badge-gray';
    }

    function renderPagination(el, currentPage, totalPages, totalItems, onPage, labels = {}) {
        const L = {
            previous: 'Previous',
            next: 'Next',
            assetsWord: 'Assets',
            ...labels,
        };
        if (!el) return;
        el.innerHTML = '';
        if (totalItems === 0) {
            el.style.display = 'none';
            return;
        }
        el.style.display = 'flex';
        const start = totalItems <= PAGE_SIZE ? 1 : (currentPage - 1) * PAGE_SIZE + 1;
        const end = Math.min(currentPage * PAGE_SIZE, totalItems);
        const info = document.createElement('span');
        info.className = 'dr-grid-page-info';
        info.textContent = `Showing ${start}-${end} of ${totalItems} ${L.assetsWord}`;
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
        btns.appendChild(mkBtn(L.previous, currentPage - 1, currentPage <= 1, false));
        const maxBtns = 5;
        let s = Math.max(1, currentPage - 2);
        let e = Math.min(totalPages, s + maxBtns - 1);
        s = Math.max(1, e - maxBtns + 1);
        for (let p = s; p <= e; p++) {
            btns.appendChild(mkBtn(String(p), p, false, p === currentPage));
        }
        btns.appendChild(mkBtn(L.next, currentPage + 1, currentPage >= totalPages, false));
        el.appendChild(btns);
    }

    function initAssetGrid(options) {
        const {
            rows = [],
            tbodyId,
            paginationId,
            searchId,
            categoryId,
            statusId,
            currency = '₹',
            csrf = '',
            destroyBase = '/assets',
            noImageLabel = 'No Image',
            pageSizeId = null,
            pageSize: initialPageSize = DEFAULT_PAGE_SIZE,
            totalMetaId = null,
            listingId = null,
            labels = {},
        } = options;

        const L = {
            noAssetsFound: 'No assets found',
            deleteConfirm: 'Delete this asset?',
            previous: 'Previous',
            next: 'Next',
            assetsWord: 'Assets',
            view: 'View',
            edit: 'Edit',
            srNoLabel: 'Sr. No.',
            ...labels,
        };

        let pageSize = PAGE_SIZE_OPTIONS.includes(initialPageSize) ? initialPageSize : DEFAULT_PAGE_SIZE;

        function renderThumb(url, name, showUrl) {
            const alt = escapeHtml(name || 'Asset');
            if (url) {
                return `<a href="${escapeHtml(showUrl)}" class="am-asset-thumb-link"><img src="${escapeHtml(url)}" alt="${alt}" class="am-asset-thumb" loading="lazy" onerror="this.replaceWith(Object.assign(document.createElement('span'),{className:'am-asset-thumb am-asset-thumb--empty',textContent:'🚜'}))"></a>`;
            }
            return `<span class="am-asset-thumb am-asset-thumb--empty" title="${escapeHtml(noImageLabel)}">🚜</span>`;
        }

        const tbody = document.getElementById(tbodyId);
        const paginationEl = document.getElementById(paginationId);
        const searchEl = document.getElementById(searchId);
        const categoryEl = document.getElementById(categoryId);
        const statusEl = document.getElementById(statusId);
        const pageSizeEl = pageSizeId ? document.getElementById(pageSizeId) : null;
        const totalMetaEl = totalMetaId ? document.getElementById(totalMetaId) : null;
        const Grid = global.ErpListingGrid || {};

        let filtered = [...rows];
        let currentPage = 1;
        let sortKey = 'asset_code';
        let sortDir = 'asc';

        function applyFilters() {
            const q = (searchEl?.value || '').toLowerCase().trim();
            const cat = categoryEl?.value || '';
            const st = statusEl?.value || '';

            filtered = rows.filter((r) => {
                if (cat && r.category !== cat) return false;
                if (st && r.status !== st) return false;
                if (!q) return true;
                const hay = `${r.asset_code} ${r.name} ${r.vendor_name} ${r.category_label}`.toLowerCase();
                return hay.includes(q);
            });

            filtered.sort((a, b) => {
                let av = a[sortKey];
                let bv = b[sortKey];
                if (sortKey === 'purchase_price' || sortKey === 'current_value' || sortKey === 'total_maintenance') {
                    av = Number(av) || 0;
                    bv = Number(bv) || 0;
                } else {
                    av = String(av ?? '').toLowerCase();
                    bv = String(bv ?? '').toLowerCase();
                }
                if (av < bv) return sortDir === 'asc' ? -1 : 1;
                if (av > bv) return sortDir === 'asc' ? 1 : -1;
                return 0;
            });

            currentPage = 1;
            renderPage();
        }

        function renderPage() {
            if (!tbody) return;
            const pages = Math.max(1, Math.ceil(filtered.length / pageSize) || 1);
            currentPage = Math.min(currentPage, pages);
            const start = (currentPage - 1) * pageSize;
            const slice = filtered.slice(start, start + pageSize);

            tbody.innerHTML = '';
            if (!slice.length) {
                tbody.innerHTML = `<tr><td colspan="12" class="text-center text-muted" style="padding:24px;">${escapeHtml(L.noAssetsFound)}</td></tr>`;
            } else {
                slice.forEach((r, idx) => {
                    const srNo = start + idx + 1;
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td data-label="${escapeHtml(L.srNoLabel)}">${srNo}</td>
                        <td data-label="Image">${renderThumb(r.image_url, r.name, r.show_url)}</td>
                        <td data-label="Code"><strong>${escapeHtml(r.asset_code)}</strong></td>
                        <td data-label="Asset"><a href="${escapeHtml(r.show_url)}">${escapeHtml(r.name)}</a></td>
                        <td data-label="Category">${escapeHtml(r.category_label)}</td>
                        <td data-label="Purchase">${formatMoney(r.purchase_price, currency)}</td>
                        <td data-label="Value">${formatMoney(r.current_value, currency)}</td>
                        <td data-label="Maintenance">${formatMoney(r.total_maintenance, currency)}</td>
                        <td data-label="Next Service">${escapeHtml(r.next_service_date || '—')}</td>
                        <td data-label="Status"><span class="badge ${statusBadge(r.status)}">${escapeHtml(r.status_label)}</span></td>
                        <td data-label="Actions" class="am-actions mobile-card-actions erp-listing__actions">
                            <div class="mobile-card-actions__group">
                            <a href="${escapeHtml(r.show_url)}" class="btn btn-outline btn-sm" title="${escapeHtml(L.view)}">👁</a>
                            <a href="${escapeHtml(r.edit_url)}" class="btn btn-outline btn-sm" title="${escapeHtml(L.edit)}">✏️</a>
                            <form method="POST" action="${escapeHtml(destroyBase)}/${r.id}" class="am-inline-form" onsubmit="return confirm(${JSON.stringify(L.deleteConfirm)})">
                                <input type="hidden" name="_token" value="${escapeHtml(csrf)}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                            </form>
                            </div>
                        </td>`;
                    tbody.appendChild(tr);
                });
            }

            if (totalMetaEl && Grid.updateTotalMeta) {
                Grid.updateTotalMeta(totalMetaEl, filtered.length);
            } else if (totalMetaEl) {
                totalMetaEl.textContent = `${filtered.length} total`;
            }

            if (Grid.renderFooter) {
                Grid.renderFooter(paginationEl, {
                    showingRecords: (from, to, total) => `દર્શાવી રહ્યા છીએ ${from}–${to} પૈકી ${total} રેકોર્ડ`,
                    previous: L.previous,
                    next: L.next,
                    noRecords: L.noAssetsFound,
                }, currentPage, pages, filtered.length, pageSize, (p) => {
                    currentPage = p;
                    renderPage();
                });
            } else {
                renderPagination(paginationEl, currentPage, pages, filtered.length, (p) => {
                    currentPage = p;
                    renderPage();
                }, L);
            }
        }

        document.querySelectorAll('[data-asset-sort]').forEach((th) => {
            th.addEventListener('click', () => {
                const key = th.dataset.assetSort;
                if (sortKey === key) {
                    sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    sortKey = key;
                    sortDir = 'asc';
                }
                applyFilters();
            });
        });

        searchEl?.addEventListener('input', applyFilters);
        categoryEl?.addEventListener('change', applyFilters);
        statusEl?.addEventListener('change', applyFilters);

        if (pageSizeEl) {
            pageSizeEl.value = String(pageSize);
            const onSize = (size) => {
                pageSize = size;
                currentPage = 1;
                applyFilters();
            };
            if (Grid.bindPageSize) {
                Grid.bindPageSize(pageSizeEl, onSize);
            } else {
                pageSizeEl.addEventListener('change', () => {
                    const val = parseInt(pageSizeEl.value, 10);
                    onSize(PAGE_SIZE_OPTIONS.includes(val) ? val : DEFAULT_PAGE_SIZE);
                });
            }
        }

        if (totalMetaEl && rows.length) {
            if (Grid.updateTotalMeta) Grid.updateTotalMeta(totalMetaEl, rows.length);
            else totalMetaEl.textContent = `${rows.length} total`;
        }

        applyFilters();
    }

    function initReportGrid(options) {
        const {
            rows = [],
            tbodyId,
            paginationId,
            label = 'Records',
            labels = {},
            pageSizeId = null,
            pageSize: initialPageSize = DEFAULT_PAGE_SIZE,
            totalMetaId = null,
        } = options;
        const L = {
            noRecords: 'No records',
            previous: 'Previous',
            next: 'Next',
            assetsWord: 'Records',
            srNoLabel: 'Sr. No.',
            ...labels,
        };
        const tbody = document.getElementById(tbodyId);
        const paginationEl = document.getElementById(paginationId);
        const pageSizeEl = pageSizeId ? document.getElementById(pageSizeId) : null;
        const totalMetaEl = totalMetaId ? document.getElementById(totalMetaId) : null;
        const Grid = global.ErpListingGrid || {};
        let pageSize = PAGE_SIZE_OPTIONS.includes(initialPageSize) ? initialPageSize : DEFAULT_PAGE_SIZE;
        let currentPage = 1;

        function renderPage() {
            if (!tbody) return;
            const pages = Math.max(1, Math.ceil(rows.length / pageSize) || 1);
            currentPage = Math.min(currentPage, pages);
            const start = (currentPage - 1) * pageSize;
            const slice = rows.slice(start, start + pageSize);
            tbody.innerHTML = slice.length ? '' : `<tr><td colspan="20" class="text-center text-muted" style="padding:20px;">${escapeHtml(L.noRecords)}</td></tr>`;
            slice.forEach((html, idx) => {
                const tr = document.createElement('tr');
                const srNo = start + idx + 1;
                tr.innerHTML = `<td data-label="${escapeHtml(L.srNoLabel)}" class="erp-listing__sr-col">${srNo}</td>${html}`;
                tbody.appendChild(tr);
            });

            if (totalMetaEl && Grid.updateTotalMeta) {
                Grid.updateTotalMeta(totalMetaEl, rows.length);
            } else if (totalMetaEl) {
                totalMetaEl.textContent = `${rows.length} total`;
            }

            const footerLabels = {
                showingRecords: (from, to, total) => `દર્શાવી રહ્યા છીએ ${from}–${to} પૈકી ${total} રેકોર્ડ`,
                previous: L.previous,
                next: L.next,
                noRecords: L.noRecords,
            };

            if (Grid.renderFooter) {
                Grid.renderFooter(paginationEl, footerLabels, currentPage, pages, rows.length, pageSize, (p) => {
                    currentPage = p;
                    renderPage();
                });
            } else {
                renderPagination(paginationEl, currentPage, pages, rows.length, (p) => {
                    currentPage = p;
                    renderPage();
                }, { ...L, assetsWord: label });
            }
        }

        if (pageSizeEl) {
            pageSizeEl.value = String(pageSize);
            const onSize = (size) => {
                pageSize = size;
                currentPage = 1;
                renderPage();
            };
            if (Grid.bindPageSize) {
                Grid.bindPageSize(pageSizeEl, onSize);
            } else {
                pageSizeEl.addEventListener('change', () => {
                    const val = parseInt(pageSizeEl.value, 10);
                    onSize(PAGE_SIZE_OPTIONS.includes(val) ? val : DEFAULT_PAGE_SIZE);
                });
            }
        }

        if (totalMetaEl && rows.length) {
            if (Grid.updateTotalMeta) Grid.updateTotalMeta(totalMetaEl, rows.length);
            else totalMetaEl.textContent = `${rows.length} total`;
        }

        renderPage();
    }

    global.AssetDataGrid = { initAssetGrid, initReportGrid, PAGE_SIZE_OPTIONS, DEFAULT_PAGE_SIZE };
})(window);
