(function () {
    'use strict';
  
    /* ---------- View routing ---------- */
  
    var views = ['login', 'signup', 'forgot', 'sent', 'reset', 'success'];
    var brandCopy = {
      login:   { heading: "Your keys.<br>Your voyage.", sub: "No custodian holds your assets. No gatekeeper approves your moves. Just you, and where you're headed." },
      signup:  { heading: "Chart your<br>own course.", sub: "Creating a wallet takes about a minute. Your recovery phrase is generated on this device and never leaves it unencrypted." },
      forgot:  { heading: "Lost your<br>bearings?", sub: "We'll help you get back to your wallet. Your keys stay exactly where they've always been — with you." },
      sent:    { heading: "Lost your<br>bearings?", sub: "We'll help you get back to your wallet. Your keys stay exactly where they've always been — with you." },
      reset:   { heading: "A fresh<br>password.", sub: "Choose a strong, unique password to keep your account secured." },
      success: { heading: "Land ho.", sub: "Welcome aboard." }
    };
  
    function showView(name, opts) {
      opts = opts || {};
      views.forEach(function (v) {
        var el = document.getElementById('view-' + v);
        if (!el) return;
        if (v === name) {
          el.hidden = false;
          el.classList.remove('view');
          void el.offsetWidth; 
          el.classList.add('view');
        } else {
          el.hidden = true;
        }
      });
  
      var copy = brandCopy[name] || brandCopy.login;
      var headingEl = document.getElementById('brand-heading');
      var subEl = document.getElementById('brand-subheading');
      if (headingEl) headingEl.innerHTML = copy.heading;
      if (subEl) subEl.textContent = copy.sub;
  
      if (name === 'sent' && opts.email) {
        var echo = document.getElementById('sent-email-echo');
        if (echo) echo.textContent = opts.email;
      }
  
      if (name === 'success') {
        var h = document.getElementById('success-heading');
        var s = document.getElementById('success-sub');
        if (opts.successHeading && h) h.textContent = opts.successHeading;
        if (opts.successSub && s) s.textContent = opts.successSub;
      }
  
      var url = new URL(window.location.href);
      url.searchParams.set('view', name);
      window.history.replaceState({}, '', url);
    }
  
    document.addEventListener('click', function (e) {
      var trigger = e.target.closest('[data-nav]');
      if (!trigger) return;
      e.preventDefault();
      showView(trigger.getAttribute('data-nav'));
    });
  
    var initialView = new URLSearchParams(window.location.search).get('view');
    showView(views.indexOf(initialView) !== -1 ? initialView : 'login');
  
    /* ---------- Show / hide password ---------- */
  
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-toggle-password]');
      if (!btn) return;
      var input = document.getElementById(btn.getAttribute('data-toggle-password'));
      if (!input) return;
      var isHidden = input.type === 'password';
      input.type = isHidden ? 'text' : 'password';
      btn.textContent = isHidden ? 'Hide' : 'Show';
    });
  
    /* ---------- Validation helpers ---------- */
  
    var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  
    function setFieldError(fieldId, message) {
      var input = document.getElementById(fieldId);
      var field = input ? input.closest('.field') : null;
      var errorEl = document.querySelector('[data-error-for="' + fieldId + '"]');
      if (message) {
        if (field) { field.classList.add('field-invalid'); field.classList.remove('field-valid'); }
        if (errorEl) { errorEl.textContent = message; errorEl.classList.add('visible'); }
        return false;
      } else {
        if (field) { field.classList.remove('field-invalid'); field.classList.add('field-valid'); }
        if (errorEl) { errorEl.textContent = ''; errorEl.classList.remove('visible'); }
        return true;
      }
    }
  
    function clearFieldState(fieldId) {
      var input = document.getElementById(fieldId);
      var field = input ? input.closest('.field') : null;
      var errorEl = document.querySelector('[data-error-for="' + fieldId + '"]');
      if (field) { field.classList.remove('field-invalid', 'field-valid'); }
      if (errorEl) { errorEl.textContent = ''; errorEl.classList.remove('visible'); }
    }
  
    function showBanner(bannerId, message, tone) {
      var el = document.getElementById(bannerId);
      if (!el) return;
      el.textContent = message;
      el.hidden = !message;
      el.classList.toggle('banner-locked', tone === 'locked');
    }
  
    function passwordScore(pw) {
      var score = 0;
      if (pw.length >= 8) score++;
      if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) score++;
      if (/\d/.test(pw)) score++;
      if (/[^A-Za-z0-9]/.test(pw)) score++;
      if (pw.length < 8) score = Math.min(score, 1);
      return score; // 0-4
    }
  
    var strengthWords = ['Too weak', 'Weak — add a number or symbol', 'Okay — try adding a symbol', 'Strong', 'Very strong'];
  
    function updateStrengthMeter(meterEl, labelEl, pw) {
      var score = passwordScore(pw);
      meterEl.className = 'strength-meter' + (pw ? ' s' + score : '');
      labelEl.textContent = pw ? strengthWords[score] : 'Use 8+ characters with a number and a symbol';
      return score;
    }
  
    function setLoading(button, isLoading) {
      button.classList.toggle('btn-loading', isLoading);
      button.disabled = isLoading;
    }
  
