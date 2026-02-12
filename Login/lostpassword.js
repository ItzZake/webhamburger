(function(){
  'use strict';

  const form = document.getElementById('lpForm');
  const idEl = document.getElementById('lp_identifier');
  const newEl = document.getElementById('lp_new');
  const confEl = document.getElementById('lp_confirm');
  const msgEl = document.getElementById('lpMessage');
  const toggleNew = document.getElementById('lpToggleNew');
  const toggleConfirm = document.getElementById('lpToggleConfirm');

  function showMessage(txt, isError){
    msgEl.textContent = txt || '';
    msgEl.classList.remove('error','success');
    if(!txt){
      return;
    }
    msgEl.classList.add(isError ? 'error' : 'success');
  }

  function validate(){
    const id = (idEl.value || '').trim();
    const np = (newEl.value || '');
    const cp = (confEl.value || '');
    if(!id) return {ok:false,msg:'Enter your email or gym code'};
    if(np.length < 6) return {ok:false,msg:'Password must be at least 6 characters'};
    if(np !== cp) return {ok:false,msg:'Passwords do not match'};
    return {ok:true,msg:''};
  }

  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const v = validate();
    if(!v.ok){ showMessage(v.msg, true); return; }
    // Send reset request to server
    const payload = { identifier: idEl.value.trim(), newpass: newEl.value };
    showMessage('Saving password...', false);
    try{
      const res = await fetch('reset_password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const body = await res.json().catch(()=>null);
      if (res.ok && body && body.success) {
        // success
        const success = document.createElement('div');
        success.className = 'lp-success';
        success.innerHTML = '<h2>Password has been changed!</h2><p>You can now <a href="loginsignup.php">sign in</a>.</p>';
        form.parentNode.replaceChild(success, form);
      } else {
        const err = (body && (body.error || body.db_error || body.exception)) || 'Server error';
        showMessage('Failed to reset password: ' + err, true);
      }
    } catch(e) {
      showMessage('Network error while saving password', true);
    }
  });

  function toggleVisibility(input, button){
    if(!input || !button) return;
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    button.textContent = isHidden ? 'Hide' : 'Show';
    button.setAttribute('aria-pressed', String(isHidden));
    button.setAttribute('aria-label', (isHidden ? 'Hide' : 'Show') + ' password');
  }
  toggleNew?.addEventListener('click', ()=> toggleVisibility(newEl, toggleNew));
  toggleConfirm?.addEventListener('click', ()=> toggleVisibility(confEl, toggleConfirm));

})();