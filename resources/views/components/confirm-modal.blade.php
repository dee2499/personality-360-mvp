<div x-data="confirmModalSystem()"
     x-cloak
     x-show="isOpen"
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true"
     @keydown.escape.window="cancel()">
    
    <!-- Backdrop Overlay with subtle blur -->
    <div x-show="isOpen"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
         @click="cancel()"></div>

    <!-- Modal Container -->
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div x-show="isOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 sm:p-7 border border-slate-100"
             @click.stop>
            
            <!-- Icon by Type -->
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl mb-4"
                 :class="{
                     'bg-rose-100 text-rose-600 ring-8 ring-rose-50': type === 'danger',
                     'bg-amber-100 text-amber-600 ring-8 ring-amber-50': type === 'warning',
                     'bg-indigo-100 text-indigo-600 ring-8 ring-indigo-50': type === 'primary',
                     'bg-emerald-100 text-emerald-600 ring-8 ring-emerald-50': type === 'success',
                     'bg-sky-100 text-sky-600 ring-8 ring-sky-50': type === 'info'
                 }">
                <!-- Danger Icon (Trash / Alert) -->
                <template x-if="type === 'danger'">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </template>
                <!-- Warning Icon -->
                <template x-if="type === 'warning'">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </template>
                <!-- Primary / Confirm Icon -->
                <template x-if="type === 'primary'">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </template>
                <!-- Success Icon -->
                <template x-if="type === 'success'">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </template>
                <!-- Info Icon -->
                <template x-if="type === 'info'">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </template>
            </div>

            <!-- Content -->
            <div class="text-center">
                <h3 class="text-lg font-black text-slate-900 tracking-tight" id="modal-title" x-text="title"></h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed whitespace-pre-line" x-text="message"></p>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex flex-col-reverse sm:flex-row gap-2.5 sm:gap-3">
                <template x-if="!isAlertOnly">
                    <button type="button"
                            @click="cancel()"
                            class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition cursor-pointer"
                            x-text="cancelText">
                    </button>
                </template>
                <button type="button"
                        @click="confirm()"
                        class="w-full py-2.5 px-4 rounded-xl text-xs font-black text-white shadow-md transition cursor-pointer"
                        :class="{
                            'bg-rose-600 hover:bg-rose-700 shadow-rose-200': type === 'danger',
                            'bg-amber-600 hover:bg-amber-700 shadow-amber-200': type === 'warning',
                            'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200': type === 'primary',
                            'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-200': type === 'success',
                            'bg-slate-900 hover:bg-slate-800 shadow-slate-200': type === 'info'
                        }"
                        x-text="confirmText">
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('confirmModalSystem', () => ({
        isOpen: false,
        isAlertOnly: false,
        title: 'Confirm Action',
        message: 'Are you sure you want to perform this action?',
        confirmText: 'Confirm',
        cancelText: 'Cancel',
        type: 'danger',
        resolver: null,

        init() {
            window.confirmAction = (opts = {}) => {
                return new Promise((resolve) => {
                    this.isAlertOnly = false;
                    this.title = opts.title || 'Confirm Action';
                    this.message = opts.message || 'Are you sure you want to perform this action?';
                    this.confirmText = opts.confirmText || 'Confirm';
                    this.cancelText = opts.cancelText || 'Cancel';
                    this.type = opts.type || 'danger';
                    this.resolver = resolve;
                    this.isOpen = true;
                });
            };

            window.alertAction = (opts = {}) => {
                if (typeof opts === 'string') {
                    opts = { message: opts };
                }
                return new Promise((resolve) => {
                    this.isAlertOnly = true;
                    this.title = opts.title || 'Attention';
                    this.message = opts.message || '';
                    this.confirmText = opts.confirmText || 'Got It';
                    this.type = opts.type || 'warning';
                    this.resolver = resolve;
                    this.isOpen = true;
                });
            };

            window.showConfirmModal = window.confirmAction;
            window.showAlertModal = window.alertAction;
        },

        confirm() {
            this.isOpen = false;
            if (this.resolver) this.resolver(true);
            this.resolver = null;
        },

        cancel() {
            this.isOpen = false;
            if (this.resolver) this.resolver(false);
            this.resolver = null;
        }
    }));
});

// Intercept form submissions that request confirmation
document.addEventListener('submit', function(e) {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;

    // Check if form is already confirmed
    if (form.getAttribute('data-confirmed') === 'true') {
        form.removeAttribute('data-confirmed');
        return; // Proceed with native form submit
    }

    const hasConfirmAttr = form.getAttribute('data-confirm') === 'true' || form.hasAttribute('data-confirm-message');
    const onsubmitAttr = form.getAttribute('onsubmit') || '';
    const hasConfirmOnSubmit = onsubmitAttr.includes('confirm(');

    if (hasConfirmAttr || hasConfirmOnSubmit) {
        e.preventDefault();
        e.stopImmediatePropagation();

        let message = form.getAttribute('data-confirm-message');
        if (!message && hasConfirmOnSubmit) {
            const match = onsubmitAttr.match(/confirm\(['"](.*?)['"]\)/);
            if (match && match[1]) {
                message = match[1];
            }
        }
        if (!message) {
            message = 'Are you sure you want to proceed with this action?';
        }

        const title = form.getAttribute('data-confirm-title') || 'Confirm Action';
        const confirmText = form.getAttribute('data-confirm-btn') || 'Confirm';
        const cancelText = form.getAttribute('data-confirm-cancel') || 'Cancel';
        const type = form.getAttribute('data-confirm-type') || 'danger';

        if (window.confirmAction) {
            window.confirmAction({
                title: title,
                message: message,
                confirmText: confirmText,
                cancelText: cancelText,
                type: type
            }).then((confirmed) => {
                if (confirmed) {
                    form.setAttribute('data-confirmed', 'true');
                    form.submit();
                }
            });
        } else {
            if (window.confirm(message)) {
                form.setAttribute('data-confirmed', 'true');
                form.submit();
            }
        }
    }
}, true);
</script>
