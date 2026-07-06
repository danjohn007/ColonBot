<?php
$pageTitle = 'CRM – ' . APP_NAME;
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-6xl mx-auto px-4 py-8 mb-24">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">📇 CRM - Mis Contactos</h1>
    <div class="flex gap-2">
      <button onclick="openAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-blue-700 transition">
        + Nuevo contacto
      </button>
    </div>
  </div>

  <!-- Business selector -->
  <div class="mb-6">
    <label class="label block text-sm font-medium text-gray-700 mb-1">Seleccionar negocio</label>
    <select id="business-select" onchange="loadContacts()" class="input w-full sm:w-72 px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
      <option value="">-- Selecciona un negocio --</option>
      <?php foreach ($businesses as $b): ?>
      <option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <!-- Category filter tabs -->
  <div class="flex gap-2 mb-6" id="category-tabs">
    <button onclick="filterCategory('')" data-cat="" class="cat-filter px-4 py-2 rounded-full text-sm font-medium bg-blue-600 text-white transition">📊 Todos</button>
    <button onclick="filterCategory('prospecto_sin_historial')" data-cat="prospecto_sin_historial" class="cat-filter px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-700 hover:bg-blue-100 transition">🆕 Prospectos sin historial</button>
    <button onclick="filterCategory('prospecto_recurrente')" data-cat="prospecto_recurrente" class="cat-filter px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-700 hover:bg-blue-100 transition">🔄 Prospectos recurrentes</button>
    <button onclick="filterCategory('cliente')" data-cat="cliente" class="cat-filter px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-700 hover:bg-blue-100 transition">✅ Clientes</button>
    <button onclick="filterCategory('cliente_frecuente')" data-cat="cliente_frecuente" class="cat-filter px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-700 hover:bg-blue-100 transition">⭐ Clientes frecuentes</button>
  </div>

  <!-- Contacts table -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm" id="contacts-table">
        <thead class="bg-gray-50">
          <tr class="text-left text-xs text-gray-500 uppercase tracking-wide">
            <th class="px-4 py-3">Nombre</th>
            <th class="px-4 py-3">Teléfono</th>
            <th class="px-4 py-3">Email</th>
            <th class="px-4 py-3">Categoría</th>
            <th class="px-4 py-3">Visitas</th>
            <th class="px-4 py-3">Ventas</th>
            <th class="px-4 py-3">Origen</th>
            <th class="px-4 py-3">Último contacto</th>
            <th class="px-4 py-3">Acciones</th>
          </tr>
        </thead>
        <tbody id="contacts-tbody" class="divide-y divide-gray-100">
          <tr><td colspan="9" class="text-center py-8 text-gray-400">Selecciona un negocio para ver sus contactos</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- Add Contact Modal -->
<div id="add-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center px-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 relative">
    <button onclick="closeAddModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">✕</button>
    <h2 class="text-lg font-bold text-gray-900 mb-4">Nuevo contacto</h2>
    <form onsubmit="saveContact(event)" class="space-y-4">
      <input type="hidden" id="add-business-id" value="">
      <div>
        <label class="label block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
        <input type="text" id="add-name" required class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
          <input type="tel" id="add-phone" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input type="email" id="add-email" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
        </div>
      </div>
      <div>
        <label class="label block text-sm font-medium text-gray-700 mb-1">Categoría</label>
        <select id="add-category" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
          <option value="prospecto">📋 Prospecto</option>
          <option value="cliente">✅ Cliente</option>
          <option value="lovemark">⭐ Lovemark</option>
        </select>
      </div>
      <div>
        <label class="label block text-sm font-medium text-gray-700 mb-1">Notas</label>
        <textarea id="add-notes" rows="2" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm"></textarea>
      </div>
      <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-medium hover:bg-blue-700 transition">
        Guardar contacto
      </button>
    </form>
  </div>
</div>

<!-- Upgrade to Cliente Modal (Customer Journey - Etapa A → B) -->
<div id="upgrade-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center px-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 relative">
    <button onclick="closeUpgradeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">✕</button>
    <h2 class="text-lg font-bold text-gray-900 mb-1">Convertir PROSPECTO a CLIENTE</h2>
    <p class="text-xs text-gray-500 mb-4">Customer Journey — Etapa A → B: Captura los datos de la venta</p>
    <p class="text-sm text-gray-500 mb-4" id="upgrade-contact-name"></p>
    <form onsubmit="upgradeToCliente(event)" class="space-y-4">
      <input type="hidden" id="upgrade-contact-id" value="">
      <div>
        <label class="label block text-sm font-medium text-gray-700 mb-1">Nombre del Cliente *</label>
        <input type="text" id="upgrade-name" required class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="Nombre del cliente">
      </div>
      <div>
        <label class="label block text-sm font-medium text-gray-700 mb-1">Email (opcional)</label>
        <input type="email" id="upgrade-email" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="cliente@ejemplo.com">
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Producto o Servicio vendido *</label>
          <input type="text" id="upgrade-products" required class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="Ej. Vino, Tour">
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Monto total de compra</label>
          <input type="number" id="upgrade-amount" step="0.01" min="0" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="0.00">
        </div>
      </div>
      <div>
        <label class="label block text-sm font-medium text-gray-700 mb-1">Notas / Anotaciones del perfil</label>
        <textarea id="upgrade-notes" rows="2" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="Comentarios adicionales..."></textarea>
      </div>
      <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-xl font-medium hover:bg-green-700 transition">
        Convertir a Cliente
      </button>
    </form>
  </div>
