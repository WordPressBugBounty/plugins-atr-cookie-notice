/**
 * ATR Cookie Notice - Mobile layout
 * Adds .scb-mobile to the banner at or below the "Mobile Breakpoint" setting (so the
 * breakpoint can come from the settings instead of a fixed CSS media query), and runs
 * the compact strip: short button labels, tap the text or the chevron to expand.
 * Expanding is not consent.
 *
 * @package Atr_Cookie_Notice
 * @since 1.4.0
 */
(function () {
  'use strict';

  var settings = (typeof window.atrCookieNoticeSettings !== 'undefined') ? window.atrCookieNoticeSettings : {};
  var breakpoint = parseInt(settings.mobileBreakpoint, 10) || 768;
  var mq = window.matchMedia ? window.matchMedia('(max-width: ' + breakpoint + 'px)') : null;

  function labelTarget(btn) {
    return btn.querySelector('.scb-btn-text') || btn;
  }

  // Short labels while the strip is collapsed, full labels otherwise (default buttons only: data-scb-short)
  function applyLabels(banner, collapsed) {
    var buttons = banner.querySelectorAll('.scb-btn[data-scb-short]');
    Array.prototype.forEach.call(buttons, function (btn) {
      var target = labelTarget(btn);
      if (!btn.hasAttribute('data-scb-full')) {
        btn.setAttribute('data-scb-full', target.textContent);
      }
      target.textContent = collapsed ? btn.getAttribute('data-scb-short') : btn.getAttribute('data-scb-full');
    });
  }

  function syncCompact(banner) {
    var expand = banner.querySelector('.scb-expand');
    var text = banner.querySelector('.scb-text');
    var active = banner.classList.contains('scb-mobile') && banner.classList.contains('scb-mobile-compact');
    var collapsed = active && !banner.classList.contains('scb-expanded');

    if (expand) {
      expand.hidden = !active;
      expand.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    }
    if (text) {
      if (collapsed) {
        text.setAttribute('role', 'button');
        text.setAttribute('tabindex', '0');
        text.setAttribute('aria-expanded', 'false');
      } else {
        text.removeAttribute('role');
        text.removeAttribute('tabindex');
        text.removeAttribute('aria-expanded');
      }
    }
    applyLabels(banner, collapsed);
  }

  function setMobile(banner, isMobile) {
    banner.classList.toggle('scb-mobile', isMobile);
    if (!isMobile) {
      banner.classList.remove('scb-expanded');
      banner.style.width = ''; // width locked by the drag script for the mobile layout
    }
    syncCompact(banner);
  }

  function init() {
    var banner = document.getElementById('scb-banner');
    if (!banner || !mq) {
      return;
    }

    setMobile(banner, mq.matches);
    var onChange = function (e) { setMobile(banner, e.matches); };
    if (mq.addEventListener) {
      mq.addEventListener('change', onChange);
    } else if (mq.addListener) {
      mq.addListener(onChange);
    }

    var toggle = function () {
      banner.classList.toggle('scb-expanded');
      syncCompact(banner);
    };

    var expand = banner.querySelector('.scb-expand');
    expand && expand.addEventListener('click', toggle);

    var text = banner.querySelector('.scb-text');
    if (text) {
      text.addEventListener('click', function (e) {
        // Only while collapsed; links inside the text keep working
        if (banner.classList.contains('scb-expanded') || !banner.classList.contains('scb-mobile-compact') || !banner.classList.contains('scb-mobile')) {
          return;
        }
        if (e.target && e.target.closest && e.target.closest('a')) {
          return;
        }
        toggle();
      });
      text.addEventListener('keydown', function (e) {
        if (text.getAttribute('role') !== 'button') {
          return;
        }
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          toggle();
        }
      });
    }
  }

  // The banner markup is printed before the footer scripts; fall back to DOMContentLoaded otherwise
  if (document.getElementById('scb-banner')) {
    init();
  } else if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
