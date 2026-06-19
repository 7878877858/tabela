/**
 * Daily Report — step wizard (one section at a time).
 */
(function (global) {
    'use strict';

    const TOTAL_STEPS = 10;
    let currentStep = 1;
    let pendingFilter = null;

    function getSections() {
        return Array.from(document.querySelectorAll('.dr-step-section[data-dr-step]'))
            .sort((a, b) => Number(a.dataset.drStep) - Number(b.dataset.drStep));
    }

    function getNavItems() {
        return document.querySelectorAll('.dr-step-nav__item[data-step]');
    }

    function updateWizardProgress(step) {
        const pct = Math.round((step / TOTAL_STEPS) * 100);
        const fill = document.getElementById('drWizardProgressFill');
        const pctEl = document.getElementById('drWizardProgressPct');
        const labelEl = document.getElementById('drWizardProgressLabel');
        if (fill) fill.style.width = pct + '%';
        if (pctEl) pctEl.textContent = String(pct);
        if (labelEl) labelEl.textContent = String(step);
    }

    function showStep(step) {
        currentStep = Math.max(1, Math.min(TOTAL_STEPS, step));
        const sections = getSections();

        sections.forEach((section) => {
            const n = Number(section.dataset.drStep);
            section.classList.toggle('is-current', n === currentStep);
            section.hidden = n !== currentStep;
        });

        const summary = document.getElementById('drSummaryCard');
        if (summary) {
            summary.hidden = currentStep !== 1;
        }

        getNavItems().forEach((link) => {
            const n = Number(link.dataset.step);
            link.classList.toggle('is-active', n === currentStep);
            link.classList.toggle('is-complete', n < currentStep);
        });

        updateWizardProgress(currentStep);

        document.querySelectorAll('.dr-collapsible-section.dr-step-section').forEach((section) => {
            const n = Number(section.dataset.drStep);
            if (n === currentStep) {
                section.classList.remove('is-collapsed');
            }
        });

        const prevBtn = document.getElementById('drWizardPrev');
        const nextBtn = document.getElementById('drWizardNext');
        const saveBtn = document.getElementById('drWizardSave');

        if (prevBtn) prevBtn.disabled = currentStep <= 1;
        if (nextBtn) nextBtn.hidden = currentStep >= TOTAL_STEPS;
        if (saveBtn) saveBtn.hidden = currentStep < TOTAL_STEPS;

        const footer = document.getElementById('drWizardFooter');
        if (footer) {
            footer.classList.toggle('is-final-step', currentStep >= TOTAL_STEPS);
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });

        if (pendingFilter && pendingFilter.step === currentStep) {
            global.DailyReportEvents?.applyQuickFilter(pendingFilter);
            pendingFilter = null;
        }

        document.dispatchEvent(new CustomEvent('dr:step-change', { detail: { step: currentStep } }));
        global.MilkCustomerSelect?.onStepChange?.(currentStep);
        global.AnimalSelect?.onStepChange?.(currentStep);
    }

    function goNext() {
        if (currentStep < TOTAL_STEPS) {
            showStep(currentStep + 1);
        }
    }

    function goPrev() {
        if (currentStep > 1) {
            showStep(currentStep - 1);
        }
    }

    function init() {
        const form = document.getElementById('dailyReportForm');
        if (!form) return;

        form.closest('.daily-report-page')?.classList.add('dr-wizard-mode');
        form.classList.add('dr-wizard-mode');

        getNavItems().forEach((link) => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const step = Number(link.dataset.step);
                if (step) showStep(step);
            });
        });

        document.getElementById('drWizardPrev')?.addEventListener('click', goPrev);
        document.getElementById('drWizardNext')?.addEventListener('click', goNext);

        document.querySelectorAll('.dr-quick-action').forEach((btn) => {
            btn.addEventListener('click', () => {
                const step = Number(btn.dataset.step);
                const ids = (btn.dataset.ids || '').split(',').filter(Boolean);
                const filter = btn.dataset.filter;
                pendingFilter = { step, ids, filter };
                if (filter === 'health') {
                    global.DailyReportEvents?.openHealthYes();
                } else if (filter === 'vaccination') {
                    global.DailyReportEvents?.openVaccinationYes();
                } else if (filter === 'pregnancy') {
                    global.DailyReportEvents?.openPregnancyCheck();
                }
                showStep(step);
            });
        });

        showStep(1);
    }

    global.DailyReportWizard = { init, showStep, goNext, goPrev, getCurrentStep: () => currentStep };

    document.addEventListener('DOMContentLoaded', init);
})(window);
