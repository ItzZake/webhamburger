function ensureToastStyles() {
  if (document.getElementById("toast-styles")) return;
  const s = document.createElement("style");
  s.id = "toast-styles";
  s.innerText = `#toast-container{position:fixed;right:20px;top:20px;z-index:99999} .toast{background:#222;color:#fff;padding:10px 14px;border-radius:6px;margin-top:8px;box-shadow:0 4px 10px rgba(0,0,0,0.2);opacity:0;transform:translateY(-8px);transition:all .25s}.toast.show{opacity:1;transform:none}.toast.success{background:#2ecc71}.toast.error{background:#e74c3c}`;
  document.head.appendChild(s);
  const c = document.createElement("div");
  c.id = "toast-container";
  document.body.appendChild(c);
}

function showToast(message, type) {
  ensureToastStyles();
  const el = document.createElement("div");
  el.className = "toast " + (type || "");
  el.innerText = message;
  document.getElementById("toast-container").appendChild(el);
  // show
  setTimeout(() => el.classList.add("show"), 10);
  setTimeout(() => {
    el.classList.remove("show");
    setTimeout(() => el.remove(), 300);
  }, 3000);
}

// Export for older browsers
window.showToast = showToast;
