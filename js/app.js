/* FinanceApp — app.js */

// ── Modals ──────────────────────────────────────────────────
function openModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('open');
}
function closeModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('open');
}
// Close on overlay click
document.addEventListener('click', function(e) {
  if (e.target && e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('open');
  }
});
// Close on Escape
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
  }
});

// ── Tabs (JS-driven, not href-driven) ───────────────────────
function swTab(id, btn) {
  const container = btn.closest('[data-tabs]') || btn.closest('.card') || document;
  const prefix = id.split('-')[0];
  // Hide all siblings with same prefix
  document.querySelectorAll('[id^="tab-"]').forEach(p => {
    if (p.id.startsWith('tab-' + prefix) || p.id === 'tab-' + id) p.classList.remove('active');
  });
  // Deactivate sibling tab buttons
  btn.closest('.tab-bar').querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  // Activate target
  const pane = document.getElementById('tab-' + id);
  if (pane) pane.classList.add('active');
  btn.classList.add('active');
}

// ── Toast notifications ──────────────────────────────────────
let _toastTimer;
function showToast(msg, duration = 2800) {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.classList.add('show');
  clearTimeout(_toastTimer);
  _toastTimer = setTimeout(() => t.classList.remove('show'), duration);
}

// ── Auto-dismiss flash messages ──────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.alert-box.alert-success').forEach(function(el) {
    setTimeout(function() {
      el.style.transition = 'opacity .5s';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 500);
    }, 3500);
  });

  // Highlight active color swatch
  document.querySelectorAll('input[type="radio"][name="color"]').forEach(function(inp) {
    const swatch = inp.nextElementSibling;
    if (swatch) {
      if (inp.checked) swatch.style.borderColor = '#378ADD';
      inp.addEventListener('change', function() {
        document.querySelectorAll('input[type="radio"][name="color"]').forEach(function(r) {
          const s = r.nextElementSibling;
          if (s) s.style.borderColor = 'transparent';
        });
        if (swatch) swatch.style.borderColor = '#378ADD';
      });
    }
  });
});