/* ---------- Login ---------- */
  var loginForm = document.getElementById('form-login');
  var loginLocked = false;

  loginForm.addEventListener('submit', function (e) {
    e.preventDefault();
    if (loginLocked) return;

    var email = document.getElementById('login-email');
    var password = document.getElementById('login-password');
    var submit = document.getElementById('login-submit');

    var emailOk = EMAIL_RE.test(email.value.trim())
      ? setFieldError('login-email', '')
      : setFieldError('login-email', 'Enter a valid email address');
    var passOk = password.value.length > 0
      ? setFieldError('login-password', '')
      : setFieldError('login-password', 'Enter your password');

    showBanner('login-banner', '');
    if (!emailOk || !passOk) return;

    setLoading(submit, true);

    fetch('/API/UserAPI.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        email: email.value.trim(),
        password: password.value
      })
    })
      .then(function (res) {
        return res.json().then(function (data) {
          return { httpStatus: res.status, data: data };
        });
      })
      .then(function (result) {
        setLoading(submit, false);
        var data = result.data;

        if (data.status === 'success') {
          showView('success', {
            successHeading: 'Welcome back',
            successSub: ''
          });
          return;
        }

        if (data.status === 'locked') {
          loginLocked = true;
          showBanner('login-banner', data.message, 'locked');
          loginForm.querySelectorAll('input, button').forEach(function (el) { el.disabled = true; });
          setTimeout(function () {
            loginLocked = false;
            loginForm.querySelectorAll('input, button').forEach(function (el) { el.disabled = false; });
            showBanner('login-banner', '');
          }, 30000);
          return;
        }

        showBanner('login-banner', data.message || 'Incorrect email or password.');
      })
      .catch(function () {
        setLoading(submit, false);
        showBanner('login-banner', 'Something went wrong. Please try again.');
      });
  });

    ['login-email', 'login-password'].forEach(function (id) {
      document.getElementById(id).addEventListener('input', function () { clearFieldState(id); showBanner('login-banner', ''); });
    });
  
   /* ---------- Signup ---------- */

var signupForm = document.getElementById('form-signup');
var signupMeter = signupForm.querySelector('.strength-meter');
var signupStrengthLabel = document.getElementById('signup-strength-label');
var signupScore = 0;

document.getElementById('signup-password').addEventListener('input', function (e) {
  signupScore = updateStrengthMeter(signupMeter, signupStrengthLabel, e.target.value);
  clearFieldState('signup-password');
});

