{{-- Modern Toaster Notification System --}}
<div id="medco-toast-container" class="medco-toast-container" aria-live="polite" aria-atomic="true"></div>

<style>
    .medco-toast-container {
        position: fixed;
        top: 24px;
        right: 24px;
        z-index: 999999;
        display: flex;
        flex-direction: column;
        gap: 12px;
        max-width: 420px;
        width: calc(100vw - 36px);
        pointer-events: none;
    }

    [dir="rtl"] .medco-toast-container {
        right: auto;
        left: 24px;
    }

    .medco-toast {
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 16px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.12), 0 4px 12px -2px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.06);
        border-left: 4px solid #10b981;
        overflow: hidden;
        pointer-events: auto;
        opacity: 0;
        transform: translateX(100%);
        transition: opacity 0.3s ease, transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), margin-bottom 0.3s ease, max-height 0.3s ease;
        max-height: 250px;
    }

    [dir="rtl"] .medco-toast {
        border-left: 1px solid rgba(0, 0, 0, 0.06);
        border-right: 4px solid #10b981;
        transform: translateX(-100%);
    }

    .medco-toast.show {
        opacity: 1;
        transform: translateX(0);
    }

    .medco-toast.hide {
        opacity: 0;
        transform: translateX(100%);
        max-height: 0;
        padding-top: 0;
        padding-bottom: 0;
        margin-bottom: -12px;
        border: none;
    }

    [dir="rtl"] .medco-toast.hide {
        transform: translateX(-100%);
    }

    body.dark-mode .medco-toast {
        background: #1e293b;
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.4), 0 4px 12px -2px rgba(0, 0, 0, 0.3);
    }

    /* Type-specific styling */
    .medco-toast.toast-success {
        border-left-color: #10b981;
    }
    [dir="rtl"] .medco-toast.toast-success {
        border-right-color: #10b981;
    }

    .medco-toast.toast-error,
    .medco-toast.toast-danger {
        border-left-color: #ef4444;
    }
    [dir="rtl"] .medco-toast.toast-error,
    [dir="rtl"] .medco-toast.toast-danger {
        border-right-color: #ef4444;
    }

    .medco-toast.toast-warning {
        border-left-color: #f59e0b;
    }
    [dir="rtl"] .medco-toast.toast-warning {
        border-right-color: #f59e0b;
    }

    .medco-toast.toast-info {
        border-left-color: #3b82f6;
    }
    [dir="rtl"] .medco-toast.toast-info {
        border-right-color: #3b82f6;
    }

    /* Icon badge */
    .medco-toast-icon {
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 1px;
    }

    .toast-success .medco-toast-icon {
        background: rgba(16, 185, 129, 0.12);
        color: #10b981;
    }

    .toast-error .medco-toast-icon,
    .toast-danger .medco-toast-icon {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }

    .toast-warning .medco-toast-icon {
        background: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
    }

    .toast-info .medco-toast-icon {
        background: rgba(59, 130, 246, 0.12);
        color: #3b82f6;
    }

    /* Toast Content */
    .medco-toast-body {
        flex-grow: 1;
        min-width: 0;
    }

    .medco-toast-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 2px;
        line-height: 1.3;
    }

    body.dark-mode .medco-toast-title {
        color: #f8fafc;
    }

    .medco-toast-message {
        font-size: 0.825rem;
        color: #64748b;
        line-height: 1.4;
        word-break: break-word;
    }

    body.dark-mode .medco-toast-message {
        color: #94a3b8;
    }

    /* Close button */
    .medco-toast-close {
        flex-shrink: 0;
        background: transparent;
        border: none;
        color: #94a3b8;
        font-size: 16px;
        cursor: pointer;
        padding: 2px 6px;
        border-radius: 6px;
        line-height: 1;
        transition: all 0.2s ease;
        margin-top: -2px;
        margin-right: -4px;
    }

    .medco-toast-close:hover {
        color: #475569;
        background: rgba(0, 0, 0, 0.05);
    }

    body.dark-mode .medco-toast-close:hover {
        color: #f1f5f9;
        background: rgba(255, 255, 255, 0.1);
    }

    /* Progress bar */
    .medco-toast-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        width: 100%;
        background: rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    .medco-toast-progress-bar {
        height: 100%;
        width: 100%;
        transition: width linear;
    }

    .toast-success .medco-toast-progress-bar {
        background: #10b981;
    }

    .toast-error .medco-toast-progress-bar,
    .toast-danger .medco-toast-progress-bar {
        background: #ef4444;
    }

    .toast-warning .medco-toast-progress-bar {
        background: #f59e0b;
    }

    .toast-info .medco-toast-progress-bar {
        background: #3b82f6;
    }
</style>

