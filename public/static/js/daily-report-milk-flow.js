/**
 * Daily Report — milk distribution & dairy collection (master entry).
 */
(function (global) {
    'use strict';

    function num(v) {
        const n = parseFloat(v);
        return Number.isFinite(n) ? n : 0;
    }

    function getAnimalTypes() {
        try {
            return JSON.parse(document.getElementById('drMilkAnimalTypes')?.textContent || '{}');
        } catch {
            return {};
        }
    }

    function readMilkGridProduction() {
        const types = getAnimalTypes();
        const store = document.getElementById('milkGridHiddenStore');
        let buffalo = 0;
        let cow = 0;

        if (!store) {
            return { buffalo, cow, total: 0 };
        }

        store.querySelectorAll('[data-period]').forEach((input) => {
            const id = input.dataset.buffaloId;
            const period = input.dataset.period;
            if (!id || !period) return;

            const liters = num(input.value);
            const type = types[id] === 'cow' ? 'cow' : 'buffalo';
            if (type === 'cow') cow += liters;
            else buffalo += liters;
        });

        return {
            buffalo: Math.round(buffalo * 100) / 100,
            cow: Math.round(cow * 100) / 100,
            total: Math.round((buffalo + cow) * 100) / 100,
        };
    }

    function readDistributionTotals() {
        const store = document.getElementById('distHiddenStore');
        if (!store) {
            return { buffalo: 0, cow: 0, totalLiters: 0, totalAmount: 0 };
        }

        let buffalo = 0;
        let cow = 0;
        let totalLiters = 0;
        let totalAmount = 0;

        store.querySelectorAll('input[data-sync-key$="-customer"]').forEach((customerInput) => {
            const customerId = customerInput.value;
            if (!customerId) return;

            const type = store.querySelector(`[data-sync-key="dist-${customerId}-type"]`)?.value === 'cow' ? 'cow' : 'buffalo';
            const morning = num(store.querySelector(`[data-sync-key="dist-${customerId}-morning"]`)?.value);
            const evening = num(store.querySelector(`[data-sync-key="dist-${customerId}-evening"]`)?.value);
            const rate = num(store.querySelector(`[data-sync-key="dist-${customerId}-rate"]`)?.value);
            const liters = morning + evening;
            const amount = liters * rate;

            totalLiters += liters;
            totalAmount += amount;
            if (type === 'cow') cow += liters;
            else buffalo += liters;
        });

        return {
            buffalo: Math.round(buffalo * 100) / 100,
            cow: Math.round(cow * 100) / 100,
            totalLiters: Math.round(totalLiters * 100) / 100,
            totalAmount: Math.round(totalAmount * 100) / 100,
        };
    }

    function recalcAll() {
        global.DailyReportDistPager?.sync?.();

        const dist = readDistributionTotals();
        const prod = readMilkGridProduction();
        const buffaloRemaining = Math.max(0, Math.round((prod.buffalo - dist.buffalo) * 100) / 100);
        const cowRemaining = Math.max(0, Math.round((prod.cow - dist.cow) * 100) / 100);
        const totalRemaining = Math.max(0, Math.round((prod.total - dist.totalLiters) * 100) / 100);

        const bEl = document.getElementById('distBuffaloTotal');
        const cEl = document.getElementById('distCowTotal');
        if (bEl) bEl.textContent = dist.buffalo.toFixed(2);
        if (cEl) cEl.textContent = dist.cow.toFixed(2);

        const dB = document.getElementById('dairyBuffaloLiterDisplay');
        const dC = document.getElementById('dairyCowLiterDisplay');
        if (dB) dB.textContent = buffaloRemaining.toFixed(2);
        if (dC) dC.textContent = cowRemaining.toFixed(2);

        const sumLiter = document.getElementById('distSummaryTotalLiter');
        const sumAmount = document.getElementById('distSummaryTotalAmount');
        const sumProd = document.getElementById('distSummaryProduction');
        const sumRem = document.getElementById('distSummaryRemaining');
        if (sumLiter) sumLiter.textContent = dist.totalLiters.toFixed(2);
        if (sumAmount) sumAmount.textContent = dist.totalAmount.toFixed(2);
        if (sumProd) sumProd.textContent = prod.total.toFixed(2);
        if (sumRem) sumRem.textContent = totalRemaining.toFixed(2);

        global.DailyReportIncome?.recalcAutoCards?.();
    }

    function bind() {
        document.addEventListener('input', (e) => {
            if (e.target.closest('#milkGridHiddenStore')) {
                recalcAll();
            }
        });

        document.addEventListener('dr:milk-grid-change', recalcAll);
        document.addEventListener('dr:step-change', (e) => {
            if (e.detail?.step === 8) {
                global.DailyReportDistPager?.refresh?.(true);
                recalcAll();
            }
            if (e.detail?.step === 8 || e.detail?.step === 9) {
                recalcAll();
            }
        });

        recalcAll();
    }

    function restoreDistributionDraft(rows, ui) {
        global.DailyReportDistPager?.restoreFromDraft?.(rows, ui);
        recalcAll();
    }

    document.addEventListener('DOMContentLoaded', bind);

    global.DailyReportMilkFlow = {
        recalcAll,
        readMilkGridProduction,
        readDistributionTotals,
        restoreDistributionDraft,
    };
})(window);
