(() => {
  'use strict';

  // Panel toggles
  const signUpButton = document.getElementById('signUp');
  const signInButton = document.getElementById('signIn');
  const container = document.getElementById('container');

  if (signUpButton && container) {
    signUpButton.addEventListener('click', () => {
      container.classList.add('right-panel-active');
    });
  }

  if (signInButton && container) {
    signInButton.addEventListener('click', () => {
      container.classList.remove('right-panel-active');
    });
  }

  // Navbar / Theme
  const navbar = document.getElementById('navbar');
  const toggle = document.getElementById('switch'); // theme switch
  const html = document.documentElement;
  const savedTheme = localStorage.getItem('theme');

  function OpenSideBar() { if (navbar) navbar.classList.add('show'); }
  function CloseSideBar() { if (navbar) navbar.classList.remove('show'); }
  window.OpenSideBar = OpenSideBar;
  window.CloseSideBar = CloseSideBar;

  // Apply theme and set checkbox mapping (checked = dark)
  function applyTheme(theme) {
    if (!html) return;
    html.setAttribute('data-theme', theme);
    if (toggle) toggle.checked = theme === 'dark';
  }

  if (savedTheme) {
    applyTheme(savedTheme);
  } else {
    applyTheme('dark');
  }

  if (toggle) {
    toggle.addEventListener('change', () => {
      const theme = (toggle.checked) ? 'dark' : 'light';
      applyTheme(theme);
      try { localStorage.setItem('theme', theme); } catch (e) {}
    });
  }

  // Sidebar controls
  const openBtn = document.getElementById('open-sidebar-button');
  const closeBtn = document.getElementById('close-sidebar-button');
  const overlay = document.getElementById('overlay');

  if (openBtn) openBtn.addEventListener('click', OpenSideBar);
  if (closeBtn) closeBtn.addEventListener('click', CloseSideBar);
  if (overlay) overlay.addEventListener('click', CloseSideBar);

  // Blob interaction
  const blobs = Array.from(document.querySelectorAll('.blob-dodge'));
  if (blobs.length) {
    let pointerX = window.innerWidth / 2;
    let pointerY = window.innerHeight / 2;
    const repelRadius = 280;
    const repelPower = 5.0;
    const attractPower = 0.15;

    function updatePointer(e) {
      if (!e) return;
      if (e.touches && e.touches[0]) {
        pointerX = e.touches[0].clientX;
        pointerY = e.touches[0].clientY;
      } else {
        pointerX = e.clientX;
        pointerY = e.clientY;
      }
    }

    function step() {
      blobs.forEach(blob => {
        const rect = blob.getBoundingClientRect();
        const cx = rect.left + rect.width / 2;
        const cy = rect.top + rect.height / 2;
        const dx = cx - pointerX;
        const dy = cy - pointerY;
        const dist = Math.hypot(dx, dy);

       if (dist < repelRadius) {
        const force = (repelRadius - dist) / repelRadius;
       blob.style.setProperty('--dx', `${dx * force * repelPower}px`);
       blob.style.setProperty('--dy', `${dy * force * repelPower}px`);
}      else {
       blob.style.setProperty('--dx', `${-dx * attractPower}px`);
       blob.style.setProperty('--dy', `${-dy * attractPower}px`);
            }

      });
      requestAnimationFrame(step);
    }

    document.addEventListener('mousemove', updatePointer, { passive: true });
    document.addEventListener('touchmove', updatePointer, { passive: true });
    requestAnimationFrame(step);
  }

  // Form validation
  const signupForm = document.querySelector('.sign-up-container form');
  const signinForm = document.querySelector('.sign-in-container form');

  function showError(input, message) {
    let errorEl = input.parentNode.querySelector('.error');
    if (!errorEl) {
      errorEl = document.createElement('p');
      errorEl.className = 'error';
      input.parentNode.insertBefore(errorEl, input.nextSibling);
    }
    errorEl.textContent = message;
  }

  function clearErrors(form) {
    form.querySelectorAll('.error').forEach(el => el.remove());
  }

  function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  }

  if (signupForm) {
    signupForm.addEventListener('submit', function(e) {
      clearErrors(this);
      let valid = true;

      const firstName = this.querySelector('input[name="first_name"]');
      const lastName = this.querySelector('input[name="last_name"]');
      const email = this.querySelector('input[name="email"]');
      const password = this.querySelector('input[name="password"]');

      if (!firstName.value.trim() || firstName.value.trim().length < 2) {
        showError(firstName, 'First name must be at least 2 characters.');
        valid = false;
      }
      if (!lastName.value.trim() || lastName.value.trim().length < 2) {
        showError(lastName, 'Last name must be at least 2 characters.');
        valid = false;
      }
      if (!validateEmail(email.value.trim())) {
        showError(email, 'Please enter a valid email address.');
        valid = false;
      }
      if (password.value.length < 6) {
        showError(password, 'Password must be at least 6 characters.');
        valid = false;
      }

      if (!valid) {
        e.preventDefault();
      }
    });
  }

  if (signinForm) {
    signinForm.addEventListener('submit', function(e) {
      clearErrors(this);
      let valid = true;

      const email = this.querySelector('input[name="email"]');
      const password = this.querySelector('input[name="password"]');

      if (!validateEmail(email.value.trim())) {
        showError(email, 'Please enter a valid email address.');
        valid = false;
      }
      if (!password.value.trim()) {
        showError(password, 'Please enter your password.');
        valid = false;
      }

      if (!valid) {
        e.preventDefault();
      }
    });
  }

})();