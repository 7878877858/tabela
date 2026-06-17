{{-- LocalStorage draft backup: modal, status badge, and JS API --}}
<style>
    #dailyReportDraftBadge {
        position: fixed;
        top: 72px;
        right: 16px;
        z-index: 1050;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        background: #fff;
        border: 1px solid #e5e7eb;
        display: none;
        align-items: center;
        gap: 6px;
    }

    #dailyReportDraftBadge.visible {
        display: inline-flex;
    }

    #dailyReportDraftBadge.saving {
        color: #b45309;
        border-color: #fcd34d;
        background: #fffbeb;
    }

    #dailyReportDraftBadge.saved {
        color: #15803d;
        border-color: #86efac;
        background: #f0fdf4;
    }

    #dailyReportDraftBadge .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
    }

    #dailyReportDraftModal.dr-draft-open {
        display: block;
    }

    #dailyReportDraftModal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 2000;
    }

    #dailyReportDraftModal .dr-draft-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
    }

    #dailyReportDraftModal .dr-draft-dialog {
        position: relative;
        z-index: 1;
        max-width: 480px;
        margin: 10vh auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        overflow: hidden;
    }

    #dailyReportDraftModal .dr-draft-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 18px;
        font-weight: 600;
    }

    #dailyReportDraftModal .dr-draft-body {
        padding: 16px 20px;
    }

    #dailyReportDraftModal .dr-draft-footer {
        padding: 12px 20px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
</style>

<div id="dailyReportDraftBadge" class="visible saved" role="status" aria-live="polite">
    <span class="dot"></span>
    <span id="dailyReportDraftBadgeText">🟢 Draft Saved</span>
</div>

