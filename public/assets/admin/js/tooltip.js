/**
 * tooltip.js — Reyal Solutions Admin
 * ─────────────────────────────────────────────────────────────────────────────
 * Premium tooltip engine using EVENT DELEGATION — works automatically on any
 * element (static or dynamically injected by DataTables / AJAX) that has:
 *
 *   data-tooltip="Label text"
 *   data-tooltip-icon="ti-edit"          optional Tabler icon class
 *   data-tooltip-color="primary"         primary|success|danger|warning|info|dark
 *   data-tooltip-pos="top"               top|bottom|left|right  (default: top)
 *
 * No imports, no init calls needed — just include this file once and every
 * [data-tooltip] element across the entire admin panel gets enhanced tooltips.
 * ─────────────────────────────────────────────────────────────────────────────
 */

(function () {
  'use strict';

  /* ── Colour palette ───────────────────────────────────────────────────── */
  var PALETTE = {
    primary : { bg: '#4f46e5', border: '#4338ca', shadow: 'rgba(79,70,229,.4)'   },
    success : { bg: '#10b981', border: '#059669', shadow: 'rgba(16,185,129,.4)'  },
    danger  : { bg: '#ef4444', border: '#dc2626', shadow: 'rgba(239,68,68,.4)'   },
    warning : { bg: '#f59e0b', border: '#d97706', shadow: 'rgba(245,158,11,.4)'  },
    info    : { bg: '#0ea5e9', border: '#0284c7', shadow: 'rgba(14,165,233,.4)'  },
    dark    : { bg: '#1e293b', border: '#0f172a', shadow: 'rgba(15,23,42,.5)'    },
  };

  /* ── Singleton tooltip DOM element ───────────────────────────────────── */
  var box, arrow, hideTimer, currentTarget;

  function createBox() {
    box = document.createElement('div');
    box.setAttribute('role', 'tooltip');
    box.style.cssText = [
      'position:fixed',
      'z-index:2147483647',
      'display:none',
      'align-items:center',
      'gap:6px',
      'padding:5px 11px',
      'border-radius:8px',
      'font-size:0.77rem',
      'font-weight:700',
      'font-family:"Plus Jakarta Sans",system-ui,sans-serif',
      'letter-spacing:0.2px',
      'color:#fff',
      'white-space:nowrap',
      'pointer-events:none',
      'opacity:0',
      'border:1px solid transparent',
      'transition:opacity .15s ease,transform .15s ease',
    ].join(';');

    arrow = document.createElement('span');
    arrow.style.cssText = [
      'position:absolute',
      'width:8px',
      'height:8px',
      'transform:rotate(45deg)',
      'border:1px solid transparent',
    ].join(';');

    document.body.appendChild(box);
  }

  function applyTheme(color) {
    var c = PALETTE[color] || PALETTE.dark;
    box.style.background  = c.bg;
    box.style.borderColor = c.border;
    box.style.boxShadow   = '0 6px 20px ' + c.shadow;
    arrow.style.background   = c.bg;
    arrow.style.borderColor  = c.border;
  }

  function positionBox(el, pos) {
    var r   = el.getBoundingClientRect();
    var bw  = box.offsetWidth;
    var bh  = box.offsetHeight;
    var GAP = 9;
    var left, top;

    /* reset arrow sides */
    ['top','bottom','left','right'].forEach(function(s) {
      arrow.style[s] = 'auto';
      arrow.style['border' + s.charAt(0).toUpperCase() + s.slice(1) + 'Color'] = 'transparent';
    });

    switch (pos) {
      case 'bottom':
        left = r.left + r.width / 2 - bw / 2;
        top  = r.bottom + GAP;
        arrow.style.top    = '-5px';
        arrow.style.left   = '50%';
        arrow.style.transform = 'translateX(-50%) rotate(45deg)';
        arrow.style.borderTopColor  = box.style.borderColor;
        arrow.style.borderLeftColor = box.style.borderColor;
        break;
      case 'left':
        left = r.left - bw - GAP;
        top  = r.top  + r.height / 2 - bh / 2;
        arrow.style.right  = '-5px';
        arrow.style.top    = '50%';
        arrow.style.transform = 'translateY(-50%) rotate(45deg)';
        arrow.style.borderTopColor   = box.style.borderColor;
        arrow.style.borderRightColor = box.style.borderColor;
        break;
      case 'right':
        left = r.right + GAP;
        top  = r.top   + r.height / 2 - bh / 2;
        arrow.style.left   = '-5px';
        arrow.style.top    = '50%';
        arrow.style.transform = 'translateY(-50%) rotate(45deg)';
        arrow.style.borderBottomColor = box.style.borderColor;
        arrow.style.borderLeftColor   = box.style.borderColor;
        break;
      default: /* top */
        left = r.left + r.width / 2 - bw / 2;
        top  = r.top  - bh - GAP;
        arrow.style.bottom = '-5px';
        arrow.style.left   = '50%';
        arrow.style.transform = 'translateX(-50%) rotate(45deg)';
        arrow.style.borderBottomColor = box.style.borderColor;
        arrow.style.borderRightColor  = box.style.borderColor;
    }

    /* clamp within viewport */
    left = Math.max(4, Math.min(left, window.innerWidth  - bw - 4));
    top  = Math.max(4, Math.min(top,  window.innerHeight - bh - 4));

    box.style.left = left + 'px';
    box.style.top  = top  + 'px';
  }

  function show(el) {
    clearTimeout(hideTimer);
    if (!box) createBox();

    var label = el.getAttribute('data-tooltip') || '';
    var icon  = el.getAttribute('data-tooltip-icon')  || '';
    var color = el.getAttribute('data-tooltip-color') || 'dark';
    var pos   = el.getAttribute('data-tooltip-pos')   || 'top';

    if (!label) return;

    currentTarget = el;

    /* build content */
    var html = '';
    if (icon) html += '<i class="ti ' + icon + '" style="font-size:.85rem;flex-shrink:0;opacity:.9;"></i>';
    html += '<span>' + label + '</span>';

    /* clear previous content but keep arrow */
    if (arrow.parentNode === box) box.removeChild(arrow);
    box.innerHTML = html;
    box.appendChild(arrow);

    applyTheme(color);

    box.style.display   = 'flex';
    box.style.opacity   = '0';
    box.style.transform = (pos === 'bottom') ? 'translateY(-5px)' : 'translateY(5px)';

    /* position on next frame once layout is computed */
    requestAnimationFrame(function () {
      if (currentTarget !== el) return; /* target changed while waiting */
      positionBox(el, pos);
      box.style.opacity   = '1';
      box.style.transform = 'translateY(0)';
    });
  }

  function hide() {
    if (!box) return;
    currentTarget = null;
    box.style.opacity   = '0';
    box.style.transform = 'translateY(5px)';
    hideTimer = setTimeout(function () {
      if (box) box.style.display = 'none';
    }, 160);
  }

  /* ── EVENT DELEGATION ─────────────────────────────────────────────────
     Listens on document — catches ALL elements, static or dynamic (DataTables,
     AJAX, etc.) — without any MutationObserver or DOMContentLoaded needed.
  ─────────────────────────────────────────────────────────────────────── */
  document.addEventListener('mouseover', function (e) {
    var el = e.target;
    /* walk up to find closest [data-tooltip] */
    while (el && el !== document.body) {
      if (el.hasAttribute && el.hasAttribute('data-tooltip')) {
        show(el);
        return;
      }
      el = el.parentElement;
    }
    /* no tooltip target — hide if showing */
    if (currentTarget) hide();
  });

  document.addEventListener('mouseout', function (e) {
    var el = e.target;
    while (el && el !== document.body) {
      if (el.hasAttribute && el.hasAttribute('data-tooltip')) {
        /* only hide if we're leaving this element (not entering a child) */
        if (!el.contains(e.relatedTarget)) hide();
        return;
      }
      el = el.parentElement;
    }
  });

  /* hide on scroll / resize to avoid stale positioning */
  document.addEventListener('scroll', hide, true);
  window.addEventListener('resize', hide);

  /* expose for programmatic use: RSTooltip.show(el) / RSTooltip.hide() */
  window.RSTooltip = { show: show, hide: hide };

}());
