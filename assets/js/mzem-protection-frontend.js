/**
 * MZ Easy Manage – Frontend Protection Script
 */
(function () {
    'use strict';

    if (typeof mzemProtection === 'undefined') return;

    var config = mzemProtection;

    /* ── Toast notification ───────────────────────────────── */
    function showNotification(msg) {
        if (config.type === 'alert') {
            alert(msg);
            return;
        }

        // Modern toast
        var existing = document.getElementById('mzem-protection-toast');
        if (existing) existing.remove();

        var toast = document.createElement('div');
        toast.id = 'mzem-protection-toast';
        toast.textContent = msg;
        toast.style.cssText = [
            'position:fixed',
            'bottom:24px',
            'right:24px',
            'background:linear-gradient(135deg,#1e293b,#334155)',
            'color:#f1f5f9',
            'padding:14px 24px',
            'border-radius:12px',
            'font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif',
            'font-size:14px',
            'font-weight:500',
            'box-shadow:0 8px 32px rgba(0,0,0,.25)',
            'z-index:999999',
            'opacity:0',
            'transform:translateY(16px)',
            'transition:all .35s cubic-bezier(.4,0,.2,1)',
            'max-width:360px',
            'pointer-events:none',
            'border:1px solid rgba(255,255,255,.08)'
        ].join(';');

        document.body.appendChild(toast);

        // In
        requestAnimationFrame(function () {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        });

        // Out
        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(16px)';
            setTimeout(function () { toast.remove(); }, 400);
        }, 3000);
    }

    /* ── Disable Right Click ──────────────────────────────── */
    if (config.disableRightClick) {
        document.addEventListener('contextmenu', function (e) {
            e.preventDefault();
            showNotification(config.message);
            return false;
        });
    }

    /* ── Disable Keyboard Shortcuts ───────────────────────── */
    if (config.disableKeyboard) {
        document.addEventListener('keydown', function (e) {
            // F12
            if (e.key === 'F12' || e.keyCode === 123) {
                e.preventDefault();
                showNotification(config.message);
                return false;
            }
            // Ctrl+Shift+I / Ctrl+Shift+J / Ctrl+Shift+C
            if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i' || e.key === 'J' || e.key === 'j' || e.key === 'C' || e.key === 'c')) {
                e.preventDefault();
                showNotification(config.message);
                return false;
            }
            // Ctrl+U (View Source)
            if (e.ctrlKey && (e.key === 'U' || e.key === 'u') && !e.shiftKey) {
                e.preventDefault();
                showNotification(config.message);
                return false;
            }
            // Ctrl+S (Save)
            if (e.ctrlKey && (e.key === 'S' || e.key === 's') && !e.shiftKey) {
                e.preventDefault();
                showNotification(config.message);
                return false;
            }
            // Ctrl+P (Print)
            if (e.ctrlKey && (e.key === 'P' || e.key === 'p') && !e.shiftKey) {
                e.preventDefault();
                showNotification(config.message);
                return false;
            }
        });
    }

    /* ── Disable Text Selection ───────────────────────────── */
    if (config.disableTextSelect) {
        document.addEventListener('selectstart', function (e) {
            e.preventDefault();
            return false;
        });
        document.body.style.userSelect = 'none';
        document.body.style.webkitUserSelect = 'none';
        document.body.style.msUserSelect = 'none';
    }

    /* ── Disable Image Drag ───────────────────────────────── */
    if (config.disableImageDrag) {
        document.addEventListener('dragstart', function (e) {
            if (e.target.tagName === 'IMG') {
                e.preventDefault();
                showNotification(config.message);
                return false;
            }
        });
    }

    /* ── Disable Copy (Ctrl+C) ────────────────────────────── */
    if (config.disableCopy) {
        document.addEventListener('copy', function (e) {
            e.preventDefault();
            showNotification(config.message);
            return false;
        });
        document.addEventListener('keydown', function (e) {
            if (e.ctrlKey && (e.key === 'C' || e.key === 'c') && !e.shiftKey) {
                e.preventDefault();
                showNotification(config.message);
                return false;
            }
        });
    }

})();
