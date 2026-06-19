/**
 * Milk customer dropdown — Daily Report step 8 + other pages.
 * Registry: #milkCustomersRegistryJson (same data as /milk-customers).
 */
(function (global) {
    'use strict';

    const SELECTOR = '.milk-customer-select';
    const STEP = 8;
    const DROPDOWN_CLASS = 'ts-dropdown-milk-customer';

    let optionsCache = null;

    function isDailyReportPage() {
        return !!document.getElementById('dailyReportForm');
    }

    function loadRegistry() {
        const el = document.getElementById('milkCustomersRegistryJson');
        if (!el) {
            return [];
        }

        try {
            const rows = JSON.parse(el.textContent || '[]');
            return Array.isArray(rows) ? rows : [];
        } catch {
            return [];
        }
    }

    function getCachedOptions() {
        if (optionsCache) {
            return optionsCache;
        }

        optionsCache = loadRegistry()
            .map((customer) => ({
                value: String(customer.id),
                text: String(customer.label || '').trim(),
                search: String(customer.search || customer.label || '').toLowerCase(),
            }))
            .filter((opt) => opt.value && opt.text);

        return optionsCache;
    }

    function unwrapTomSelect(selectEl) {
        if (!selectEl) {
            return;
        }

        if (selectEl.tomselect) {
            const value = selectEl.tomselect.getValue();
            selectEl.tomselect.destroy();
            if (value) {
                selectEl.value = value;
            }
        }

        const wrapper = selectEl.closest('.ts-wrapper');
        if (wrapper?.parentNode) {
            wrapper.parentNode.insertBefore(selectEl, wrapper);
            wrapper.remove();
        }

        selectEl.removeAttribute('tabindex');
        selectEl.classList.remove('tomselected', 'ts-hidden-accessible');
    }

    function populateNativeSelect(selectEl, options) {
        if (!selectEl || !options.length) {
            return false;
        }

        const placeholder = selectEl.dataset.placeholder || '';
        const currentValue = selectEl.value;

        selectEl.innerHTML = '';
        const blank = document.createElement('option');
        blank.value = '';
        blank.textContent = placeholder;
        selectEl.appendChild(blank);

        options.forEach((opt) => {
            const node = document.createElement('option');
            node.value = opt.value;
            node.textContent = opt.text;
            selectEl.appendChild(node);
        });

        if (currentValue && options.some((opt) => opt.value === currentValue)) {
            selectEl.value = currentValue;
        }

        return true;
    }

    function createTomSelectInstance(el) {
        if (typeof TomSelect === 'undefined') {
            return null;
        }

        if (el.tomselect) {
            return el.tomselect;
        }

        const options = getCachedOptions();
        if (!options.length) {
            return null;
        }

        const currentValue = el.value;
        const placeholder = el.dataset.placeholder || '';

        const ts = new TomSelect(el, {
            options,
            valueField: 'value',
            labelField: 'text',
            searchField: ['text', 'search'],
            create: false,
            allowEmptyOption: true,
            maxOptions: 500,
            placeholder,
            dropdownParent: document.body,
            dropdownClass: DROPDOWN_CLASS,
            sortField: { field: 'text', direction: 'asc' },
            onDropdownOpen() {
                this.positionDropdown();
            },
        });

        if (ts.wrapper) {
            ts.wrapper.classList.add('milk-customer-ts');
        }

        if (currentValue) {
            ts.setValue(currentValue, true);
        }

        return ts;
    }

    function setupNativeDropdown(el) {
        if (!el || el.tagName !== 'SELECT' || !el.matches(SELECTOR)) {
            return false;
        }

        unwrapTomSelect(el);
        const ok = populateNativeSelect(el, getCachedOptions());
        if (ok) {
            el.classList.add('milk-customer-native');
        }
        return ok;
    }

    /** Daily Report step 8 — searchable Tom Select, fallback to native <select>. */
    function setupDailyReportDropdown(el) {
        if (!el || el.tagName !== 'SELECT' || !el.matches(SELECTOR)) {
            return false;
        }

        unwrapTomSelect(el);
        el.classList.remove('milk-customer-native');

        const options = getCachedOptions();
        if (!options.length) {
            return false;
        }

        if (typeof TomSelect !== 'undefined') {
            createTomSelectInstance(el);
            return true;
        }

        return setupNativeDropdown(el);
    }

    function initDistributionSection() {
        const section = document.getElementById('milkDistributionSection');
        if (!section || section.hidden || !section.classList.contains('is-current')) {
            return;
        }

        optionsCache = null;

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                section.querySelectorAll(SELECTOR).forEach((el) => {
                    const row = el.closest('tr');
                    if (row && row.hidden) {
                        return;
                    }
                    setupDailyReportDropdown(el);
                });
            });
        });
    }

    function initVisibleRowSelects() {
        document.querySelectorAll('#distBody .dr-dist-row:not([hidden]) ' + SELECTOR).forEach((el) => {
            if (!el.tomselect && !el.classList.contains('milk-customer-native')) {
                setupDailyReportDropdown(el);
            }
        });
    }

    function prepareClonedSelect(el) {
        unwrapTomSelect(el);
        const placeholder = el.dataset.placeholder || '';
        el.innerHTML = '';
        const blank = document.createElement('option');
        blank.value = '';
        blank.textContent = placeholder;
        el.appendChild(blank);
        el.value = '';
        el.classList.remove('milk-customer-native');
        setupDailyReportDropdown(el);
    }

    function onStepChange(step) {
        if (Number(step) !== STEP) {
            return;
        }
        initDistributionSection();
    }

    function watchDistributionSection() {
        const section = document.getElementById('milkDistributionSection');
        if (!section || !isDailyReportPage()) {
            return;
        }

        const observer = new MutationObserver(() => {
            if (!section.hidden && section.classList.contains('is-current')) {
                initDistributionSection();
            }
        });

        observer.observe(section, { attributes: true, attributeFilter: ['hidden', 'class'] });
    }

    function bind() {
        document.addEventListener('dr:step-change', (e) => {
            onStepChange(e.detail?.step);
        });

        watchDistributionSection();

        if (isDailyReportPage()) {
            return;
        }

        requestAnimationFrame(() => {
            document.querySelectorAll(SELECTOR).forEach(setupDailyReportDropdown);
        });
    }

    global.MilkCustomerSelect = {
        SELECTOR,
        STEP,
        setupDailyReportDropdown,
        setupNativeDropdown,
        prepareClonedSelect,
        onStepChange,
        initDistributionSection,
        initVisibleRowSelects,
        createTomSelectInstance,
        getCachedOptions,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind);
    } else {
        bind();
    }
})(window);
