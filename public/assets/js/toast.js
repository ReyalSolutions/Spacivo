/**
 * Global Toast Notification Engine
 * Can be used anywhere across StayHub (Admin, Public, Auth, Owner, Tenant)
 * Usage:
 *   ToastStack.create({ type: 'success', title: 'Welcome', message: 'Logged in successfully', duration: 5000 });
 *   ToastStack.success('Operation completed successfully');
 *   ToastStack.danger('Invalid credentials provided');
 *   ToastStack.warning('Session expiring soon');
 *   ToastStack.info('New notification received');
 */
window.ToastStack = window.ToastStack || (() => {
  // Support both Tabler Icons (Admin) and FontAwesome (Public/App)
  const hasTabler = () => !!document.querySelector('link[href*="tabler"]');

  const getIconClass = (type) => {
    const isTi = hasTabler();
    switch (type) {
      case 'success':
        return isTi ? 'ti ti-circle-check' : 'fa-solid fa-circle-check';
      case 'danger':
      case 'error':
        return isTi ? 'ti ti-alert-circle' : 'fa-solid fa-circle-exclamation';
      case 'warning':
        return isTi ? 'ti ti-alert-triangle' : 'fa-solid fa-triangle-exclamation';
      case 'info':
      default:
        return isTi ? 'ti ti-info-circle' : 'fa-solid fa-circle-info';
    }
  };

  const TITLES = {
    success: 'Success',
    danger: 'Error',
    error: 'Error',
    warning: 'Warning',
    info: 'Information'
  };

  function ensureStack() {
    let stack = document.getElementById('toast-stack');
    if (!stack) {
      stack = document.createElement('div');
      stack.id = 'toast-stack';
      document.body.appendChild(stack);
    }
    return stack;
  }

  function create({ type = 'info', title, message = '', password, duration = 5000 }) {
    const stack = ensureStack();
    const normalizedType = type === 'error' ? 'danger' : type;

    const el = document.createElement('div');
    el.className = `gtoast gtoast--${normalizedType}`;
    el.setAttribute('role', normalizedType === 'danger' ? 'alert' : 'status');

    const pwHtml = password
      ? `<div class="gtoast__pw">
           <code>${escapeHtml(password)}</code>
           <button type="button" class="gtoast__copy" onclick="ToastStack.copyPw(this)" title="Copy password">
             <i class="${hasTabler() ? 'ti ti-copy' : 'fa-regular fa-copy'}"></i>
           </button>
         </div>`
      : '';

    const closeIcon = hasTabler() ? 'ti ti-x' : 'fa-solid fa-xmark';
    const iconClass = getIconClass(normalizedType);

    el.innerHTML = `
      <div class="gtoast__icon"><i class="${iconClass}"></i></div>
      <div class="gtoast__body">
        <div class="gtoast__title">${escapeHtml(title || TITLES[normalizedType] || 'Notification')}</div>
        <p class="gtoast__msg">${escapeHtml(message)}</p>
        ${pwHtml}
      </div>
      <button type="button" class="gtoast__close" onclick="this.closest('.gtoast').remove()" title="Dismiss">
        <i class="${closeIcon}"></i>
      </button>
      <div class="gtoast__progress" style="animation: toastShrink ${duration}ms linear forwards;"></div>
    `;

    stack.appendChild(el);

    // Trigger smooth entrance animation
    requestAnimationFrame(() => {
      requestAnimationFrame(() => el.classList.add('show'));
    });

    const timer = setTimeout(() => {
      el.classList.add('hide');
      setTimeout(() => el.remove(), 400);
    }, duration);

    // Pause on hover
    el.addEventListener('mouseenter', () => {
      const bar = el.querySelector('.gtoast__progress');
      if (bar) bar.style.animationPlayState = 'paused';
      clearTimeout(timer);
    });

    el.addEventListener('mouseleave', () => {
      const bar = el.querySelector('.gtoast__progress');
      if (bar) bar.style.animationPlayState = 'running';
      setTimeout(() => {
        el.classList.add('hide');
        setTimeout(() => el.remove(), 400);
      }, 1500);
    });

    return el;
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function copyPw(btn) {
    const code = btn.previousElementSibling;
    if (!code) return;
    const isTi = hasTabler();
    navigator.clipboard.writeText(code.textContent).then(() => {
      btn.innerHTML = `<i class="${isTi ? 'ti ti-check' : 'fa-solid fa-check'}"></i>`;
      btn.style.color = '#10b981';
      setTimeout(() => {
        btn.innerHTML = `<i class="${isTi ? 'ti ti-copy' : 'fa-regular fa-copy'}"></i>`;
        btn.style.color = '';
      }, 2000);
    });
  }

  function success(message, title = 'Success', duration = 5000) {
    return create({ type: 'success', title, message, duration });
  }

  function danger(message, title = 'Error', duration = 6000) {
    return create({ type: 'danger', title, message, duration });
  }

  function warning(message, title = 'Warning', duration = 6000) {
    return create({ type: 'warning', title, message, duration });
  }

  function info(message, title = 'Info', duration = 5000) {
    return create({ type: 'info', title, message, duration });
  }

  return {
    create,
    success,
    danger,
    error: danger,
    warning,
    info,
    copyPw
  };
})();

// Universal fallback helper

window.showToast = function(message, type = 'success', title = '') {
  ToastStack.create({
    type: type === 'error' ? 'danger' : type,
    title: title || (type === 'error' ? 'Error' : 'Notification'),
    message: message
  });
};

// Shared notification API. Interactive prompts remain dialogs.
window.Feedback = {
  fire: function (options, message, icon) {
    var opts = typeof options === 'object' ? options : {title: options, text: message, icon: icon};
    if (opts.showCancelButton || opts.showDenyButton || opts.input || opts.preConfirm || opts.didOpen || opts.willOpen || !opts.icon || opts.icon === 'question') {
      return window.Swal.fire.apply(window.Swal, arguments);
    }
    if (window.Swal && window.Swal.isLoading && window.Swal.isLoading()) window.Swal.close();
    var text = opts.text || '';
    if (!text && opts.html) { var content = document.createElement('div'); content.innerHTML = opts.html; text = content.textContent; }
    var duration = Number(opts.timer) > 0 ? Number(opts.timer) : 5000;
    window.ToastStack.create({type:opts.icon, title:opts.title, message:text, duration:duration});
    // Keep existing post-feedback redirects and refreshes after the display interval.
    return new Promise(function(resolve) { setTimeout(function() { resolve({isConfirmed:true, isDismissed:false}); }, duration); });
  }
};
