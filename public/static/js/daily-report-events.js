/**
 * Daily Report — event-based health / vaccination / pregnancy sections.
 */
(function (global) {
    'use strict';

    function appendFromTemplate(templateId, tbodyId) {
        const tpl = document.getElementById(templateId);
        const body = document.getElementById(tbodyId);
        if (!tpl || !body) return null;
        const row = tpl.content.firstElementChild.cloneNode(true);
        body.appendChild(row);
        global.AnimalSelect?.enhanceAll(row);
        toggleRemoveButtons(body, '.remove-health-row, .remove-vaccination-row, .remove-preg-row');
        return row;
    }

    function toggleRemoveButtons(tbody, selector) {
        const rows = tbody.querySelectorAll('tr');
        rows.forEach((row, i) => {
            const btn = row.querySelector(selector);
            if (btn) btn.style.display = rows.length === 1 ? 'none' : 'inline-flex';
        });
    }

    function setPanelEnabled(panel, enabled) {
        if (!panel) return;
        panel.querySelectorAll('input, select, textarea, button').forEach((el) => {
            if (el.type === 'radio' || el.type === 'checkbox') return;
            if (el.classList.contains('dr-remove-btn') || el.classList.contains('add-preg-row')) return;
            el.disabled = !enabled;
        });
    }

    function initHealthGate() {
        const yes = document.querySelector('input[name="has_health"][value="yes"]');
        const no = document.querySelector('input[name="has_health"][value="no"]');
        const revealWrap = document.getElementById('healthRevealWrap');
        const revealBtn = document.getElementById('healthRevealBtn');
        const panel = document.getElementById('healthEntryPanel');
        const body = document.getElementById('healthBody');

        function sync() {
            const isYes = yes?.checked;
            if (revealWrap) revealWrap.hidden = !isYes;
            if (!isYes) {
                if (panel) panel.hidden = true;
                if (body) body.innerHTML = '';
                setPanelEnabled(panel, false);
            } else {
                setPanelEnabled(panel, true);
            }
        }

        document.querySelectorAll('input[name="has_health"]').forEach((r) => r.addEventListener('change', sync));

        revealBtn?.addEventListener('click', () => {
            if (panel) panel.hidden = false;
            if (body && !body.querySelector('tr')) {
                appendFromTemplate('healthRowTemplate', 'healthBody');
            }
        });

        document.getElementById('addHealthRow')?.addEventListener('click', (e) => {
            e.preventDefault();
            appendFromTemplate('healthRowTemplate', 'healthBody');
        });

        document.addEventListener('click', (e) => {
            if (e.target.closest('.remove-health-row')) {
                e.target.closest('tr')?.remove();
                if (body) toggleRemoveButtons(body, '.remove-health-row');
            }
        });

        if (yes?.checked && body?.querySelector('tr')) {
            if (revealWrap) revealWrap.hidden = false;
            if (panel) panel.hidden = false;
        }
        sync();
    }

    function initVaccinationGate() {
        const yes = document.querySelector('input[name="has_vaccination"][value="yes"]');
        const revealWrap = document.getElementById('vaccinationRevealWrap');
        const revealBtn = document.getElementById('vaccinationRevealBtn');
        const panel = document.getElementById('vaccinationEntryPanel');
        const body = document.getElementById('vaccinationBody');

        function sync() {
            const isYes = yes?.checked;
            if (revealWrap) revealWrap.hidden = !isYes;
            if (!isYes) {
                if (panel) panel.hidden = true;
                if (body) body.innerHTML = '';
                setPanelEnabled(panel, false);
            } else {
                setPanelEnabled(panel, true);
            }
        }

        document.querySelectorAll('input[name="has_vaccination"]').forEach((r) => r.addEventListener('change', sync));

        revealBtn?.addEventListener('click', () => {
            if (panel) panel.hidden = false;
            if (body && !body.querySelector('tr')) {
                appendFromTemplate('vaccinationRowTemplate', 'vaccinationBody');
            }
        });

        document.getElementById('addVaccinationRow')?.addEventListener('click', (e) => {
            e.preventDefault();
            appendFromTemplate('vaccinationRowTemplate', 'vaccinationBody');
        });

        document.addEventListener('click', (e) => {
            if (e.target.closest('.remove-vaccination-row')) {
                e.target.closest('tr')?.remove();
                if (body) toggleRemoveButtons(body, '.remove-vaccination-row');
            }
        });

        if (yes?.checked && body?.querySelector('tr')) {
            if (revealWrap) revealWrap.hidden = false;
            if (panel) panel.hidden = false;
        }
        sync();
    }

    function initPregnancyActivities() {
        const wrap = document.getElementById('pregnancyPanelsWrap');
        const checkboxes = document.querySelectorAll('.pregnancy-activity-cb');

        function sync() {
            const selected = Array.from(checkboxes).filter((cb) => cb.checked).map((cb) => cb.dataset.activity);
            if (wrap) wrap.hidden = selected.length === 0;

            document.querySelectorAll('.dr-preg-panel').forEach((panel) => {
                const activity = panel.dataset.activity;
                const show = selected.includes(activity);
                panel.hidden = !show;
                setPanelEnabled(panel, show);
                const body = panel.querySelector('tbody');
                if (show && body && !body.querySelector('tr')) {
                    const btn = panel.querySelector('.add-preg-row');
                    const templateId = btn?.dataset.template;
                    const targetId = btn?.dataset.target;
                    if (templateId && targetId) {
                        appendFromTemplate(templateId, targetId);
                    }
                }
            });
        }

        checkboxes.forEach((cb) => cb.addEventListener('change', sync));

        document.querySelectorAll('.add-preg-row').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                appendFromTemplate(btn.dataset.template, btn.dataset.target);
            });
        });

        document.addEventListener('click', (e) => {
            if (e.target.closest('.remove-preg-row')) {
                const tr = e.target.closest('tr');
                const tbody = tr?.closest('tbody');
                tr?.remove();
                if (tbody) toggleRemoveButtons(tbody, '.remove-preg-row');
            }
        });

        sync();
    }

    function openHealthYes() {
        const yes = document.querySelector('input[name="has_health"][value="yes"]');
        if (yes) {
            yes.checked = true;
            yes.dispatchEvent(new Event('change', { bubbles: true }));
        }
        document.getElementById('healthRevealBtn')?.click();
    }

    function openVaccinationYes() {
        const yes = document.querySelector('input[name="has_vaccination"][value="yes"]');
        if (yes) {
            yes.checked = true;
            yes.dispatchEvent(new Event('change', { bubbles: true }));
        }
        document.getElementById('vaccinationRevealBtn')?.click();
    }

    function openPregnancyCheck() {
        const cb = document.querySelector('.pregnancy-activity-cb[data-activity="pregcheck"]');
        if (cb) {
            cb.checked = true;
            cb.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function applyQuickFilter({ ids, filter }) {
        const idSet = ids || [];
        const firstId = idSet[0];

        if (filter === 'health') {
            const row = document.querySelector('#healthBody tr');
            const select = row?.querySelector('select.animal-select');
            if (select && firstId) global.AnimalSelect?.preselectAnimal(select, firstId);
            idSet.forEach((id, i) => {
                if (i === 0) return;
                const r = appendFromTemplate('healthRowTemplate', 'healthBody');
                const sel = r?.querySelector('select.animal-select');
                if (sel) global.AnimalSelect?.preselectAnimal(sel, id);
            });
        }

        if (filter === 'vaccination') {
            const row = document.querySelector('#vaccinationBody tr') || appendFromTemplate('vaccinationRowTemplate', 'vaccinationBody');
            const select = row?.querySelector('select.animal-select');
            if (select && firstId) global.AnimalSelect?.preselectAnimal(select, firstId);
        }

        if (filter === 'pregnancy') {
            openPregnancyCheck();
            const row = document.querySelector('#pregcheckBody tr') || appendFromTemplate('pregcheckRowTemplate', 'pregcheckBody');
            const select = row?.querySelector('select.animal-select');
            if (select && firstId) global.AnimalSelect?.preselectAnimal(select, firstId);
        }
    }

    function init() {
        initHealthGate();
        initVaccinationGate();
        initPregnancyActivities();
    }

    global.DailyReportEvents = {
        init,
        openHealthYes,
        openVaccinationYes,
        openPregnancyCheck,
        applyQuickFilter,
    };

    document.addEventListener('DOMContentLoaded', init);
})(window);
