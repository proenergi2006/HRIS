/*
 * Semua <input type="date"> tampil dd/mm/yyyy (bukan mengikuti bahasa browser),
 * nilai yang dikirim ke server tetap Y-m-d. Pakai flatpickr (altInput): input
 * asli jadi hidden dan tetap menyimpan Y-m-d, input teks di sebelahnya yang
 * dilihat user. Input yang ditambah belakangan lewat JS ikut di-handle.
 * Opt-out per input: data-native-date.
 */
(function () {
    if (typeof flatpickr === 'undefined') return;
    if (flatpickr.l10ns && flatpickr.l10ns.id) flatpickr.localize(flatpickr.l10ns.id);

    var SEL = 'input[type="date"]';
    var nativeValue = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');

    function enhance(el) {
        if (el._flatpickr || el.hasAttribute('data-native-date')) return;

        var syncing = false;
        var fp = flatpickr(el, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            allowInput: true,
            disableMobile: true,
            minDate: el.getAttribute('min') || null,
            maxDate: el.getAttribute('max') || null,
        });

        if (!fp.altInput.placeholder) fp.altInput.placeholder = 'dd/mm/yyyy';
        fp.altInput.readOnly = el.readOnly;

        // Kode lain yang mengisi el.value = 'YYYY-MM-DD' harus ikut mengubah tampilan.
        Object.defineProperty(el, 'value', {
            configurable: true,
            get: function () { return nativeValue.get.call(el); },
            set: function (v) {
                nativeValue.set.call(el, v);
                if (syncing) return;
                syncing = true;
                try { fp.setDate(v || null, false); } finally { syncing = false; }
            },
        });
    }

    // Baris hasil cloneNode dari input yang sudah di-enhance: alt input ikut
    // tersalin tapi flatpickr-nya tidak. Bersihkan lalu enhance ulang.
    function repairClone(el) {
        var alt = el.nextElementSibling;
        if (alt && alt.tagName === 'INPUT' && alt.type === 'text' && !alt.hasAttribute('name')) alt.remove();
        el.classList.remove('flatpickr-input');
        el.setAttribute('type', 'date');
        enhance(el);
    }

    function scan(root) {
        if (root.nodeType !== 1) return;
        var list = root.matches && root.matches(SEL) ? [root] : [];
        list.concat([].slice.call(root.querySelectorAll(SEL))).forEach(enhance);
        [].slice.call(root.querySelectorAll('input[type="hidden"].flatpickr-input')).forEach(function (el) {
            if (!el._flatpickr) repairClone(el);
        });
    }

    scan(document.body);

    new MutationObserver(function (muts) {
        muts.forEach(function (m) {
            if (m.type === 'childList') {
                [].slice.call(m.addedNodes).forEach(scan);
            } else if (m.target._flatpickr) {
                // disabled/readonly/min/max diubah lewat JS → samakan dengan input tampilan.
                var el = m.target, fp = el._flatpickr;
                fp.altInput.disabled = el.disabled;
                fp.altInput.readOnly = el.readOnly;
                fp.set('minDate', el.getAttribute('min') || null);
                fp.set('maxDate', el.getAttribute('max') || null);
            }
        });
    }).observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['disabled', 'readonly', 'min', 'max'],
    });
})();
