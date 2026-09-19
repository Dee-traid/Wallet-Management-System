(function () {
    'use strict';
  
    /* ---------- Date in topbar ---------- */
    var dateEl = document.getElementById('topbar-date');
    if (dateEl) {
      dateEl.textContent = new Date().toLocaleDateString(undefined, {
        weekday: 'long', month: 'long', day: 'numeric'
      });
    }
  
    /* ---------- Sidebar toggle (mobile) ---------- */
    var shell = document.getElementById('dashboard-shell');
    var toggleBtn = document.getElementById('sidebar-toggle');
    if (toggleBtn) {
      toggleBtn.addEventListener('click', function () {
        var isOpen = shell.classList.toggle('sidebar-open');
        toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });
    }
    document.querySelectorAll('[data-nav-item]').forEach(function (link) {
      link.addEventListener('click', function () {
        shell.classList.remove('sidebar-open');
        document.querySelectorAll('.nav-item').forEach(function (n) { n.classList.remove('is-active'); });
        link.classList.add('is-active');
      });
    });
  
    /* ---------- Modals ---------- */
    var lastFocused = null;
  
    function openModal(id, prefill) {
      var modal = document.getElementById(id);
      if (!modal) return;
      lastFocused = document.activeElement;
      modal.hidden = false;
      shell.classList.remove('sidebar-open');
      if (prefill) {
        var toField = modal.querySelector('input[name="to"], input[name="from"]');
        if (toField) toField.value = prefill;
      }
      var firstInput = modal.querySelector('input, select');
      if (firstInput) firstInput.focus();
    }
  
    function closeModal(modal) {
      modal.hidden = true;
      if (lastFocused) lastFocused.focus();
    }
  
    document.addEventListener('click', function (e) {
      var opener = e.target.closest('[data-open-modal]');
      if (opener) {
        e.preventDefault();
        openModal(opener.getAttribute('data-open-modal'), opener.getAttribute('data-contact'));
        return;
      }
      var closer = e.target.closest('[data-close-modal]');
      if (closer) {
        closeModal(closer.closest('.modal-overlay'));
        return;
      }
      if (e.target.classList.contains('modal-overlay')) {
        closeModal(e.target);
      }
    });
  
    document.addEventListener('keydown', function (e) {
      if (e.key !== 'Escape') return;
      document.querySelectorAll('.modal-overlay').forEach(function (m) {
        if (!m.hidden) closeModal(m);
      });
    });
  
    /* ---------- Validation helpers ---------- */
  
    function setFieldError(fieldId, message) {
      var input = document.getElementById(fieldId);
      var field = input ? input.closest('.field') : null;
      var errorEl = document.querySelector('[data-error-for="' + fieldId + '"]');
      if (message) {
        if (field) field.classList.add('field-invalid');
        if (errorEl) { errorEl.textContent = message; errorEl.classList.add('visible'); }
        return false;
      } else {
        if (field) field.classList.remove('field-invalid');
        if (errorEl) { errorEl.textContent = ''; errorEl.classList.remove('visible'); }
        return true;
      }
    }
  
    function isValidAmount(value) {
      var n = parseFloat(value);
      return value.trim().length > 0 && !isNaN(n) && n > 0;
    }
  
    function setLoading(button, isLoading) {
      button.classList.toggle('btn-loading', isLoading);
      button.disabled = isLoading;
    }
  
    function showBanner(id, message) {
      var el = document.getElementById(id);
      if (!el) return;
      el.textContent = message;
      el.hidden = !message;
    }
  
    function prependTransaction(name, meta, amountText, direction) {
      var list = document.getElementById('tx-list');
      if (!list) return;
      var li = document.createElement('li');
      li.className = 'tx-row';
      var iconClass = direction === 'in' ? 'tx-icon-in' : 'tx-icon-out';
      var amountClass = direction === 'in' ? 'tx-amount-in' : 'tx-amount-out';
      var arrowPath = direction === 'in'
        ? '<path d="M17 7 L7 17 M7 9 V17 H15" stroke-linecap="round" stroke-linejoin="round"/>'
        : '<path d="M7 17 L17 7 M9 7 H17 V15" stroke-linecap="round" stroke-linejoin="round"/>';
      li.innerHTML =
        '<span class="tx-icon ' + iconClass + '"><svg viewBox="0 0 24 24" width="16" height="16">' + arrowPath + '</svg></span>' +
        '<span class="tx-info"><span class="tx-name">' + name + '</span><span class="tx-meta">' + meta + '</span></span>' +
        '<span class="tx-amount ' + amountClass + '">' + amountText + '</span>';
      list.insertBefore(li, list.firstChild);
    }
  
    /* ---------- Send form ---------- */
    var sendForm = document.getElementById('form-send');
    if (sendForm) {
      sendForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var to = document.getElementById('send-to');
        var amount = document.getElementById('send-amount');
        var submit = document.getElementById('send-submit');
  
        var toOk = to.value.trim().length > 0
          ? setFieldError('send-to', '')
          : setFieldError('send-to', 'Enter a recipient');
        var amountOk = isValidAmount(amount.value)
          ? setFieldError('send-amount', '')
          : setFieldError('send-amount', 'Enter a valid amount');
  
        showBanner('send-banner', '');
        if (!toOk || !amountOk) return;
  
        setLoading(submit, true);
        setTimeout(function () {
          setLoading(submit, false);
          prependTransaction(to.value.trim(), 'Sent · Just now', '-$' + parseFloat(amount.value).toFixed(2), 'out');
          showBanner('send-banner', 'Sent $' + parseFloat(amount.value).toFixed(2) + ' to ' + to.value.trim() + '.');
          sendForm.reset();
          setTimeout(function () { closeModal(document.getElementById('send-modal')); showBanner('send-banner', ''); }, 1100);
        }, 700);
      });
      ['send-to', 'send-amount'].forEach(function (id) {
        document.getElementById(id).addEventListener('input', function () { setFieldError(id, ''); });
      });
    }
  
    /* ---------- Top up form ---------- */
    var topupForm = document.getElementById('form-topup');
    if (topupForm) {
      topupForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var amount = document.getElementById('topup-amount');
        var source = document.getElementById('topup-source');
        var submit = document.getElementById('topup-submit');
  
        var amountOk = isValidAmount(amount.value)
          ? setFieldError('topup-amount', '')
          : setFieldError('topup-amount', 'Enter a valid amount');
  
        showBanner('topup-banner', '');
        if (!amountOk) return;
  
        setLoading(submit, true);
        setTimeout(function () {
          setLoading(submit, false);
          prependTransaction('Top up', 'From ' + source.value + ' · Just now', '+$' + parseFloat(amount.value).toFixed(2), 'in');
          showBanner('topup-banner', 'Added $' + parseFloat(amount.value).toFixed(2) + ' to your balance.');
          topupForm.reset();
          setTimeout(function () { closeModal(document.getElementById('topup-modal')); showBanner('topup-banner', ''); }, 1100);
        }, 700);
      });
      document.getElementById('topup-amount').addEventListener('input', function () { setFieldError('topup-amount', ''); });
    }
  
    /* ---------- Request form ---------- */
    var requestForm = document.getElementById('form-request');
    if (requestForm) {
      requestForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var from = document.getElementById('request-from');
        var amount = document.getElementById('request-amount');
        var submit = document.getElementById('request-submit');
  
        var fromOk = from.value.trim().length > 0
          ? setFieldError('request-from', '')
          : setFieldError('request-from', 'Enter who you\'re requesting from');
        var amountOk = isValidAmount(amount.value)
          ? setFieldError('request-amount', '')
          : setFieldError('request-amount', 'Enter a valid amount');
  
        showBanner('request-banner', '');
        if (!fromOk || !amountOk) return;
  
        setLoading(submit, true);
        setTimeout(function () {
          setLoading(submit, false);
          showBanner('request-banner', 'Request sent to ' + from.value.trim() + '.');
          requestForm.reset();
          setTimeout(function () { closeModal(document.getElementById('request-modal')); showBanner('request-banner', ''); }, 1100);
        }, 700);
      });
      ['request-from', 'request-amount'].forEach(function (id) {
        document.getElementById(id).addEventListener('input', function () { setFieldError(id, ''); });
      });
    }
  
    /* ---------- Copy Thorfinn ID ---------- */
    var copyBtn = document.getElementById('copy-id-btn');
    if (copyBtn) {
      copyBtn.addEventListener('click', function () {
        var value = document.getElementById('receive-id-value').textContent;
        var done = function () {
          copyBtn.textContent = 'Copied';
          setTimeout(function () { copyBtn.textContent = 'Copy'; }, 1500);
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(value).then(done).catch(done);
        } else {
          done();
        }
      });
    }
  
  })();