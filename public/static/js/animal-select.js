/**
 * Searchable animal dropdown — Tom Select, client-side registry (1000+ animals).
 */
(function (global) {
    'use strict';

    const PLACEHOLDER = 'ટેગ નંબર અથવા પશુ નામ શોધો...';
    const instances = new WeakMap();
    let registry = [];

    function loadRegistry() {
        const el = document.getElementById('animalsRegistryJson');
        if (!el) return registry;
        try {
            registry = JSON.parse(el.textContent || '[]');
        } catch (e) {
            registry = [];
        }
        return registry;
    }

    function getRegistry() {
        return registry.length ? registry : loadRegistry();
    }

    function buildOptions(selectEl) {
        if (selectEl.dataset.optionsFrom === 'select') {
            return Array.from(selectEl.querySelectorAll('option[value]'))
                .filter((opt) => opt.value)
                .map((opt) => {
                    const tag = opt.dataset.tag || '';
                    const name = opt.dataset.name || '';
                    return {
                        value: String(opt.value),
                        text: opt.textContent.trim(),
                        search: `${tag} ${name} ${opt.textContent}`.toLowerCase(),
                        tag,
                        name,
                        type: opt.dataset.animalType || '',
                        type_label: '',
                    };
                });
        }

        const existing = new Map();
        selectEl.querySelectorAll('option[value]').forEach((opt) => {
            if (opt.value) existing.set(String(opt.value), opt.textContent.trim());
        });

        return getRegistry().map((a) => ({
            value: String(a.id),
            text: existing.get(String(a.id)) || a.label,
            search: a.search || '',
            tag: a.tag,
            name: a.name,
            type: a.type,
            type_label: a.type_label,
        }));
    }

    function scoreOption(option, query) {
        if (!query) return 1;
        const q = query.toLowerCase().trim();
        const tag = (option.tag || '').toLowerCase();
        const name = (option.name || '').toLowerCase();
        const search = (option.search || '').toLowerCase();
        const text = (option.text || '').toLowerCase();

        if (tag === q || tag.startsWith(q)) return 100;
        if (tag.includes(q)) return 80;
        if (name.startsWith(q)) return 70;
        if (name.includes(q)) return 60;
        if (search.includes(q) || text.includes(q)) return 40;
        return 0;
    }

    function isInHiddenWizardStep(selectEl) {
        const section = selectEl.closest('.dr-step-section');
        if (!section) {
            return false;
        }
        const page = selectEl.closest('.daily-report-page');
        if (!page?.classList.contains('dr-wizard-mode')) {
            return false;
        }
        return section.hidden || !section.classList.contains('is-current');
    }

    function formatOptionLabel(data, labelMode) {
        if (labelMode === 'tag-name') {
            const tag = data.tag || '';
            const name = data.name || '';
            if (tag && name) {
                return `${tag} - ${name}`;
            }
            return data.text || tag || name;
        }
        if (labelMode === 'tag') {
            return data.tag || data.text;
        }
        return data.text;
    }

    function cleanupSelectElement(selectEl) {
        const ts = instances.get(selectEl) || selectEl.tomselect;
        if (ts) {
            try {
                ts.destroy();
            } catch (e) {
                /* ignore */
            }
            instances.delete(selectEl);
        }

        const wrapper = selectEl.closest('.ts-wrapper');
        if (wrapper?.parentNode) {
            wrapper.parentNode.insertBefore(selectEl, wrapper);
            wrapper.remove();
        }

        selectEl.classList.remove('tomselected', 'ts-hidden-accessible');
        selectEl.removeAttribute('tabindex');
        selectEl.style.display = '';
    }

    function positionAnimalDropdown(ts) {
        if (!ts?.positionDropdown) {
            return;
        }
        ts.positionDropdown();
        const dd = ts.dropdown;
        if (!dd) {
            return;
        }
        dd.style.zIndex = '100000';
        dd.style.display = 'block';
        dd.classList.add('ts-dropdown-animal');
    }

    function enhance(selectEl, userOptions = {}) {
        if (!selectEl || selectEl.tagName !== 'SELECT') return null;
        if (typeof TomSelect === 'undefined') return null;
        if (isInHiddenWizardStep(selectEl)) {
            return null;
        }

        cleanupSelectElement(selectEl);

        const placeholder = selectEl.dataset.placeholder || userOptions.placeholder || PLACEHOLDER;
        const options = buildOptions(selectEl);
        const currentValue = selectEl.value;
        const labelMode = selectEl.dataset.labelMode || 'full';
        const maxOptions = selectEl.closest('#animalSaleBody') ? 100 : 80;

        const ts = new TomSelect(selectEl, {
            options,
            maxOptions,
            maxItems: 1,
            valueField: 'value',
            labelField: 'text',
            searchField: ['search', 'text', 'tag', 'name', 'type', 'type_label'],
            placeholder,
            allowEmptyOption: true,
            create: false,
            openOnFocus: true,
            closeAfterSelect: true,
            dropdownParent: document.body,
            dropdownClass: 'ts-dropdown-animal',
            sortField: { field: 'text', direction: 'asc' },
            score: (search) => (item) => scoreOption(item, search),
            render: {
                option: (data, escape) => {
                    const label = formatOptionLabel(data, labelMode);
                    if (labelMode === 'tag' && data.name) {
                        return `<div class="animal-ts-option"><strong>${escape(data.tag || data.text)}</strong> — ${escape(data.name)}</div>`;
                    }
                    return `<div class="animal-ts-option">${escape(label)}</div>`;
                },
                item: (data, escape) => `<div>${escape(formatOptionLabel(data, labelMode))}</div>`,
            },
            onInitialize() {
                this.wrapper.classList.add('animal-ts');
                this.wrapper.classList.remove('form-control', 'animal-select');
            },
            onFocus() {
                this.open();
            },
            onDropdownOpen() {
                requestAnimationFrame(() => positionAnimalDropdown(this));
            },
            onChange(value) {
                selectEl.dispatchEvent(new Event('change', { bubbles: true }));
                if (typeof userOptions.onChange === 'function') {
                    userOptions.onChange(value, selectEl);
                }
            },
            ...userOptions.tomSelect,
            dropdownParent: userOptions.tomSelect?.dropdownParent ?? userOptions.dropdownParent ?? document.body,
            dropdownClass: userOptions.tomSelect?.dropdownClass ?? userOptions.dropdownClass ?? 'ts-dropdown-animal',
        });

        if (currentValue) {
            ts.setValue(currentValue, true);
        }

        instances.set(selectEl, ts);
        return ts;
    }

    function destroy(selectEl) {
        cleanupSelectElement(selectEl);
    }

    function enhanceAll(root = document, userOptions = {}) {
        const scope = root instanceof Element ? root : document;
        scope.querySelectorAll('select.animal-select').forEach((el) => {
            if (!el.closest('template') && !isInHiddenWizardStep(el)) {
                enhance(el, userOptions);
            }
        });
    }

    function initWizardStep(step) {
        const n = Number(step);
        if (!n) return;

        document.querySelectorAll(`.dr-step-section[data-dr-step="${n}"] select.animal-select`).forEach((el) => {
            if (el.closest('template')) return;
            enhance(el);
        });
    }

    function onStepChange(step) {
        initWizardStep(step);
    }

    function watchWizardSteps() {
        if (!document.getElementById('dailyReportForm')) return;

        document.addEventListener('dr:step-change', (e) => {
            onStepChange(e.detail?.step);
        });
    }

    function setFilterIds(selectEl, ids) {
        const ts = instances.get(selectEl);
        if (!ts) return;
        const idSet = new Set((ids || []).map(String));
        const all = buildOptions(selectEl);
        const filtered = idSet.size ? all.filter((o) => idSet.has(o.value)) : all;
        ts.clearOptions();
        ts.addOptions(filtered);
        if (!idSet.has(String(ts.getValue()))) {
            ts.clear(true);
        }
    }

    function preselectAnimal(selectEl, animalId) {
        const ts = instances.get(selectEl);
        if (ts && animalId) {
            ts.setValue(String(animalId), true);
        } else if (selectEl) {
            selectEl.value = animalId || '';
        }
    }

    function cloneRowSelect(newSelect) {
        destroy(newSelect);
        newSelect.classList.add('animal-select');
        return enhance(newSelect);
    }

    global.AnimalSelect = {
        PLACEHOLDER,
        getRegistry,
        loadRegistry,
        enhance,
        destroy,
        enhanceAll,
        initWizardStep,
        onStepChange,
        setFilterIds,
        preselectAnimal,
        cloneRowSelect,
    };

    document.addEventListener('DOMContentLoaded', () => {
        loadRegistry();
        watchWizardSteps();
        enhanceAll();
    });
})(window);
