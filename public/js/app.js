/**
 * ColonBot – App JS
 * Plataforma Turística Interactiva – Municipio de Colón
 */

// ─── Utility ──────────────────────────────────────────────────────────────
const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

// ─── Flash auto-dismiss ────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const flash = document.getElementById('flash-msg');
  if (flash) {
    setTimeout(() => flash.remove(), 4000);
  }

  // Mobile menu toggle (in case footer script hasn't run yet)
  const btn  = document.getElementById('mobile-menu-btn');
  const menu = document.getElementById('mobile-menu');
  if (btn && menu) {
    btn.addEventListener('click', () => menu.classList.toggle('hidden'));
  }

  // Color sync (hex inputs ↔ color pickers)
  $$('input[type="color"]').forEach(picker => {
    picker.addEventListener('input', () => {
      const textInput = picker.nextElementSibling;
      if (textInput && textInput.tagName === 'INPUT') {
        textInput.value = picker.value;
      }
    });
  });
});

// ─── PWA Service Worker registration ──────────────────────────────────────
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch(() => {});
  });
}
