{{--
    Shared modern alert / confirmation layer (SweetAlert2).

    Include once per page, just before </body>:
        @include('partials.alerts')

    ─── Declarative use (preferred) ──────────────────────────────────────────
    Put data-confirm on any form, link or button. The click/submit is paused,
    a themed dialog is shown, and the original action only runs if confirmed:

        <form method="POST" action="..." data-confirm="Delete this subject?" data-confirm-danger>
        <a href="..." data-confirm="Leave this page?">
        <button data-confirm="Submit your exam?" data-confirm-title="Submit exam">

    Supported attributes:
        data-confirm          the question text                       (required)
        data-confirm-title    heading            (default "Are you sure?")
        data-confirm-ok       confirm button label      (default "Yes, continue")
        data-confirm-cancel   cancel button label            (default "Cancel")
        data-confirm-icon     warning|question|error|info    (default warning)
        data-confirm-danger   present = red confirm button
        data-loading          text for the blocking spinner shown after confirming
                              a form submit (default "Processing...")

    ─── Programmatic use ─────────────────────────────────────────────────────
        await CPACE.confirm({ title, text, icon, danger, confirmText })  -> bool
        CPACE.success(title, text)   CPACE.error(title, text)
        CPACE.warning(title, text)   CPACE.info(title, text)
        CPACE.toast('Saved', 'success')            // corner toast
        CPACE.loading('Uploading...')  CPACE.closeAlert()
        CPACE.prompt({ title, inputLabel, ... })   -> string|null

    Server-side flash messages are picked up automatically:
        return back()->with('status', 'Saved');       -> success toast
        return back()->with('success', 'Saved');      -> success toast
        return back()->with('error', 'Failed');       -> error modal
        return back()->with('warning', '...');        -> warning modal
        return back()->with('info', '...');           -> info toast
        return back()->with('alert', ['type' => 'success', 'title' => '...', 'text' => '...']);
    Validation errors are shown as a single error modal listing every message.
--}}

