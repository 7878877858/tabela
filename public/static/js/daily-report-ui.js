/**
 * Daily Report — premium UI helpers (visual only, no business logic).
 */
(function (global) {
    'use strict';

    const TOTAL_STEPS = 10;

    function formatDisplayDate(iso) {
        if (!iso) {
            return '—';
        }
        try {
            const d = new Date(iso + 'T12:00:00');
            return d.toLocaleDateString('gu-IN', {
                weekday: 'short',
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
        } catch (e) {
            return iso;
        }
    }

    function syncHeaderDate() {
        const input = document.querySelector('[name="report_date"]');
        const el = document.getElementById('drHeaderDate');
        if (!el || !input) {
            return;
        }
        el.textContent = formatDisplayDate(input.value);
    }

    function syncAutosaveHeader() {
        const badge = document.getElementById('dailyReportDraftBadge');
        const header = document.getElementById('drHeaderAutosave');
        if (!badge || !header) {
            return;
        }

        const label = document.getElementById('dailyReportDraftBadgeText');
        const text = label?.textContent?.trim() || 'ડ્રાફ્ટ સક્રિય';
        header.textContent = text;

        header.classList.remove('is-saving', 'is-saved');
        if (badge.classList.contains('saving')) {
            header.classList.add('is-saving');
        } else if (badge.classList.contains('saved')) {
            header.classList.add('is-saved');
        }
    }

    function recalcExpenseTotal() {
        const el = document.getElementById('expenseSectionTotal');
        if (!el) {
            return;
        }
        let total = 0;
        document.querySelectorAll('[name="expense_amount[]"]').forEach((input) => {
            const n = parseFloat(input.value);
            if (Number.isFinite(n)) {
                total += n;
            }
        });
        el.textContent = '₹' + total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function watchAutosaveBadge() {
        const badge = document.getElementById('dailyReportDraftBadge');
        if (!badge) {
            return;
        }
        syncAutosaveHeader();
        const observer = new MutationObserver(syncAutosaveHeader);
        observer.observe(badge, { attributes: true, attributeFilter: ['class'], subtree: true, childList: true, characterData: true });
    }

    function bindExpenseTotal() {
        document.addEventListener('input', (e) => {
            if (e.target.matches('[name="expense_amount[]"]')) {
                recalcExpenseTotal();
            }
        });
        recalcExpenseTotal();
    }

    function init() {
        const form = document.getElementById('dailyReportForm');
        if (!form) {
            return;
        }

        syncHeaderDate();
        document.querySelector('[name="report_date"]')?.addEventListener('change', syncHeaderDate);
        document.querySelector('[name="report_date"]')?.addEventListener('input', syncHeaderDate);

        watchAutosaveBadge();
        bindExpenseTotal();

        document.addEventListener('dr:step-change', (e) => {
            if (e.detail?.step === 7) {
                recalcExpenseTotal();
            }
        });

        document.addEventListener('click', (e) => {
            if (e.target.closest('#addExpenseRow') || e.target.closest('.remove-expense-row')) {
                setTimeout(recalcExpenseTotal, 0);
            }
        });

        const headerAutosave = document.getElementById('drHeaderAutosave');
        if (headerAutosave && !document.getElementById('dailyReportDraftBadge')) {
            headerAutosave.textContent = 'ડ્રાફ્ટ સક્રિય';
        }
    }

    global.DailyReportUI = {
        TOTAL_STEPS,
        init,
        recalcExpenseTotal,
        syncHeaderDate,
    };

    document.addEventListener('DOMContentLoaded', init);
})(window);
