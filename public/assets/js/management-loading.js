(function () {
  'use strict';
  var pending = 0;
  var overlay;
  function sync() {
    var region = document.querySelector('#main-wrapper .body-wrapper > .container-fluid');
    if (!region) return;
    if (!overlay) {
      overlay = document.createElement('div');
      overlay.className = 'management-loading';
      overlay.hidden = true;
      overlay.setAttribute('role', 'status');
      overlay.setAttribute('aria-label', 'Loading data');
      overlay.innerHTML = '<span class="visually-hidden">Loading data</span><div class="ui-skeleton ui-skeleton-title"></div><div class="loading-grid">' +
        Array(4).fill('<div class="loading-card"><div class="ui-skeleton ui-skeleton-line"></div><div class="ui-skeleton ui-skeleton-title"></div></div>').join('') +
        '</div><div class="loading-card">' + Array(6).fill('<div class="ui-skeleton ui-skeleton-row"></div>').join('') + '</div>';
      region.appendChild(overlay);
    }
    overlay.hidden = pending === 0;
    region.setAttribute('aria-busy', pending > 0 ? 'true' : 'false');
  }
  function begin() { pending++; sync(); }
  function end() { pending = Math.max(0, pending - 1); sync(); }
  function local(url) { try { return new URL(url, location.href).origin === location.origin; } catch (_) { return false; } }
  if (window.jQuery) {
    jQuery(document).ajaxSend(function (_, xhr, settings) { if (local(settings.url)) { xhr.__managementLoading = true; begin(); } });
    jQuery(document).ajaxComplete(function (_, xhr) { if (xhr.__managementLoading) { xhr.__managementLoading = false; end(); } });
  }
  if (window.fetch) {
    var originalFetch = window.fetch;
    window.fetch = function (input, options) {
      if (!local(typeof input === 'string' || input instanceof URL ? input : input.url)) return originalFetch.apply(this, arguments);
      begin();
      try {
        return originalFetch.apply(this, arguments).then(function (response) {
          // Wait for bytes, then let the page parse/render its original response.
          return response.clone().arrayBuffer().then(function () { end(); return response; }, function () { end(); return response; });
        }, function (error) { end(); throw error; });
      } catch (error) { end(); throw error; }
    };
  }
  function lazyImages(root) {
    if (root.nodeType !== 1 && root !== document) return;
    root.querySelectorAll('img').forEach(function (img) { img.loading = 'lazy'; img.decoding = 'async'; });
    if (root.matches && root.matches('img')) { root.loading = 'lazy'; root.decoding = 'async'; }
  }
  document.addEventListener('DOMContentLoaded', function () {
    sync(); lazyImages(document);
    new MutationObserver(function (changes) { changes.forEach(function (change) { change.addedNodes.forEach(lazyImages); }); }).observe(document.body, {childList:true, subtree:true});
  });
}());