<div id="dailyReportDraftModal" tabindex="-1" aria-labelledby="dailyReportDraftModalLabel" aria-hidden="true" role="dialog">
    <div class="dr-draft-backdrop"></div>
    <div class="dr-draft-dialog">
        <div class="dr-draft-header" id="dailyReportDraftModalLabel">Draft Found. Restore?</div>
        <div class="dr-draft-body">
            <p class="mb-2">Unsaved Daily Report data was found in this browser.</p>
            <p class="text-muted mb-0" style="font-size:13px; color:#6b7280;" id="dailyReportDraftModalMeta"></p>
        </div>
        <div class="dr-draft-footer">
            <button type="button" class="btn btn-outline-secondary" id="dailyReportDraftDiscard">Discard Draft</button>
            <button type="button" class="btn btn-primary" id="dailyReportDraftRestore">Restore Draft</button>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    const DRAFT_KEY = 'daily_report_draft';
    const DEBOUNCE_MS = 3000;
    const INTERVAL_MS = 3000;
    const FORM_ID = 'dailyReportForm';

    const SECTIONS = {
        staff: {
            tbody: '#staffBody',
            addBtn: 'addStaffRow',
            fields: ['employee_id[]', 'status[]', 'remarks[]'],
            afterRestore: 'toggleStaffMinus',
        },
        health: {
            tbody: '#healthBody',
            addBtn: 'addHealthRow',
            fields: ['health_buffalo_id[]', 'health_issue[]', 'treatment[]', 'medicine_cost[]'],
            afterRestore: 'toggleHealthMinus',
        },
        vaccination: {
            tbody: '#vaccinationBody',
            addBtn: 'addVaccinationRow',
            fields: ['vaccination_buffalo_id[]', 'vaccine_name[]', 'vaccination_date[]', 'vaccination_remarks[]'],
            afterRestore: 'toggleVaccinationMinus',
        },
        pregnancy: {
            tbody: '#pregnancyBody',
            addBtn: 'addPregnancyRow',
            fields: ['pregnancy_buffalo_id[]', 'last_heat_date[]', 'ai_date[]', 'pregnant_date[]', 'expected_delivery[]'],
            afterRestore: 'togglePregnancyMinus',
        },
        expenses: {
            tbody: '#expenseBody',
            addBtn: 'addExpenseRow',
            fields: ['expense_title[]', 'expense_amount[]', 'expense_remarks[]'],
            afterRestore: 'toggleExpenseMinus',
        },
        income: {
            tbody: '#incomeBody',
            addBtn: 'addIncomeRow',
            fields: ['income_title[]', 'income_amount[]', 'income_remarks[]'],
            afterRestore: 'toggleIncomeMinus',
        },
    };

    let saveTimer = null;
    let intervalTimer = null;
    let isRestoring = false;

    function getForm() {
        return document.getElementById(FORM_ID);
    }

    function setBadge(state, text) {
        const badge = document.getElementById('dailyReportDraftBadge');
        const label = document.getElementById('dailyReportDraftBadgeText');
        if (!badge || !label) return;
        badge.classList.add('visible');
        badge.classList.remove('saving', 'saved');
        if (state) badge.classList.add(state);
        label.textContent = text;
    }

    function collectMilkGrid() {
        window.DailyReportMilkPager?.sync();
        const grid = {};
        const store = document.getElementById('milkGridHiddenStore');
        if (!store) return grid;
        store.querySelectorAll('[data-buffalo-id][data-period]').forEach(function(input) {
            const id = input.dataset.buffaloId;
            const period = input.dataset.period;
            const val = input.value;
            if (!id || !period || val === '' || val === '0') return;
            if (!grid[id]) grid[id] = {};
            grid[id][period] = val;
        });
        return grid;
    }

    function collectFeedGrid() {
        window.DailyReportFeedPager?.sync();
        const grid = {};
        const store = document.getElementById('feedGridHiddenStore');
        if (!store) return grid;
        store.querySelectorAll('.feed-qty-store').forEach(function(input) {
            const id = input.dataset.buffaloId;
            const period = input.dataset.period;
            const feedId = input.dataset.feedId;
            const val = input.value;
            if (!id || !period || !feedId || val === '' || val === '0') return;
            if (!grid[id]) grid[id] = { morning: {}, evening: {} };
            if (!grid[id][period]) grid[id][period] = {};
            grid[id][period][feedId] = val;
        });
        return grid;
    }

    function collectTableRows(tbodySelector, fieldNames) {
        const rows = [];
        document.querySelectorAll(tbodySelector + ' tr').forEach(function(tr) {
            const row = {};
            let hasValue = false;
            fieldNames.forEach(function(name) {
                const el = tr.querySelector('[name="' + name + '"]');
                if (!el) return;
                const val = el.type === 'checkbox' ? el.checked : el.value;
                row[name] = val;
                if (val !== '' && val !== false && val !== '0') hasValue = true;
            });
            if (hasValue) rows.push(row);
        });
        return rows;
    }

    function saveDraft() {
        if (isRestoring) return;

        const form = getForm();
        if (!form) return;

        try {
            const draft = {
                savedAt: new Date().toISOString(),
                isEdit: form.dataset.isEdit === '1',
                reportId: form.dataset.reportId || null,
                report_date: form.querySelector('[name="report_date"]')?.value || '',
                shift: form.querySelector('[name="shift"]')?.value || '',
                report_number: form.querySelector('[name="report_number"]')?.value || '',
                reporter: form.querySelector('[name="reporter"]')?.value || '',
                notes: form.querySelector('[name="notes"]')?.value || '',
                clean_cowshed: !!form.querySelector('[name="clean_cowshed"]')?.checked,
                clean_cowshed_by: form.querySelector('[name="clean_cowshed_by"]')?.value || '',
                clean_cowshed_note: form.querySelector('[name="clean_cowshed_note"]')?.value || '',
                clean_milk_room: !!form.querySelector('[name="clean_milk_room"]')?.checked,
                clean_milk_room_by: form.querySelector('[name="clean_milk_room_by"]')?.value || '',
                clean_milk_room_note: form.querySelector('[name="clean_milk_room_note"]')?.value || '',
                clean_store: !!form.querySelector('[name="clean_store"]')?.checked,
                clean_store_by: form.querySelector('[name="clean_store_by"]')?.value || '',
                clean_store_note: form.querySelector('[name="clean_store_note"]')?.value || '',
                milk_grid: collectMilkGrid(),
                feed_grid: collectFeedGrid(),
            };

            Object.keys(SECTIONS).forEach(function(key) {
                const cfg = SECTIONS[key];
                draft[key] = collectTableRows(cfg.tbody, cfg.fields);
            });

            localStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
            setBadge('saved', '🟢 Draft Saved');
        } catch (err) {
            console.warn('Daily report draft save failed', err);
        }
    }

    function loadDraft() {
        try {
            const raw = localStorage.getItem(DRAFT_KEY);
            if (!raw) return null;
            return JSON.parse(raw);
        } catch (e) {
            return null;
        }
    }

    function clearDraft() {
        try {
            localStorage.removeItem(DRAFT_KEY);
        } catch (e) {}
        setBadge('saved', 'Draft Saved');
    }

    function setFieldValue(form, name, value) {
        const el = form.querySelector('[name="' + name + '"]');
        if (!el) return;
        if (el.type === 'checkbox') {
            el.checked = !!value;
        } else {
            el.value = value ?? '';
        }
    }

    function ensureRowCount(cfg, count) {
        const tbody = document.querySelector(cfg.tbody);
        const addBtn = document.getElementById(cfg.addBtn);
        if (!tbody || !addBtn || count < 1) return;

        let safety = 0;
        while (tbody.querySelectorAll('tr').length < count && safety < 50) {
            addBtn.click();
            safety++;
        }

        const rows = tbody.querySelectorAll('tr');
        while (rows.length > count && count >= 0 && safety < 50) {
            const last = rows[rows.length - 1];
            const removeBtn = last.querySelector('[class*="remove-"]');
            if (removeBtn && rows.length > 1) {
                removeBtn.click();
            } else {
                break;
            }
            safety++;
        }
    }

    function fillTableRows(cfg, rows) {
        if (!rows || !rows.length) return;

        ensureRowCount(cfg, rows.length);
        const trs = document.querySelectorAll(cfg.tbody + ' tr');

        rows.forEach(function(rowData, index) {
            const tr = trs[index];
            if (!tr) return;
            cfg.fields.forEach(function(name) {
                const el = tr.querySelector('[name="' + name + '"]');
                if (!el || rowData[name] === undefined) return;
                if (el.type === 'checkbox') {
                    el.checked = !!rowData[name];
                } else {
                    el.value = rowData[name];
                }
            });
        });

        if (cfg.afterRestore && typeof window[cfg.afterRestore] === 'function') {
            window[cfg.afterRestore]();
        }
    }

    function restoreMilkGrid(grid) {
        if (!grid) return;
        const store = document.getElementById('milkGridHiddenStore');
        if (!store) return;
        Object.keys(grid).forEach(function(buffaloId) {
            const values = grid[buffaloId] || {};
            ['morning', 'evening'].forEach(function(period) {
                const input = store.querySelector('[data-sync-key="milk-' + buffaloId + '-' + period + '"]')
                    || store.querySelector('input[name="milk_grid[' + buffaloId + '][' + period + ']"]');
                if (input && values[period] !== undefined) {
                    input.value = values[period];
                }
            });
        });
        if (window.DailyReportGrids?.recalcMilkTotals) {
            window.DailyReportGrids.recalcMilkTotals();
        }
        if (window.DailyReportMilkPager?.refresh) {
            window.DailyReportMilkPager.refresh();
        }
    }

    function restoreFeedGrid(grid) {
        if (!grid) return;
        const store = document.getElementById('feedGridHiddenStore');
        if (!store) return;
        Object.keys(grid).forEach(function(buffaloId) {
            const periods = grid[buffaloId] || {};
            ['morning', 'evening'].forEach(function(period) {
                const feeds = periods[period] || {};
                Object.keys(feeds).forEach(function(feedId) {
                    const input = store.querySelector('[data-sync-key="feed-' + buffaloId + '-' + period + '-' + feedId + '"]')
                        || store.querySelector('input[name="feed_grid[' + buffaloId + '][' + period + '][' + feedId + ']"]');
                    if (input) input.value = feeds[feedId];
                });
            });
        });
        if (window.DailyReportGrids?.recalcFeedTotals) {
            window.DailyReportGrids.recalcFeedTotals();
        }
        if (window.DailyReportFeedPager?.refresh) {
            window.DailyReportFeedPager.refresh();
        }
    }

    function restoreDraft(draft) {
        if (!draft) return;

        const form = getForm();
        if (!form) return;

        isRestoring = true;

        setFieldValue(form, 'report_date', draft.report_date);
        setFieldValue(form, 'shift', draft.shift);
        setFieldValue(form, 'report_number', draft.report_number);
        setFieldValue(form, 'reporter', draft.reporter);
        setFieldValue(form, 'notes', draft.notes);

        setFieldValue(form, 'clean_cowshed', draft.clean_cowshed);
        setFieldValue(form, 'clean_cowshed_by', draft.clean_cowshed_by);
        setFieldValue(form, 'clean_cowshed_note', draft.clean_cowshed_note);
        setFieldValue(form, 'clean_milk_room', draft.clean_milk_room);
        setFieldValue(form, 'clean_milk_room_by', draft.clean_milk_room_by);
        setFieldValue(form, 'clean_milk_room_note', draft.clean_milk_room_note);
        setFieldValue(form, 'clean_store', draft.clean_store);
        setFieldValue(form, 'clean_store_by', draft.clean_store_by);
        setFieldValue(form, 'clean_store_note', draft.clean_store_note);

        Object.keys(SECTIONS).forEach(function(key) {
            fillTableRows(SECTIONS[key], draft[key]);
        });

        restoreMilkGrid(draft.milk_grid);
        restoreFeedGrid(draft.feed_grid);

        isRestoring = false;
        scheduleSave();
    }

    function scheduleSave() {
        if (isRestoring) return;
        setBadge('saving', 'Saving...');
        clearTimeout(saveTimer);
        saveTimer = setTimeout(saveDraft, DEBOUNCE_MS);
    }

    function draftMatchesPage(draft) {
        const form = getForm();
        if (!form || !draft) return false;

        const isEdit = form.dataset.isEdit === '1';
        const reportId = form.dataset.reportId || '';

        if (draft.isEdit !== isEdit) {
            if (!isEdit && !draft.isEdit) return true;
            if (isEdit && draft.isEdit && String(draft.reportId) === String(reportId)) return true;
            return false;
        }

        return true;
    }

    function hasDraftContent(draft) {
        if (!draft) return false;
        if (draft.notes || draft.shift || draft.reporter) return true;
        if (Object.keys(draft.milk_grid || {}).length) return true;
        if (Object.keys(draft.feed_grid || {}).length) return true;
        return ['staff', 'health', 'vaccination', 'pregnancy', 'expenses', 'income'].some(function(k) {
            return (draft[k] || []).length > 0;
        });
    }

    function showDraftModal(draft) {
        const modalEl = document.getElementById('dailyReportDraftModal');
        if (!modalEl) {
            restoreDraft(draft);
            return;
        }

        const meta = document.getElementById('dailyReportDraftModalMeta');
        if (meta && draft.savedAt) {
            try {
                meta.textContent = 'Last saved: ' + new Date(draft.savedAt).toLocaleString();
            } catch (e) {
                meta.textContent = '';
            }
        }

        function hideModal() {
            modalEl.classList.remove('dr-draft-open');
            modalEl.setAttribute('aria-hidden', 'true');
        }

        document.getElementById('dailyReportDraftRestore')?.addEventListener('click', function onRestore() {
            restoreDraft(draft);
            hideModal();
            document.getElementById('dailyReportDraftRestore')?.removeEventListener('click', onRestore);
        }, { once: true });

        document.getElementById('dailyReportDraftDiscard')?.addEventListener('click', function onDiscard() {
            clearDraft();
            hideModal();
            document.getElementById('dailyReportDraftDiscard')?.removeEventListener('click', onDiscard);
        }, { once: true });

        modalEl.classList.add('dr-draft-open');
        modalEl.setAttribute('aria-hidden', 'false');
    }

    function initDraftSystem() {
        const form = getForm();
        if (!form) return;

        form.addEventListener('input', function(e) {
            if (e.target && !e.target.matches('input, select, textarea')) return;
            scheduleSave();
        }, true);

        form.addEventListener('change', function(e) {
            if (e.target && !e.target.matches('input, select, textarea')) return;
            scheduleSave();
        }, true);

        document.addEventListener('click', function(e) {
            if (!e.target.closest('#' + FORM_ID) && !e.target.id?.match(/^add(Staff|Health|Vaccination|Pregnancy|Expense|Income)Row$/)) return;
            if (e.target.closest('[class*="remove-"]') || e.target.id?.match(/^add/)) {
                scheduleSave();
            }
        });

        const observeTargets = ['#staffBody', '#healthBody', '#vaccinationBody', '#pregnancyBody', '#expenseBody', '#incomeBody'];
        if (typeof MutationObserver !== 'undefined') {
            const observer = new MutationObserver(function() {
                scheduleSave();
            });
            observeTargets.forEach(function(sel) {
                const el = document.querySelector(sel);
                if (el) observer.observe(el, { childList: true });
            });
        }

        form.addEventListener('submit', function() {
            clearDraft();
        });

        const draft = loadDraft();
        if (draft && draftMatchesPage(draft) && hasDraftContent(draft)) {
            showDraftModal(draft);
        } else {
            setBadge('saved', '🟢 Draft Saved');
        }

        intervalTimer = setInterval(function() {
            if (!isRestoring) {
                saveDraft();
            }
        }, INTERVAL_MS);
    }

    window.DailyReportDraft = {
        saveDraft: saveDraft,
        loadDraft: loadDraft,
        restoreDraft: restoreDraft,
        clearDraft: clearDraft,
        scheduleSave: scheduleSave,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDraftSystem);
    } else {
        initDraftSystem();
    }
})();
</script>
