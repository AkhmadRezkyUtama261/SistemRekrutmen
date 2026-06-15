/**
Otak navigasi utama. Berisi fungsi untuk membuka/menutup Sidebar di mode mobile, dan memunculkan Pop-up Modal atau Dropdown profil./

'use strict';

/* ═══════════════════════════════════════════════════════════════
   §1  GLOBAL NAMESPACE
   ═══════════════════════════════════════════════════════════════ */
window.RecruitPro = window.RecruitPro || {};

(function (RP) {

  /* ─── Constants ──────────────────────────────────────────── */
  const TOAST_DURATION = 5000;        // 5 seconds
  const TOAST_ANIMATION_MS = 400;
  const ALERT_AUTO_DISMISS_MS = 5000;

  /* ─── Toast Container (lazily created) ───────────────────── */
  let toastContainer = null;

  function getToastContainer() {
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.id = 'toast-container';
      toastContainer.setAttribute('role', 'status');
      toastContainer.setAttribute('aria-live', 'polite');
      Object.assign(toastContainer.style, {
        position: 'fixed',
        top: '24px',
        right: '24px',
        display: 'flex',
        flexDirection: 'column',
        gap: '12px',
        zIndex: '600',
        pointerEvents: 'none',
        maxWidth: '420px',
        width: '100%'
      });
      document.body.appendChild(toastContainer);
    }
    return toastContainer;
  }

  /* ═══════════════════════════════════════════════════════════
     §2  TOAST NOTIFICATION SYSTEM
     ═══════════════════════════════════════════════════════════ */
  const TOAST_ICONS = {
    success: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 18a8 8 0 100-16 8 8 0 000 16z" fill="#10B981" opacity=".15"/><path d="M7 10l2 2 4-4" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
    error:   `<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 18a8 8 0 100-16 8 8 0 000 16z" fill="#F43F5E" opacity=".15"/><path d="M13 7l-6 6M7 7l6 6" stroke="#F43F5E" stroke-width="2" stroke-linecap="round"/></svg>`,
    warning: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 18a8 8 0 100-16 8 8 0 000 16z" fill="#F59E0B" opacity=".15"/><path d="M10 7v4M10 13h.01" stroke="#F59E0B" stroke-width="2" stroke-linecap="round"/></svg>`,
    info:    `<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 18a8 8 0 100-16 8 8 0 000 16z" fill="#0EA5E9" opacity=".15"/><path d="M10 9v4M10 7h.01" stroke="#0EA5E9" stroke-width="2" stroke-linecap="round"/></svg>`
  };

  const TOAST_COLORS = {
    success: { border: '#10B981', bar: '#10B981' },
    error:   { border: '#F43F5E', bar: '#F43F5E' },
    warning: { border: '#F59E0B', bar: '#F59E0B' },
    info:    { border: '#0EA5E9', bar: '#0EA5E9' }
  };

  /**
   * Show a toast notification.
   * @param {string} message  — Message to display
   * @param {string} type     — 'success' | 'error' | 'warning' | 'info'
   * @param {number} duration — Auto-dismiss time in ms (default 5000)
   */
  function showToast(message, type = 'info', duration = TOAST_DURATION) {
    const container = getToastContainer();
    const colors = TOAST_COLORS[type] || TOAST_COLORS.info;
    const icon = TOAST_ICONS[type] || TOAST_ICONS.info;

    const toast = document.createElement('div');
    toast.className = 'toast toast-enter';
    toast.style.pointerEvents = 'auto';
    toast.innerHTML = `
      <div class="toast-body" style="
        display:flex; align-items:flex-start; gap:12px;
        background:rgba(30,41,59,0.95); backdrop-filter:blur(12px);
        border:1px solid ${colors.border}30; border-left:3px solid ${colors.border};
        border-radius:12px; padding:16px; box-shadow:0 10px 25px rgba(0,0,0,0.25);
        position:relative; overflow:hidden;
      ">
        <span class="toast-icon" style="flex-shrink:0; margin-top:2px;">${icon}</span>
        <span class="toast-message" style="flex:1; font-size:14px; color:#E2E8F0; line-height:1.5;">${escapeHtml(message)}</span>
        <button class="toast-close" aria-label="Close" style="
          flex-shrink:0; background:none; border:none; color:#94A3B8;
          cursor:pointer; padding:0; line-height:1; font-size:18px;
        ">&times;</button>
        <div class="toast-progress" style="
          position:absolute; bottom:0; left:0; height:3px;
          background:${colors.bar}; border-radius:0 0 0 12px;
          width:100%;
        "></div>
      </div>
    `;

    // Close handler
    const closeBtn = toast.querySelector('.toast-close');
    closeBtn.addEventListener('click', () => dismissToast(toast));

    container.appendChild(toast);

    // Auto-dismiss
    const timerId = setTimeout(() => dismissToast(toast), duration);
    toast._timerId = timerId;
  }

  function dismissToast(toast) {
    if (toast._dismissed) return;
    toast._dismissed = true;
    clearTimeout(toast._timerId);
    toast.classList.remove('toast-enter');
    toast.classList.add('toast-exit');
    setTimeout(() => toast.remove(), TOAST_ANIMATION_MS);
  }

  /* ═══════════════════════════════════════════════════════════
     §3  FLASH MESSAGE HANDLER (from URL params)
     ═══════════════════════════════════════════════════════════ */
  function handleFlashMessages() {
    const params = new URLSearchParams(window.location.search);
    const flashMsg = params.get('flash_message') || params.get('message');
    const flashType = params.get('flash_type') || params.get('type') || 'info';

    if (flashMsg) {
      showToast(decodeURIComponent(flashMsg), flashType);
      // Clean URL
      const url = new URL(window.location);
      url.searchParams.delete('flash_message');
      url.searchParams.delete('flash_type');
      url.searchParams.delete('message');
      url.searchParams.delete('type');
      window.history.replaceState({}, '', url.toString());
    }
  }

  /* ═══════════════════════════════════════════════════════════
     §4  CONFIRM DIALOG WRAPPER
     ═══════════════════════════════════════════════════════════ */
  /**
   * Show a styled confirm dialog.
   * @param {string}   message   — Confirmation message
   * @param {Function} onConfirm — Callback if confirmed
   * @param {Object}   options   — { title, confirmText, cancelText, type }
   */
  function confirmDialog(message, onConfirm, options = {}) {
    const {
      title = 'Confirm Action',
      confirmText = 'Confirm',
      cancelText = 'Cancel',
      type = 'danger'
    } = options;

    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.style.cssText = `
      position:fixed; inset:0; background:rgba(0,0,0,0.6);
      backdrop-filter:blur(4px); display:flex; align-items:center;
      justify-content:center; z-index:500; padding:24px;
    `;

    const btnColor = type === 'danger' ? '#F43F5E' : '#6366F1';
    overlay.innerHTML = `
      <div class="modal-content glass-card" style="
        max-width:440px; width:100%; padding:32px;
        animation:scaleIn 400ms cubic-bezier(0.175,0.885,0.32,1.275) forwards;
      ">
        <h3 style="color:#F8FAFC; margin-bottom:8px; font-size:20px;">${escapeHtml(title)}</h3>
        <p style="color:#94A3B8; margin-bottom:24px; font-size:14px; line-height:1.6;">${escapeHtml(message)}</p>
        <div style="display:flex; gap:12px; justify-content:flex-end;">
          <button class="confirm-cancel btn-secondary" style="padding:10px 20px; border-radius:12px; border:1px solid #334155; color:#94A3B8; cursor:pointer; background:transparent; font-size:14px; font-weight:500;">${escapeHtml(cancelText)}</button>
          <button class="confirm-ok" style="padding:10px 20px; border-radius:12px; background:${btnColor}; color:#fff; cursor:pointer; border:none; font-size:14px; font-weight:600;">${escapeHtml(confirmText)}</button>
        </div>
      </div>
    `;

    document.body.appendChild(overlay);

    // Prevent background scroll
    document.body.style.overflow = 'hidden';

    const cleanup = () => {
      document.body.style.overflow = '';
      overlay.querySelector('.modal-content').classList.add('closing');
      overlay.classList.add('closing');
      setTimeout(() => overlay.remove(), 250);
    };

    overlay.querySelector('.confirm-cancel').addEventListener('click', cleanup);
    overlay.querySelector('.confirm-ok').addEventListener('click', () => {
      cleanup();
      if (typeof onConfirm === 'function') onConfirm();
    });

    // Close on overlay click
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) cleanup();
    });

    // Close on Escape
    const escHandler = (e) => {
      if (e.key === 'Escape') {
        cleanup();
        document.removeEventListener('keydown', escHandler);
      }
    };
    document.addEventListener('keydown', escHandler);
  }

  /* ═══════════════════════════════════════════════════════════
     §5  FORMAT UTILITIES
     ═══════════════════════════════════════════════════════════ */

  /**
   * Format a date string into a human-readable format.
   * @param {string|Date} dateInput
   * @param {object}      options   — Intl.DateTimeFormat options
   * @returns {string}
   */
  function formatDate(dateInput, options = {}) {
    const date = dateInput instanceof Date ? dateInput : new Date(dateInput);
    if (isNaN(date.getTime())) return '—';

    const defaults = {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      ...options
    };
    return new Intl.DateTimeFormat('en-US', defaults).format(date);
  }

  /**
   * Format a number with locale separators.
   * @param {number} num
   * @param {object} options — Intl.NumberFormat options
   * @returns {string}
   */
  function formatNumber(num, options = {}) {
    if (num == null || isNaN(num)) return '—';
    return new Intl.NumberFormat('en-US', options).format(num);
  }

  /**
   * Format bytes to human-readable string.
   * @param {number} bytes
   * @returns {string}
   */
  function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return parseFloat((bytes / Math.pow(1024, i)).toFixed(1)) + ' ' + units[i];
  }

  /**
   * Relative time (e.g., "2 hours ago").
   * @param {string|Date} dateInput
   * @returns {string}
   */
  function timeAgo(dateInput) {
    const date = dateInput instanceof Date ? dateInput : new Date(dateInput);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);

    const intervals = [
      { label: 'year',   seconds: 31536000 },
      { label: 'month',  seconds: 2592000 },
      { label: 'week',   seconds: 604800 },
      { label: 'day',    seconds: 86400 },
      { label: 'hour',   seconds: 3600 },
      { label: 'minute', seconds: 60 },
    ];

    for (const interval of intervals) {
      const count = Math.floor(seconds / interval.seconds);
      if (count >= 1) {
        return `${count} ${interval.label}${count > 1 ? 's' : ''} ago`;
      }
    }
    return 'Just now';
  }

  /* ═══════════════════════════════════════════════════════════
     §6  AUTO-DISMISS ALERTS
     ═══════════════════════════════════════════════════════════ */
  function initAutoDismissAlerts() {
    const alerts = document.querySelectorAll('.alert[data-auto-dismiss]');
    alerts.forEach((alert) => {
      const delay = parseInt(alert.dataset.autoDismiss, 10) || ALERT_AUTO_DISMISS_MS;
      setTimeout(() => {
        alert.classList.add('toast-exit');
        setTimeout(() => alert.remove(), TOAST_ANIMATION_MS);
      }, delay);
    });
  }

  /* ═══════════════════════════════════════════════════════════
     §7  MOBILE DETECTION
     ═══════════════════════════════════════════════════════════ */
  function isMobile() {
    return window.innerWidth < 768 ||
           /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
  }

  function isTablet() {
    return window.innerWidth >= 768 && window.innerWidth < 1024;
  }

  function getBreakpoint() {
    const w = window.innerWidth;
    if (w < 480)  return 'xs';
    if (w < 768)  return 'sm';
    if (w < 1024) return 'md';
    if (w < 1400) return 'lg';
    return 'xl';
  }

  /* ═══════════════════════════════════════════════════════════
     §8  DEBOUNCE & THROTTLE
     ═══════════════════════════════════════════════════════════ */

  /**
   * Debounce — delays execution until after `wait` ms of inactivity.
   * @param {Function} fn
   * @param {number}   wait — milliseconds
   * @returns {Function}
   */
  function debounce(fn, wait = 300) {
    let timer;
    return function (...args) {
      clearTimeout(timer);
      timer = setTimeout(() => fn.apply(this, args), wait);
    };
  }

  /**
   * Throttle — limits execution to once every `limit` ms.
   * @param {Function} fn
   * @param {number}   limit — milliseconds
   * @returns {Function}
   */
  function throttle(fn, limit = 200) {
    let inThrottle = false;
    return function (...args) {
      if (!inThrottle) {
        fn.apply(this, args);
        inThrottle = true;
        setTimeout(() => (inThrottle = false), limit);
      }
    };
  }

  /* ═══════════════════════════════════════════════════════════
     §9  HELPER UTILITIES
     ═══════════════════════════════════════════════════════════ */

  /** Escape HTML entities to prevent XSS */
  function escapeHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
  }

  /** Generate a unique ID */
  function uid(prefix = 'rp') {
    return `${prefix}-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 8)}`;
  }

  /** Copy text to clipboard */
  async function copyToClipboard(text) {
    try {
      await navigator.clipboard.writeText(text);
      showToast('Copied to clipboard', 'success');
    } catch {
      // Fallback
      const ta = document.createElement('textarea');
      ta.value = text;
      ta.style.cssText = 'position:fixed;opacity:0;';
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      ta.remove();
      showToast('Copied to clipboard', 'success');
    }
  }

  /* ═══════════════════════════════════════════════════════════
     §10  SIDEBAR TOGGLE
     ═══════════════════════════════════════════════════════════ */
  function initSidebarToggle() {
    const toggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    if (!toggle || !sidebar) return;

    toggle.addEventListener('click', () => {
      if (window.innerWidth <= 1024) {
        sidebar.classList.toggle('open');
      } else {
        sidebar.classList.toggle('collapsed');
        if (mainContent) {
          mainContent.classList.toggle('sidebar-collapsed');
        }
      }
    });

    // Close mobile sidebar on overlay click
    document.addEventListener('click', (e) => {
      if (window.innerWidth <= 1024 &&
          sidebar.classList.contains('open') &&
          !sidebar.contains(e.target) &&
          !toggle.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    });
  }

  /* ═══════════════════════════════════════════════════════════
     §11  ACTIVE SIDEBAR LINK
     ═══════════════════════════════════════════════════════════ */
  function initActiveNav() {
    const currentPath = window.location.pathname;
    const links = document.querySelectorAll('.sidebar-link');

    links.forEach((link) => {
      const href = link.getAttribute('href');
      if (href && currentPath.includes(href) && href !== '/') {
        link.classList.add('active');
      }
    });
  }

  /* ═══════════════════════════════════════════════════════════
     §12  DROPDOWN MENUS
     ═══════════════════════════════════════════════════════════ */
  function initDropdowns() {
    document.addEventListener('click', (e) => {
      const trigger = e.target.closest('[data-dropdown]');

      // Close all open dropdowns first
      document.querySelectorAll('.dropdown-menu.open').forEach((menu) => {
        if (!trigger || menu.id !== trigger.dataset.dropdown) {
          menu.classList.remove('open');
        }
      });

      if (trigger) {
        e.preventDefault();
        const menu = document.getElementById(trigger.dataset.dropdown);
        if (menu) menu.classList.toggle('open');
      }
    });
  }

  /* ═══════════════════════════════════════════════════════════
     §13  CONFIRM LINKS / FORMS
     ═══════════════════════════════════════════════════════════ */
  function initConfirmActions() {
    document.addEventListener('click', (e) => {
      const el = e.target.closest('[data-confirm]');
      if (!el) return;

      e.preventDefault();
      const message = el.dataset.confirm || 'Are you sure?';
      const title = el.dataset.confirmTitle || 'Confirm Action';

      confirmDialog(message, () => {
        // If it's a link, navigate
        if (el.tagName === 'A' && el.href) {
          window.location.href = el.href;
        }
        // If it's inside a form, submit the form
        const form = el.closest('form');
        if (form) form.submit();
      }, { title });
    });
  }

  /* ═══════════════════════════════════════════════════════════
     §14  DOMContentLoaded — Bootstrap
     ═══════════════════════════════════════════════════════════ */
  document.addEventListener('DOMContentLoaded', () => {
    handleFlashMessages();
    initAutoDismissAlerts();
    initSidebarToggle();
    initActiveNav();
    initDropdowns();
    initConfirmActions();

    // Add page-transition-enter class to main content
    const pageContent = document.querySelector('.page-content');
    if (pageContent) {
      pageContent.classList.add('page-transition-enter');
    }

    // Set body attribute for breakpoint CSS hooks
    document.body.dataset.breakpoint = getBreakpoint();
    window.addEventListener('resize', debounce(() => {
      document.body.dataset.breakpoint = getBreakpoint();
    }, 150));
  });

  /* ═══════════════════════════════════════════════════════════
     §15  PUBLIC API
     ═══════════════════════════════════════════════════════════ */
  Object.assign(RP, {
    showToast,
    confirmDialog,
    formatDate,
    formatNumber,
    formatFileSize,
    timeAgo,
    debounce,
    throttle,
    isMobile,
    isTablet,
    getBreakpoint,
    escapeHtml,
    uid,
    copyToClipboard
  });

})(window.RecruitPro);