<script>
(function() {
    // Icons SVG
    const TOAST_ICONS = {
        success: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>',
        error: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
        danger: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
        warning: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        info: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
    };

    const DEFAULT_TITLES = {
        success: 'Success',
        error: 'Error',
        danger: 'Error',
        warning: 'Warning',
        info: 'Information'
    };

    /**
     * Show a modern toast notification
     * @param {string} type - 'success' | 'error' | 'warning' | 'info'
     * @param {string} message - Message text or HTML
     * @param {string} [title] - Optional title
     * @param {number} [duration=2500] - Duration in ms
     */
    window.showToast = function(type, message, title, duration = 2500) {
        if (!message) return;

        type = (type || 'success').toLowerCase();
        if (type === 'danger') type = 'error';

        const container = document.getElementById('medco-toast-container');
        if (!container) return;

        const iconSvg = TOAST_ICONS[type] || TOAST_ICONS.info;
        const toastTitle = title || DEFAULT_TITLES[type] || 'Notice';

        // Clean message string if it contains extra quotes/escapes
        if (typeof message !== 'string') {
            message = String(message);
        }

        const toast = document.createElement('div');
        toast.className = `medco-toast toast-${type}`;
        toast.setAttribute('role', 'alert');

        toast.innerHTML = `
            <div class="medco-toast-icon">${iconSvg}</div>
            <div class="medco-toast-body">
                <div class="medco-toast-title">${toastTitle}</div>
                <div class="medco-toast-message">${message}</div>
            </div>
            <button type="button" class="medco-toast-close" aria-label="Close">&times;</button>
            <div class="medco-toast-progress">
                <div class="medco-toast-progress-bar" style="width: 100%;"></div>
            </div>
        `;

        container.appendChild(toast);

        // Force reflow for entrance animation
        void toast.offsetWidth;
        toast.classList.add('show');

        const progressBar = toast.querySelector('.medco-toast-progress-bar');
        const closeBtn = toast.querySelector('.medco-toast-close');

        let startTime = Date.now();
        let remaining = duration;
        let isPaused = false;
        let animationFrame;

        function updateProgress() {
            if (!isPaused) {
                const elapsed = Date.now() - startTime;
                const percent = Math.max(0, 100 - (elapsed / duration) * 100);
                if (progressBar) {
                    progressBar.style.width = percent + '%';
                }

                if (elapsed >= duration) {
                    dismissToast();
                    return;
                }
            }
            animationFrame = requestAnimationFrame(updateProgress);
        }

        function dismissToast() {
            cancelAnimationFrame(animationFrame);
            toast.classList.remove('show');
            toast.classList.add('hide');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 350);
        }

        // Hover to pause
        toast.addEventListener('mouseenter', () => {
            isPaused = true;
            remaining -= (Date.now() - startTime);
        });

        toast.addEventListener('mouseleave', () => {
            isPaused = false;
            startTime = Date.now() - (duration - remaining);
        });

        closeBtn.addEventListener('click', dismissToast);

        animationFrame = requestAnimationFrame(updateProgress);
    };

    // Helper functions
    window.toastSuccess = (msg, title) => window.showToast('success', msg, title);
    window.toastError = (msg, title) => window.showToast('error', msg, title);
    window.toastWarning = (msg, title) => window.showToast('warning', msg, title);
    window.toastInfo = (msg, title) => window.showToast('info', msg, title);

    // Toastr API compatibility
    window.toastr = {
        success: (msg, title) => window.showToast('success', msg, title),
        error: (msg, title) => window.showToast('error', msg, title),
        warning: (msg, title) => window.showToast('warning', msg, title),
        info: (msg, title) => window.showToast('info', msg, title)
    };

    // Auto-check localStorage message on load
    function checkLocalStorageToast() {
        try {
            const storedMessage = localStorage.getItem("message");
            if (storedMessage) {
                localStorage.removeItem("message");
                const isError = /error|fail|failed|not permitted|exceed|limit|denied|cannot/i.test(storedMessage);
                window.showToast(isError ? 'error' : 'success', storedMessage);
            }
        } catch (e) {}
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkLocalStorageToast);
    } else {
        checkLocalStorageToast();
    }
})();
</script>

{{-- Auto Trigger Laravel Session Flash Messages on Page Load --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(session()->has('create_message'))
        showToast('success', {!! json_encode(session()->get('create_message')) !!}, 'Created Successfully');
    @endif

    @if(session()->has('edit_message'))
        showToast('success', {!! json_encode(session()->get('edit_message')) !!}, 'Updated Successfully');
    @endif

    @if(session()->has('import_message'))
        showToast('success', {!! json_encode(session()->get('import_message')) !!}, 'Import Successful');
    @endif

    @if(session()->has('success'))
        showToast('success', {!! json_encode(session()->get('success')) !!}, 'Success');
    @endif

    @if(session()->has('message'))
        @php
            $flashMsg = session()->get('message');
            $msgType = (is_string($flashMsg) && preg_match('/(error|failed|fail|cannot|not found|denied)/i', $flashMsg)) ? 'error' : 'success';
        @endphp
        showToast('{{ $msgType }}', {!! json_encode($flashMsg) !!});
    @endif

    @if(session()->has('message1'))
        showToast('success', {!! json_encode(session()->get('message1')) !!});
    @endif

    @if(session()->has('message2'))
        showToast('success', {!! json_encode(session()->get('message2')) !!});
    @endif

    @if(session()->has('message3'))
        showToast('success', {!! json_encode(session()->get('message3')) !!});
    @endif

    @if(session()->has('not_permitted'))
        showToast('error', {!! json_encode(session()->get('not_permitted')) !!}, 'Error');
    @endif

    @if(session()->has('error'))
        showToast('error', {!! json_encode(session()->get('error')) !!}, 'Error');
    @endif

    @if(session()->has('warning'))
        showToast('warning', {!! json_encode(session()->get('warning')) !!}, 'Warning');
    @endif

    @if(session()->has('customMessage'))
        showToast('{{ session("type", "info") }}', {!! json_encode(session()->get('customMessage')) !!});
    @endif

    @if(session()->has('toast_notify'))
        @php $tn = session('toast_notify'); @endphp
        showToast('{{ $tn["type"] ?? "success" }}', {!! json_encode($tn["message"] ?? "") !!}, '{{ $tn["title"] ?? "Notice" }}');
    @endif

    @if($errors->any())
        @foreach($errors->all() as $error)
            showToast('error', {!! json_encode($error) !!}, 'Validation Error');
        @endforeach
    @endif
});
</script>
