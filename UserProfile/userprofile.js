(() => {
  'use strict';

  console.log('userprofile.js loaded');

  // Dummy user-data (replace with real API data)
  const user = {
    gymCode: ' ',
    username: ' ',
    name: ' ',
    email: '',
    phone: ' ',
    addresses: [' '],
    subscription: {
      type: ' ',
      start: '2025-11-01T00:00:00Z',
      end: '2026-02-01T00:00:00Z',
      frozen: false
    },
    profileUrl: window.location.href
  };

  // ===== MEDICAL WIZARD LOGIC =====
  let currentWizardStep = 0;
  const WIZARD_STEPS = ['ageweight', 'conditions1', 'conditions2'];

  function showWizardStep(stepIndex) {
    console.log('showWizardStep called with', stepIndex);
    document.querySelectorAll('[data-wizard-step]').forEach((el, i) => {
      el.style.display = (i === stepIndex) ? 'flex' : 'none';
      console.log('Setting step', i, 'to', el.style.display);
    });
  }

  window.nextWizardStep = function() {
    console.log('nextWizardStep called, current step:', currentWizardStep);
    const step = WIZARD_STEPS[currentWizardStep];

    // Validate age/weight step
    if (step === 'ageweight') {
      const age = document.getElementById('wizard-age')?.value?.trim();
      const weight = document.getElementById('wizard-weight')?.value?.trim();
      const height = document.getElementById('wizard-height')?.value?.trim();
      const experience = document.getElementById('wizard-experience')?.value?.trim();
      if (!age || parseInt(age, 10) <= 0) {
        alert('Please enter a valid age');
        return;
      }
      if (!weight || parseFloat(weight) <= 0) {
        alert('Please enter a valid weight');
        return;
      }
      if (!height || parseFloat(height) <= 0) {
        alert('Please enter a valid height');
        return;
      }
      if (!experience) {
        alert('Please select your experience level');
        return;
      }
    }

    // Move to next step or complete wizard
    if (currentWizardStep < WIZARD_STEPS.length - 1) {
      currentWizardStep++;
      console.log('Incrementing to step', currentWizardStep);
      showWizardStep(currentWizardStep);
    } else {
      completeWizard();
    }
  };

  window.skipWizardField = function() {
    // Just move to next field without validation
    if (currentWizardStep < WIZARD_STEPS.length - 1) {
      currentWizardStep++;
      showWizardStep(currentWizardStep);
    } else {
      completeWizard();
    }
  };

  async function completeWizard() {
    const age = document.getElementById('wizard-age')?.value || '';
    const weight = document.getElementById('wizard-weight')?.value || '';
    const height = document.getElementById('wizard-height')?.value || '';
    const experience = document.getElementById('wizard-experience')?.value || '';
    const notes = document.getElementById('wizard-notes')?.value || '';

    // Gather checked conditions (include notes if present)
    const condEls = Array.from(document.querySelectorAll('input[name="wizard-condition"]'));
    const conditions = condEls.filter(ch => ch.checked).map(ch => {
      const id = ch.id.replace('cond_', '');
      const fieldMap = {
        heart: 'Has_Heart_Condition',
        diabetes: 'Has_Diabetes',
        asthma: 'Has_Asthma',
        thyroid: 'Has_Thyroid_Disorder',
        cholesterol: 'Has_High_Cholesterol',
        back: 'Has_Back_Injury',
        neck: 'Has_Neck_Injury',
        lactose: 'Has_lactose_intolerance',
        gluten: 'Has_gluten_intolerance',
        nut: 'Has_nut_Allergy',
        egg: 'Has_egg_allergy',
        surgeries: 'has_recent_surgery'
      };
      const dbField = fieldMap[id] || id;
      const noteEl = document.getElementById(`cond_note_${id}`);
      return { id: dbField, note: noteEl ? (noteEl.value || '') : '' };
    });

    const medical = { age, weight, height, experience, conditions, notes };

    // Save to server
    try {
      const response = await fetch('save_medical.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(medical)
      });

      // Check if response is OK
      if (!response.ok) {
        const errorText = await response.text();
        console.error('HTTP Error:', response.status, errorText);
        alert('Failed to save medical profile: HTTP ' + response.status);
        return;
      }

      const result = await response.json();
      
      // Check if result is valid
      if (!result) {
        console.error('Invalid response from server');
        alert('Invalid response from server');
        return;
      }

      if (!result.success) {
        const errorMsg = result.error || result.message || 'Unknown error';
        console.error('Save failed:', result);
        alert('Failed to save medical profile: ' + errorMsg);
        return;
      }

      console.log('Medical profile saved successfully:', result);
    } catch (e) {
      console.error('Error saving medical profile:', e);
      alert('Error saving medical profile: ' + e.message);
      return;
    }

    // Hide wizard
    const wizard = document.getElementById('medicalWizard');
    if (wizard) wizard.style.display = 'none';

    // Populate medical form panel for editing
    populateMedicalForm();
  }

  function checkAndShowWizard() {
    const hasMedical = localStorage.getItem('medicalProfile');
    const wizard = document.getElementById('medicalWizard');

    if (!hasMedical && wizard) {
      currentWizardStep = 0;
      showWizardStep(0);
      wizard.style.display = 'flex';
      return true;
    } else if (wizard) {
      wizard.style.display = 'none';
      return false;
    }
    return false;
  }

  // NAV elements
  const navbar = document.getElementById('navbar');
  const openBtn = document.getElementById('open-sidebar-button');
  const closeBtn = document.getElementById('close-sidebar-button');
  const overlay = document.getElementById('overlay');
  const themeToggle = document.getElementById('switch');
  const html = document.documentElement;
  const savedTheme = localStorage.getItem('theme');

  // Nav functions
  function OpenSideBar() { if (navbar) navbar.classList.add('show'); }
  function CloseSideBar() { if (navbar) navbar.classList.remove('show'); }
  window.OpenSideBar = OpenSideBar;
  window.CloseSideBar = CloseSideBar;

  if (openBtn) openBtn.addEventListener('click', OpenSideBar);
  if (closeBtn) closeBtn.addEventListener('click', CloseSideBar);
  if (overlay) overlay.addEventListener('click', CloseSideBar);

  if (themeToggle && html) {
    if (savedTheme) {
      html.setAttribute('data-theme', savedTheme);
      themeToggle.checked = savedTheme === 'dark';
    } else {
      html.setAttribute('data-theme', 'dark');
      themeToggle.checked = true;
    }
    themeToggle.addEventListener('change', () => {
      if (themeToggle.checked) {
        html.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
      } else {
        html.setAttribute('data-theme', 'light');
        localStorage.setItem('theme', 'light');
      }
    });
  }

  // DOM refs (profile)
  const gymCodeEl = document.getElementById('gymCode');
  const nameEl = document.getElementById('name');
  const emailEl = document.getElementById('email');
  const phoneEl = document.getElementById('phone');
  const addressesEl = document.getElementById('addresses');
  const avatarEl = document.getElementById('avatar');
  const avatarInput = document.getElementById('avatarInput');
  const uploadBtn = document.getElementById('uploadBtn');
  const qrImg = document.getElementById('qrImg');

  const subTypeEl = document.getElementById('subType');
  const subDatesEl = document.getElementById('subDates');
  const timeValueEl = document.getElementById('timeValue');
  const timeRingEl = document.getElementById('timeRing');
  const frozenNotice = document.getElementById('frozenNotice');

  // Medical panel refs (we'll re-query specific buttons later to attach listeners defensively)
  const medicalPanel = document.getElementById('medicalPanel');
  const medicalForm = document.getElementById('medicalForm');

  // Populate fields
  async function populateUser() {
    try {
      const response = await fetch('get_user.php');
      const userData = await response.json();
      if (userData) {
        // Update global user object
        Object.assign(user, userData);
        
        gymCodeEl.textContent = user.gymCode || '—';
        nameEl.textContent = user.name || '—';
        emailEl.textContent = user.email || '—';
        phoneEl.textContent = user.phone || '—';
        addressesEl.innerHTML = '';
        (user.addresses || []).forEach(a => {
          const li = document.createElement('li');
          li.textContent = a;
          addressesEl.appendChild(li);
        });
        subTypeEl.textContent = user.subscription.type || '—';
        // Generate QR code if profileUrl is available
        if (user.profileUrl) {
          generateQR(user.profileUrl);
        }
        updateSubscriptionUI();
        await populateMedicalForm();
        await loadProfilePic();
      }
    } catch (e) {
      console.error('Failed to load user data', e);
    }
  }

  async function populateMedicalForm() {
    const m = await loadMedical();
    if (m && medicalForm) {
      const weightEl = medicalForm.querySelector('#weight');
      if (weightEl) weightEl.value = m.weight || '';
      const ageEl = medicalForm.querySelector('#age');
      if (ageEl) ageEl.value = m.age || '';
      // set condition checkboxes in medical panel
      try {
        const conds = m.conditions || [];
        const fieldMap = {
          heart: 'Has_Heart_Condition',
          diabetes: 'Has_Diabetes',
          asthma: 'Has_Asthma',
          thyroid: 'Has_Thyroid_Disorder',
          cholesterol: 'Has_High_Cholesterol',
          back: 'Has_Back_Injury',
          neck: 'Has_Neck_Injury',
          lactose: 'Has_lactose_intolerance',
          gluten: 'Has_gluten_intolerance',
          nut: 'Has_nut_Allergy',
          egg: 'Has_egg_allergy',
          surgeries: 'has_recent_surgery'
        };
        Object.keys(fieldMap).forEach(k => {
          const el = document.getElementById(`m_cond_${k}`);
          const noteEl = document.getElementById(`m_note_${k}`);
          if (el) {
            const found = conds.some(c => c.id === fieldMap[k]);
            el.checked = !!found;
          }
          if (noteEl) {
            const foundObj = conds.find(c => c.id === fieldMap[k]);
            noteEl.value = foundObj ? (foundObj.note || '') : '';
            noteEl.style.display = (el && el.checked) ? 'block' : 'none';
          }
        });
      } catch (e) {}
      const heightEl = medicalForm.querySelector('#height');
      if (heightEl) heightEl.value = m.height || '';
    }
  }

  // Toggle visibility of per-condition note input when checkbox toggles
  function attachConditionNoteToggles(root = document) {
    const chs = Array.from(root.querySelectorAll('input[type="checkbox"][id^="cond_"]'));
    chs.forEach(ch => {
      ch.addEventListener('change', () => {
        const id = ch.id.replace('cond_', '');
        const note = document.getElementById(`cond_note_${id}`);
        if (note) note.style.display = ch.checked ? 'block' : 'none';
      });
    });

    const phs = Array.from(root.querySelectorAll('input[type="checkbox"][id^="m_cond_"]'));
    phs.forEach(ch => {
      ch.addEventListener('change', () => {
        const id = ch.id.replace('m_cond_', '');
        const note = document.getElementById(`m_note_${id}`);
        if (note) note.style.display = ch.checked ? 'block' : 'none';
      });
    });
  }

  function generateQR(url) {
    if (!url || !qrImg) return;
    try {
      const encoded = encodeURIComponent(url);
      const size = 300;
      const qrUrl = `https://chart.googleapis.com/chart?chs=${size}x${size}&cht=qr&chl=${encoded}&chld=L|1&choe=UTF-8`;
      qrImg.src = qrUrl;
      // Add error handler to fallback to original image if QR generation fails
      qrImg.onerror = function() {
        console.warn('QR code generation failed, using fallback image');
        qrImg.src = 'Image/qr-code.png';
      };
    } catch (e) {
      console.error('Error generating QR code:', e);
      if (qrImg) qrImg.src = 'Image/qr-code.png';
    }
  }

  async function loadProfilePic() {
    try {
      const response = await fetch('get_profile_pic.php');
      const data = await response.json();
      if (data.url && avatarEl) {
        avatarEl.src = data.url;
        try { localStorage.setItem('profileAvatar', data.url); } catch {}
      } else {
        const savedAvatar = localStorage.getItem('profileAvatar');
        if (savedAvatar && avatarEl) avatarEl.src = savedAvatar;
      }
    } catch (e) {
      const savedAvatar = localStorage.getItem('profileAvatar');
      if (savedAvatar && avatarEl) avatarEl.src = savedAvatar;
    }
  }

  // Avatar upload
  uploadBtn?.addEventListener('click', () => avatarInput?.click());
  avatarInput?.addEventListener('change', (e) => {
    const f = e.target.files?.[0];
    if (!f) return;
    const formData = new FormData();
    formData.append('profile_pic', f);
    fetch('upload_profile_pic.php', {
      method: 'POST',
      body: formData
    }).then(response => response.json()).then(data => {
      if (data.success && data.url) {
        if (avatarEl) avatarEl.src = data.url;
        try { localStorage.setItem('profileAvatar', data.url); } catch {}
      }
    }).catch(() => {});
  });
  // Load from DB or localStorage
  loadProfilePic();

  // Subscription UI
  function updateSubscriptionUI() {
    if (!user.subscription.start || !user.subscription.end) {
      if (timeValueEl) timeValueEl.textContent = '—';
      if (subDatesEl) subDatesEl.textContent = 'No active subscription';
      return;
    }
    
    const start = new Date(user.subscription.start);
    const end = new Date(user.subscription.end);
    const now = new Date();
    const total = Math.max(1, end - start);
    let remaining = end - now;
    if (remaining < 0) remaining = 0;
    const daysLeft = Math.ceil(remaining / (1000 * 60 * 60 * 24));
    const percent = Math.max(0, Math.min(100, (remaining / total) * 100));
    const deg = (percent / 100) * 360;

    const accent = getComputedStyle(document.documentElement).getPropertyValue('--accent-secondary').trim() || '#a66fff';
    const fallback = '#913ef0';
    if (timeRingEl) timeRingEl.style.background = `conic-gradient(${accent || fallback} ${deg}deg, rgba(255,255,255,0.04) ${deg}deg)`;

    if (timeValueEl) timeValueEl.textContent = daysLeft;
    if (subDatesEl) subDatesEl.textContent = `Subscribed: ${start.toLocaleDateString()} — ${end.toLocaleDateString()}`;

    // frozen state
    if (user.subscription.frozen) {
      if (frozenNotice) frozenNotice.classList.remove('hidden');
      const freezeBtn = document.getElementById('toggleFreeze');
      if (freezeBtn) { freezeBtn.textContent = 'Unfreeze'; freezeBtn.classList.add('danger'); }
    } else {
      if (frozenNotice) frozenNotice.classList.add('hidden');
      const freezeBtn = document.getElementById('toggleFreeze');
      if (freezeBtn) { freezeBtn.textContent = 'Freeze'; freezeBtn.classList.remove('danger'); }
    }
  }

  // Print & Freeze listeners - attach defensively at runtime
  {
    const btn = document.getElementById('printReceipt');
    if (btn) {
      btn.addEventListener('click', () => {
        const receipt = `
      <html>
      <head><title>Receipt</title></head>
      <body style="font-family:Inter, Arial; padding:24px;">
      <h2>Gym Receipt</h2>
      <p><strong>Name:</strong> ${user.name}</p>
      <p><strong>Username:</strong> ${user.gymCode}</p>
      <p><strong>Subscription:</strong> ${user.subscription.type}</p>
      <p><strong>From:</strong> ${new Date(user.subscription.start).toLocaleDateString()}</p>
      <p><strong>To:</strong> ${new Date(user.subscription.end).toLocaleDateString()}</p>
      <hr />
      <p>Thanks for training with us.</p>
      </body>
      </html>`;
        const w = window.open('', '_blank', 'width=600,height=800');
        if (!w) return;
        w.document.write(receipt);
        w.document.close();
        w.focus();
        w.print();
        setTimeout(() => w.close(), 100);
      });
    } else console.warn('printReceipt button not found');
  }

  // Freeze subscription functionality
  {
    const btn = document.getElementById('toggleFreeze');
    const freezeModal = document.getElementById('freezeModal');
    const freezeForm = document.getElementById('freezeForm');
    const closeFreezeModal = document.getElementById('closeFreezeModal');
    const cancelFreeze = document.getElementById('cancelFreeze');
    
    function showFreezeModal() {
      if (!freezeModal) return;
      freezeModal.classList.remove('hidden');
      // Set minimum date to today
      const today = new Date().toISOString().split('T')[0];
      const startDateInput = document.getElementById('freezeStartDate');
      const endDateInput = document.getElementById('freezeEndDate');
      if (startDateInput) {
        startDateInput.min = today;
        startDateInput.value = today;
        // Update end date min when start date changes
        startDateInput.addEventListener('change', function() {
          if (endDateInput && this.value) {
            endDateInput.min = this.value;
            if (endDateInput.value && endDateInput.value < this.value) {
              endDateInput.value = this.value;
            }
          }
        });
      }
      if (endDateInput) {
        endDateInput.min = today;
      }
    }
    
    function hideFreezeModal() {
      if (!freezeModal) return;
      freezeModal.classList.add('hidden');
      if (freezeForm) freezeForm.reset();
    }
    
    if (btn) {
      btn.addEventListener('click', () => {
        if (user.subscription.frozen) {
          // Unfreeze
          if (confirm('Are you sure you want to unfreeze your subscription?')) {
            unfreezeSubscription();
          }
        } else {
          // Show freeze modal
          showFreezeModal();
        }
      });
    } else console.warn('toggleFreeze button not found');
    
    if (closeFreezeModal) {
      closeFreezeModal.addEventListener('click', hideFreezeModal);
    }
    
    if (cancelFreeze) {
      cancelFreeze.addEventListener('click', hideFreezeModal);
    }
    
    if (freezeModal) {
      freezeModal.addEventListener('click', (e) => {
        if (e.target === freezeModal) {
          hideFreezeModal();
        }
      });
    }
    
    if (freezeForm) {
      freezeForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(freezeForm);
        const data = {
          start_date: formData.get('start_date'),
          end_date: formData.get('end_date'),
          reason: formData.get('reason')
        };
        
        try {
          const response = await fetch('freeze_subscription.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
          });
          const result = await response.json();
          
          if (result.success) {
            alert('Subscription frozen successfully!');
            hideFreezeModal();
            // Reload user data
            await populateUser();
          } else {
            alert('Failed to freeze subscription: ' + (result.error || 'Unknown error'));
          }
        } catch (e) {
          console.error('Error freezing subscription:', e);
          alert('Error freezing subscription. Please try again.');
        }
      });
    }
    
    async function unfreezeSubscription() {
      try {
        const response = await fetch('unfreeze_subscription.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' }
        });
        const result = await response.json();
        
        if (result.success) {
          alert('Subscription unfrozen successfully!');
          // Reload user data
          await populateUser();
        } else {
          alert('Failed to unfreeze subscription: ' + (result.error || 'Unknown error'));
        }
      } catch (e) {
        console.error('Error unfreezing subscription:', e);
        alert('Error unfreezing subscription. Please try again.');
      }
    }
  }

  if (localStorage.getItem('subFrozen') === '1') user.subscription.frozen = true;

  // Blob interaction (repel/attract)
  const blobs = Array.from(document.querySelectorAll('.blob-dodge'));
  if (blobs.length) {
    let pointerX = window.innerWidth / 2, pointerY = window.innerHeight / 2;
    const repelRadius = 250, repelPower = 5.0, attractPower = 0.25;
    function onMove(e){ pointerX = e.clientX; pointerY = e.clientY; }
    function onTouch(e){ if (e.touches && e.touches[0]) { pointerX = e.touches[0].clientX; pointerY = e.touches[0].clientY; } }
    document.addEventListener('mousemove', onMove, { passive: true });
    document.addEventListener('touchmove', onTouch, { passive: true });
    function step(){
      blobs.forEach(blob=>{
        const r = blob.getBoundingClientRect();
        const cx = r.left + r.width/2, cy = r.top + r.height/2;
        const dx = cx - pointerX, dy = cy - pointerY;
        const dist = Math.hypot(dx, dy);
        if (dist < repelRadius) {
          const force = (repelRadius - dist) / repelRadius;
          blob.style.setProperty('--dx', `${dx * force * repelPower}px`);
          blob.style.setProperty('--dy', `${dy * force * repelPower}px`);
        } else {
          blob.style.setProperty('--dx', `${-dx * attractPower}px`);
          blob.style.setProperty('--dy', `${-dy * attractPower}px`);
        }
      });
      requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }

  // Medical panel: open/close, save/load
  function showMedical() {
    if (!medicalPanel) return;
    medicalPanel.classList.remove('hidden');
    medicalPanel.setAttribute('aria-hidden', 'false');
    medicalPanel.style.opacity = '1';
    medicalPanel.style.transform = 'translateY(0)';
    medicalPanel.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
  function hideMedical() {
    if (!medicalPanel) return;
    medicalPanel.classList.add('hidden');
    medicalPanel.setAttribute('aria-hidden', 'true');
  }

  async function loadMedical() {
    try {
      const response = await fetch('get_medical.php');
      return await response.json();
    } catch (e) { return null; }
  }

  async function saveMedical() {
    if (!medicalForm) return;
    // collect conditions from panel checkboxes (with notes)
    const condEls = Array.from(medicalForm.querySelectorAll('input[id^="m_cond_"]'));
    const conditions = condEls.filter(ch => ch.checked).map(ch => {
      const id = ch.id.replace('m_cond_', '');
      const fieldMap = {
        heart: 'Has_Heart_Condition',
        diabetes: 'Has_Diabetes',
        asthma: 'Has_Asthma',
        thyroid: 'Has_Thyroid_Disorder',
        cholesterol: 'Has_High_Cholesterol',
        back: 'Has_Back_Injury',
        neck: 'Has_Neck_Injury',
        lactose: 'Has_lactose_intolerance',
        gluten: 'Has_gluten_intolerance',
        nut: 'Has_nut_Allergy',
        egg: 'Has_egg_allergy',
        surgeries: 'has_recent_surgery'
      };
      const dbField = fieldMap[id] || id;
      const noteEl = document.getElementById(`m_note_${id}`);
      return { id: dbField, note: noteEl ? (noteEl.value || '') : '' };
    });

    const m = {
      age: medicalForm.querySelector('#age')?.value || '',
      weight: medicalForm.querySelector('#weight')?.value || '',
      conditions: conditions,
      notes: '', // panel doesn't have notes
      height: medicalForm.querySelector('#height')?.value || ''
    };
    try {
      const response = await fetch('save_medical.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(m)
      });
      if (!response.ok) {
        const body = await response.text();
        alert('Server error saving medical profile: HTTP ' + response.status + '\n' + body.substring(0, 2000));
        console.error('saveMedical non-OK response', response.status, body);
        return;
      }

      const result = await response.json();
      if (result.success) {
        alert('Medical profile saved');
        hideMedical();
      } else {
        // show server error details to help debugging
        const msg = result.error || result.db_error || JSON.stringify(result);
        alert('Could not save medical profile: ' + msg);
        console.error('saveMedical failed:', result);
      }
    } catch (e) {
      alert('Could not save medical profile');
    }
  }

  // Attach panel button listeners defensively
  {
    const btn = document.getElementById('openMedical');
    if (btn) btn.addEventListener('click', (e) => { e.preventDefault(); showMedical(); });
    else console.warn('openMedical button not found');
  }
  {
    const btn = document.getElementById('closeMedical');
    if (btn) btn.addEventListener('click', (e) => { e.preventDefault(); hideMedical(); });
    else console.warn('closeMedical button not found');
  }
  {
    const btn = document.getElementById('cancelMedical');
    if (btn) btn.addEventListener('click', (e) => { e.preventDefault(); hideMedical(); });
    else console.warn('cancelMedical button not found');
  }
  {
    const btn = document.getElementById('saveMedical');
    if (btn) btn.addEventListener('click', (e) => { e.preventDefault(); saveMedical(); });
    else console.warn('saveMedical button not found');
  }

  // Initialize
  // checkAndShowWizard(); // removed, shown via PHP
  // If wizard is shown by PHP, initialize the first step
  const wizard = document.getElementById('medicalWizard');
  if (wizard && wizard.style.display !== 'none') {
    console.log('Wizard is shown, initializing step 0');
    currentWizardStep = 0;
    showWizardStep(0);
  } else {
    console.log('Wizard not shown or not found');
  }
  // attach note toggles for both wizard and panel
  attachConditionNoteToggles(document);
  populateUser();
  setInterval(updateSubscriptionUI, 30000);
})();