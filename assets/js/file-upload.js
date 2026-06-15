/**
Mengontrol area drag & drop saat pelamar mengunggah CV. Secara instan menolak file jika bukan PDF atau ukurannya melebihi kapasitas (misal 2MB).*/

'use strict';

window.RecruitPro = window.RecruitPro || {};

(function (RP) {

  /* ─── Constants ──────────────────────────────────────────── */
  const ALLOWED_TYPES = ['application/pdf'];
  const ALLOWED_EXTENSIONS = ['pdf'];
  const MAX_FILE_SIZE = 2 * 1024 * 1024;  // 2MB
  const MAX_FILE_SIZE_LABEL = '2MB';

  /* ═══════════════════════════════════════════════════════════
     §1  FILE UPLOAD COMPONENT
     ═══════════════════════════════════════════════════════════ */

  /**
   * Initialize a file upload drop zone.
   * @param {string|HTMLElement} selector — CSS selector or element for the drop zone container
   * @param {Object} options
   * @param {string}   options.inputName      — Name attribute for the hidden file input (default: 'cv_file')
   * @param {string[]} options.allowedTypes   — MIME types (default: ['application/pdf'])
   * @param {number}   options.maxSize        — Max bytes (default: 2MB)
   * @param {Function} options.onFileSelected — Callback with (file, dropZone)
   * @param {Function} options.onFileRemoved  — Callback
   * @param {Function} options.onError        — Callback with (message)
   */
  function initFileUpload(selector, options = {}) {
    const container = typeof selector === 'string'
      ? document.querySelector(selector)
      : selector;

    if (!container) {
      console.warn('[FileUpload] Container not found:', selector);
      return null;
    }

    const config = {
      inputName:    options.inputName || 'cv_file',
      allowedTypes: options.allowedTypes || ALLOWED_TYPES,
      allowedExt:   options.allowedExtensions || ALLOWED_EXTENSIONS,
      maxSize:      options.maxSize || MAX_FILE_SIZE,
      maxSizeLabel: options.maxSizeLabel || MAX_FILE_SIZE_LABEL,
      onFileSelected: options.onFileSelected || null,
      onFileRemoved:  options.onFileRemoved || null,
      onError:        options.onError || null,
    };

    // ── State ────────────────────────────────────────────── */
    let currentFile = null;

    // ── Build DOM ────────────────────────────────────────── */
    container.classList.add('file-upload-container');
    container.innerHTML = `
      <div class="drop-zone" tabindex="0" role="button" aria-label="Upload PDF file">
        <div class="drop-zone-idle">
          <div class="drop-zone-icon">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
              <rect x="4" y="4" width="40" height="40" rx="12" fill="rgba(99,102,241,0.08)" stroke="rgba(99,102,241,0.2)" stroke-width="1.5"/>
              <path d="M24 16v16M16 24h16" stroke="#6366F1" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
          <p class="drop-zone-text">
            <span class="drop-zone-cta">Click to upload</span> or drag and drop
          </p>
          <p class="drop-zone-hint">PDF only · Max ${config.maxSizeLabel}</p>
        </div>
        <div class="drop-zone-drag-active" style="display:none;">
          <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
            <rect x="4" y="4" width="40" height="40" rx="12" fill="rgba(99,102,241,0.15)" stroke="#6366F1" stroke-width="2" stroke-dasharray="4 4"/>
            <path d="M24 18v12M18 24l6-6 6 6" stroke="#6366F1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <p style="color:#818CF8; font-weight:600;">Drop your file here</p>
        </div>
        <input type="file" name="${config.inputName}" class="drop-zone-input" accept=".pdf,application/pdf" style="display:none;" />
      </div>
      <div class="upload-progress" style="display:none;">
        <div class="upload-progress-bar-track">
          <div class="upload-progress-bar-fill"></div>
        </div>
        <span class="upload-progress-text">Uploading... 0%</span>
      </div>
      <div class="upload-preview" style="display:none;">
        <div class="upload-preview-info">
          <div class="upload-preview-icon">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
              <rect x="4" y="2" width="24" height="28" rx="4" fill="rgba(243,63,94,0.1)" stroke="rgba(243,63,94,0.3)" stroke-width="1"/>
              <text x="16" y="20" text-anchor="middle" fill="#F43F5E" font-size="8" font-weight="700">PDF</text>
            </svg>
          </div>
          <div class="upload-preview-details">
            <span class="upload-preview-name"></span>
            <span class="upload-preview-size"></span>
          </div>
        </div>
        <button type="button" class="upload-preview-remove" aria-label="Remove file" title="Remove file">
          <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
            <path d="M6 6l8 8M14 6l-8 8" stroke="#94A3B8" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </button>
      </div>
      <div class="upload-error" style="display:none;" role="alert"></div>
    `;

    // ── Element References ───────────────────────────────── */
    const dropZone      = container.querySelector('.drop-zone');
    const idleState     = container.querySelector('.drop-zone-idle');
    const dragState     = container.querySelector('.drop-zone-drag-active');
    const fileInput     = container.querySelector('.drop-zone-input');
    const progressWrap  = container.querySelector('.upload-progress');
    const progressFill  = container.querySelector('.upload-progress-bar-fill');
    const progressText  = container.querySelector('.upload-progress-text');
    const preview       = container.querySelector('.upload-preview');
    const previewName   = container.querySelector('.upload-preview-name');
    const previewSize   = container.querySelector('.upload-preview-size');
    const removeBtn     = container.querySelector('.upload-preview-remove');
    const errorDisplay  = container.querySelector('.upload-error');

    // ── Event Handlers ───────────────────────────────────── */

    // Click to browse
    dropZone.addEventListener('click', () => {
      if (!currentFile) fileInput.click();
    });

    // Keyboard accessibility
    dropZone.addEventListener('keydown', (e) => {
      if ((e.key === 'Enter' || e.key === ' ') && !currentFile) {
        e.preventDefault();
        fileInput.click();
      }
    });

    // File input change
    fileInput.addEventListener('change', () => {
      if (fileInput.files && fileInput.files[0]) {
        handleFile(fileInput.files[0]);
      }
    });

    // ── Drag Events ──────────────────────────────────────── */
    let dragCounter = 0;

    dropZone.addEventListener('dragenter', (e) => {
      e.preventDefault();
      dragCounter++;
      dropZone.classList.add('drag-over');
      idleState.style.display = 'none';
      dragState.style.display = 'flex';
    });

    dropZone.addEventListener('dragover', (e) => {
      e.preventDefault();
    });

    dropZone.addEventListener('dragleave', (e) => {
      e.preventDefault();
      dragCounter--;
      if (dragCounter === 0) {
        dropZone.classList.remove('drag-over');
        idleState.style.display = '';
        dragState.style.display = 'none';
      }
    });

    dropZone.addEventListener('drop', (e) => {
      e.preventDefault();
      dragCounter = 0;
      dropZone.classList.remove('drag-over');
      idleState.style.display = '';
      dragState.style.display = 'none';

      const files = e.dataTransfer.files;
      if (files && files[0]) {
        handleFile(files[0]);
      }
    });

    // Remove file
    removeBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      removeFile();
    });

    // ── File Processing ──────────────────────────────────── */

    function handleFile(file) {
      clearError();

      // Validate MIME type
      const ext = file.name.split('.').pop().toLowerCase();
      const validType = config.allowedTypes.includes(file.type) ||
                        config.allowedExt.includes(ext);

      if (!validType) {
        showError(`Invalid file type. Only ${config.allowedExt.map(e => e.toUpperCase()).join(', ')} files are accepted.`);
        fileInput.value = '';
        return;
      }

      // Validate size
      if (file.size > config.maxSize) {
        showError(`File is too large. Maximum size is ${config.maxSizeLabel}.`);
        fileInput.value = '';
        return;
      }

      // Additional MIME check via FileReader (magic bytes)
      validatePdfMagicBytes(file)
        .then((isValid) => {
          if (!isValid) {
            showError('File does not appear to be a valid PDF.');
            fileInput.value = '';
            return;
          }
          currentFile = file;
          simulateUpload(file);
        })
        .catch(() => {
          // Fallback: accept if we can't read magic bytes
          currentFile = file;
          simulateUpload(file);
        });
    }

    function validatePdfMagicBytes(file) {
      return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => {
          const arr = new Uint8Array(reader.result);
          // PDF magic bytes: %PDF (0x25 0x50 0x44 0x46)
          const isPdf = arr[0] === 0x25 && arr[1] === 0x50 &&
                        arr[2] === 0x44 && arr[3] === 0x46;
          resolve(isPdf);
        };
        reader.onerror = reject;
        reader.readAsArrayBuffer(file.slice(0, 4));
      });
    }

    function simulateUpload(file) {
      dropZone.style.display = 'none';
      progressWrap.style.display = 'block';
      preview.style.display = 'none';

      let progress = 0;
      const interval = setInterval(() => {
        progress += Math.random() * 15 + 5;
        if (progress >= 100) {
          progress = 100;
          clearInterval(interval);
          setTimeout(() => showPreview(file), 200);
        }
        progressFill.style.width = `${Math.min(progress, 100)}%`;
        progressText.textContent = `Uploading... ${Math.round(Math.min(progress, 100))}%`;
      }, 80);
    }

    function showPreview(file) {
      progressWrap.style.display = 'none';
      preview.style.display = 'flex';
      preview.classList.add('animate-fade-in-up');

      previewName.textContent = file.name;
      previewSize.textContent = formatFileSize(file.size);

      if (typeof config.onFileSelected === 'function') {
        config.onFileSelected(file, container);
      }
    }

    function removeFile() {
      currentFile = null;
      fileInput.value = '';
      preview.style.display = 'none';
      preview.classList.remove('animate-fade-in-up');
      dropZone.style.display = '';
      progressWrap.style.display = 'none';
      clearError();

      if (typeof config.onFileRemoved === 'function') {
        config.onFileRemoved(container);
      }
    }

    function showError(message) {
      errorDisplay.style.display = 'flex';
      errorDisplay.innerHTML = `
        <svg width="16" height="16" viewBox="0 0 20 20" fill="#F43F5E" style="flex-shrink:0; margin-top:1px;">
          <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9 9V7a1 1 0 112 0v2a1 1 0 11-2 0zm0 4a1 1 0 112 0 1 1 0 01-2 0z"/>
        </svg>
        <span>${message}</span>
      `;

      if (typeof config.onError === 'function') {
        config.onError(message);
      }
    }

    function clearError() {
      errorDisplay.style.display = 'none';
      errorDisplay.innerHTML = '';
    }

    function formatFileSize(bytes) {
      if (bytes === 0) return '0 B';
      const units = ['B', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(1024));
      return parseFloat((bytes / Math.pow(1024, i)).toFixed(1)) + ' ' + units[i];
    }

    // ── Public API for this instance ─────────────────────── */
    return {
      getFile: () => currentFile,
      removeFile,
      reset: removeFile,
      container
    };
  }

  /* ═══════════════════════════════════════════════════════════
     §2  AUTO-INIT — data-file-upload
     ═══════════════════════════════════════════════════════════ */
  document.addEventListener('DOMContentLoaded', () => {
    const zones = document.querySelectorAll('[data-file-upload]');
    zones.forEach((zone) => {
      const opts = {};
      if (zone.dataset.inputName)  opts.inputName = zone.dataset.inputName;
      if (zone.dataset.maxSize)    opts.maxSize = parseInt(zone.dataset.maxSize, 10);
      if (zone.dataset.maxSizeLabel) opts.maxSizeLabel = zone.dataset.maxSizeLabel;

      const instance = initFileUpload(zone, opts);
      // Store instance on element for external access
      zone._fileUpload = instance;
    });
  });

  /* ═══════════════════════════════════════════════════════════
     §3  PUBLIC API
     ═══════════════════════════════════════════════════════════ */
  RP.fileUpload = {
    init: initFileUpload
  };

})(window.RecruitPro);