</div>
<!-- Purchase Modal (Customer Journey - Etapa B → C seguimiento) -->
<div id="purchase-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center px-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 relative">
    <button onclick="closePurchaseModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">✕</button>
    <h2 class="text-lg font-bold text-gray-900 mb-1">Registrar Nueva Compra</h2>
    <p class="text-xs text-gray-500 mb-4">Customer Journey — 3 compras o más = Lovemark ⭐ (automático)</p>
    <p class="text-sm text-gray-500 mb-4" id="purchase-contact-name"></p>
    <form onsubmit="addPurchase(event)" class="space-y-4">
      <input type="hidden" id="purchase-contact-id" value="">
      <div>
        <label class="label block text-sm font-medium text-gray-700 mb-1">Nombre del Cliente *</label>
        <input type="text" id="purchase-name" required class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="Nombre del cliente">
      </div>
      <div>
        <label class="label block text-sm font-medium text-gray-700 mb-1">Email (opcional)</label>
        <input type="email" id="purchase-email" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="cliente@ejemplo.com">
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Producto o Servicio *</label>
          <input type="text" id="purchase-products" required class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="Ej. Vino, Tour">
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Monto de compra</label>
          <input type="number" id="purchase-amount" step="0.01" min="0" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="0.00">
        </div>
      </div>
      <div>
        <label class="label block text-sm font-medium text-gray-700 mb-1">Notas</label>
        <textarea id="purchase-notes" rows="2" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="Comentarios adicionales..."></textarea>
      </div>
      <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-medium hover:bg-blue-700 transition">
        Registrar Compra
      </button>
    </form>
  </div>
</div>

<script>
const CSRF = '<?= e($csrf) ?>';
const BASE_URL = '<?= BASE_URL ?>';
let currentCategory = '';

function loadContacts() {
  const businessId = document.getElementById('business-select').value;
  const tbody = document.getElementById('contacts-tbody');
  if (!businessId) {
    tbody.innerHTML = '<tr><td colspan="9" class="text-center py-8 text-gray-400">Selecciona un negocio para ver sus contactos</td></tr>';
    document.getElementById('add-business-id').value = '';
    return;
  }

  document.getElementById('add-business-id').value = businessId;

  const url = `${BASE_URL}/admin/crm/${businessId}/list?category=${currentCategory}`;
  fetch(url)
    .then(r => r.json())
    .then(contacts => {
      if (contacts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-8 text-gray-400">No hay contactos en esta categoría</td></tr>';
        return;
      }
      tbody.innerHTML = contacts.map(c => {
        let categoryLabel, categoryClass;
        if (c.category === 'lovemark') {
          categoryLabel = '⭐ Lovemark';
          categoryClass = 'text-pink-600 bg-pink-50';
        } else if (c.category === 'cliente') {
          categoryLabel = '✅ Cliente';
          categoryClass = 'text-green-600 bg-green-50';
        } else if (c.category === 'prospecto_recurrente') {
          categoryLabel = '🔄 Prospecto recurrente';
          categoryClass = 'text-orange-600 bg-orange-50';
        } else if (c.category === 'prospecto_sin_historial' || c.category === 'prospecto') {
          categoryLabel = c.is_chatbot ? '🆕 WhatsApp' : '📋 Prospecto';
          categoryClass = 'text-purple-600 bg-purple-50';
        } else {
          categoryLabel = '📋 Prospecto';
          categoryClass = 'text-purple-600 bg-purple-50';
        }
        const sourceIcon = c.source === 'whatsapp' ? '📱' : c.source === 'mapa' ? '🗺️' : '✍️';
        const lastContact = c.last_contact_at ? new Date(c.last_contact_at).toLocaleDateString('es-MX') : '—';
        const phone = c.phone || c.wa_id || '—';
        return `<tr class="hover:bg-gray-50">
          <td class="px-4 py-3 font-medium text-gray-800">${escHtml(c.name)}</td>
          <td class="px-4 py-3 text-gray-500">${escHtml(phone)}</td>
          <td class="px-4 py-3 text-gray-500">${escHtml(c.email || '—')}</td>
          <td class="px-4 py-3"><span class="text-xs px-2 py-1 rounded-full font-medium ${categoryClass}">${categoryLabel}</span></td>
          <td class="px-4 py-3">${c.total_visits}</td>
          <td class="px-4 py-3">$${parseFloat(c.total_spent || 0).toFixed(2)}</td>
          <td class="px-4 py-3 text-gray-400">${sourceIcon}</td>
          <td class="px-4 py-3 text-gray-400 text-xs">${lastContact}</td>
          <td class="px-4 py-3">
            <div class="flex gap-1">
              ${c.category !== 'cliente' && c.category !== 'lovemark' ? `<button onclick="openUpgradeModal(${c.id}, '${escHtml(c.name)}')" class="text-xs px-2 py-1 bg-green-50 text-green-700 rounded-lg hover:bg-green-100" title="Convertir a cliente">⬆</button>` : ''}
              ${c.category === 'cliente' || c.category === 'lovemark' ? `<button onclick="openPurchaseModal(${c.id}, '${escHtml(c.name)}')" class="text-xs px-2 py-1 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100" title="Registrar compra">💰</button>` : ''}
              ${phone !== '—' ? `<a href="https://wa.me/${phone.replace(/\D/g,'')}" target="_blank" class="text-xs px-2 py-1 bg-green-50 text-green-700 rounded-lg hover:bg-green-100" title="WhatsApp">💬</a>` : ''}
            </div>
          </td>
        </tr>`;
      }).join('');
    });
}

