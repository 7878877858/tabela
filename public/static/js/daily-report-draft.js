/**
 * Daily Report offline draft — IndexedDB (primary), localStorage (fallback).
 * Zero server requests while the operator is filling the form.
 */
(function (global) {
    'use strict';

    const DB_NAME = 'tabela_daily_report';
    const DB_VERSION = 1;
    const STORE_NAME = 'drafts';
    const LS_FALLBACK_KEY = 'daily_report_draft_v2';
    const BACKUP_INTERVAL_MS = 60000;
    const SAVE_DEBOUNCE_MS = 80;
    const FORM_ID = 'dailyReportForm';
    const DEBUG = true;

    function log(...args) {
        if (DEBUG) console.log('[DailyReportDraft]', ...args);
    }

    let dbPromise = null;
    let saveTimer = null;
    let backupTimer = null;
    let isRestoring = false;
    let lastSnapshot = null;
    let draftKey = null;

    function getForm() {
        return document.getElementById(FORM_ID);
    }

    function resolveDraftKey() {
        const form = getForm();
        if (!form) return 'create';
        const isEdit = form.dataset.isEdit === '1';
        const reportId = form.dataset.reportId || 'new';
        return isEdit ? `edit_${reportId}` : 'create';
    }

    function openDb() {
        if (dbPromise) return dbPromise;
        dbPromise = new Promise((resolve, reject) => {
            if (!('indexedDB' in global)) {
                reject(new Error('IndexedDB unavailable'));
                return;
            }
            const req = indexedDB.open(DB_NAME, DB_VERSION);
            req.onupgradeneeded = () => {
                const db = req.result;
                if (!db.objectStoreNames.contains(STORE_NAME)) {
                    db.createObjectStore(STORE_NAME);
                }
            };
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
        });
        return dbPromise;
    }

    function idbGet(key) {
        return openDb().then((db) => new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readonly');
            const req = tx.objectStore(STORE_NAME).get(key);
            req.onsuccess = () => resolve(req.result ?? null);
            req.onerror = () => reject(req.error);
        })).catch(() => null);
    }

    function idbSet(key, value) {
        return openDb().then((db) => new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            tx.oncomplete = () => resolve();
            tx.onerror = () => reject(tx.error);
            tx.objectStore(STORE_NAME).put(value, key);
        })).catch((err) => {
            console.warn('[DailyReportDraft] IndexedDB save failed', err);
        });
    }

    function idbDelete(key) {
        return openDb().then((db) => new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            tx.oncomplete = () => resolve();
            tx.onerror = () => reject(tx.error);
            tx.objectStore(STORE_NAME).delete(key);
        })).catch(() => {});
    }

    function lsGet() {
        try {
            const raw = localStorage.getItem(LS_FALLBACK_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    }

    function lsSet(draft) {
        try {
            localStorage.setItem(LS_FALLBACK_KEY, JSON.stringify(draft));
        } catch (e) {
            console.warn('[DailyReportDraft] localStorage save failed', e);
        }
    }

    function lsClear() {
        try {
            localStorage.removeItem(LS_FALLBACK_KEY);
            localStorage.removeItem('daily_report_draft');
        } catch (e) {}
    }

    function setBadge(state, mainText, timeText) {
        const badge = document.getElementById('dailyReportDraftBadge');
        const label = document.getElementById('dailyReportDraftBadgeText');
        const timeEl = document.getElementById('dailyReportDraftBadgeTime');
        if (!badge || !label) return;
        badge.classList.add('visible');
        badge.classList.remove('saving', 'saved');
        if (state) badge.classList.add(state);
        label.textContent = mainText;
        if (timeEl) timeEl.textContent = timeText || '';
    }

    function formatTime(iso) {
        try {
            return new Date(iso).toLocaleString('en-GB', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        } catch (e) {
            return '';
        }
    }

    function formatTimeShort(iso) {
        try {
            return new Date(iso).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
        } catch (e) {
            return '';
        }
    }

    function buildAnimalTagMap() {
        const map = {};
        ['milkAnimalsJson', 'feedAnimalsJson'].forEach((id) => {
            try {
                const animals = JSON.parse(document.getElementById(id)?.textContent || '[]');
                animals.forEach((a) => {
                    map[String(a.id)] = a.tag || String(a.id);
                });
            } catch (e) { /* ignore */ }
        });
        return map;
    }

    function countMilkGridValues(grid) {
        let count = 0;
        Object.keys(grid || {}).forEach((id) => {
            const v = grid[id] || {};
            if (v.morning !== '' && v.morning != null) count++;
            if (v.evening !== '' && v.evening != null) count++;
        });
        return count;
    }

    function countFeedGridValues(grid) {
        let count = 0;
        Object.keys(grid || {}).forEach((id) => {
            const periods = grid[id] || {};
            ['morning', 'evening'].forEach((period) => {
                Object.keys(periods[period] || {}).forEach((feedId) => {
                    const val = periods[period][feedId];
                    if (val !== '' && val != null) count++;
                });
            });
        });
        return count;
    }

    function waitForGridPagers() {
        return new Promise((resolve) => {
            let attempts = 0;
            const tick = () => {
                const milkReady = global.DailyReportMilkPager?.restoreFromDraft;
                const feedReady = global.DailyReportFeedPager?.restoreFromDraft;
                const distReady = !document.getElementById('distHiddenStore') || global.DailyReportDistPager?.restoreFromDraft;
                if (milkReady && feedReady && distReady) {
                    resolve();
                    return;
                }
                attempts += 1;
                if (attempts > 60) {
                    log('Grid pagers not ready after timeout — continuing anyway');
                    resolve();
                    return;
                }
                setTimeout(tick, 50);
            };
            tick();
        });
    }

    function waitForNextFrame() {
        return new Promise((resolve) => {
            requestAnimationFrame(() => requestAnimationFrame(resolve));
        });
    }

    function collectMilkGrid() {
        global.DailyReportMilkPager?.sync();
        const grid = {};
        const store = document.getElementById('milkGridHiddenStore');
        if (!store) return grid;
        store.querySelectorAll('[data-buffalo-id][data-period]').forEach((input) => {
            const id = input.dataset.buffaloId;
            const period = input.dataset.period;
            if (!id || !period) return;
            if (!grid[id]) grid[id] = {};
            grid[id][period] = input.value ?? '';
        });
        return grid;
    }

    function collectFeedGrid() {
        global.DailyReportFeedPager?.sync();
        const grid = {};
        const store = document.getElementById('feedGridHiddenStore');
        if (!store) return grid;
        store.querySelectorAll('.feed-qty-store').forEach((input) => {
            const id = input.dataset.buffaloId;
            const period = input.dataset.period;
            const feedId = input.dataset.feedId;
            if (!id || !period || !feedId) return;
            if (!grid[id]) grid[id] = { morning: {}, evening: {} };
            if (!grid[id][period]) grid[id][period] = {};
            grid[id][period][feedId] = input.value ?? '';
        });
        return grid;
    }

    function collectDistGrid() {
        global.DailyReportDistPager?.sync();
        const rows = [];
        const store = document.getElementById('distHiddenStore');
        if (!store) return rows;

        store.querySelectorAll('input[data-sync-key$="-customer"]').forEach((customerInput) => {
            const customerId = customerInput.value;
            if (!customerId) return;

            const row = {
                'dist_customer_id[]': customerId,
                'dist_milk_type[]': store.querySelector(`[data-sync-key="dist-${customerId}-type"]`)?.value ?? '',
                'dist_morning_liter[]': store.querySelector(`[data-sync-key="dist-${customerId}-morning"]`)?.value ?? '',
                'dist_evening_liter[]': store.querySelector(`[data-sync-key="dist-${customerId}-evening"]`)?.value ?? '',
                'dist_rate_per_liter[]': store.querySelector(`[data-sync-key="dist-${customerId}-rate"]`)?.value ?? '',
            };

            const hasValue = ['dist_milk_type[]', 'dist_morning_liter[]', 'dist_evening_liter[]', 'dist_rate_per_liter[]']
                .some((key) => row[key] !== '' && row[key] != null);
            if (hasValue) rows.push(row);
        });

        return rows;
    }

    function collectTableRows(tbodySelector, fieldNames) {
        const rows = [];
        document.querySelectorAll(`${tbodySelector} tr`).forEach((tr) => {
            const row = {};
            let hasValue = false;
            fieldNames.forEach((name) => {
                const el = tr.querySelector(`[name="${name}"]`);
                if (!el) return;
                const val = el.type === 'checkbox' ? el.checked : el.value;
                row[name] = val;
                if (val !== '' && val !== false) hasValue = true;
            });
            if (hasValue) rows.push(row);
        });
        return rows;
    }

    function collectDraft() {
        const form = getForm();
        if (!form) return null;

        const draft = {
            version: 2,
            savedAt: new Date().toISOString(),
            draftKey: draftKey,
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
            milk_ui: global.DailyReportMilkPager?.getState?.() || {},
            feed_ui: global.DailyReportFeedPager?.getState?.() || {},
            dist_ui: global.DailyReportDistPager?.getState?.() || {},
            wizard_step: global.DailyReportWizard?.getCurrentStep?.() || 1,
            has_health: form.querySelector('input[name="has_health"]:checked')?.value || 'no',
            has_vaccination: form.querySelector('input[name="has_vaccination"]:checked')?.value || 'no',
            health_panel_open: !document.getElementById('healthEntryPanel')?.hidden,
            vaccination_panel_open: !document.getElementById('vaccinationEntryPanel')?.hidden,
            pregnancy_activities: Array.from(form.querySelectorAll('.pregnancy-activity-cb:checked')).map((cb) => cb.value),
            staff: collectTableRows('#staffBody', ['employee_id[]', 'status[]', 'remarks[]']),
            health: collectTableRows('#healthBody', ['health_buffalo_id[]', 'health_issue[]', 'treatment[]', 'medicine_cost[]']),
            vaccination: collectTableRows('#vaccinationBody', ['vaccination_buffalo_id[]', 'vaccine_name[]', 'vaccination_date[]', 'vaccination_remarks[]']),
            heat: collectTableRows('#heatBody', ['heat_buffalo_id[]', 'heat_date[]', 'heat_note[]']),
            ai: collectTableRows('#aiBody', ['ai_buffalo_id[]', 'ai_date[]', 'ai_note[]']),
            pregcheck: collectTableRows('#pregcheckBody', ['pregcheck_buffalo_id[]', 'pregcheck_date[]', 'pregcheck_note[]']),
            delivery: collectTableRows('#deliveryBody', ['delivery_buffalo_id[]', 'delivery_date[]', 'delivery_note[]']),
            expenses: collectTableRows('#expenseBody', ['expense_title[]', 'expense_amount[]', 'expense_remarks[]']),
            income: collectTableRows('#incomeBody', ['income_title[]', 'income_amount[]', 'income_remarks[]']),
            distribution: collectDistGrid(),
            dairy: {
                slip_number: form.querySelector('[name="dairy_slip_number"]')?.value || '',
                buffalo_fat: form.querySelector('[name="dairy_buffalo_fat"]')?.value || '',
                buffalo_snf: form.querySelector('[name="dairy_buffalo_snf"]')?.value || '',
                buffalo_amount: form.querySelector('[name="dairy_buffalo_amount"]')?.value || '',
                cow_fat: form.querySelector('[name="dairy_cow_fat"]')?.value || '',
                cow_snf: form.querySelector('[name="dairy_cow_snf"]')?.value || '',
                cow_amount: form.querySelector('[name="dairy_cow_amount"]')?.value || '',
                notes: form.querySelector('[name="dairy_notes"]')?.value || '',
            },
        };

        return draft;
    }

    async function persistDraft(draft) {
        if (!draft || !draftKey) return;
        lastSnapshot = draft;
        lsSet(draft);
        await idbSet(draftKey, draft);
        setBadge('saved', '🟢 Draft Saved', `Last Saved: ${formatTimeShort(draft.savedAt)}`);
    }

    async function saveDraft() {
        if (isRestoring) return;
        const draft = collectDraft();
        if (!draft) return;
        await persistDraft(draft);
    }

    function scheduleSave() {
        if (isRestoring) return;
        setBadge('saving', 'Saving draft...', '');
        clearTimeout(saveTimer);
        saveTimer = setTimeout(() => {
            saveDraft();
        }, SAVE_DEBOUNCE_MS);
    }

    function flushDraftSync() {
        if (isRestoring) return;
        const draft = collectDraft();
        if (!draft) return;
        lastSnapshot = draft;
        lsSet(draft);
        if (draftKey) {
            idbSet(draftKey, draft);
        }
    }

    async function loadDraft() {
        draftKey = resolveDraftKey();
        let draft = await idbGet(draftKey);
        if (!draft) {
            const legacy = lsGet();
            if (legacy && (!legacy.draftKey || legacy.draftKey === draftKey)) {
                draft = legacy;
                if (draftKey) await idbSet(draftKey, draft);
            }
        }
        return draft;
    }

    async function clearDraft() {
        draftKey = resolveDraftKey();
        lastSnapshot = null;
        lsClear();
        if (draftKey) await idbDelete(draftKey);
        setBadge('saved', '🟢 Draft Saved', '');
    }

    async function clearAllDrafts() {
        lastSnapshot = null;
        lsClear();
        try {
            const db = await openDb();
            await new Promise((resolve, reject) => {
                const tx = db.transaction(STORE_NAME, 'readwrite');
                tx.oncomplete = () => resolve();
                tx.onerror = () => reject(tx.error);
                tx.objectStore(STORE_NAME).clear();
            });
        } catch (e) {}
    }

    function setFieldValue(form, name, value) {
        const el = form.querySelector(`[name="${name}"]`);
        if (!el) return;
        if (el.type === 'checkbox') {
            el.checked = !!value;
        } else if (el.type === 'radio') {
            const radio = form.querySelector(`[name="${name}"][value="${value}"]`);
            if (radio) radio.checked = true;
        } else {
            el.value = value ?? '';
        }
    }

    function clearTbody(tbodyId) {
        const tbody = document.getElementById(tbodyId);
        if (tbody) tbody.innerHTML = '';
    }

    function rebuildTableFromTemplate(templateId, tbodyId, rows, fieldNames) {
        const template = document.getElementById(templateId);
        const tbody = document.getElementById(tbodyId);
        if (!tbody) return;
        tbody.innerHTML = '';
        if (!rows?.length) return;

        rows.forEach((rowData) => {
            let tr;
            if (template?.content?.firstElementChild) {
                tr = template.content.firstElementChild.cloneNode(true);
            } else {
                tr = document.createElement('tr');
            }
            tbody.appendChild(tr);
            fieldNames.forEach((name) => {
                const el = tr.querySelector(`[name="${name}"]`);
                if (!el || rowData[name] === undefined) return;
                if (el.type === 'checkbox') {
                    el.checked = !!rowData[name];
                } else {
                    el.value = rowData[name];
                }
            });
            global.AnimalSelect?.enhanceAll(tr);
            fieldNames.forEach((name) => {
                const el = tr.querySelector(`[name="${name}"]`);
                if (el?.classList?.contains('animal-select') && rowData[name]) {
                    global.AnimalSelect?.preselectAnimal(el, rowData[name]);
                }
            });
        });
    }

    function ensureRowCount(cfg, count) {
        const tbody = document.querySelector(cfg.tbody);
        const addBtn = document.getElementById(cfg.addBtn);
        if (!tbody || !addBtn || count < 1) return;

        let safety = 0;
        while (tbody.querySelectorAll('tr').length < count && safety < 80) {
            addBtn.click();
            safety++;
        }
    }

    function fillLegacyTableRows(cfg, rows) {
        if (!rows?.length) return;
        ensureRowCount(cfg, rows.length);
        const trs = document.querySelectorAll(`${cfg.tbody} tr`);
        rows.forEach((rowData, index) => {
            const tr = trs[index];
            if (!tr) return;
            cfg.fields.forEach((name) => {
                const el = tr.querySelector(`[name="${name}"]`);
                if (!el || rowData[name] === undefined) return;
                if (el.type === 'checkbox') {
                    el.checked = !!rowData[name];
                } else {
                    el.value = rowData[name];
                }
                if (el.classList?.contains('animal-select') && rowData[name]) {
                    global.AnimalSelect?.preselectAnimal(el, rowData[name]);
                }
            });
        });
        if (cfg.afterRestore && typeof global[cfg.afterRestore] === 'function') {
            global[cfg.afterRestore]();
        }
    }

    function restoreMilkGrid(grid, ui, tagMap) {
        if (!grid || !Object.keys(grid).length) return;

        Object.keys(grid).forEach((buffaloId) => {
            const values = grid[buffaloId] || {};
            ['morning', 'evening'].forEach((period) => {
                const val = values[period];
                if (val === '' || val == null) return;
                const tag = tagMap[buffaloId] || buffaloId;
                log(`Restoring ${tag} ${period.charAt(0).toUpperCase() + period.slice(1)} = ${val}`);
            });
        });

        if (global.DailyReportMilkPager?.restoreFromDraft) {
            global.DailyReportMilkPager.restoreFromDraft(grid, ui);
        } else {
            const store = document.getElementById('milkGridHiddenStore');
            if (!store) return;
            Object.keys(grid).forEach((buffaloId) => {
                const values = grid[buffaloId] || {};
                ['morning', 'evening'].forEach((period) => {
                    const input = store.querySelector(`[data-sync-key="milk-${buffaloId}-${period}"]`)
                        || store.querySelector(`input[name="milk_grid[${buffaloId}][${period}]"]`);
                    if (input && values[period] !== undefined) input.value = values[period];
                });
            });
            global.DailyReportMilkPager?.refresh?.(true);
        }
        global.DailyReportGrids?.recalcMilkTotals?.();
    }

    function restoreFeedGrid(grid, ui, tagMap) {
        if (!grid || !Object.keys(grid).length) return;

        Object.keys(grid).forEach((buffaloId) => {
            const periods = grid[buffaloId] || {};
            ['morning', 'evening'].forEach((period) => {
                Object.keys(periods[period] || {}).forEach((feedId) => {
                    const val = periods[period][feedId];
                    if (val === '' || val == null) return;
                    const tag = tagMap[buffaloId] || buffaloId;
                    log(`Restoring feed ${tag} ${period} feed#${feedId} = ${val}`);
                });
            });
        });

        if (global.DailyReportFeedPager?.restoreFromDraft) {
            global.DailyReportFeedPager.restoreFromDraft(grid, ui);
        } else {
            const store = document.getElementById('feedGridHiddenStore');
            if (!store) return;
            Object.keys(grid).forEach((buffaloId) => {
                const periods = grid[buffaloId] || {};
                ['morning', 'evening'].forEach((period) => {
                    Object.keys(periods[period] || {}).forEach((feedId) => {
                        const input = store.querySelector(`[data-sync-key="feed-${buffaloId}-${period}-${feedId}"]`)
                            || store.querySelector(`input[name="feed_grid[${buffaloId}][${period}][${feedId}]"]`);
                        if (input) input.value = periods[period][feedId];
                    });
                });
            });
            global.DailyReportFeedPager?.refresh?.(true);
        }
        global.DailyReportGrids?.recalcFeedTotals?.();
    }

    function restoreEventSections(draft) {
        if (draft.has_health === 'yes') {
            const yes = document.querySelector('input[name="has_health"][value="yes"]');
            if (yes) {
                yes.checked = true;
                yes.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (draft.health_panel_open || (draft.health && draft.health.length)) {
                document.getElementById('healthRevealBtn')?.click();
            }
            rebuildTableFromTemplate(
                'healthRowTemplate',
                'healthBody',
                draft.health,
                ['health_buffalo_id[]', 'health_issue[]', 'treatment[]', 'medicine_cost[]']
            );
        }

        if (draft.has_vaccination === 'yes') {
            const yes = document.querySelector('input[name="has_vaccination"][value="yes"]');
            if (yes) {
                yes.checked = true;
                yes.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (draft.vaccination_panel_open || (draft.vaccination && draft.vaccination.length)) {
                document.getElementById('vaccinationRevealBtn')?.click();
            }
            rebuildTableFromTemplate(
                'vaccinationRowTemplate',
                'vaccinationBody',
                draft.vaccination,
                ['vaccination_buffalo_id[]', 'vaccine_name[]', 'vaccination_date[]', 'vaccination_remarks[]']
            );
        }

        (draft.pregnancy_activities || []).forEach((activity) => {
            const cb = document.querySelector(`.pregnancy-activity-cb[data-activity="${activity}"]`);
            if (cb) {
                cb.checked = true;
                cb.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        const pregMaps = [
            ['heatRowTemplate', 'heatBody', draft.heat, ['heat_buffalo_id[]', 'heat_date[]', 'heat_note[]']],
            ['aiRowTemplate', 'aiBody', draft.ai, ['ai_buffalo_id[]', 'ai_date[]', 'ai_note[]']],
            ['pregcheckRowTemplate', 'pregcheckBody', draft.pregcheck, ['pregcheck_buffalo_id[]', 'pregcheck_date[]', 'pregcheck_note[]']],
            ['deliveryRowTemplate', 'deliveryBody', draft.delivery, ['delivery_buffalo_id[]', 'delivery_date[]', 'delivery_note[]']],
        ];
        pregMaps.forEach(([tpl, body, rows, fields]) => {
            if (rows?.length) rebuildTableFromTemplate(tpl, body, rows, fields);
        });
    }

    async function restoreDraft(draft) {
        if (!draft) return;
        const form = getForm();
        if (!form) return;

        isRestoring = true;
        log('Draft Loaded', draft.savedAt || '');

        const milkCount = countMilkGridValues(draft.milk_grid);
        const feedCount = countFeedGridValues(draft.feed_grid);
        log(`Milk Records Found: ${milkCount}`);
        log(`Feed Records Found: ${feedCount}`);

        setFieldValue(form, 'report_date', draft.report_date);
        setFieldValue(form, 'shift', draft.shift);
        setFieldValue(form, 'report_number', draft.report_number);
        setFieldValue(form, 'reporter', draft.reporter);
        setFieldValue(form, 'notes', draft.notes);

        ['clean_cowshed', 'clean_milk_room', 'clean_store'].forEach((name) => {
            setFieldValue(form, name, draft[name]);
            setFieldValue(form, `${name}_by`, draft[`${name}_by`]);
            setFieldValue(form, `${name}_note`, draft[`${name}_note`]);
        });

        const legacyStaff = { tbody: '#staffBody', addBtn: 'addStaffRow', fields: ['employee_id[]', 'status[]', 'remarks[]'], afterRestore: 'toggleStaffMinus' };
        const legacyExpense = { tbody: '#expenseBody', addBtn: 'addExpenseRow', fields: ['expense_title[]', 'expense_amount[]', 'expense_remarks[]'], afterRestore: 'toggleExpenseMinus' };
        const legacyIncome = { tbody: '#incomeBody', addBtn: 'addIncomeRow', fields: ['income_title[]', 'income_amount[]', 'income_remarks[]'], afterRestore: 'toggleIncomeMinus' };
        const legacyDist = {
            tbody: '#distBody',
            fields: ['dist_customer_id[]', 'dist_milk_type[]', 'dist_morning_liter[]', 'dist_evening_liter[]', 'dist_rate_per_liter[]'],
            afterRestore: () => global.DailyReportMilkFlow?.recalcAll?.(),
        };

        fillLegacyTableRows(legacyStaff, draft.staff);
        fillLegacyTableRows(legacyExpense, draft.expenses);
        fillLegacyTableRows(legacyIncome, draft.income);

        if (draft.distribution?.length && global.DailyReportDistPager?.restoreFromDraft) {
            global.DailyReportDistPager.restoreFromDraft(
                draft.distribution,
                draft.dist_ui || {}
            );
            global.DailyReportMilkFlow?.recalcAll?.();
        } else if (draft.distribution?.length && global.DailyReportMilkFlow?.restoreDistributionDraft) {
            global.DailyReportMilkFlow.restoreDistributionDraft(draft.distribution, draft.dist_ui || {});
        } else {
            fillLegacyTableRows(legacyDist, draft.distribution);
        }

        if (draft.dairy) {
            setFieldValue(form, 'dairy_slip_number', draft.dairy.slip_number);
            setFieldValue(form, 'dairy_buffalo_fat', draft.dairy.buffalo_fat);
            setFieldValue(form, 'dairy_buffalo_snf', draft.dairy.buffalo_snf);
            setFieldValue(form, 'dairy_buffalo_amount', draft.dairy.buffalo_amount);
            setFieldValue(form, 'dairy_cow_fat', draft.dairy.cow_fat);
            setFieldValue(form, 'dairy_cow_snf', draft.dairy.cow_snf);
            setFieldValue(form, 'dairy_cow_amount', draft.dairy.cow_amount);
            setFieldValue(form, 'dairy_notes', draft.dairy.notes);
        }

        if (draft.wizard_step && global.DailyReportWizard?.showStep) {
            global.DailyReportWizard.showStep(draft.wizard_step);
        }

        await waitForGridPagers();
        await waitForNextFrame();

        const tagMap = buildAnimalTagMap();
        restoreMilkGrid(draft.milk_grid, draft.milk_ui, tagMap);
        restoreFeedGrid(draft.feed_grid, draft.feed_ui, tagMap);
        restoreEventSections(draft);

        await waitForNextFrame();
        global.DailyReportGrids?.recalcMilkTotals?.();
        global.DailyReportGrids?.recalcFeedTotals?.();
        global.DailyReportMilkFlow?.recalcAll?.();

        log('Restore Complete');

        isRestoring = false;
        await saveDraft();
    }

    function draftMatchesPage(draft) {
        const form = getForm();
        if (!form || !draft) return false;
        const isEdit = form.dataset.isEdit === '1';
        const reportId = form.dataset.reportId || '';
        if (draft.draftKey && draft.draftKey !== resolveDraftKey()) return false;
        if (draft.isEdit !== isEdit) {
            if (!isEdit && !draft.isEdit) return true;
            return isEdit && draft.isEdit && String(draft.reportId) === String(reportId);
        }
        return true;
    }

    function hasDraftContent(draft) {
        if (!draft) return false;
        if (draft.notes?.trim() || draft.shift || draft.reporter) return true;
        if (Object.keys(draft.milk_grid || {}).some((id) => {
            const v = draft.milk_grid[id];
            return (v.morning && v.morning !== '' && v.morning !== '0')
                || (v.evening && v.evening !== '' && v.evening !== '0');
        })) return true;
        if (Object.keys(draft.feed_grid || {}).length) return true;
        const lists = ['staff', 'health', 'vaccination', 'heat', 'ai', 'pregcheck', 'delivery', 'expenses', 'income', 'distribution'];
        if (lists.some((k) => (draft[k] || []).length > 0)) return true;
        if (draft.dairy && Object.values(draft.dairy).some((v) => v && String(v).trim() !== '')) return true;
        if (draft.has_health === 'yes' || draft.has_vaccination === 'yes') return true;
        if ((draft.pregnancy_activities || []).length) return true;
        if ((draft.wizard_step || 1) > 1) return true;
        return false;
    }

    function showDraftModal(draft) {
        const modalEl = document.getElementById('dailyReportDraftModal');
        if (!modalEl) {
            restoreDraft(draft);
            return;
        }

        const meta = document.getElementById('dailyReportDraftModalMeta');
        if (meta && draft.savedAt) {
            meta.textContent = `Last Saved: ${formatTime(draft.savedAt)}`;
        }

        const hideModal = () => {
            modalEl.classList.remove('dr-draft-open');
            modalEl.setAttribute('aria-hidden', 'true');
        };

        document.getElementById('dailyReportDraftRestore')?.addEventListener('click', async function onRestore() {
            await restoreDraft(draft);
            hideModal();
        }, { once: true });

        document.getElementById('dailyReportDraftDiscard')?.addEventListener('click', async function onDiscard() {
            await clearDraft();
            hideModal();
        }, { once: true });

        modalEl.classList.add('dr-draft-open');
        modalEl.setAttribute('aria-hidden', 'false');
    }

    function bindListeners() {
        const form = getForm();
        if (!form) return;

        const onFieldActivity = (e) => {
            if (!e.target.matches('input, select, textarea, button')) return;
            scheduleSave();
        };

        form.addEventListener('input', onFieldActivity, true);
        form.addEventListener('change', onFieldActivity, true);
        form.addEventListener('keyup', onFieldActivity, true);

        document.addEventListener('click', (e) => {
            if (e.target.closest(`#${FORM_ID}`) || e.target.id?.match(/^add(Staff|Health|Vaccination|Expense|Income|Dist)Row$/)
                || e.target.closest('.add-preg-row') || e.target.closest('.dr-quick-action')
                || e.target.closest('#drWizardNext') || e.target.closest('#drWizardPrev')
                || e.target.closest('[class*="remove-"]') || e.target.closest('#healthRevealBtn')
                || e.target.closest('#vaccinationRevealBtn')) {
                scheduleSave();
            }
        });

        document.addEventListener('dr:step-change', () => scheduleSave());

        const observeTargets = ['#staffBody', '#healthBody', '#vaccinationBody', '#heatBody', '#aiBody', '#pregcheckBody', '#deliveryBody', '#expenseBody', '#incomeBody', '#distBody', '#milkGridHiddenStore', '#feedGridHiddenStore'];
        if (typeof MutationObserver !== 'undefined') {
            const observer = new MutationObserver(() => scheduleSave());
            observeTargets.forEach((sel) => {
                const el = document.querySelector(sel);
                if (el) observer.observe(el, { childList: true, subtree: true, characterData: true });
            });
        }

        form.addEventListener('submit', () => {
            global.DailyReportDistPager?.sync?.();
            global.DailyReportMilkPager?.sync?.();
            global.DailyReportFeedPager?.sync?.();
            flushDraftSync();
        });

        window.addEventListener('beforeunload', flushDraftSync);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') flushDraftSync();
        });
    }

    async function initDraftSystem() {
        draftKey = resolveDraftKey();
        bindListeners();

        const draft = await loadDraft();
        if (draft) {
            log('Draft Loaded from storage', draft.savedAt, `Milk keys: ${Object.keys(draft.milk_grid || {}).length}`);
        }
        if (draft && draftMatchesPage(draft) && hasDraftContent(draft)) {
            showDraftModal(draft);
        } else {
            setBadge('saved', '🟢 Draft Saved', draft?.savedAt ? `Last Saved: ${formatTimeShort(draft.savedAt)}` : '');
        }

        backupTimer = setInterval(() => {
            if (!isRestoring) saveDraft();
        }, BACKUP_INTERVAL_MS);

        if (sessionStorage.getItem('dr_draft_cleared') === '1') {
            sessionStorage.removeItem('dr_draft_cleared');
            setBadge('saved', '✅ Draft Cleared', '');
        }
    }

    global.DailyReportDraft = {
        saveDraft,
        loadDraft,
        restoreDraft,
        clearDraft,
        clearAllDrafts,
        scheduleSave,
        flushDraftSync,
        collectDraft,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(initDraftSystem, 150);
        });
    } else {
        setTimeout(initDraftSystem, 150);
    }
})(window);
