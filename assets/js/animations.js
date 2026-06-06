/**
 * @Module:      Animations — Scroll Reveal & Interactions
 * @Author:      FE-06 (Interaction Design)
 * @Date:        2026-05-24
 * @Description: IntersectionObserver scroll reveals, staggered card
 *               animations, counter animation for stat numbers,
 *               smooth scroll for anchors, parallax hero effect,
 *               and page transition fade.
 * @Ownership:   FE-06
 */

'use strict';

window.RecruitPro = window.RecruitPro || {};

(function (RP) {

  /* ─── Reduced Motion Check ──────────────────────────────── */
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ═══════════════════════════════════════════════════════════
     §1  SCROLL REVEAL — IntersectionObserver
     ═══════════════════════════════════════════════════════════ */

  /**
   * Initialize scroll reveal for elements with .reveal class.
   * When elements enter the viewport, .revealed is added.
   * @param {Object} options
   * @param {string}  options.selector  — CSS selector (default '.reveal')
   * @param {number}  options.threshold — Visibility threshold 0–1 (default 0.1)
   * @param {string}  options.rootMargin — IntersectionObserver root margin
   * @param {boolean} options.once       — Reveal only once (default true)
   */
  function initScrollReveal(options = {}) {
    if (prefersReducedMotion) {
      // Immediately show all elements
      document.querySelectorAll(options.selector || '.reveal, .reveal-left, .reveal-right, .reveal-scale')
        .forEach(el => el.classList.add('revealed'));
      return;
    }

    const config = {
      selector:   options.selector || '.reveal, .reveal-left, .reveal-right, .reveal-scale',
      threshold:  options.threshold || 0.1,
      rootMargin: options.rootMargin || '0px 0px -40px 0px',
      once:       options.once !== undefined ? options.once : true,
    };

    const elements = document.querySelectorAll(config.selector);
    if (elements.length === 0) return;

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('revealed');
          if (config.once) {
            observer.unobserve(entry.target);
          }
        } else if (!config.once) {
          entry.target.classList.remove('revealed');
        }
      });
    }, {
      threshold: config.threshold,
      rootMargin: config.rootMargin
    });

    elements.forEach((el) => observer.observe(el));

    return observer;
  }

  /* ═══════════════════════════════════════════════════════════
     §2  STAGGERED CARD REVEAL
     ═══════════════════════════════════════════════════════════ */

  /**
   * Auto-assign stagger delays to children within a container.
   * @param {string} containerSelector — CSS selector for the grid container
   * @param {string} childSelector      — CSS selector for children (default '.reveal')
   * @param {number} delayStep          — ms between each child (default 60)
   */
  function initStaggeredReveal(containerSelector = '.stagger-grid', childSelector = '.reveal', delayStep = 60) {
    if (prefersReducedMotion) return;

    const containers = document.querySelectorAll(containerSelector);

    containers.forEach((container) => {
      const children = container.querySelectorAll(childSelector);

      const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            // Stagger the children animations
            children.forEach((child, index) => {
              child.style.transitionDelay = `${index * delayStep}ms`;
              child.classList.add('revealed');
            });
            observer.unobserve(entry.target);
          }
        });
      }, {
        threshold: 0.05,
        rootMargin: '0px 0px -20px 0px'
      });

      observer.observe(container);
    });
  }

  /* ═══════════════════════════════════════════════════════════
     §3  COUNTER ANIMATION
     ═══════════════════════════════════════════════════════════ */

  /**
   * Animate numbers counting up from 0 to their target value.
   * Target elements should have data-count-to="1234" attribute.
   * @param {string} selector — CSS selector (default '[data-count-to]')
   * @param {number} duration — Animation duration in ms (default 2000)
   */
  function initCounterAnimation(selector = '[data-count-to]', duration = 2000) {
    const elements = document.querySelectorAll(selector);
    if (elements.length === 0) return;

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          animateCounter(entry.target, duration);
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.3
    });

    elements.forEach((el) => observer.observe(el));
  }

  function animateCounter(element, duration) {
    const target = parseFloat(element.dataset.countTo) || 0;
    const decimals = (element.dataset.countDecimals !== undefined)
      ? parseInt(element.dataset.countDecimals, 10)
      : (target % 1 !== 0 ? 1 : 0);
    const prefix = element.dataset.countPrefix || '';
    const suffix = element.dataset.countSuffix || '';

    if (prefersReducedMotion) {
      element.textContent = prefix + formatCounterValue(target, decimals) + suffix;
      return;
    }

    const startTime = performance.now();

    function update(currentTime) {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);

      // Ease-out cubic
      const eased = 1 - Math.pow(1 - progress, 3);
      const currentValue = target * eased;

      element.textContent = prefix + formatCounterValue(currentValue, decimals) + suffix;

      if (progress < 1) {
        requestAnimationFrame(update);
      } else {
        element.textContent = prefix + formatCounterValue(target, decimals) + suffix;
      }
    }

    requestAnimationFrame(update);
  }

  function formatCounterValue(value, decimals) {
    if (decimals === 0) {
      return Math.round(value).toLocaleString('en-US');
    }
    return value.toLocaleString('en-US', {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals
    });
  }

  /* ═══════════════════════════════════════════════════════════
     §4  SMOOTH SCROLL — Anchor Links
     ═══════════════════════════════════════════════════════════ */

  function initSmoothScroll() {
    document.addEventListener('click', (e) => {
      const link = e.target.closest('a[href^="#"]');
      if (!link) return;

      const targetId = link.getAttribute('href');
      if (targetId === '#' || targetId === '#!') return;

      const target = document.querySelector(targetId);
      if (!target) return;

      e.preventDefault();

      const headerHeight = parseInt(
        getComputedStyle(document.documentElement).getPropertyValue('--header-height') || '64',
        10
      );

      const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerHeight - 16;

      window.scrollTo({
        top: targetPosition,
        behavior: prefersReducedMotion ? 'auto' : 'smooth'
      });

      // Update URL hash without jumping
      history.pushState(null, null, targetId);
    });
  }

  /* ═══════════════════════════════════════════════════════════
     §5  PARALLAX EFFECT — Hero Section
     ═══════════════════════════════════════════════════════════ */

  /**
   * Apply a parallax scrolling effect to hero elements.
   * Elements with data-parallax attribute will translate on scroll.
   * @param {string} selector — CSS selector (default '[data-parallax]')
   */
  function initParallax(selector = '[data-parallax]') {
    if (prefersReducedMotion) return;

    const elements = document.querySelectorAll(selector);
    if (elements.length === 0) return;

    let ticking = false;

    function updateParallax() {
      const scrollY = window.pageYOffset;

      elements.forEach((el) => {
        const speed = parseFloat(el.dataset.parallax) || 0.3;
        const rect = el.getBoundingClientRect();

        // Only animate when in or near viewport
        if (rect.bottom < -200 || rect.top > window.innerHeight + 200) return;

        const yOffset = scrollY * speed;
        el.style.transform = `translateY(${yOffset}px)`;
      });

      ticking = false;
    }

    window.addEventListener('scroll', () => {
      if (!ticking) {
        requestAnimationFrame(updateParallax);
        ticking = true;
      }
    }, { passive: true });
  }

  /* ═══════════════════════════════════════════════════════════
     §6  PAGE TRANSITION FADE
     ═══════════════════════════════════════════════════════════ */

  function initPageTransitions() {
    if (prefersReducedMotion) return;

    // Fade out before navigating
    document.addEventListener('click', (e) => {
      const link = e.target.closest('a[href]');
      if (!link) return;

      // Skip for external links, hash links, new tabs, confirm links
      const href = link.getAttribute('href');
      if (!href ||
          href.startsWith('#') ||
          href.startsWith('javascript:') ||
          href.startsWith('mailto:') ||
          href.startsWith('tel:') ||
          link.target === '_blank' ||
          link.hasAttribute('data-confirm') ||
          link.hasAttribute('data-no-transition') ||
          e.ctrlKey || e.metaKey || e.shiftKey) {
        return;
      }

      // Check if same origin
      try {
        const url = new URL(href, window.location.origin);
        if (url.origin !== window.location.origin) return;
      } catch {
        return;
      }

      e.preventDefault();

      const pageContent = document.querySelector('.page-content') || document.body;
      pageContent.classList.add('page-transition-exit');

      setTimeout(() => {
        window.location.href = href;
      }, 250);
    });
  }

  /* ═══════════════════════════════════════════════════════════
     §7  HOVER TILT EFFECT (Cards)
     ═══════════════════════════════════════════════════════════ */

  function initTiltEffect(selector = '[data-tilt]') {
    if (prefersReducedMotion) return;

    const elements = document.querySelectorAll(selector);
    elements.forEach((el) => {
      const maxTilt = parseFloat(el.dataset.tilt) || 4;

      el.addEventListener('mousemove', (e) => {
        const rect = el.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;

        const rotateX = ((y - centerY) / centerY) * -maxTilt;
        const rotateY = ((x - centerX) / centerX) * maxTilt;

        el.style.transform = `perspective(800px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-2px)`;
      });

      el.addEventListener('mouseleave', () => {
        el.style.transform = 'perspective(800px) rotateX(0) rotateY(0) translateY(0)';
        el.style.transition = 'transform 400ms cubic-bezier(0.4, 0, 0.2, 1)';
      });

      el.addEventListener('mouseenter', () => {
        el.style.transition = 'transform 100ms ease-out';
      });
    });
  }

  /* ═══════════════════════════════════════════════════════════
     §8  DOMContentLoaded — Auto-Initialize
     ═══════════════════════════════════════════════════════════ */

  document.addEventListener('DOMContentLoaded', () => {
    initScrollReveal();
    initStaggeredReveal();
    initCounterAnimation();
    initSmoothScroll();
    initParallax();
    initPageTransitions();
    initTiltEffect();
  });

  /* ═══════════════════════════════════════════════════════════
     §9  PUBLIC API
     ═══════════════════════════════════════════════════════════ */
  RP.animations = {
    initScrollReveal,
    initStaggeredReveal,
    initCounterAnimation,
    initSmoothScroll,
    initParallax,
    initPageTransitions,
    initTiltEffect
  };

})(window.RecruitPro);
