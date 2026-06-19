/**
 * Daily Report — income step (auto milk cards + manual rows).
 */
(function (global) {
    'use strict';

    function num(v) {
        const n = parseFloat(v);
        return Number.isFinite(n) ? n : 0;
    }

    function readCustomerMilkIncome() {
        let total = 0;
        document.querySelectorAll('#distBody .dr-dist-row').forEach((row) => {
            const morning = num(row.querySelector('.dist-morning')?.value);
            const evening = num(row.querySelector('.dist-evening')?.value);
            const rate = num(row.querySelector('.dist-rate')?.value);
            total += (morning + evening) * rate;
        });
        return Math.round(total * 100) / 100;
    }

    function readDairyIncome() {
        const buffalo = num(document.querySelector('[name="dairy_buffalo_amount"]')?.value);
        const cow = num(document.querySelector('[name="dairy_cow_amount"]')?.value);
        return Math.round((buffalo + cow) * 100) / 100;
    }

    function recalcManureRow(row) {
        const weight = num(row.querySelector('.manure-weight')?.value);
        const rate = num(row.querySelector('.manure-rate')?.value);
        const cell = row.querySelector('.manure-amount');
        if (cell) {
            cell.textContent = (weight * rate).toFixed(2);
        }
    }

    function recalcAutoCards() {
        const customerEl = document.getElementById('drAutoCustomerMilkIncome');
        const dairyEl = document.getElementById('drAutoDairyIncome');
        if (customerEl) {
            customerEl.textContent = readCustomerMilkIncome().toFixed(2);
        }
        if (dairyEl) {
            dairyEl.textContent = readDairyIncome().toFixed(2);
        }
    }

    function toggleRowMinus(tbodyId, rowClass, btnClass) {
        const rows = document.querySelectorAll(`#${tbodyId} .${rowClass}`);
        rows.forEach((row) => {
            const btn = row.querySelector(`.${btnClass}`);
            if (btn) {
                btn.style.display = rows.length <= 1 ? 'none' : 'inline-flex';
            }
        });
    }

    const ANIMAL_SALE_TABS = [
        { key: 'all', labelGu: 'બધા' },
        { key: 'buffalo', labelGu: 'ભેંસ' },
        { key: 'cow', labelGu: 'ગાય' },
        { key: 'buffalo_calf', labelGu: 'ભેંસના બચ્ચા' },
        { key: 'cow_calf', labelGu: 'ગાયના બચ્ચા' },
    ];

    let animalSaleActiveFilter = 'all';
    let animalSaleSearchQuery = '';

    function normalizeAnimalSaleType(type) {
        const t = (type || 'buffalo').toLowerCase();
        if (['buffalo', 'cow', 'buffalo_calf', 'cow_calf'].includes(t)) {
            return t;
        }
        if (t.includes('calf') && t.includes('buffalo')) {
            return 'buffalo_calf';
        }
        if (t.includes('calf')) {
            return 'cow_calf';
        }
        return t === 'cow' ? 'cow' : 'buffalo';
    }

    function getAnimalSaleSelects() {
        return document.querySelectorAll('#animalSaleBody select.animal-sale-select');
    }

    function countAnimalSaleTypes() {
        const counts = { all: 0, buffalo: 0, cow: 0, buffalo_calf: 0, cow_calf: 0 };
        const first = getAnimalSaleSelects()[0];
        if (!first) {
            return counts;
        }

        first.querySelectorAll('option[value]').forEach((opt) => {
            const type = normalizeAnimalSaleType(opt.dataset.animalType);
            counts.all += 1;
            if (counts[type] !== undefined) {
                counts[type] += 1;
            }
        });

        return counts;
    }

    function animalSaleOptionMatches(opt, filter, query) {
        const type = normalizeAnimalSaleType(opt.dataset.animalType);
        if (filter !== 'all' && type !== filter) {
            return false;
        }
        if (!query) {
            return true;
        }
        const tag = (opt.dataset.tag || '').toLowerCase();
        const name = (opt.dataset.name || '').toLowerCase();
        const text = opt.textContent.trim().toLowerCase();
        return tag.includes(query) || name.includes(query) || text.includes(query);
    }

    function applyAnimalSaleFilters() {
        const filter = animalSaleActiveFilter;
        const query = animalSaleSearchQuery;
        const selects = getAnimalSaleSelects();

        selects.forEach((select) => {
            const current = select.value;
            select.querySelectorAll('option').forEach((opt) => {
                if (!opt.value) {
                    opt.hidden = false;
                    return;
                }
                const show = animalSaleOptionMatches(opt, filter, query) || opt.value === current;
                opt.hidden = !show;
            });
        });

        const meta = document.getElementById('animalSaleFilterMeta');
        const first = selects[0];
        if (meta && first) {
            let visible = 0;
            first.querySelectorAll('option[value]').forEach((opt) => {
                if (!opt.hidden) {
                    visible += 1;
                }
            });
            meta.textContent = `${visible} પશુ`;
        }
    }

    function renderAnimalSaleTabs() {
        const tabsEl = document.getElementById('animalSaleTypeTabs');
        if (!tabsEl) {
            return;
        }

        const counts = countAnimalSaleTypes();
        tabsEl.innerHTML = '';

        ANIMAL_SALE_TABS.forEach((tab) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'dr-animal-tab' + (animalSaleActiveFilter === tab.key ? ' is-active' : '');
            btn.dataset.tab = tab.key;
            const count = tab.key === 'all' ? counts.all : (counts[tab.key] || 0);
            btn.innerHTML = `<span class="dr-animal-tab__label">${tab.labelGu}</span><span class="dr-animal-tab__count">${count}</span>`;
            btn.addEventListener('click', () => {
                if (animalSaleActiveFilter === tab.key) {
                    return;
                }
                animalSaleActiveFilter = tab.key;
                renderAnimalSaleTabs();
                applyAnimalSaleFilters();
            });
            tabsEl.appendChild(btn);
        });
    }

    function initAnimalSaleFilters() {
        const searchEl = document.getElementById('animalSaleSearch');
        if (!document.getElementById('animalSaleTypeTabs')) {
            return;
        }

        renderAnimalSaleTabs();
        applyAnimalSaleFilters();

        if (searchEl && !searchEl.dataset.bound) {
            searchEl.dataset.bound = '1';
            searchEl.addEventListener('input', (e) => {
                animalSaleSearchQuery = (e.target.value || '').trim().toLowerCase();
                applyAnimalSaleFilters();
            });
        }
    }

    function cloneDynamicRow(tbodyId, rowClass, resetFn) {
        const first = document.querySelector(`#${tbodyId} .${rowClass}`);
        if (!first) return null;
        const row = first.cloneNode(true);
        if (resetFn) resetFn(row);
        document.getElementById(tbodyId).appendChild(row);
        return row;
    }

    function bind() {
        document.addEventListener('input', (e) => {
            if (e.target.closest('#distBody') || e.target.closest('#milkGridHiddenStore')
                || e.target.matches('[name="dairy_buffalo_amount"], [name="dairy_cow_amount"]')) {
                recalcAutoCards();
            }
            if (e.target.closest('.dr-manure-row')) {
                recalcManureRow(e.target.closest('.dr-manure-row'));
            }
        });

        document.addEventListener('dr:milk-grid-change', recalcAutoCards);

        document.addEventListener('dr:step-change', (e) => {
            if (e.detail?.step === 8 || e.detail?.step === 9 || e.detail?.step === 10) {
                recalcAutoCards();
                document.querySelectorAll('#manureBody .dr-manure-row').forEach(recalcManureRow);
            }
            if (e.detail?.step === 10) {
                initAnimalSaleFilters();
            }
        });

        document.getElementById('addManureRow')?.addEventListener('click', (e) => {
            e.stopPropagation();
            cloneDynamicRow('manureBody', 'dr-manure-row', (row) => {
                row.querySelectorAll('input').forEach((i) => { i.value = ''; });
                const amt = row.querySelector('.manure-amount');
                if (amt) amt.textContent = '0.00';
            });
            toggleRowMinus('manureBody', 'dr-manure-row', 'remove-manure-row');
        });

        document.getElementById('addAnimalSaleRow')?.addEventListener('click', (e) => {
            e.stopPropagation();
            cloneDynamicRow('animalSaleBody', 'dr-animal-sale-row', (row) => {
                row.querySelectorAll('input').forEach((i) => { i.value = ''; });
                row.querySelectorAll('select').forEach((s) => { s.selectedIndex = 0; });
            });
            applyAnimalSaleFilters();
            toggleRowMinus('animalSaleBody', 'dr-animal-sale-row', 'remove-animal-sale-row');
        });

        document.getElementById('addOtherIncomeRow')?.addEventListener('click', (e) => {
            e.stopPropagation();
            cloneDynamicRow('otherIncomeBody', 'dr-other-income-row', (row) => {
                row.querySelectorAll('input').forEach((i) => { i.value = ''; });
            });
            toggleRowMinus('otherIncomeBody', 'dr-other-income-row', 'remove-other-income-row');
        });

        document.addEventListener('click', (e) => {
            const map = [
                ['.remove-manure-row', 'manureBody', 'dr-manure-row', 'remove-manure-row'],
                ['.remove-animal-sale-row', 'animalSaleBody', 'dr-animal-sale-row', 'remove-animal-sale-row'],
                ['.remove-other-income-row', 'otherIncomeBody', 'dr-other-income-row', 'remove-other-income-row'],
            ];
            map.forEach(([sel, body, rowClass, btnClass]) => {
                if (e.target.closest(sel)) {
                    e.target.closest('tr')?.remove();
                    toggleRowMinus(body, rowClass, btnClass);
                    recalcAutoCards();
                }
            });
        });

        document.querySelectorAll('#manureBody .dr-manure-row').forEach(recalcManureRow);
        recalcAutoCards();
        initAnimalSaleFilters();
    }

    document.addEventListener('DOMContentLoaded', bind);

    global.DailyReportIncome = {
        recalcAutoCards,
        readCustomerMilkIncome,
        readDairyIncome,
    };
})(window);
