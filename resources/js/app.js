/**
 * TahiConnect — Global UI Enhancements
 * All frontend-only, no backend interactions.
 */

// ─── Livewire Navigation Progress Bar ──────────────────────────────────────

document.addEventListener('livewire:navigating', () => {
    const bar = document.getElementById('nprogress-bar');
    if (bar) {
        bar.classList.remove('done');
        bar.classList.add('loading');
    }
});

document.addEventListener('livewire:navigated', () => {
    const bar = document.getElementById('nprogress-bar');
    if (bar) {
        bar.classList.remove('loading');
        bar.classList.add('done');
        setTimeout(() => bar.classList.remove('done'), 600);
    }
    // Re-apply stagger animations after navigation
    applyStaggerAnimations();
    // Re-run scroll animations
    initScrollAnimations();
});

// ─── Stagger Animations ─────────────────────────────────────────────────────

function applyStaggerAnimations() {
    document.querySelectorAll('[style*="--stagger-index"]').forEach((el) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(12px)';
        requestAnimationFrame(() => {
            el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            el.style.transitionDelay = `${(parseInt(el.style.getPropertyValue('--stagger-index') || 0)) * 80}ms`;
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        });
    });
}

// ─── Scroll-triggered Animations ────────────────────────────────────────────

function initScrollAnimations() {
    if (!('IntersectionObserver' in window)) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('in-view');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.scroll-reveal').forEach(el => observer.observe(el));
}

// ─── Auto-dismiss Flash Messages ────────────────────────────────────────────

function initAutoDissmiss() {
    document.querySelectorAll('[data-auto-dismiss]').forEach(el => {
        const delay = parseInt(el.dataset.autoDismiss) || 4000;
        setTimeout(() => {
            el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateX(100%)';
            setTimeout(() => el.remove(), 400);
        }, delay);
    });
}

// ─── Enhanced Tooltip System ────────────────────────────────────────────────

function initTooltips() {
    document.querySelectorAll('[data-tooltip]').forEach(el => {
        let tooltip = null;

        el.addEventListener('mouseenter', () => {
            tooltip = document.createElement('div');
            tooltip.className = 'fixed z-[100] px-3 py-2 text-xs font-medium text-white bg-zinc-900 rounded-lg shadow-lg pointer-events-none whitespace-nowrap';
            tooltip.textContent = el.dataset.tooltip;
            document.body.appendChild(tooltip);

            const rect = el.getBoundingClientRect();
            tooltip.style.left = `${rect.left + rect.width / 2 - tooltip.offsetWidth / 2}px`;
            tooltip.style.top = `${rect.top - tooltip.offsetHeight - 8}px`;
            tooltip.style.opacity = '0';
            tooltip.style.transform = 'translateY(4px)';
            tooltip.style.transition = 'opacity 0.2s, transform 0.2s';

            requestAnimationFrame(() => {
                tooltip.style.opacity = '1';
                tooltip.style.transform = 'translateY(0)';
            });
        });

        el.addEventListener('mouseleave', () => {
            if (tooltip) {
                tooltip.style.opacity = '0';
                setTimeout(() => tooltip?.remove(), 200);
                tooltip = null;
            }
        });
    });
}

// ─── Click Ripple Effect ─────────────────────────────────────────────────────

function initRipple() {
    document.addEventListener('click', (e) => {
        const button = e.target.closest('.click-feedback');
        if (!button) return;

        const rect = button.getBoundingClientRect();
        const ripple = document.createElement('span');
        const size = Math.max(rect.width, rect.height);

        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: scale(0);
            animation: ripple-effect 0.5s ease-out forwards;
            left: ${e.clientX - rect.left - size / 2}px;
            top: ${e.clientY - rect.top - size / 2}px;
            pointer-events: none;
        `;

        if (getComputedStyle(button).position === 'static') {
            button.style.position = 'relative';
        }
        button.style.overflow = 'hidden';
        button.appendChild(ripple);
        setTimeout(() => ripple.remove(), 600);
    });
}

// Add ripple keyframe if not already present
const rippleStyle = document.createElement('style');
rippleStyle.textContent = `
@keyframes ripple-effect {
    to { transform: scale(2.5); opacity: 0; }
}
`;
document.head.appendChild(rippleStyle);

// ─── Number Counter Animation ────────────────────────────────────────────────

function animateCounters() {
    document.querySelectorAll('[data-count-to]').forEach(el => {
        const target = parseInt(el.dataset.countTo);
        const duration = parseInt(el.dataset.countDuration) || 1500;
        const prefix = el.dataset.countPrefix || '';
        const suffix = el.dataset.countSuffix || '';
        let start = null;

        function step(timestamp) {
            if (!start) start = timestamp;
            const progress = Math.min((timestamp - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
            el.textContent = prefix + Math.floor(eased * target).toLocaleString() + suffix;
            if (progress < 1) requestAnimationFrame(step);
        }

        requestAnimationFrame(step);
    });
}

// ─── Keyboard Navigation Enhancements ───────────────────────────────────────

document.addEventListener('keydown', (e) => {
    // Escape closes any open modals (with data-modal-close)
    if (e.key === 'Escape') {
        document.querySelectorAll('[data-modal-close]').forEach(btn => btn.click());
    }
});

// ─── Smart Back Button ───────────────────────────────────────────────────────

document.querySelectorAll('[data-back]').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        if (document.referrer && document.referrer !== window.location.href) {
            window.history.back();
        } else {
            window.location.href = btn.dataset.back || '/';
        }
    });
});

// ─── Dark Mode Toggle Helper ─────────────────────────────────────────────────

window.toggleDarkMode = function () {
    document.documentElement.classList.toggle('dark');
    const isDark = document.documentElement.classList.contains('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
};

// ─── Clipboard Helper ────────────────────────────────────────────────────────

window.copyToClipboard = function (text, feedbackEl = null) {
    navigator.clipboard.writeText(text).then(() => {
        if (feedbackEl) {
            const original = feedbackEl.textContent;
            feedbackEl.textContent = 'Copied!';
            setTimeout(() => (feedbackEl.textContent = original), 2000);
        }
    });
};

// ─── Initialize on DOM Ready ─────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    applyStaggerAnimations();
    initScrollAnimations();
    initAutoDissmiss();
    initTooltips();
    initRipple();
    animateCounters();
});

// Re-initialize after Livewire updates
document.addEventListener('livewire:update', () => {
    initAutoDissmiss();
    initTooltips();
    animateCounters();
});
