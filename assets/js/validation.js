/**
 Penjaga gerbang Front-End. Melakukan Client-Side Validation secara instan. Contoh: menolak password yang kurang dari 8 huruf atau email yang tidak memakai logo "@" langsung di layar sebelum tombol diklik.*/

'use strict';

window.RecruitPro = window.RecruitPro || {};

(function (RP) {

  /* ═══════════════════════════════════════════════════════════
     §1  VALIDATION RULES
     ═══════════════════════════════════════════════════════════ */

  const RULES = {

    /** Required — value must not be empty */
    required(value, fieldName = 'This field') {
      if (value === null || value === undefined) return `${fieldName} is required.`;
      const v = typeof value === 'string' ? value.trim() : value;
      if (v === '' || v === false) return `${fieldName} is required.`;
      return null;
    },

    /** Email — RFC 5322-ish regex */
    email(value) {
      if (!value || !value.trim()) return null; // skip if empty (use required separately)
      const re = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/;
      return re.test(value.trim()) ? null : 'Please enter a valid email address.';
    },

    /** Password — min 8, uppercase, number, special char */
    password(value) {
      if (!value) return null;
      const issues = [];
      if (value.length < 8)         issues.push('at least 8 characters');
      if (!/[A-Z]/.test(value))     issues.push('one uppercase letter');
      if (!/[0-9]/.test(value))     issues.push('one number');
      if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value))
                                     issues.push('one special character');
      return issues.length
        ? `Password must contain ${issues.join(', ')}.`
        : null;
    },

    /** Password Strength Score (0–4) */
    passwordStrength(value) {
      if (!value) return { score: 0, label: 'Too short', color: '#94A3B8' };
      let score = 0;
      if (value.length >= 8) score++;
      if (value.length >= 12) score++;
      if (/[A-Z]/.test(value) && /[a-z]/.test(value)) score++;
      if (/[0-9]/.test(value)) score++;
      if (/[^A-Za-z0-9]/.test(value)) score++;

      // Normalize to 0–4
      score = Math.min(score, 4);
      const labels = [
        { label: 'Too short', color: '#94A3B8' },
        { label: 'Weak',      color: '#F43F5E' },
        { label: 'Fair',      color: '#F59E0B' },
        { label: 'Good',      color: '#0EA5E9' },
        { label: 'Strong',    color: '#10B981' }
      ];
      return { score, ...labels[score] };
    },

    /** Confirm password match */
    confirmPassword(value, compareValue) {
      if (!value) return null;
      return value === compareValue ? null : 'Passwords do not match.';
    },

    /** Min length */
    minLength(value, min) {
      if (!value) return null;
      return value.trim().length >= min
        ? null
        : `Must be at least ${min} characters.`;
    },

    /** Max length */
    maxLength(value, max) {
      if (!value) return null;
      return value.trim().length <= max
        ? null
        : `Must be no more than ${max} characters.`;
    },

    /** Phone — international-friendly */
    phone(value) {
      if (!value || !value.trim()) return null;
      const cleaned = value.replace(/[\s\-().]/g, '');
      const re = /^\+?[0-9]{8,15}$/;
      return re.test(cleaned) ? null : 'Please enter a valid phone number.';
    },

    /** URL */
    url(value) {
      if (!value || !value.trim()) return null;
      try {
        new URL(value.startsWith('http') ? value : `https://${value}`);
        return null;
      } catch {
        return 'Please enter a valid URL.';
      }
    },

    /** File type — accepts array of MIME types or extensions */
    fileType(file, allowedTypes) {
      if (!file) return null;
      const allowed = allowedTypes.map(t => t.toLowerCase());
      const ext = file.name.split('.').pop().toLowerCase();
      const mime = (file.type || '').toLowerCase();
      const ok = allowed.some(a => a === mime || a === `.${ext}` || a === ext);
      return ok ? null : `File type not allowed. Accepted: ${allowedTypes.join(', ')}`;
    },

    /** File size — max in bytes */
    fileSize(file, maxBytes) {
      if (!file) return null;
      if (file.size > maxBytes) {
        const maxMB = (maxBytes / 1024 / 1024).toFixed(1);
        return `File size exceeds ${maxMB}MB limit.`;
      }
      return null;
    },

    /** Numeric — must be a valid number */
    numeric(value) {
      if (!value && value !== 0) return null;
      return isNaN(Number(value)) ? 'Please enter a valid number.' : null;
    },

    /** Date — must be a valid date */
    date(value) {
      if (!value) return null;
      const d = new Date(value);
      return isNaN(d.getTime()) ? 'Please enter a valid date.' : null;
    }
  };

  /* ═══════════════════════════════════════════════════════════
     §2  ERROR DISPLAY HELPERS
     ═══════════════════════════════════════════════════════════ */

  /** Show an error message on a form field */
  function showFieldError(field, message) {
    clearFieldError(field);
    field.classList.add('input-error');
    field.setAttribute('aria-invalid', 'true');

    const errorEl = document.createElement('div');
    errorEl.className = 'field-error-message';
    errorEl.setAttribute('role', 'alert');
    errorEl.style.cssText = `
      color: #F43F5E; font-size: 12px; margin-top: 6px;
      display: flex; align-items: center; gap: 4px;
      animation: fadeInUp 200ms cubic-bezier(0.4,0,0.2,1) forwards;
    `;
    errorEl.innerHTML = `
      <svg width="14" height="14" viewBox="0 0 20 20" fill="#F43F5E">
        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9 9V7a1 1 0 112 0v2a1 1 0 11-2 0zm0 4a1 1 0 112 0 1 1 0 01-2 0z"/>
      </svg>
      <span>${message}</span>
    `;

    // Insert after the field (or its wrapper)
    const wrapper = field.closest('.form-group') || field.parentElement;
    wrapper.appendChild(errorEl);

    // Link for accessibility
    const errorId = `error-${field.name || field.id || Math.random().toString(36).slice(2)}`;
    errorEl.id = errorId;
    field.setAttribute('aria-describedby', errorId);
  }

  /** Clear error from a field */
  function clearFieldError(field) {
    field.classList.remove('input-error');
    field.removeAttribute('aria-invalid');
    field.removeAttribute('aria-describedby');

    const wrapper = field.closest('.form-group') || field.parentElement;
    const existing = wrapper.querySelector('.field-error-message');
    if (existing) existing.remove();
  }

  /** Mark a field as valid (green) */
  function showFieldSuccess(field) {
    clearFieldError(field);
    field.classList.add('input-success');
  }

  /** Clear success state */
  function clearFieldSuccess(field) {
    field.classList.remove('input-success');
  }

  /** Clear all states */
  function clearFieldState(field) {
    clearFieldError(field);
    clearFieldSuccess(field);
  }

  /* ═══════════════════════════════════════════════════════════
     §3  FIELD VALIDATOR
     ═══════════════════════════════════════════════════════════ */

  /**
   * Validate a single field based on its data-validate attribute(s).
   * Attribute format: data-validate="required|email|minLength:8"
   * @param {HTMLElement} field
   * @returns {boolean} — true if valid
   */
  function validateField(field) {
    const rules = (field.dataset.validate || '').split('|').filter(Boolean);
    if (rules.length === 0) return true;

    const value = field.type === 'file'
      ? (field.files && field.files[0])
      : field.value;

    for (const rule of rules) {
      const [ruleName, ...params] = rule.split(':');
      let error = null;

      switch (ruleName) {
        case 'required':
          error = RULES.required(value, getFieldLabel(field));
          break;
        case 'email':
          error = RULES.email(value);
          break;
        case 'password':
          error = RULES.password(value);
          break;
        case 'phone':
          error = RULES.phone(value);
          break;
        case 'url':
          error = RULES.url(value);
          break;
        case 'numeric':
          error = RULES.numeric(value);
          break;
        case 'date':
          error = RULES.date(value);
          break;
        case 'minLength':
          error = RULES.minLength(value, parseInt(params[0], 10));
          break;
        case 'maxLength':
          error = RULES.maxLength(value, parseInt(params[0], 10));
          break;
        case 'fileType':
          error = RULES.fileType(value, params[0].split(','));
          break;
        case 'fileSize':
          error = RULES.fileSize(value, parseInt(params[0], 10));
          break;
        case 'confirmPassword': {
          const targetField = field.form
            ? field.form.querySelector(`[name="${params[0]}"]`)
            : document.querySelector(`[name="${params[0]}"]`);
          error = RULES.confirmPassword(value, targetField ? targetField.value : '');
          break;
        }
        default:
          console.warn(`[Validation] Unknown rule: ${ruleName}`);
      }

      if (error) {
        showFieldError(field, error);
        return false;
      }
    }

    // All rules passed
    if (value && (typeof value === 'string' ? value.trim() : value)) {
      showFieldSuccess(field);
    } else {
      clearFieldState(field);
    }
    return true;
  }

  /** Get the label text for a field */
  function getFieldLabel(field) {
    // Try <label for="...">
    if (field.id) {
      const label = document.querySelector(`label[for="${field.id}"]`);
      if (label) return label.textContent.replace(/\*$/, '').trim();
    }
    // Try placeholder
    if (field.placeholder) return field.placeholder;
    // Try name attribute
    if (field.name) return field.name.replace(/[_-]/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    return 'This field';
  }

  /* ═══════════════════════════════════════════════════════════
     §4  PASSWORD STRENGTH METER
     ═══════════════════════════════════════════════════════════ */

  function initPasswordStrengthMeter(field) {
    // Look for or create the strength meter
    const wrapper = field.closest('.form-group') || field.parentElement;
    let meter = wrapper.querySelector('.password-strength-meter');

    if (!meter) {
      meter = document.createElement('div');
      meter.className = 'password-strength-meter';
      meter.style.cssText = 'margin-top: 8px;';
      meter.innerHTML = `
        <div class="strength-bars" style="display:flex; gap:4px; margin-bottom:4px;">
          <div class="strength-bar" style="flex:1; height:4px; border-radius:2px; background:#334155; transition:background 250ms;"></div>
          <div class="strength-bar" style="flex:1; height:4px; border-radius:2px; background:#334155; transition:background 250ms;"></div>
          <div class="strength-bar" style="flex:1; height:4px; border-radius:2px; background:#334155; transition:background 250ms;"></div>
          <div class="strength-bar" style="flex:1; height:4px; border-radius:2px; background:#334155; transition:background 250ms;"></div>
        </div>
        <span class="strength-label" style="font-size:12px; color:#94A3B8;"></span>
      `;
      wrapper.appendChild(meter);
    }

    const bars = meter.querySelectorAll('.strength-bar');
    const label = meter.querySelector('.strength-label');

    field.addEventListener('input', () => {
      const result = RULES.passwordStrength(field.value);
      bars.forEach((bar, i) => {
        bar.style.background = i < result.score ? result.color : '#334155';
      });
      label.textContent = field.value ? result.label : '';
      label.style.color = result.color;
    });
  }

  /* ═══════════════════════════════════════════════════════════
     §5  FORM INITIALIZATION
     ═══════════════════════════════════════════════════════════ */

  /**
   * Initialize validation on a form.
   * @param {string|HTMLFormElement} formSelector — CSS selector or form element
   * @param {Object} options — { onSuccess, onError, realtime }
   */
  function initFormValidation(formSelector, options = {}) {
    const form = typeof formSelector === 'string'
      ? document.querySelector(formSelector)
      : formSelector;

    if (!form) {
      console.warn('[Validation] Form not found:', formSelector);
      return;
    }

    const {
      onSuccess = null,
      onError = null,
      realtime = true
    } = options;

    const fields = form.querySelectorAll('[data-validate]');

    // ── Real-time validation on blur / input ────────────── */
    if (realtime) {
      fields.forEach((field) => {
        // Validate on blur
        field.addEventListener('blur', () => validateField(field));

        // Validate on input with debounce
        let debounceTimer;
        field.addEventListener('input', () => {
          clearTimeout(debounceTimer);
          debounceTimer = setTimeout(() => {
            // Only validate if field has been touched (has error or success class)
            if (field.classList.contains('input-error') ||
                field.classList.contains('input-success')) {
              validateField(field);
            }
          }, 300);
        });

        // Password strength meter
        if (field.dataset.validate.includes('password') &&
            !field.dataset.validate.includes('confirmPassword')) {
          initPasswordStrengthMeter(field);
        }
      });
    }

    // ── Form submission validation ──────────────────────── */
    form.addEventListener('submit', (e) => {
      let isValid = true;

      fields.forEach((field) => {
        if (!validateField(field)) {
          isValid = false;
        }
      });

      if (!isValid) {
        e.preventDefault();

        // Scroll to first error
        const firstError = form.querySelector('.input-error');
        if (firstError) {
          firstError.focus();
          firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        if (typeof onError === 'function') onError(form);
        return;
      }

      if (typeof onSuccess === 'function') {
        e.preventDefault();
        onSuccess(form, new FormData(form));
      }
    });
  }

  /* ═══════════════════════════════════════════════════════════
     §6  AUTO-INIT — data-validate-form
     ═══════════════════════════════════════════════════════════ */
  document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('[data-validate-form]');
    forms.forEach((form) => initFormValidation(form));
  });

  /* ═══════════════════════════════════════════════════════════
     §7  PUBLIC API
     ═══════════════════════════════════════════════════════════ */
  RP.validation = {
    rules: RULES,
    validateField,
    showFieldError,
    clearFieldError,
    showFieldSuccess,
    clearFieldState,
    initFormValidation,
    initPasswordStrengthMeter
  };

})(window.RecruitPro);
