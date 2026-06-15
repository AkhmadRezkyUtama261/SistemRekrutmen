/**
 Ini yang membuat halaman Cari Lowongan kerja terasa sangat cepat. Saat pelamar mengetik "Desainer" di kotak pencarian, JavaScript ini akan langsung menyembunyikan lowongan lain dan hanya memunculkan "Desainer" secara Real-Time tanpa perlu me-refresh halaman peramban sama sekali.*/

'use strict';

window.RecruitPro = window.RecruitPro || {};

(function (RP) {

  /* ─── Constants ──────────────────────────────────────────── */
  const DEBOUNCE_MS = 300;
  const DEFAULT_SORT = 'newest';
  const FILTER_KEYS = ['search', 'industry', 'location', 'job_type', 'sort', 'page'];

  /* ═══════════════════════════════════════════════════════════
     §1  FILTER MANAGER CLASS
     ═══════════════════════════════════════════════════════════ */

  class FilterManager {
    /**
     * @param {Object} options
     * @param {string}   options.formSelector       — CSS selector for the filter form
     * @param {string}   options.resultsSelector     — CSS selector for results container
     * @param {string}   options.tagsSelector        — CSS selector for filter tags container
     * @param {string}   options.countSelector       — CSS selector for result count display
     * @param {string}   options.searchInputSelector — CSS selector for search input
     * @param {string}   options.fetchUrl            — URL to fetch filtered results (AJAX)
     * @param {boolean}  options.useAjax             — If true, fetch results via AJAX; if false, submit form
     * @param {Function} options.onFiltersChanged    — Callback with current filters
     * @param {Function} options.onResultsLoaded     — Callback with fetched data
     * @param {Function} options.renderResults       — Function to render results HTML
     */
    constructor(options = {}) {
      this.config = {
        formSelector:       options.formSelector || '#filter-form',
        resultsSelector:    options.resultsSelector || '#results-container',
        tagsSelector:       options.tagsSelector || '#filter-tags',
        countSelector:      options.countSelector || '#result-count',
        searchInputSelector: options.searchInputSelector || '#search-input',
        fetchUrl:           options.fetchUrl || null,
        useAjax:            options.useAjax !== undefined ? options.useAjax : false,
        onFiltersChanged:   options.onFiltersChanged || null,
        onResultsLoaded:    options.onResultsLoaded || null,
        renderResults:      options.renderResults || null,
        filterKeys:         options.filterKeys || FILTER_KEYS,
      };

      this.filters = {};
      this.abortController = null;

      this._init();
    }

    /* ── Initialization ───────────────────────────────────── */
    _init() {
      this.form           = document.querySelector(this.config.formSelector);
      this.resultsContainer = document.querySelector(this.config.resultsSelector);
      this.tagsContainer  = document.querySelector(this.config.tagsSelector);
      this.countDisplay   = document.querySelector(this.config.countSelector);
      this.searchInput    = document.querySelector(this.config.searchInputSelector);

      // Read initial filters from URL
      this._readUrlParams();

      // Populate form fields from URL params
      this._populateForm();

      // Render initial filter tags
      this._renderTags();

      // ── Event Listeners ──────────────────────────────── */
      if (this.form) {
        // Handle filter changes (select, checkbox, radio)
        this.form.addEventListener('change', (e) => {
          const field = e.target;
          if (this.config.filterKeys.includes(field.name)) {
            this._updateFilter(field.name, field.value);
            this._applyFilters();
          }
        });

        // Prevent default form submission if using AJAX
        this.form.addEventListener('submit', (e) => {
          e.preventDefault();
          this._syncFormToFilters();
          this._applyFilters();
        });
      }

      // ── Debounced Search Input ────────────────────────── */
      if (this.searchInput) {
        let debounceTimer;
        this.searchInput.addEventListener('input', () => {
          clearTimeout(debounceTimer);
          debounceTimer = setTimeout(() => {
            this._updateFilter('search', this.searchInput.value.trim());
            this._applyFilters();
          }, DEBOUNCE_MS);
        });

        // Pre-fill from URL
        if (this.filters.search) {
          this.searchInput.value = this.filters.search;
        }
      }

      // ── Sort Toggle Buttons ───────────────────────────── */
      document.querySelectorAll('[data-sort]').forEach((btn) => {
        btn.addEventListener('click', () => {
          const sortValue = btn.dataset.sort;
          this._updateFilter('sort', sortValue);
          this._applyFilters();

          // Update active state
          document.querySelectorAll('[data-sort]').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
        });

        // Set initial active state
        const currentSort = this.filters.sort || DEFAULT_SORT;
        if (btn.dataset.sort === currentSort) {
          btn.classList.add('active');
        }
      });

      // ── Reset All Button ──────────────────────────────── */
      document.querySelectorAll('[data-filter-reset]').forEach((btn) => {
        btn.addEventListener('click', () => this.resetAll());
      });
    }

    /* ═══════════════════════════════════════════════════════
       §2  URL PARAMETER MANAGEMENT
       ═══════════════════════════════════════════════════════ */

    /** Read current URL params into this.filters */
    _readUrlParams() {
      const params = new URLSearchParams(window.location.search);
      this.filters = {};

      this.config.filterKeys.forEach((key) => {
        const value = params.get(key);
        if (value && value.trim()) {
          this.filters[key] = value.trim();
        }
      });
    }

    /** Push current filters to URL (without reload) */
    _pushUrlParams() {
      const url = new URL(window.location);
      const params = url.searchParams;

      // Clear existing filter params
      this.config.filterKeys.forEach(key => params.delete(key));

      // Set active filters
      Object.entries(this.filters).forEach(([key, value]) => {
        if (value && value.trim()) {
          params.set(key, value);
        }
      });

      // Remove page if not set
      if (!this.filters.page) params.delete('page');

      window.history.pushState({}, '', url.toString());
    }

    /** Build query string from current filters */
    _buildQueryString() {
      const params = new URLSearchParams();
      Object.entries(this.filters).forEach(([key, value]) => {
        if (value && value.trim()) {
          params.set(key, value);
        }
      });
      return params.toString();
    }

    /* ═══════════════════════════════════════════════════════
       §3  FILTER OPERATIONS
       ═══════════════════════════════════════════════════════ */

    /** Update a single filter value */
    _updateFilter(key, value) {
      if (value && value.trim()) {
        this.filters[key] = value.trim();
      } else {
        delete this.filters[key];
      }
      // Reset page when filters change
      if (key !== 'page') {
        delete this.filters.page;
      }
    }

    /** Remove a single filter */
    removeFilter(key) {
      delete this.filters[key];
      delete this.filters.page;

      // Clear form field
      if (this.form) {
        const field = this.form.querySelector(`[name="${key}"]`);
        if (field) {
          if (field.type === 'checkbox' || field.type === 'radio') {
            field.checked = false;
          } else {
            field.value = '';
          }
        }
      }

      if (key === 'search' && this.searchInput) {
        this.searchInput.value = '';
      }

      this._applyFilters();
    }

    /** Reset all filters */
    resetAll() {
      this.filters = {};

      // Clear all form fields
      if (this.form) this.form.reset();
      if (this.searchInput) this.searchInput.value = '';

      // Clear sort active states
      document.querySelectorAll('[data-sort]').forEach(b => b.classList.remove('active'));
      const defaultSort = document.querySelector(`[data-sort="${DEFAULT_SORT}"]`);
      if (defaultSort) defaultSort.classList.add('active');

      this._applyFilters();
    }

    /** Sync form fields to this.filters */
    _syncFormToFilters() {
      if (!this.form) return;

      const formData = new FormData(this.form);
      this.config.filterKeys.forEach((key) => {
        const value = formData.get(key);
        if (value && value.trim()) {
          this.filters[key] = value.trim();
        } else {
          delete this.filters[key];
        }
      });

      if (this.searchInput && this.searchInput.value.trim()) {
        this.filters.search = this.searchInput.value.trim();
      }
    }

    /** Populate form fields from current filters */
    _populateForm() {
      if (!this.form) return;

      Object.entries(this.filters).forEach(([key, value]) => {
        const field = this.form.querySelector(`[name="${key}"]`);
        if (!field) return;

        if (field.type === 'checkbox') {
          field.checked = !!value;
        } else if (field.tagName === 'SELECT' || field.type === 'text') {
          field.value = value;
        }
      });
    }

    /** Apply filters — update URL, tags, fetch results */
    _applyFilters() {
      this._pushUrlParams();
      this._renderTags();

      if (typeof this.config.onFiltersChanged === 'function') {
        this.config.onFiltersChanged({ ...this.filters });
      }

      if (this.config.useAjax && this.config.fetchUrl) {
        this._fetchResults();
      }
    }

    /* ═══════════════════════════════════════════════════════
       §4  FILTER TAGS
       ═══════════════════════════════════════════════════════ */

    _renderTags() {
      if (!this.tagsContainer) return;

      const activeFilters = Object.entries(this.filters)
        .filter(([key]) => key !== 'page' && key !== 'sort');

      if (activeFilters.length === 0) {
        this.tagsContainer.innerHTML = '';
        this.tagsContainer.style.display = 'none';
        return;
      }

      this.tagsContainer.style.display = 'flex';
      this.tagsContainer.innerHTML = activeFilters.map(([key, value]) => `
        <span class="filter-tag" data-filter-key="${this._escapeAttr(key)}">
          <span class="filter-tag-label">${this._formatLabel(key)}:</span>
          <span class="filter-tag-value">${this._escapeHtml(value)}</span>
          <button type="button" class="filter-tag-remove" aria-label="Remove ${key} filter" data-remove-filter="${this._escapeAttr(key)}">
            <svg width="14" height="14" viewBox="0 0 20 20" fill="none">
              <path d="M6 6l8 8M14 6l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </button>
        </span>
      `).join('') + (activeFilters.length > 1 ? `
        <button type="button" class="filter-tag filter-tag-clear" data-filter-reset>
          Clear all
        </button>
      ` : '');

      // Bind remove events
      this.tagsContainer.querySelectorAll('[data-remove-filter]').forEach((btn) => {
        btn.addEventListener('click', () => {
          this.removeFilter(btn.dataset.removeFilter);
        });
      });

      // Bind clear all
      const clearBtn = this.tagsContainer.querySelector('[data-filter-reset]');
      if (clearBtn) {
        clearBtn.addEventListener('click', () => this.resetAll());
      }
    }

    /* ═══════════════════════════════════════════════════════
       §5  AJAX FETCH
       ═══════════════════════════════════════════════════════ */

    async _fetchResults() {
      // Abort previous request
      if (this.abortController) {
        this.abortController.abort();
      }
      this.abortController = new AbortController();

      const url = `${this.config.fetchUrl}?${this._buildQueryString()}`;

      // Show loading state
      if (this.resultsContainer) {
        this.resultsContainer.classList.add('loading');
      }

      try {
        const response = await fetch(url, {
          signal: this.abortController.signal,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();

        // Update result count
        if (this.countDisplay && data.total !== undefined) {
          this.countDisplay.textContent = `${data.total} result${data.total !== 1 ? 's' : ''} found`;
        }

        // Render results
        if (typeof this.config.renderResults === 'function' && this.resultsContainer) {
          this.resultsContainer.innerHTML = this.config.renderResults(data);
        }

        if (typeof this.config.onResultsLoaded === 'function') {
          this.config.onResultsLoaded(data);
        }

      } catch (err) {
        if (err.name === 'AbortError') return; // Intentional abort
        console.error('[Filters] Fetch error:', err);
        if (RP.showToast) {
          RP.showToast('Failed to load results. Please try again.', 'error');
        }
      } finally {
        if (this.resultsContainer) {
          this.resultsContainer.classList.remove('loading');
        }
      }
    }

    /* ═══════════════════════════════════════════════════════
       §6  HELPERS
       ═══════════════════════════════════════════════════════ */

    _formatLabel(key) {
      const labels = {
        search:    'Search',
        industry:  'Industry',
        location:  'Location',
        job_type:  'Job Type',
        sort:      'Sort',
        page:      'Page'
      };
      return labels[key] || key.replace(/[_-]/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }

    _escapeHtml(str) {
      const div = document.createElement('div');
      div.appendChild(document.createTextNode(str));
      return div.innerHTML;
    }

    _escapeAttr(str) {
      return str.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    /** Get current filters as plain object */
    getFilters() {
      return { ...this.filters };
    }

    /** Set filters programmatically */
    setFilters(newFilters) {
      Object.entries(newFilters).forEach(([key, value]) => {
        this._updateFilter(key, value);
      });
      this._populateForm();
      this._applyFilters();
    }

    /** Destroy — remove event listeners */
    destroy() {
      if (this.abortController) this.abortController.abort();
    }
  }


  /* ═══════════════════════════════════════════════════════════
     §7  AUTO-INIT — data-filter-manager
     ═══════════════════════════════════════════════════════════ */
  document.addEventListener('DOMContentLoaded', () => {
    const managerEl = document.querySelector('[data-filter-manager]');
    if (managerEl) {
      const opts = {};
      if (managerEl.dataset.filterForm)   opts.formSelector = managerEl.dataset.filterForm;
      if (managerEl.dataset.filterResults) opts.resultsSelector = managerEl.dataset.filterResults;
      if (managerEl.dataset.filterTags)   opts.tagsSelector = managerEl.dataset.filterTags;
      if (managerEl.dataset.filterCount)  opts.countSelector = managerEl.dataset.filterCount;
      if (managerEl.dataset.filterSearch) opts.searchInputSelector = managerEl.dataset.filterSearch;
      if (managerEl.dataset.filterUrl)    opts.fetchUrl = managerEl.dataset.filterUrl;
      if (managerEl.dataset.filterAjax)   opts.useAjax = managerEl.dataset.filterAjax === 'true';

      window.RecruitPro._filterManager = new FilterManager(opts);
    }
  });

  /* ═══════════════════════════════════════════════════════════
     §8  PUBLIC API
     ═══════════════════════════════════════════════════════════ */
  RP.FilterManager = FilterManager;
  RP.filters = {
    create: (opts) => new FilterManager(opts)
  };

})(window.RecruitPro);