function filterCategory(cat) {
  currentCategory = cat;
  document.querySelectorAll('.cat-filter').forEach(b => {
    const active = b.dataset.cat === cat;
    b.classList.toggle('bg-blue-600', active);
    b.classList.toggle('text-white', active);
    b.classList.toggle('bg-gray-100', !active);
    b.classList.toggle('text-gray-700', !active);
  });
  loadContacts();
}

function openAddModal() {
  const businessId = document.getElementById('business-select').value;
  if (!businessId) { alert('Primero selecciona un negocio.'); return; }
  document.getElementById('add-modal').classList.remove('hidden');
}

function closeAddModal() {
  document.getElementById('add-modal').classList.add('hidden');
}

function saveContact(e) {
  e.preventDefault();
  const businessId = document.getElementById('add-business-id').value;
  const name = document.getElementById('add-name').value.trim();
  if (!name) return;

  const body = new URLSearchParams({
    _csrf: CSRF,
    business_id: businessId,
    name,
    phone: document.getElementById('add-phone').value.trim(),
    email: document.getElementById('add-email').value.trim(),
    category: document.getElementById('add-category').value,
    notes: document.getElementById('add-notes').value.trim(),
  });

  fetch(`${BASE_URL}/admin/crm/crear`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      closeAddModal();
      document.getElementById('add-name').value = '';
      document.getElementById('add-phone').value = '';
      document.getElementById('add-email').value = '';
      document.getElementById('add-notes').value = '';
      loadContacts();
    } else {
      alert(d.error || 'Error al guardar');
    }
  });
}

function openUpgradeModal(id, name) {
  document.getElementById('upgrade-contact-id').value = id;
  document.getElementById('upgrade-contact-name').textContent = `Convertir a "${name}" a cliente`;
  document.getElementById('upgrade-modal').classList.remove('hidden');
}

function closeUpgradeModal() {
  document.getElementById('upgrade-modal').classList.add('hidden');
}

function upgradeToCliente(e) {
  e.preventDefault();
  const id = document.getElementById('upgrade-contact-id').value;
  const body = new URLSearchParams({
    _csrf: CSRF,
    amount: document.getElementById('upgrade-amount').value || '0',
    products: document.getElementById('upgrade-products').value.trim(),
    notes: document.getElementById('upgrade-notes').value.trim(),
  });

  fetch(`${BASE_URL}/admin/crm/${id}/upgrade`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      closeUpgradeModal();
      document.getElementById('upgrade-amount').value = '';
      document.getElementById('upgrade-products').value = '';
      document.getElementById('upgrade-notes').value = '';
      loadContacts();
    } else {
      alert(d.error || 'Error');
    }
  });
}

function openPurchaseModal(id, name) {
  document.getElementById('purchase-contact-id').value = id;
  document.getElementById('purchase-contact-name').textContent = `Registrar compra para "${name}"`;
  document.getElementById('purchase-modal').classList.remove('hidden');
}

function closePurchaseModal() {
  document.getElementById('purchase-modal').classList.add('hidden');
}

function addPurchase(e) {
  e.preventDefault();
  const id = document.getElementById('purchase-contact-id').value;
  const body = new URLSearchParams({
    _csrf: CSRF,
    name: document.getElementById('purchase-name').value.trim(),
    email: document.getElementById('purchase-email').value.trim(),
    amount: document.getElementById('purchase-amount').value || '0',
    products: document.getElementById('purchase-products').value.trim(),
    notes: document.getElementById('purchase-notes').value.trim(),
  });

  fetch(`${BASE_URL}/admin/crm/${id}/compra`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      closePurchaseModal();
      document.getElementById('purchase-name').value = '';
      document.getElementById('purchase-email').value = '';
      document.getElementById('purchase-amount').value = '';
      document.getElementById('purchase-products').value = '';
      document.getElementById('purchase-notes').value = '';
      loadContacts();
    } else {
      alert(d.error || 'Error');
    }
  });
}

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = String(str);
  return d.innerHTML;
}
</script>

<style>
  .label { @apply block text-sm font-medium text-gray-700 mb-1; }
  .input  { @apply w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition; }
</style>
<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>