{{-- Paired with the standalone stylesheet (not the ".all" bundle) so the CPACE
     overrides below always win the cascade instead of racing an injected sheet. --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.14.5/sweetalert2.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.14.5/sweetalert2.min.js"></script>

<style>
    .swal2-container { z-index: 100000; }

    .swal2-popup.cpace-alert {
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        border-radius: 18px;
        padding: 26px 24px 22px;
        box-shadow: 0 24px 60px rgba(23, 8, 8, .22);
        background: #fff;
        color: #1f2937;
    }
    .swal2-popup.cpace-alert .swal2-title {
        font-size: 20px;
        font-weight: 700;
        color: #2b2b33;
        padding: 0;
        margin-bottom: 6px;
    }
    .swal2-popup.cpace-alert .swal2-html-container {
        font-size: 14.5px;
        line-height: 1.55;
        color: #5b6270;
        margin: 6px 4px 0;
    }
    .swal2-popup.cpace-alert .swal2-actions {
        gap: 10px;
        margin-top: 22px;
        flex-wrap: wrap-reverse;
    }
    .swal2-popup.cpace-alert .swal2-styled {
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        padding: 10px 22px;
        box-shadow: none;
        transition: transform .15s ease, filter .15s ease;
    }
    .swal2-popup.cpace-alert .swal2-styled:hover { filter: brightness(.94); transform: translateY(-1px); }
    .swal2-popup.cpace-alert .swal2-styled:focus { box-shadow: 0 0 0 3px rgba(123, 29, 29, .22); }
    .swal2-popup.cpace-alert .swal2-confirm { background: #7B1D1D; color: #fff; }
    .swal2-popup.cpace-alert .swal2-confirm.cpace-danger { background: #c0392b; }
    .swal2-popup.cpace-alert .swal2-confirm.cpace-danger:focus { box-shadow: 0 0 0 3px rgba(192, 57, 43, .25); }
    .swal2-popup.cpace-alert .swal2-cancel {
        background: #f1f2f4;
        color: #4b5563;
        border: 1px solid #e2e4e8;
    }
    .swal2-popup.cpace-alert .swal2-icon { margin: 4px auto 14px; width: 66px; height: 66px; border-width: 3px; }
    .swal2-popup.cpace-alert .swal2-icon .swal2-icon-content { font-size: 36px; }
    .swal2-popup.cpace-alert .swal2-input,
    .swal2-popup.cpace-alert .swal2-textarea,
    .swal2-popup.cpace-alert .swal2-select {
        font-family: inherit;
        font-size: 14px;
        border-radius: 10px;
        border: 1px solid #e2e4e8;
        box-shadow: none;
    }
    .swal2-popup.cpace-alert .swal2-input:focus,
    .swal2-popup.cpace-alert .swal2-textarea:focus {
        border-color: #7B1D1D;
        box-shadow: 0 0 0 3px rgba(123, 29, 29, .14);
    }
    .swal2-popup.cpace-alert .swal2-validation-message { border-radius: 10px; font-size: 13px; }
    .swal2-popup.cpace-alert .swal2-loader { border-color: #7B1D1D transparent #7B1D1D transparent; }
    /* Secondary note inside a dialog body ("this action is protected...") */
    .cpace-note {
        margin-top: 14px;
        padding: 10px 12px;
        border-radius: 10px;
        background: #fdf6ec;
        border: 1px solid #f4e3c8;
        color: #8a5a1a;
        font-size: 13px;
        line-height: 1.5;
        text-align: left;
    }
    html.dark .cpace-note { background: #33291c; border-color: #4a3a25; color: #e0bd85; }
    .cpace-alert-list {
        margin: 10px 0 0;
        padding-left: 18px;
        text-align: left;
        font-size: 14px;
        line-height: 1.6;
        color: #5b6270;
    }

    /* Corner toasts */
    .swal2-popup.cpace-toast {
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        border-radius: 12px;
        padding: 14px 16px;
        box-shadow: 0 12px 32px rgba(23, 8, 8, .18);
        border-left: 4px solid #7B1D1D;
        background: #fff;
    }
    .swal2-popup.cpace-toast .swal2-title { font-size: 14px; font-weight: 600; color: #2b2b33; margin: 0; }
    .swal2-popup.cpace-toast .swal2-html-container { font-size: 13px; color: #6b7280; margin: 2px 0 0; }
    .swal2-popup.cpace-toast.cpace-toast-success { border-left-color: #16a34a; }
    .swal2-popup.cpace-toast.cpace-toast-error   { border-left-color: #dc2626; }
    .swal2-popup.cpace-toast.cpace-toast-warning { border-left-color: #d97706; }
    .swal2-popup.cpace-toast.cpace-toast-info    { border-left-color: #2563eb; }
    .swal2-popup.cpace-toast .swal2-timer-progress-bar { background: rgba(123, 29, 29, .28); }

    /* Dark mode (student pages toggle html.dark) */
    html.dark .swal2-popup.cpace-alert,
    html.dark .swal2-popup.cpace-toast { background: #24242e; color: #e7e7ec; }
    html.dark .swal2-popup.cpace-alert .swal2-title,
    html.dark .swal2-popup.cpace-toast .swal2-title { color: #f2f2f5; }
    html.dark .swal2-popup.cpace-alert .swal2-html-container,
    html.dark .swal2-popup.cpace-toast .swal2-html-container,
    html.dark .cpace-alert-list { color: #b6bac4; }
    html.dark .swal2-popup.cpace-alert .swal2-cancel { background: #33333f; color: #d6d8de; border-color: #43434f; }
    html.dark .swal2-popup.cpace-alert .swal2-input,
    html.dark .swal2-popup.cpace-alert .swal2-textarea,
    html.dark .swal2-popup.cpace-alert .swal2-select { background: #1e1e26; border-color: #43434f; color: #e7e7ec; }
</style>

<script>
(function () {
    window.CPACE = window.CPACE || {};

    if (typeof Swal === 'undefined') {
        // CDN unavailable - degrade to the native dialogs so transactions still work.
        window.CPACE.confirm = function (o) {
            o = o || {};
            return Promise.resolve(window.confirm(o.text || o.title || 'Are you sure?'));
        };
        ['success', 'error', 'warning', 'info'].forEach(function (t) {
            window.CPACE[t] = function (title, text) { window.alert([title, text].filter(Boolean).join('\n\n')); };
        });
        window.CPACE.toast = function (msg) { window.alert(msg); };
        window.CPACE.loading = window.CPACE.closeAlert = function () {};
        window.CPACE.prompt = function (o) { return Promise.resolve(window.prompt((o || {}).title || '')); };
        return;
    }

    var BASE = {
        buttonsStyling: true,
        reverseButtons: true,
        heightAuto: false,          // keeps fixed sidebars from shifting
        customClass: { popup: 'cpace-alert' },
    };

    function merge() {
        var out = {}, i, k;
        for (i = 0; i < arguments.length; i++) {
            var src = arguments[i] || {};
            for (k in src) if (Object.prototype.hasOwnProperty.call(src, k)) out[k] = src[k];
        }
        return out;
    }

    /* ── Confirmation dialog ─────────────────────────────────────────────── */
    window.CPACE.confirm = function (options) {
        var o = typeof options === 'string' ? { text: options } : (options || {});
        return Swal.fire(merge(BASE, {
            title: o.title || 'Are you sure?',
            text: o.html ? undefined : (o.text || ''),
            html: o.html,
            icon: o.icon || 'warning',
            iconColor: o.danger ? '#c0392b' : '#d97706',
            showCancelButton: true,
            focusCancel: o.danger !== false,
            confirmButtonText: o.confirmText || 'Yes, continue',
            cancelButtonText: o.cancelText || 'Cancel',
            customClass: {
                popup: 'cpace-alert',
                confirmButton: o.danger ? 'cpace-danger' : '',
            },
        })).then(function (r) { return !!r.isConfirmed; });
    };

    /* ── Result dialogs ──────────────────────────────────────────────────── */
    function result(icon, iconColor) {
        return function (title, text, options) {
            return Swal.fire(merge(BASE, {
                title: title,
                html: (options && options.html) || undefined,
                text: (options && options.html) ? undefined : (text || ''),
                icon: icon,
                iconColor: iconColor,
                confirmButtonText: (options && options.confirmText) || 'OK',
            }, options || {}));
        };
    }
    window.CPACE.success = result('success', '#16a34a');
    window.CPACE.error   = result('error', '#dc2626');
    window.CPACE.warning = result('warning', '#d97706');
    window.CPACE.info    = result('info', '#2563eb');

    /* ── Corner toast ────────────────────────────────────────────────────── */
    window.CPACE.toast = function (message, type, text) {
        type = type || 'success';
        return Swal.fire({
            toast: true,
            position: 'top-end',
            title: message,
            text: text || undefined,
            icon: type,
            iconColor: { success: '#16a34a', error: '#dc2626', warning: '#d97706', info: '#2563eb' }[type],
            showConfirmButton: false,
            timer: type === 'error' ? 5000 : 3200,
            timerProgressBar: true,
            heightAuto: false,
            customClass: { popup: 'cpace-toast cpace-toast-' + type },
            didOpen: function (el) {
                el.addEventListener('mouseenter', Swal.stopTimer);
                el.addEventListener('mouseleave', Swal.resumeTimer);
            },
        });
    };

    /* ── Blocking spinner ────────────────────────────────────────────────── */
    window.CPACE.loading = function (title, text) {
        return Swal.fire(merge(BASE, {
            title: title || 'Processing...',
            text: text || 'Please wait a moment.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: function () { Swal.showLoading(); },
        }));
    };
    window.CPACE.closeAlert = function () { Swal.close(); };

    /* ── Prompt ──────────────────────────────────────────────────────────── */
    window.CPACE.prompt = function (options) {
        var o = options || {};
        return Swal.fire(merge(BASE, {
            title: o.title || 'Enter a value',
            text: o.text || '',
            input: o.input || 'text',
            inputLabel: o.inputLabel,
            inputValue: o.value || '',
            inputPlaceholder: o.placeholder || '',
            inputAttributes: o.inputAttributes,
            showCancelButton: true,
            confirmButtonText: o.confirmText || 'Save',
            cancelButtonText: o.cancelText || 'Cancel',
            inputValidator: o.required === false ? undefined : function (v) {
                if (!v || !String(v).trim()) return o.requiredMessage || 'This field is required.';
            },
        })).then(function (r) { return r.isConfirmed ? r.value : null; });
    };

    /* ── Declarative data-confirm interception ───────────────────────────── */
    var CONFIRMED = '__cpaceConfirmed';

    function optionsFrom(el) {
        return {
            text: el.getAttribute('data-confirm') || '',
            title: el.getAttribute('data-confirm-title') || 'Are you sure?',
            confirmText: el.getAttribute('data-confirm-ok') || 'Yes, continue',
            cancelText: el.getAttribute('data-confirm-cancel') || 'Cancel',
            icon: el.getAttribute('data-confirm-icon') || 'warning',
            danger: el.hasAttribute('data-confirm-danger'),
        };
    }

    function guard(el, replay) {
        el[CONFIRMED] = false;
        window.CPACE.confirm(optionsFrom(el)).then(function (ok) {
            if (!ok) return;
            el[CONFIRMED] = true;
            replay();
            el[CONFIRMED] = false;
        });
    }

    // Capture phase so we run before any page-level handler and can suppress it.
    document.addEventListener('click', function (e) {
        var el = e.target.closest ? e.target.closest('[data-confirm]') : null;
        if (!el || el[CONFIRMED] || el.tagName === 'FORM') return;
        if (el.disabled) return;

        e.preventDefault();
        e.stopImmediatePropagation();

        guard(el, function () {
            if (el.tagName === 'A' && el.getAttribute('href')) {
                if (el.target === '_blank') window.open(el.href, '_blank');
                else window.location.href = el.href;
                return;
            }
            el.click();   // replays inline onclick, listeners and native submit
        });
    }, true);

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || !form.hasAttribute || !form.hasAttribute('data-confirm') || form[CONFIRMED]) return;

        e.preventDefault();
        e.stopImmediatePropagation();

        guard(form, function () {
            if (typeof form.requestSubmit === 'function') form.requestSubmit();
            else form.submit();
        });
    }, true);

    // Blocking spinner for slow submits (imports, exports, bulk mail).
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || !form.hasAttribute || !form.hasAttribute('data-loading')) return;
        if (form.hasAttribute('data-confirm') && !form[CONFIRMED]) return;
        window.CPACE.loading(form.getAttribute('data-loading') || 'Processing...');
    });

    /* ── Server flash messages ───────────────────────────────────────────────
       Only one SweetAlert modal can be on screen at a time, so a request that
       flashes several messages queues them and plays them back in order. Toasts
       are non-blocking and go out first. */
    function flash() {
        var toasts = [];
        var modals = [];

        @if (session('status'))
            toasts.push([@json(session('status')), 'success']);
        @elseif (session('success'))
            toasts.push([@json(session('success')), 'success']);
        @endif

        @if (session('info'))
            toasts.push([@json(session('info')), 'info']);
        @endif

        @if (session('warning'))
            modals.push(['warning', 'Heads up', @json(session('warning')), null]);
        @endif

        @if (session('error'))
            modals.push(['error', 'Something went wrong', @json(session('error')), null]);
        @endif

        @if (session('alert'))
            @php $cpaceAlert = session('alert'); @endphp
            modals.push([
                @json(data_get($cpaceAlert, 'type', 'info')),
                @json(data_get($cpaceAlert, 'title', 'Notice')),
                @json(data_get($cpaceAlert, 'text', '')),
                null
            ]);
        @endif

        @if (isset($errors) && $errors->any())
            modals.push([
                'error',
                @json($errors->count() > 1 ? 'Please fix the following' : 'Please check your entry'),
                null,
                @json('<ul class="cpace-alert-list"><li>' . implode('</li><li>', array_map('e', $errors->all())) . '</li></ul>')
            ]);
        @endif

        @if (session('import_errors'))
            modals.push([
                'warning',
                'Some rows were skipped',
                null,
                @json('<ul class="cpace-alert-list"><li>' . implode('</li><li>', array_map('e', (array) session('import_errors'))) . '</li></ul>')
            ]);
        @endif

        toasts.forEach(function (t) { window.CPACE.toast(t[0], t[1]); });

        (function next(i) {
            if (i >= modals.length) return;
            var m = modals[i];
            var fn = window.CPACE[m[0]] || window.CPACE.info;
            Promise.resolve(fn(m[1], m[2], m[3] ? { html: m[3] } : undefined))
                .then(function () { next(i + 1); });
        })(0);
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', flash);
    else flash();
})();
</script>