signupForm.addEventListener('submit', function (e) {
  e.preventDefault();

  var name = document.getElementById('signup-name');
  var email = document.getElementById('signup-email');
  var password = document.getElementById('signup-password');
  var confirm = document.getElementById('signup-confirm');
  var pin = document.getElementById('signup-pin');
  var pinConfirm = document.getElementById('signup-pin-confirm');
  var terms = document.getElementById('signup-terms');
  var submit = document.getElementById('signup-submit');

  var PIN_RE = /^\d{6}$/;

  var nameOk = name.value.trim().length >= 2
    ? setFieldError('signup-name', '')
    : setFieldError('signup-name', 'Enter your name');

  var emailOk = EMAIL_RE.test(email.value.trim())
    ? setFieldError('signup-email', '')
    : setFieldError('signup-email', 'Enter a valid email address');

  var passOk = passwordScore(password.value) >= 2
    ? setFieldError('signup-password', '')
    : setFieldError('signup-password', 'Password is too weak');

  var confirmOk = confirm.value.length > 0 && confirm.value === password.value
    ? setFieldError('signup-confirm', '')
    : setFieldError('signup-confirm', 'Passwords don\'t match');
    
  var pinOk = PIN_RE.test(pin.value)
    ? setFieldError('signup-pin', '')
    : setFieldError('signup-pin', 'Enter a 6-digit PIN');

  var pinConfirmOk = pinConfirm.value.length > 0 && pinConfirm.value === pin.value
    ? setFieldError('signup-pin-confirm', '')
    : setFieldError('signup-pin-confirm', 'PINs don\'t match');

  var termsOk = terms.checked
    ? setFieldError('signup-terms', '')
    : setFieldError('signup-terms', 'You must accept this to continue');

  showBanner('signup-banner', '');
  if (!nameOk || !emailOk || !passOk || !confirmOk || !pinOk || !pinConfirmOk || !termsOk) return;
  setLoading(submit, true);

  fetch('/src/Router/Router.php?action=register', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      full_name: name.value.trim(),
      email: email.value.trim(),
      password: password.value,
      confirm_password: confirm.value,
      wallet_transaction_pin: pin.value
    })
  })
    .then(function (res) {
      return res.json().then(function (data) {
        return { httpStatus: res.status, data: data };
      });
    })
    .then(function (result) {
      setLoading(submit, false);
      var data = result.data;

      if (data.status === 'success') {
        showView('success', {
          successHeading: 'Wallet created',
          successSub: 'Your wallet is ready.'
        });
        return;
      }

      if (result.httpStatus === 409) {
        setFieldError('signup-email', data.message || 'An account with that email already exists');
          return;
      }

        showBanner('signup-banner', data.message || 'Something went wrong. Please try again.');
      })
      .catch(function () {
        setLoading(submit, false);
        showBanner('signup-banner', 'Something went wrong. Please try again.');
      });
  });

  ['signup-name', 'signup-email', 'signup-confirm', 'signup-pin', 'signup-pin-confirm'].forEach(function (id) {
  document.getElementById(id).addEventListener('input', function () { clearFieldState(id); });
  });
  
  /* ---------- Forgot password ---------- */
  
    var forgotForm = document.getElementById('form-forgot');
  
    forgotForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var email = document.getElementById('forgot-email');
      var submit = document.getElementById('forgot-submit');
  
      var emailOk = EMAIL_RE.test(email.value.trim())
        ? setFieldError('forgot-email', '')
        : setFieldError('forgot-email', 'Enter a valid email address');
  
      showBanner('forgot-banner', '');
      if (!emailOk) return;
  
      setLoading(submit, true);
      setTimeout(function () {
        setLoading(submit, false);
        showView('sent', { email: email.value.trim() });
      }, 700);
    });
  
    document.getElementById('forgot-email').addEventListener('input', function () { clearFieldState('forgot-email'); });
  
    /* ---------- Reset password ---------- */
  
    var resetForm = document.getElementById('form-reset');
    var resetMeter = resetForm.querySelector('.strength-meter');
    var resetStrengthLabel = document.getElementById('reset-strength-label');
  
    document.getElementById('reset-password').addEventListener('input', function (e) {
      updateStrengthMeter(resetMeter, resetStrengthLabel, e.target.value);
      clearFieldState('reset-password');
    });
  
    resetForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var password = document.getElementById('reset-password');
      var confirm = document.getElementById('reset-confirm');
      var submit = document.getElementById('reset-submit');
  
      var passOk = passwordScore(password.value) >= 2
        ? setFieldError('reset-password', '')
        : setFieldError('reset-password', 'Password is too weak');
  
      var confirmOk = confirm.value.length > 0 && confirm.value === password.value
        ? setFieldError('reset-confirm', '')
        : setFieldError('reset-confirm', 'Passwords don\'t match');
  
      showBanner('reset-banner', '');
      if (!passOk || !confirmOk) return;
  
      setLoading(submit, true);
      setTimeout(function () {
        setLoading(submit, false);
        showView('success', {
          successHeading: 'Password reset',
          successSub: 'Your password has been changed. Sign in with your new password.'
        });
      }, 700);
    });
  
    document.getElementById('reset-confirm').addEventListener('input', function () { clearFieldState('reset-confirm'); });
  
  })();