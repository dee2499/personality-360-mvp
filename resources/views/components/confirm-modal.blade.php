<!-- Universal Action Confirmation & Alert Popup Modal -->
<div id="app-confirm-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="app-confirm-title" role="dialog" aria-modal="true">
    <!-- Backdrop Overlay with subtle blur -->
    <div id="app-confirm-backdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200 opacity-0"></div>

    <!-- Modal Container -->
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div id="app-confirm-card" class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all duration-200 opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95 sm:my-8 sm:w-full sm:max-w-md p-6 sm:p-7 border border-slate-100">
            
            <!-- Icon Wrapper -->
            <div id="app-confirm-icon-wrapper" class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl mb-4 bg-rose-100 text-rose-600 ring-8 ring-rose-50">
                <!-- Danger Icon -->
                <svg id="app-confirm-icon-danger" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                <!-- Warning Icon -->
                <svg id="app-confirm-icon-warning" class="w-7 h-7 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <!-- Primary / Info Icon -->
                <svg id="app-confirm-icon-primary" class="w-7 h-7 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <!-- Success Icon -->
                <svg id="app-confirm-icon-success" class="w-7 h-7 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <!-- Content -->
            <div class="text-center">
                <h3 id="app-confirm-title" class="text-lg font-black text-slate-900 tracking-tight">Confirm Action</h3>
                <p id="app-confirm-message" class="text-xs text-slate-500 mt-2 leading-relaxed whitespace-pre-line"></p>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex flex-col-reverse sm:flex-row gap-2.5 sm:gap-3">
                <button type="button" id="app-confirm-cancel" class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition cursor-pointer">
                    Cancel
                </button>
                <button type="button" id="app-confirm-btn" class="w-full py-2.5 px-4 rounded-xl text-xs font-black text-white shadow-md transition cursor-pointer bg-rose-600 hover:bg-rose-700 shadow-rose-200">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    let currentResolver = null;

    function getElements() {
        return {
            modal: document.getElementById('app-confirm-modal'),
            backdrop: document.getElementById('app-confirm-backdrop'),
            card: document.getElementById('app-confirm-card'),
            titleEl: document.getElementById('app-confirm-title'),
            msgEl: document.getElementById('app-confirm-message'),
            btnConfirm: document.getElementById('app-confirm-btn'),
            btnCancel: document.getElementById('app-confirm-cancel'),
            iconWrapper: document.getElementById('app-confirm-icon-wrapper'),
            iconDanger: document.getElementById('app-confirm-icon-danger'),
            iconWarning: document.getElementById('app-confirm-icon-warning'),
            iconPrimary: document.getElementById('app-confirm-icon-primary'),
            iconSuccess: document.getElementById('app-confirm-icon-success')
        };
    }

    function closeModal(result) {
        const els = getElements();
        if (!els.modal) return;

        // Animate out
        els.backdrop.classList.remove('opacity-100');
        els.backdrop.classList.add('opacity-0');
        els.card.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
        els.card.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');

        setTimeout(function() {
            els.modal.classList.add('hidden');
            if (currentResolver) {
                currentResolver(result);
                currentResolver = null;
            }
        }, 150);
    }

    function openModal(options) {
        options = options || {};
        return new Promise(function(resolve) {
            currentResolver = resolve;
            const els = getElements();
            if (!els.modal) {
                resolve(true);
                return;
            }

            const isAlertOnly = !!options.isAlertOnly;
            const title = options.title || (isAlertOnly ? 'Notice' : 'Confirm Action');
            const message = options.message || '';
            const confirmText = options.confirmText || (isAlertOnly ? 'Got It' : 'Confirm');
            const cancelText = options.cancelText || 'Cancel';
            const type = options.type || 'danger';

            els.titleEl.textContent = title;
            els.msgEl.textContent = message;
            els.btnConfirm.textContent = confirmText;
            els.btnCancel.textContent = cancelText;

            if (isAlertOnly) {
                els.btnCancel.classList.add('hidden');
            } else {
                els.btnCancel.classList.remove('hidden');
            }

            // Hide all icons
            els.iconDanger.classList.add('hidden');
            els.iconWarning.classList.add('hidden');
            els.iconPrimary.classList.add('hidden');
            els.iconSuccess.classList.add('hidden');

            // Reset icon wrapper & confirm button
            els.iconWrapper.className = 'mx-auto flex h-14 w-14 items-center justify-center rounded-2xl mb-4';
            els.btnConfirm.className = 'w-full py-2.5 px-4 rounded-xl text-xs font-black text-white shadow-md transition cursor-pointer';

            if (type === 'danger') {
                els.iconDanger.classList.remove('hidden');
                els.iconWrapper.classList.add('bg-rose-100', 'text-rose-600', 'ring-8', 'ring-rose-50');
                btnConfirm = els.btnConfirm;
                btnConfirm.classList.add('bg-rose-600', 'hover:bg-rose-700', 'shadow-rose-200');
            } else if (type === 'warning') {
                els.iconWarning.classList.remove('hidden');
                els.iconWrapper.classList.add('bg-amber-100', 'text-amber-600', 'ring-8', 'ring-amber-50');
                els.btnConfirm.classList.add('bg-amber-600', 'hover:bg-amber-700', 'shadow-amber-200');
            } else if (type === 'success') {
                els.iconSuccess.classList.remove('hidden');
                els.iconWrapper.classList.add('bg-emerald-100', 'text-emerald-600', 'ring-8', 'ring-emerald-50');
                els.btnConfirm.classList.add('bg-emerald-600', 'hover:bg-emerald-700', 'shadow-emerald-200');
            } else {
                els.iconPrimary.classList.remove('hidden');
                els.iconWrapper.classList.add('bg-indigo-100', 'text-indigo-600', 'ring-8', 'ring-indigo-50');
                els.btnConfirm.classList.add('bg-indigo-600', 'hover:bg-indigo-700', 'shadow-indigo-200');
            }

            // Unhide container
            els.modal.classList.remove('hidden');

            // Trigger animation in next tick
            requestAnimationFrame(function() {
                els.backdrop.classList.remove('opacity-0');
                els.backdrop.classList.add('opacity-100');
                els.card.classList.remove('opacity-0', 'translate-y-4', 'sm:scale-95');
                els.card.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
                els.btnConfirm.focus();
            });
        });
    }

    // Attach to window immediately
    window.confirmAction = function(opts) {
        return openModal(Object.assign({}, opts, { isAlertOnly: false }));
    };

    window.alertAction = function(opts) {
        if (typeof opts === 'string') opts = { message: opts };
        return openModal(Object.assign({}, opts, { isAlertOnly: true }));
    };

    window.showConfirmModal = window.confirmAction;
    window.showAlertModal = window.alertAction;

    // Attach modal buttons listeners once DOM is ready
    function initListeners() {
        const els = getElements();
        if (els.btnConfirm) {
            els.btnConfirm.onclick = function() { closeModal(true); };
        }
        if (els.btnCancel) {
            els.btnCancel.onclick = function() { closeModal(false); };
        }
        if (els.backdrop) {
            els.backdrop.onclick = function() { closeModal(false); };
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initListeners);
    } else {
        initListeners();
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const els = getElements();
            if (els.modal && !els.modal.classList.contains('hidden')) {
                closeModal(false);
            }
        }
    });

    // Capture submit events on forms requesting confirmation
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;

        // If form has already been confirmed, allow standard submission
        if (form.getAttribute('data-confirmed') === 'true') {
            form.removeAttribute('data-confirmed');
            return;
        }

        const submitter = e.submitter;
        const targetWithConfirm = (submitter && (submitter.getAttribute('data-confirm') === 'true' || submitter.hasAttribute('data-confirm-message')))
            ? submitter
            : form;

        const hasConfirmAttr = targetWithConfirm.getAttribute('data-confirm') === 'true' || targetWithConfirm.hasAttribute('data-confirm-message') || form.getAttribute('data-confirm') === 'true';
        const onsubmitAttr = form.getAttribute('onsubmit') || '';
        const hasConfirmOnSubmit = onsubmitAttr.includes('confirm(');

        if (hasConfirmAttr || hasConfirmOnSubmit) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            let message = targetWithConfirm.getAttribute('data-confirm-message') || form.getAttribute('data-confirm-message');
            if (!message && hasConfirmOnSubmit) {
                const match = onsubmitAttr.match(/confirm\(['"](.*?)['"]\)/);
                if (match && match[1]) {
                    message = match[1];
                }
            }
            if (!message) {
                message = 'Are you sure you want to proceed with this action?';
            }

            const title = targetWithConfirm.getAttribute('data-confirm-title') || form.getAttribute('data-confirm-title') || 'Confirm Action';
            const confirmText = targetWithConfirm.getAttribute('data-confirm-btn') || form.getAttribute('data-confirm-btn') || 'Confirm';
            const cancelText = targetWithConfirm.getAttribute('data-confirm-cancel') || form.getAttribute('data-confirm-cancel') || 'Cancel';
            const type = targetWithConfirm.getAttribute('data-confirm-type') || form.getAttribute('data-confirm-type') || 'danger';

            window.confirmAction({
                title: title,
                message: message,
                confirmText: confirmText,
                cancelText: cancelText,
                type: type
            }).then(function(confirmed) {
                if (confirmed) {
                    form.setAttribute('data-confirmed', 'true');
                    HTMLFormElement.prototype.submit.call(form);
                }
            });
        }
    }, true);

    // Capture clicks on links/standalone buttons with data-confirm
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-confirm="true"]');
        if (!btn) return;
        if (btn instanceof HTMLFormElement) return;
        if (btn.tagName === 'BUTTON' && btn.type === 'submit' && btn.form) return;

        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        const title = btn.getAttribute('data-confirm-title') || 'Confirm Action';
        const message = btn.getAttribute('data-confirm-message') || 'Are you sure you want to proceed?';
        const confirmText = btn.getAttribute('data-confirm-btn') || 'Confirm';
        const cancelText = btn.getAttribute('data-confirm-cancel') || 'Cancel';
        const type = btn.getAttribute('data-confirm-type') || 'danger';

        window.confirmAction({
            title: title,
            message: message,
            confirmText: confirmText,
            cancelText: cancelText,
            type: type
        }).then(function(confirmed) {
            if (confirmed) {
                if (btn.tagName === 'A' && btn.href) {
                    window.location.href = btn.href;
                }
            }
        });
    }, true);
})();
</script>
