/**
 * ATR Cookie Notice - Simple Mode JavaScript
 * Informational banner only; sets a simple consent flag on OK.
 *
 * @package Atr_Cookie_Notice
 * @since 2.0.0
 */
(function(){
  var settings = (typeof window.atrCookieNoticeSettings !== 'undefined') ? window.atrCookieNoticeSettings : {};
  var name = settings.cookieName || 'atr_cookie_notice_consent';
  var decisionCookieName = settings.decisionCookieName || 'atr_cookie_notice_consent_given';
  var expiryDays = settings.expiryDays || 365;

  function setCookie(key, val, opts){
    try {
      var cookie = String(key) + '=' + encodeURIComponent(String(val)) + '; path=/; SameSite=Lax';
      if (opts && typeof opts.maxAgeSeconds === 'number' && isFinite(opts.maxAgeSeconds)) {
        var maxAge = Math.floor(opts.maxAgeSeconds);
        var expires = new Date(Date.now() + maxAge * 1000).toUTCString();
        cookie += '; Max-Age=' + maxAge + '; Expires=' + expires;
      }
      if (window.location && window.location.protocol === 'https:') {
        cookie += '; Secure';
      }
      document.cookie = cookie;
    } catch(e) {}
  }

  function getConsent(){
    try {
      var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
      if (match) {
        var parsed = JSON.parse(decodeURIComponent(match[2]));
        if (parsed && typeof parsed === 'object' && parsed.essential === true) {
          return parsed;
        }
      }
    } catch(e) {}
    return null;
  }

  function hideBanner(){
    var banner = document.getElementById('scb-banner');
    if (banner) {
      banner.classList.remove('visible');
    }
  }

  function showBanner(){
    var banner = document.getElementById('scb-banner');
    if (!banner) return;
    if (getConsent()) return;
    banner.classList.add('visible');
  }

  function ready(fn){
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else { fn(); }
  }

  ready(function(){
    var okBtn = document.getElementById('scb-btn-ok');
    if (okBtn) {
      okBtn.addEventListener('click', function(){
        try {
          var consent = { essential: true, analytics: false, marketing: false, ts: Date.now() };
          // Prefer localStorage if available; fall back to cookie
          try { localStorage.setItem(name, JSON.stringify(consent)); } catch(e) {}
          setCookie(name, JSON.stringify(consent), { maxAgeSeconds: (expiryDays * 24 * 60 * 60) });
          setCookie(decisionCookieName, '1', { maxAgeSeconds: (expiryDays * 24 * 60 * 60) });
        } catch(e) {}
        hideBanner();
      });
    }
    showBanner();
  });
})();


