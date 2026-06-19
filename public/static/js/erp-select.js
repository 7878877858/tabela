/**
 * ERP — shared Select2 / Tom Select defaults (dropdown portal + width).
 */
(function (global) {
    'use strict';

    const BODY_PARENT = 'body';

    function resolveDropdownParent(el) {
        if (!el) return BODY_PARENT;
        const modal = el.closest('.modal');
        return modal || BODY_PARENT;
    }

    function tomSelectOptions(extra) {
        return Object.assign(
            {
                dropdownParent: BODY_PARENT,
            },
            extra || {}
        );
    }

    function initTomSelect(el, extra) {
        if (!el || el.tomselect || typeof TomSelect === 'undefined') {
            return null;
        }

        const opts = tomSelectOptions(extra);
        opts.dropdownParent = extra?.dropdownParent ?? resolveDropdownParent(el);

        return new TomSelect(el, opts);
    }

    function initTomSelectAll(selector, extra, root) {
        const scope = root && root.querySelectorAll ? root : document;
        scope.querySelectorAll(selector).forEach((el) => initTomSelect(el, extra));
    }

    function initSelect2($el, extra) {
        if (typeof $ === 'undefined' || !$el || !$el.select2) {
            return null;
        }

        const node = $el[0];
        const modal = $el.closest('.modal');
        const dropdownParent = modal.length ? modal : $(document.body);

        return $el.select2(
            Object.assign(
                {
                    width: '100%',
                    dropdownParent,
                },
                extra || {}
            )
        );
    }

    function initSelect2All(selector, extra) {
        if (typeof $ === 'undefined' || !$.fn.select2) {
            return;
        }

        $(selector).each(function () {
            initSelect2($(this), extra);
        });
    }

    global.ErpSelect = {
        BODY_PARENT,
        resolveDropdownParent,
        tomSelectOptions,
        initTomSelect,
        initTomSelectAll,
        initSelect2,
        initSelect2All,
    };
})(window);
