/**
 * Milk Sales & Milk Transactions — filters, sort, pagination, export.
 */
(function (global) {
    'use strict';

    const PAGE_SIZE = 25;

    const ANIMAL_TYPES = [
        { key: '', label: 'બધા' },
        { key: 'buffalo', label: 'ભેંસ' },
        { key: 'cow', label: 'ગાય' },
        { key: 'buffalo_calf', label: 'ભેંસ બચ્ચું' },
        { key: 'cow_calf', label: 'ગાય બચ્ચું' },
    ];

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function parseDate(str) {
        if (!str) return null;
        const d = new Date(str + 'T00:00:00');
        return Number.isNaN(d.getTime()) ? null : d;
    }

    function startOfDay(d) {
        const x = new Date(d);
        x.setHours(0, 0, 0, 0);
        return x;
    }

    function endOfDay(d) {
        const x = new Date(d);
        x.setHours(23, 59, 59, 999);
        return x;
    }

    function formatYmd(d) {
        return d.toISOString().slice(0, 10);
    }

    function getPresetRange(preset) {
        const now = new Date();
        const today = startOfDay(now);

        switch (preset) {
            case 'today':
                return [today, endOfDay(today)];
            case 'this_week': {
                const day = today.getDay();
                const diff = day === 0 ? 6 : day - 1;
                const mon = new Date(today);
                mon.setDate(today.getDate() - diff);
                return [mon, endOfDay(today)];
            }
            case 'this_month': {
                const s = new Date(today.getFullYear(), today.getMonth(), 1);
                return [s, endOfDay(today)];
            }
            case 'last_month': {
                const s = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                const e = new Date(today.getFullYear(), today.getMonth(), 0);
                return [s, endOfDay(e)];
            }
            default:
                return [null, null];
        }
    }

    function inDateRange(recordDate, from, to) {
        const d = parseDate(recordDate);
        if (!d) return false;
        if (from && d < from) return false;
        if (to && d > to) return false;
        return true;
    }

    function renderPagination(el, currentPage, totalPages, totalItems, onPage) {
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
        info.textContent = `Showing ${start}-${end} of ${totalItems} Records`;
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
        for (let p = s; p <= e; p++) btns.appendChild(mkBtn(String(p), p, false, p === currentPage));
        btns.appendChild(mkBtn('Next', currentPage + 1, currentPage >= totalPages, false));
        el.appendChild(btns);
    }

    function downloadBlob(filename, mime, content) {
        const blob = new Blob([content], { type: mime });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = filename;
        a.click();
        URL.revokeObjectURL(a.href);
    }

    function exportCsv(rows, columns, filename) {
        const header = columns.map((c) => c.label).join(',');
        const lines = rows.map((row) => columns.map((c) => {
            const v = String(c.value(row) ?? '').replace(/"/g, '""');
            return `"${v}"`;
        }).join(','));
        downloadBlob(filename, 'text/csv;charset=utf-8;', '\uFEFF' + [header, ...lines].join('\n'));
    }

    function exportPdf(rows, columns, title) {
        const head = columns.map((c) => `<th>${escapeHtml(c.label)}</th>`).join('');
        const body = rows.map((row) => `<tr>${columns.map((c) => `<td>${escapeHtml(c.value(row))}</td>`).join('')}</tr>`).join('');
        const w = window.open('', '_blank');
        if (!w) return;
        w.document.write(`<!DOCTYPE html><html><head><meta charset="utf-8"><title>${escapeHtml(title)}</title>
            <style>body{font-family:Arial,sans-serif;padding:20px}h2{margin:0 0 12px}table{border-collapse:collapse;width:100%;font-size:12px}
            th,td{border:1px solid #ccc;padding:6px 8px;text-align:left}th{background:#f1f5f9}</style></head>
            <body><h2>${escapeHtml(title)}</h2><p>${rows.length} records</p><table><thead><tr>${head}</tr></thead><tbody>${body}</tbody></table></body></html>`);
        w.document.close();
        w.focus();
        w.print();
    }

    function initSalesGrid() {
        const root = document.getElementById('milkSalesLedger');
        if (!root) return;

        let records = [];
        let config = { csrf: '', deleteConfirm: '' };
        try { records = JSON.parse(document.getElementById('milkSalesJson')?.textContent || '[]'); } catch (e) {}
        try { config = { ...config, ...JSON.parse(document.getElementById('milkSalesConfig')?.textContent || '{}') }; } catch (e) {}

        const body = document.getElementById('milkSalesBody');
        const paginationEl = document.getElementById('milkSalesPagination');
        const els = {
            preset: document.getElementById('mlDatePreset'),
            from: document.getElementById('mlDateFrom'),
            to: document.getElementById('mlDateTo'),
            payment: document.getElementById('mlPaymentStatus'),
            litersMin: document.getElementById('mlLitersMin'),
            litersMax: document.getElementById('mlLitersMax'),
            amountMin: document.getElementById('mlAmountMin'),
            amountMax: document.getElementById('mlAmountMax'),
            search: document.getElementById('mlSearch'),
            totalLiters: document.getElementById('mlSummaryTotalLiters'),
            totalSales: document.getElementById('mlSummaryTotalSales'),
            pending: document.getElementById('mlSummaryPending'),
            count: document.getElementById('mlFilteredCount'),
        };

        let currentPage = 1;
        let sortCol = 'date';
        let sortDir = 'desc';

        function getFilters() {
            const preset = els.preset?.value || 'this_month';
            let from = null;
            let to = null;
            if (preset === 'custom') {
                from = parseDate(els.from?.value);
                to = els.to?.value ? endOfDay(parseDate(els.to.value)) : null;
            } else {
                [from, to] = getPresetRange(preset);
                if (from && els.from) els.from.value = formatYmd(from);
                if (to && els.to) els.to.value = formatYmd(startOfDay(to));
            }
            return {
                from, to,
                payment: els.payment?.value || '',
                litersMin: parseFloat(els.litersMin?.value) || null,
                litersMax: parseFloat(els.litersMax?.value) || null,
                amountMin: parseFloat(els.amountMin?.value) || null,
                amountMax: parseFloat(els.amountMax?.value) || null,
                search: (els.search?.value || '').toLowerCase().trim(),
            };
        }

        function filterRecords() {
            const f = getFilters();
            return records.filter((r) => {
                if (!inDateRange(r.date, f.from, f.to)) return false;
                if (f.payment && r.payment_status !== f.payment) return false;
                if (f.litersMin !== null && r.liters < f.litersMin) return false;
                if (f.litersMax !== null && r.liters > f.litersMax) return false;
                if (f.amountMin !== null && r.amount < f.amountMin) return false;
                if (f.amountMax !== null && r.amount > f.amountMax) return false;
                if (f.search) {
                    const hay = `${r.buyer_name} ${r.date_display}`.toLowerCase();
                    if (!hay.includes(f.search)) return false;
                }
                return true;
            });
        }

        function sortRecords(list) {
            const col = sortCol;
            const dir = sortDir === 'asc' ? 1 : -1;
            return [...list].sort((a, b) => {
                let av = a[col];
                let bv = b[col];
                if (col === 'date') { av = a.date; bv = b.date; }
                if (typeof av === 'number' && typeof bv === 'number') return (av - bv) * dir;
                return String(av ?? '').localeCompare(String(bv ?? '')) * dir;
            });
        }

        function updateSummary(filtered) {
            const liters = filtered.reduce((s, r) => s + r.liters, 0);
            const sales = filtered.reduce((s, r) => s + r.amount, 0);
            const pending = filtered.filter((r) => r.payment_status === 'pending').reduce((s, r) => s + r.amount, 0);
            if (els.totalLiters) els.totalLiters.textContent = liters.toFixed(1) + ' L';
            if (els.totalSales) els.totalSales.textContent = '₹' + Math.round(sales).toLocaleString('en-IN');
            if (els.pending) els.pending.textContent = '₹' + Math.round(pending).toLocaleString('en-IN');
            if (els.count) els.count.textContent = filtered.length + ' records';
        }

        function renderRow(r) {
            const tr = document.createElement('tr');
            const payCell = r.payment_status === 'paid'
                ? '<span class="badge badge-green">✅ Paid</span>'
                : `<form method="POST" action="${escapeHtml(r.pay_url)}" style="display:inline;">` +
                  `<input type="hidden" name="_token" value="${escapeHtml(config.csrf)}">` +
                  `<input type="hidden" name="_method" value="PATCH">` +
                  `<button type="submit" class="btn btn-sm badge-yellow" style="border:none;cursor:pointer;border-radius:20px;padding:3px 10px;font-size:11px;font-weight:600;background:#fef9c3;color:#ca8a04;">⏳ Pending</button></form>`;
            tr.innerHTML = `
                <td data-label="Date">${escapeHtml(r.date_display)}</td>
                <td data-label="Liters">${r.liters.toFixed(1)}</td>
                <td data-label="Rate">₹${r.price_per_liter}</td>
                <td data-label="Amount"><strong>₹${Math.round(r.amount).toLocaleString('en-IN')}</strong></td>
                <td data-label="Buyer">${escapeHtml(r.buyer_name || '—')}</td>
                <td data-label="Payment">${payCell}</td>
                <td data-label="">
                    <form method="POST" action="${escapeHtml(r.destroy_url)}" class="ml-delete-form" style="display:inline;">
                        <input type="hidden" name="_token" value="${escapeHtml(config.csrf)}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                    </form>
                </td>`;
            tr.querySelector('.ml-delete-form')?.addEventListener('submit', (e) => {
                if (!global.confirm(config.deleteConfirm || 'Delete?')) e.preventDefault();
            });
            return tr;
        }

        function render() {
            const filtered = sortRecords(filterRecords());
            updateSummary(filtered);
            const pages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE) || 1);
            currentPage = Math.min(currentPage, pages);
            const start = (currentPage - 1) * PAGE_SIZE;
            const slice = filtered.slice(start, start + PAGE_SIZE);
            body.innerHTML = '';
            if (!slice.length) {
                body.innerHTML = '<tr><td colspan="7" class="text-center" style="padding:2rem;color:#94a3b8;">No sales found</td></tr>';
            } else {
                slice.forEach((r) => body.appendChild(renderRow(r)));
            }
            renderPagination(paginationEl, currentPage, pages, filtered.length, (p) => {
                currentPage = p;
                render();
            });
            root._filtered = filtered;
        }

        function bindSort() {
            root.querySelectorAll('[data-sort]').forEach((th) => {
                th.style.cursor = 'pointer';
                th.addEventListener('click', () => {
                    const col = th.dataset.sort;
                    if (sortCol === col) sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                    else { sortCol = col; sortDir = col === 'date' ? 'desc' : 'asc'; }
                    currentPage = 1;
                    render();
                });
            });
        }

        const onFilter = () => { currentPage = 1; render(); };
        [els.preset, els.from, els.to, els.payment, els.litersMin, els.litersMax, els.amountMin, els.amountMax].forEach((el) => {
            el?.addEventListener('change', onFilter);
            el?.addEventListener('input', onFilter);
        });
        els.search?.addEventListener('input', onFilter);

        els.preset?.addEventListener('change', () => {
            const custom = els.preset.value === 'custom';
            if (els.from) els.from.disabled = !custom;
            if (els.to) els.to.disabled = !custom;
        });

        const exportCols = [
            { label: 'Date', value: (r) => r.date_display },
            { label: 'Liters', value: (r) => r.liters },
            { label: 'Rate', value: (r) => r.price_per_liter },
            { label: 'Amount', value: (r) => r.amount },
            { label: 'Buyer', value: (r) => r.buyer_name },
            { label: 'Payment', value: (r) => r.payment_status },
        ];

        document.getElementById('mlExportCsv')?.addEventListener('click', () => exportCsv(root._filtered || [], exportCols, 'milk-sales.csv'));
        document.getElementById('mlExportExcel')?.addEventListener('click', () => exportCsv(root._filtered || [], exportCols, 'milk-sales.xls'));
        document.getElementById('mlExportPdf')?.addEventListener('click', () => exportPdf(root._filtered || [], exportCols, 'Milk Sales'));

        bindSort();
        if (els.preset && !els.preset.value) els.preset.value = 'this_month';
        els.preset?.dispatchEvent(new Event('change'));
        render();
    }

    function initTransactionsGrid() {
        const root = document.getElementById('milkTxnLedger');
        if (!root) return;

        let records = [];
        try { records = JSON.parse(document.getElementById('milkTxnJson')?.textContent || '[]'); } catch (e) {}

        const body = document.getElementById('milkTxnBody');
        const paginationEl = document.getElementById('milkTxnPagination');
        const animalSelect = document.getElementById('mlTxnAnimal');
        const els = {
            preset: document.getElementById('mlTxnDatePreset'),
            from: document.getElementById('mlTxnDateFrom'),
            to: document.getElementById('mlTxnDateTo'),
            animalType: document.getElementById('mlTxnAnimalType'),
            txnType: document.getElementById('mlTxnType'),
            search: document.getElementById('mlTxnSearch'),
            totalMilk: document.getElementById('mlTxnSummaryMilk'),
            totalSales: document.getElementById('mlTxnSummarySales'),
            totalAdjust: document.getElementById('mlTxnSummaryAdjust'),
            netProd: document.getElementById('mlTxnSummaryNet'),
            count: document.getElementById('mlTxnFilteredCount'),
        };

        let currentPage = 1;
        let sortCol = 'date';
        let sortDir = 'desc';

        function getFilters() {
            const preset = els.preset?.value || 'this_month';
            let from = null;
            let to = null;
            if (preset === 'custom') {
                from = parseDate(els.from?.value);
                to = els.to?.value ? endOfDay(parseDate(els.to.value)) : null;
            } else {
                [from, to] = getPresetRange(preset);
                if (from && els.from) els.from.value = formatYmd(from);
                if (to && els.to) els.to.value = formatYmd(startOfDay(to));
            }
            return {
                from, to,
                animalType: els.animalType?.value || '',
                buffaloId: animalSelect?.value || '',
                txnType: els.txnType?.value || '',
                search: (els.search?.value || '').toLowerCase().trim(),
            };
        }

        function filterRecords() {
            const f = getFilters();
            return records.filter((r) => {
                if (!inDateRange(r.date, f.from, f.to)) return false;
                if (f.animalType && r.animal_type !== f.animalType) return false;
                if (f.buffaloId && String(r.buffalo_id) !== String(f.buffaloId)) return false;
                if (f.txnType && r.transaction_type !== f.txnType) return false;
                if (f.search) {
                    const hay = `${r.animal_tag} ${r.animal_name} ${r.buyer_name} ${r.remarks} ${r.type_label}`.toLowerCase();
                    if (!hay.includes(f.search)) return false;
                }
                return true;
            });
        }

        function sortRecords(list) {
            const dir = sortDir === 'asc' ? 1 : -1;
            return [...list].sort((a, b) => {
                let av = a[sortCol];
                let bv = b[sortCol];
                if (sortCol === 'date') { av = a.date; bv = b.date; }
                if (typeof av === 'number' && typeof bv === 'number') return (av - bv) * dir;
                return String(av ?? '').localeCompare(String(bv ?? '')) * dir;
            });
        }

        function updateSummary(filtered) {
            const production = filtered.filter((r) => r.transaction_type === 'production' && r.direction === 'in')
                .reduce((s, r) => s + r.liters, 0);
            const sales = filtered.filter((r) => r.transaction_type === 'sale')
                .reduce((s, r) => s + r.liters, 0);
            const adjust = filtered.filter((r) => ['adjust', 'wastage'].includes(r.transaction_type))
                .reduce((s, r) => s + r.liters, 0);
            const net = production - sales - adjust;
            if (els.totalMilk) els.totalMilk.textContent = production.toFixed(2) + ' L';
            if (els.totalSales) els.totalSales.textContent = sales.toFixed(2) + ' L';
            if (els.totalAdjust) els.totalAdjust.textContent = adjust.toFixed(2) + ' L';
            if (els.netProd) els.netProd.textContent = net.toFixed(2) + ' L';
            if (els.count) els.count.textContent = filtered.length + ' records';
        }

        function renderRow(r) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td data-label="Date">${escapeHtml(r.date_display)}</td>
                <td data-label="Type">${escapeHtml(r.type_label)}</td>
                <td data-label="In/Out"><span class="badge ${r.direction === 'in' ? 'badge-green' : 'badge-red'}">${r.direction === 'in' ? 'ઇન' : 'આઉટ'}</span></td>
                <td data-label="Liters">${r.liters.toFixed(2)} L</td>
                <td data-label="Balance"><strong>${r.balance_after.toFixed(2)} L</strong></td>
                <td data-label="Animal">${escapeHtml(r.animal_label)}</td>
                <td data-label="Note">${escapeHtml(r.remarks || '—')}</td>`;
            return tr;
        }

        function render() {
            const filtered = sortRecords(filterRecords());
            updateSummary(filtered);
            const pages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE) || 1);
            currentPage = Math.min(currentPage, pages);
            const start = (currentPage - 1) * PAGE_SIZE;
            const slice = filtered.slice(start, start + PAGE_SIZE);
            body.innerHTML = '';
            if (!slice.length) {
                body.innerHTML = '<tr><td colspan="7" class="text-center" style="padding:2rem;color:#94a3b8;">No transactions found</td></tr>';
            } else {
                slice.forEach((r) => body.appendChild(renderRow(r)));
            }
            renderPagination(paginationEl, currentPage, pages, filtered.length, (p) => {
                currentPage = p;
                render();
            });
            root._filtered = filtered;
        }

        function bindSort() {
            root.querySelectorAll('[data-sort]').forEach((th) => {
                th.style.cursor = 'pointer';
                th.addEventListener('click', () => {
                    const col = th.dataset.sort;
                    if (sortCol === col) sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                    else { sortCol = col; sortDir = col === 'date' ? 'desc' : 'asc'; }
                    currentPage = 1;
                    render();
                });
            });
        }

        const onFilter = () => { currentPage = 1; render(); };
        [els.preset, els.from, els.to, els.animalType, els.txnType, animalSelect].forEach((el) => {
            el?.addEventListener('change', onFilter);
        });
        els.search?.addEventListener('input', onFilter);

        els.preset?.addEventListener('change', () => {
            const custom = els.preset.value === 'custom';
            if (els.from) els.from.disabled = !custom;
            if (els.to) els.to.disabled = !custom;
        });

        const exportCols = [
            { label: 'Date', value: (r) => r.date_display },
            { label: 'Type', value: (r) => r.type_label },
            { label: 'Direction', value: (r) => r.direction },
            { label: 'Liters', value: (r) => r.liters },
            { label: 'Balance', value: (r) => r.balance_after },
            { label: 'Animal', value: (r) => r.animal_label },
            { label: 'Remarks', value: (r) => r.remarks },
        ];

        document.getElementById('mlTxnExportCsv')?.addEventListener('click', () => exportCsv(root._filtered || [], exportCols, 'milk-transactions.csv'));
        document.getElementById('mlTxnExportExcel')?.addEventListener('click', () => exportCsv(root._filtered || [], exportCols, 'milk-transactions.xls'));
        document.getElementById('mlTxnExportPdf')?.addEventListener('click', () => exportPdf(root._filtered || [], exportCols, 'Milk Transactions'));

        bindSort();
        if (els.preset && !els.preset.value) els.preset.value = 'this_month';
        els.preset?.dispatchEvent(new Event('change'));
        render();
    }

    function boot() {
        initSalesGrid();
        initTransactionsGrid();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    global.MilkDataGrid = { initSalesGrid, initTransactionsGrid, PAGE_SIZE };
})(window);
