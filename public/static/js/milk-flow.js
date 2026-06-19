(function () {
    function num(v) {
        const n = parseFloat(v);
        return Number.isFinite(n) ? n : 0;
    }

    function bindDistributionForm(form) {
        if (!form) return;

        const morning = form.querySelector('[name="morning_liter"]');
        const evening = form.querySelector('[name="evening_liter"]');
        const rate = form.querySelector('[name="rate_per_liter"]');
        const totalEl = form.querySelector('[data-total-liter]');
        const amountEl = form.querySelector('[data-total-amount]');

        function recalc() {
            const total = num(morning?.value) + num(evening?.value);
            const amount = total * num(rate?.value);
            if (totalEl) totalEl.textContent = total.toFixed(2);
            if (amountEl) amountEl.textContent = amount.toFixed(2);
        }

        [morning, evening, rate].forEach((el) => {
            el?.addEventListener('input', recalc);
        });
        recalc();
    }

    function bindDairyPreview(form, expectedBuffalo, expectedCow) {
        if (!form) return;

        const bLiter = form.querySelector('[name="buffalo_liter"]');
        const cLiter = form.querySelector('[name="cow_liter"]');
        const warn = form.querySelector('[data-recon-warning]');

        function check() {
            if (!warn) return;
            const bDiff = expectedBuffalo - num(bLiter?.value);
            const cDiff = expectedCow - num(cLiter?.value);
            const has = Math.abs(bDiff) >= 0.01 || Math.abs(cDiff) >= 0.01;
            warn.hidden = !has;
            if (has) {
                warn.querySelector('[data-buffalo-expected]').textContent = expectedBuffalo.toFixed(2);
                warn.querySelector('[data-buffalo-entered]').textContent = num(bLiter?.value).toFixed(2);
                warn.querySelector('[data-buffalo-diff]').textContent = bDiff.toFixed(2);
                warn.querySelector('[data-cow-expected]').textContent = expectedCow.toFixed(2);
                warn.querySelector('[data-cow-entered]').textContent = num(cLiter?.value).toFixed(2);
                warn.querySelector('[data-cow-diff]').textContent = cDiff.toFixed(2);
            }
        }

        [bLiter, cLiter].forEach((el) => el?.addEventListener('input', check));
        check();
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindDistributionForm(document.getElementById('distributionForm'));
        const dairyForm = document.getElementById('dairyForm');
        if (dairyForm) {
            bindDairyPreview(
                dairyForm,
                num(dairyForm.dataset.expectedBuffalo),
                num(dairyForm.dataset.expectedCow)
            );
        }
    });
})();
