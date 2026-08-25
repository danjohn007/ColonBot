<?php
$pageTitle = 'CRM – ' . APP_NAME;
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-6xl mx-auto px-4 py-8 mb-24">
  <div class="flex flex-col md:flex-row gap-6">
    <!-- Sidebar lateral -->
    <aside class="md:w-56 md:shrink-0">
      <nav class="flex md:flex-col gap-2 md:sticky md:top-4">
        <button onclick="showPanel('contactos')" id="tab-btn-contactos" class="crm-tab w-full text-left px-4 py-2.5 rounded-xl text-sm font-medium transition bg-blue-600 text-white">
          📇 Contactos
        </button>
        <button onclick="showPanel('mensajes')" id="tab-btn-mensajes" class="crm-tab w-full text-left px-4 py-2.5 rounded-xl text-sm font-medium transition bg-gray-100 text-gray-700 hover:bg-blue-100">
          💬 Mensajes
        </button>
      </nav>
    </aside>

    <!-- Contenido -->
    <section class="flex-1 min-w-0">
      <!-- Business selector (compartido entre paneles Contactos y Mensajes) -->
      <div class="mb-6">
        <label class="label block text-sm font-medium text-gray-700 mb-1">Seleccionar negocio</label>
        <select id="business-select" onchange="loadContacts(); loadHistorial(); actualizarVistaPrevia()" class="input w-full sm:w-72 px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
          <option value="">-- Selecciona un negocio --</option>
          <?php foreach ($businesses as $b): ?>
          <option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div id="panel-contactos">
        <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">📇 CRM - Mis Contactos</h1>
    <div class="flex gap-2">
      <button onclick="openAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-blue-700 transition">
        + Nuevo contacto
      </button>
    </div>
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
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="overflow-x-auto">
      <table class="text-sm min-w-[900px] w-full" id="contacts-table">
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
      </div>

      <!-- Panel Mensajes (apartado lateral "Mensajes") -->
      <div id="panel-mensajes" class="hidden">
        <div class="flex items-center justify-between mb-6">
          <h1 class="text-2xl font-bold text-gray-900">💬 Mensajes</h1>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
          <!-- Formulario -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-4">
            <div>
              <label class="label block text-sm font-medium text-gray-700 mb-1">Nombre de la campaña *</label>
              <input type="text" id="msg-campana-nombre" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="Ej. Promoción de verano">
            </div>

            <div>
              <label class="label block text-sm font-medium text-gray-700 mb-1">Enviar a</label>
              <select id="msg-segmento" onchange="onSegmentoChange()" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
                <option value="todos">Todos los contactos</option>
                <option value="prospecto_sin_historial">Prospectos sin historial</option>
                <option value="prospecto_recurrente">Prospectos recurrentes</option>
                <option value="cliente">Clientes</option>
                <option value="cliente_frecuente">Clientes frecuentes</option>
                <option value="individual">Un contacto individual</option>
              </select>
            </div>

            <!-- Búsqueda individual -->
            <div id="msg-individual-box" class="hidden space-y-2">
              <label class="label block text-sm font-medium text-gray-700 mb-1">Buscar contacto (nombre o WhatsApp)</label>
              <input type="text" id="msg-buscar-input" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="Escribe nombre o número..." oninput="buscarContactoIndividual()">
              <div id="msg-buscar-results" class="border border-gray-200 rounded-xl max-h-48 overflow-y-auto divide-y divide-gray-100"></div>
            </div>

            <div>
              <label class="label block text-sm font-medium text-gray-700 mb-1">Mensaje (solo texto) *</label>
              <textarea id="msg-mensaje" rows="4" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" maxlength="4000" oninput="actualizarVistaPrevia()" placeholder="Escribe aquí tu mensaje..."></textarea>
              <p class="text-xs text-gray-400 mt-1 text-right"><span id="msg-caracteres">0</span>/4000</p>
            </div>

            <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">⚠️ No se permiten mensajes de prueba (palabras como "prueba" o "mensaje de prueba").</p>

            <button onclick="enviarMensaje()" class="w-full bg-blue-600 text-white py-3 rounded-xl font-medium hover:bg-blue-700 transition">
              📨 Enviar mensaje
            </button>
            <p id="msg-enviar-nota" class="text-xs text-gray-500">El mensaje se encolará para su envío por WhatsApp. No se contacta a Meta en este momento.</p>
          </div>

          <!-- Vista previa -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 h-fit">
            <h3 class="text-sm font-semibold text-gray-800 mb-3">👁️ Vista previa</h3>
            <div id="msg-preview-target" class="text-xs text-gray-500 mb-2">Destinatario: —</div>
            <div class="bg-[#e5ddd5] rounded-2xl p-4">
              <div class="bg-[#dcf8c6] rounded-xl rounded-tr-none px-3 py-2 shadow-sm max-w-[85%] ml-auto">
                <p id="msg-preview-text" class="text-sm text-gray-800 whitespace-pre-wrap">Escribe un mensaje para ver la vista previa...</p>
                <p class="text-[10px] text-gray-500 text-right mt-1">Vista previa</p>
              </div>
            </div>
            <div id="msg-preview-info" class="text-xs text-gray-400 mt-3">Selecciona el negocio y el segmento para calcular destinatarios.</div>
          </div>
        </div>

        <!-- Historial -->
        <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100">
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">📜 Historial de campañas / envíos</h3>
            <button onclick="loadHistorial()" class="text-xs text-blue-600 hover:underline">↻ Refrescar</button>
          </div>
          <div id="msg-historial" class="p-5">
            <p class="text-sm text-gray-400">Selecciona un negocio para ver el historial.</p>
          </div>
        </div>
      </div>
    </section>
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
          <option value="prospecto_sin_historial">Prospecto sin historial</option>
          <option value="prospecto_recurrente">Prospecto recurrente</option>
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
    <p class="text-xs text-gray-500 mb-4">Customer Journey: un prospecto se convierte en cliente solo al registrar una compra.</p>
    <p class="text-sm text-gray-500 mb-4" id="upgrade-contact-name"></p>
    <form id="upgrade-form" class="space-y-4">
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
    <p class="text-xs text-gray-500 mb-4">Customer Journey: mas de 3 compras = cliente recurrente / Lovemark.</p>
    <p class="text-sm text-gray-500 mb-4" id="purchase-contact-name"></p>
    <form id="purchase-form" class="space-y-4">
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

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = String(str);
  return d.innerHTML;
}

// Attach form submit handlers after DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('upgrade-form').addEventListener('submit', function(e) {
    upgradeToCliente(e);
  });
  document.getElementById('purchase-form').addEventListener('submit', function(e) {
    addPurchaseEvent(e);
  });
});

function loadContacts() {
  const businessId = document.getElementById('business-select').value;
  const tbody = document.getElementById('contacts-tbody');
  if (!businessId) {
    tbody.innerHTML = '<tr><td colspan="9" class="text-center py-8 text-gray-400">Selecciona un negocio para ver sus contactos</td></tr>';
    document.getElementById('add-business-id').value = '';
    return;
  }

  document.getElementById('add-business-id').value = businessId;

  const url = BASE_URL + '/admin/crm/' + businessId + '/list?category=' + currentCategory;
  fetch(url)
    .then(r => r.json())
    .then(contacts => {
      if (contacts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-8 text-gray-400">No hay contactos en esta categor\u00eda</td></tr>';
        return;
      }
      tbody.innerHTML = contacts.map(c => {
        // Use dynamic_category from contact_purchases if available, otherwise fallback to static category
        const effectiveCategory = c.dynamic_category || c.category;
        let categoryLabel, categoryClass;
        if (effectiveCategory === 'lovemark') {
          categoryLabel = '⭐ Lovemark';
          categoryClass = 'text-pink-600 bg-pink-50';
        } else if (effectiveCategory === 'cliente') {
          categoryLabel = '✅ Cliente';
          categoryClass = 'text-green-600 bg-green-50';
        } else if (effectiveCategory === 'prospecto_recurrente') {
          categoryLabel = '🔄 Prospecto recurrente';
          categoryClass = 'text-orange-600 bg-orange-50';
        } else if (effectiveCategory === 'prospecto_sin_historial' || effectiveCategory === 'prospecto') {
          categoryLabel = c.is_chatbot ? '🆕 WhatsApp' : '📋 Prospecto';
          categoryClass = 'text-purple-600 bg-purple-50';
        } else {
          categoryLabel = '📋 Prospecto';
          categoryClass = 'text-purple-600 bg-purple-50';
        }
        const sourceIcon = c.source === 'whatsapp' ? '📱' : c.source === 'mapa' ? '🗺️' : '✍️';
        const lastContact = c.last_contact_at ? new Date(c.last_contact_at).toLocaleDateString('es-MX') : '—';
        const phone = c.phone || c.wa_id || '—';
        const encodedName = encodeURIComponent(c.name);
        // Determine if upgrade or purchase buttons are needed based on dynamic category
        const isDynamicCliente = effectiveCategory === 'cliente';
        const isDynamicLovemark = effectiveCategory === 'lovemark';
        let upgradeBtn = '', purchaseBtn = '', waBtn = '';
        if (!isDynamicCliente && !isDynamicLovemark) {
          upgradeBtn = '<button class="btn-upgrade text-xs px-2 py-1 bg-green-50 text-green-700 rounded-lg hover:bg-green-100" data-cid="' + c.id + '" data-cname="' + encodedName + '" type="button" title="Registrar compra y convertir a cliente">⬆</button>';
        }
        if (isDynamicCliente || isDynamicLovemark) {
          purchaseBtn = '<button class="btn-purchase text-xs px-2 py-1 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100" data-cid="' + c.id + '" data-cname="' + encodedName + '" type="button" title="Registrar compra">💰</button>';
        }
        if (phone !== '—') {
          waBtn = '<a href="https://wa.me/' + phone.replace(/\D/g,'') + '" target="_blank" class="text-xs px-2 py-1 bg-green-50 text-green-700 rounded-lg hover:bg-green-100" title="WhatsApp">💬</a>';
        }
        return '<tr class="hover:bg-gray-50">' +
          '<td class="px-4 py-3 font-medium text-gray-800">' + escHtml(c.name) + '</td>' +
          '<td class="px-4 py-3 text-gray-500">' + escHtml(phone) + '</td>' +
          '<td class="px-4 py-3 text-gray-500">' + escHtml(c.email || '—') + '</td>' +
          '<td class="px-4 py-3"><span class="text-xs px-2 py-1 rounded-full font-medium ' + categoryClass + '">' + categoryLabel + '</span></td>' +
          '<td class="px-4 py-3">' + c.total_visits + '</td>' +
          '<td class="px-4 py-3">$' + parseFloat(c.total_spent || 0).toFixed(2) + '</td>' +
          '<td class="px-4 py-3 text-gray-400">' + sourceIcon + '</td>' +
          '<td class="px-4 py-3 text-gray-400 text-xs">' + lastContact + '</td>' +
          '<td class="px-4 py-3"><div class="flex gap-1">' + upgradeBtn + purchaseBtn + waBtn + '</div></td>' +
          '</tr>';
      }).join('');

      document.querySelectorAll('.btn-upgrade').forEach(function(btn) {
        btn.addEventListener('click', function() {
          openUpgradeModal(this.dataset.cid, decodeURIComponent(this.dataset.cname));
        });
      });
      document.querySelectorAll('.btn-purchase').forEach(function(btn) {
        btn.addEventListener('click', function() {
          openPurchaseModal(this.dataset.cid, decodeURIComponent(this.dataset.cname));
        });
      });
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
  if (!name) { alert('El nombre es requerido'); return; }

  const body = new URLSearchParams();
  body.append('_csrf', CSRF);
  body.append('business_id', businessId);
  body.append('name', name);
  body.append('phone', document.getElementById('add-phone').value.trim());
  body.append('email', document.getElementById('add-email').value.trim());
  body.append('category', document.getElementById('add-category').value);
  body.append('notes', document.getElementById('add-notes').value.trim());

  fetch(BASE_URL + '/admin/crm/crear', {
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
  })
  .catch(err => alert('Error: ' + err.message));
}

function openUpgradeModal(id, name) {
  document.getElementById('upgrade-contact-id').value = id;
  document.getElementById('upgrade-name').value = name;
  document.getElementById('upgrade-contact-name').textContent = 'Convertir a "' + name + '" a cliente';
  document.getElementById('upgrade-modal').classList.remove('hidden');
}

function closeUpgradeModal() {
  document.getElementById('upgrade-modal').classList.add('hidden');
}

function upgradeToCliente(e) {
  e.preventDefault();
  const id = document.getElementById('upgrade-contact-id').value;
  if (!id) { alert('Error: ID de contacto no encontrado'); return; }

  const products = document.getElementById('upgrade-products').value.trim();
  if (!products) { alert('Captura el producto o servicio vendido'); return; }

  const businessId = document.getElementById('business-select').value;
  const body = new URLSearchParams();
  body.append('_csrf', CSRF);
  body.append('business_id', businessId);
  body.append('name', document.getElementById('upgrade-name').value.trim());
  body.append('email', document.getElementById('upgrade-email').value.trim());
  body.append('amount', document.getElementById('upgrade-amount').value || '0');
  body.append('products', products);
  body.append('notes', document.getElementById('upgrade-notes').value.trim());

  fetch(BASE_URL + '/admin/crm/' + id + '/upgrade', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  })
  .then(r => {
    if (!r.ok) {
      return r.text().then(t => { throw new Error(t); });
    }
    return r.json();
  })
  .then(d => {
    if (d.ok) {
      closeUpgradeModal();
      document.getElementById('upgrade-amount').value = '';
      document.getElementById('upgrade-products').value = '';
      document.getElementById('upgrade-notes').value = '';
      loadContacts();
    } else {
      alert(d.error || 'Error al convertir');
    }
  })
  .catch(err => alert('Error del servidor: ' + err.message));
}

function openPurchaseModal(id, name) {
  document.getElementById('purchase-contact-id').value = id;
  document.getElementById('purchase-name').value = name;
  document.getElementById('purchase-contact-name').textContent = 'Registrar compra para "' + name + '"';
  document.getElementById('purchase-modal').classList.remove('hidden');
}

function closePurchaseModal() {
  document.getElementById('purchase-modal').classList.add('hidden');
}

function addPurchaseEvent(e) {
  e.preventDefault();
  const id = document.getElementById('purchase-contact-id').value;
  if (!id) { alert('Error: ID de contacto no encontrado'); return; }

  const products = document.getElementById('purchase-products').value.trim();
  if (!products) { alert('Captura el producto o servicio vendido'); return; }

  const body = new URLSearchParams();
  body.append('_csrf', CSRF);
  body.append('name', document.getElementById('purchase-name').value.trim());
  body.append('email', document.getElementById('purchase-email').value.trim());
  body.append('amount', document.getElementById('purchase-amount').value || '0');
  body.append('products', products);
  body.append('notes', document.getElementById('purchase-notes').value.trim());

  fetch(BASE_URL + '/admin/crm/' + id + '/compra', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  })
  .then(r => {
    if (!r.ok) {
      return r.text().then(t => { throw new Error(t); });
    }
    return r.json();
  })
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
      alert(d.error || 'Error al registrar compra');
    }
  })
  .catch(err => alert('Error del servidor: ' + err.message));
}

// ═══════════════════ Módulo Mensajes ═══════════════════

let selectedContacto = null;
let msgBuscarTimer = null;

function showPanel(name) {
  document.getElementById('panel-contactos').classList.toggle('hidden', name !== 'contactos');
  document.getElementById('panel-mensajes').classList.toggle('hidden', name !== 'mensajes');

  const tabs = document.querySelectorAll('.crm-tab');
  tabs.forEach(b => {
    const active = b.id === ('tab-btn-' + name);
    b.classList.toggle('bg-blue-600', active);
    b.classList.toggle('text-white', active);
    b.classList.toggle('bg-gray-100', !active);
    b.classList.toggle('text-gray-700', !active);
  });

  if (name === 'mensajes') {
    loadHistorial();
    actualizarVistaPrevia();
  }
}

function onSegmentoChange() {
  const seg = document.getElementById('msg-segmento').value;
  document.getElementById('msg-individual-box').classList.toggle('hidden', seg !== 'individual');
  if (seg !== 'individual') {
    selectedContacto = null;
    document.getElementById('msg-buscar-results').innerHTML = '';
  }
  actualizarVistaPrevia();
}

function buscarContactoIndividual() {
  const q = document.getElementById('msg-buscar-input').value.trim();
  const box = document.getElementById('msg-buscar-results');
  const businessId = document.getElementById('business-select').value;

  if (!businessId) { box.innerHTML = '<p class="text-xs text-gray-400 px-3 py-2">Primero selecciona un negocio.</p>'; return; }
  if (q.length < 2) { box.innerHTML = ''; return; }

  clearTimeout(msgBuscarTimer);
  msgBuscarTimer = setTimeout(() => {
    fetch(BASE_URL + '/admin/crm/buscar-contacto?business_id=' + encodeURIComponent(businessId) + '&q=' + encodeURIComponent(q))
      .then(r => r.json())
      .then(list => {
        if (!Array.isArray(list)) { box.innerHTML = '<p class="text-xs text-red-500 px-3 py-2">Error en la búsqueda.</p>'; return; }
        if (list.length === 0) { box.innerHTML = '<p class="text-xs text-gray-400 px-3 py-2">Sin resultados.</p>'; return; }
        const found = {};
        var opts = list.filter(c => {
          if (found[c.id]) return false;
          found[c.id] = true;
          return true;
        });
        box.innerHTML = opts.map(c => {
          const phone = c.phone || c.wa_id || '—';
          const display = (c.name || 'Contacto') + ' (' + phone + ')';
          const sel = (selectedContacto && selectedContacto.id == c.id) ? 'checked' : '';
          return '<label class="flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-gray-50">' +
            '<input type="radio" name="msg-contacto" value="' + c.id + '" data-phone="' + escHtml(phone) + '" data-name="' + escHtml(c.name || 'Contacto') + '" ' + sel + ' onchange="seleccionarContacto(this)">' +
            '<span class="text-sm text-gray-700 truncate">' + escHtml(display) + '</span>' +
            '</label>';
        }).join('');
      })
      .catch(() => { box.innerHTML = '<p class="text-xs text-red-500 px-3 py-2">Error de red.</p>'; });
  }, 350);
}

function seleccionarContacto(input) {
  const phone = input.dataset.phone || '';
  const name = input.dataset.name || 'Contacto';
  selectedContacto = { id: input.value, phone: phone, name: name };
  actualizarVistaPrevia();
}

function actualizarVistaPrevia() {
  const msj = document.getElementById('msg-mensaje').value;
  document.getElementById('msg-caracteres').textContent = msj.length;
  document.getElementById('msg-preview-text').textContent = msj || 'Escribe un mensaje para ver la vista previa...';

  const businessId = document.getElementById('business-select').value;
  const seg = document.getElementById('msg-segmento').value;
  const box = document.getElementById('msg-preview-target');
  const info = document.getElementById('msg-preview-info');

  if (!businessId) {
    box.textContent = 'Destinatario: —';
    info.textContent = 'Selecciona el negocio para continuar.';
    return;
  }

  const labels = {
    'todos': 'Todos los contactos',
    'prospecto_sin_historial': 'Prospectos sin historial',
    'prospecto_recurrente': 'Prospectos recurrentes',
    'cliente': 'Clientes',
    'cliente_frecuente': 'Clientes frecuentes',
  };

  if (seg === 'individual') {
    if (selectedContacto && selectedContacto.id) {
      box.textContent = 'Destinatario: ' + (selectedContacto.name || 'Contacto') + ' (' + selectedContacto.phone + ')';
      info.textContent = 'La campaña se encolará a un único contacto.';
    } else {
      box.textContent = 'Destinatario: selecciona un contacto';
      info.textContent = 'Usa la búsqueda para elegir un solo contacto por nombre o WhatsApp.';
    }
    return;
  }

  box.textContent = 'Destinatario: ' + (labels[seg] || seg);
  fetch(BASE_URL + '/admin/crm/' + encodeURIComponent(businessId) + '/list?category=' + encodeURIComponent(seg === 'todos' ? '' : seg))
    .then(r => r.json())
    .then(contacts => {
      if (Array.isArray(contacts)) {
        info.textContent = 'Se encolarán mensajes a ' + contacts.length + ' contacto(s) con WhatsApp del segmento "' + (labels[seg] || seg) + '".';
      }
    })
    .catch(() => { info.textContent = 'No se pudo calcular destinatarios.'; });
}

function validarTextoPermitido(texto) {
  const t = texto.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
  const frasesProhibidas = ['mensaje de prueba', 'mensaje de test', 'mensajeprueba', 'prueba', 'pruebas', 'probando'];
  if (frasesProhibidas.some(p => t.includes(p))) return false;
  if (/\btest(?:ing)?\b/.test(t)) return false;
  return true;
}

function enviarMensaje() {
  const businessId = document.getElementById('business-select').value;
  if (!businessId) { alert('Primero selecciona un negocio.'); return; }

  const nombre = document.getElementById('msg-campana-nombre').value.trim();
  if (!nombre) { alert('El nombre de la campaña es obligatorio.'); return; }

  const mensaje = document.getElementById('msg-mensaje').value.trim();
  if (!mensaje) { alert('Escribe un mensaje.'); return; }

  if (!validarTextoPermitido(nombre) || !validarTextoPermitido(mensaje)) {
    alert('No se permiten mensajes de prueba (palabras como "prueba" o "mensaje de prueba").');
    return;
  }

  const segmento = document.getElementById('msg-segmento').value;
  let contactId = 0;
  if (segmento === 'individual') {
    if (!selectedContacto || !selectedContacto.id) {
      alert('Busca y selecciona un contacto individual.'); return;
    }
    contactId = selectedContacto.id;
  }

  const body = new URLSearchParams();
  body.append('_csrf', CSRF);
  body.append('business_id', businessId);
  body.append('nombre', nombre);
  body.append('mensaje', mensaje);
  body.append('segmento', segmento);
  if (contactId) body.append('contact_id', contactId);

  fetch(BASE_URL + '/admin/crm/mensajes/crear', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  })
  .then(r => {
    if (!r.ok) {
      return r.text().then(t => {
        let msg = 'Error del servidor';
        try { const j = JSON.parse(t); msg = j.error || msg; } catch (e) {}
        throw new Error(msg);
      });
    }
    return r.json();
  })
  .then(d => {
    if (d.ok) {
      document.getElementById('msg-campana-nombre').value = '';
      document.getElementById('msg-mensaje').value = '';
      selectedContacto = null;
      document.getElementById('msg-buscar-results').innerHTML = '';
      actualizarVistaPrevia();
      loadHistorial();
      alert('Campaña creada y ' + d.total + ' envío(s) encolado(s).');
    } else {
      alert(d.error || 'Error al crear la campaña');
    }
  })
  .catch(err => alert(err.message));
}

function segmentoLabel(seg) {
  const labels = {
    'todos': 'Todos',
    'prospecto_sin_historial': 'Prospectos sin historial',
    'prospecto_recurrente': 'Prospectos recurrentes',
    'cliente': 'Clientes',
    'cliente_frecuente': 'Clientes frecuentes',
    'individual': 'Contacto individual',
  };
  return labels[seg] || seg;
}

function estadoClase(estado) {
  const map = {
    'pendiente': 'bg-gray-100 text-gray-700',
    'procesando': 'bg-blue-100 text-blue-700',
    'aceptado_meta': 'bg-purple-100 text-purple-700',
    'enviado': 'bg-indigo-100 text-indigo-700',
    'entregado': 'bg-green-100 text-green-700',
    'leido': 'bg-teal-100 text-teal-700',
    'error': 'bg-red-100 text-red-700',
  };
  return map[estado] || 'bg-gray-100 text-gray-700';
}

function loadHistorial() {
  const businessId = document.getElementById('business-select').value;
  const cont = document.getElementById('msg-historial');
  if (!businessId) {
    cont.innerHTML = '<p class="text-sm text-gray-400">Selecciona un negocio para ver el historial.</p>';
    return;
  }

  cont.innerHTML = '<p class="text-sm text-gray-400">Cargando historial...</p>';
  fetch(BASE_URL + '/admin/crm/mensajes/historial?business_id=' + encodeURIComponent(businessId))
    .then(r => r.json())
    .then(campanas => {
      if (!Array.isArray(campanas) || campanas.length === 0) {
        cont.innerHTML = '<p class="text-sm text-gray-400">Sin campañas registradas para este negocio.</p>';
        return;
      }
      cont.innerHTML = campanas.map(c => {
        const estados = [
          ['pendiente', c.pendientes], ['procesando', c.procesando], ['aceptado_meta', c.aceptados],
          ['enviado', c.enviados], ['entregado', c.entregados], ['leido', c.leidos], ['error', c.errores]
        ].filter(x => parseInt(x[1] || 0, 10) > 0);
        const chips = estados.length
          ? estados.map(e => '<span class="inline-block text-[10px] px-2 py-0.5 rounded-full ' + estadoClase(e[0]) + '">' + e[0].replace('_', ' ') + ' ' + e[1] + '</span>').join(' ')
          : '<span class="text-[10px] text-gray-400">sin envíos</span>';

        return '<div class="border border-gray-200 rounded-xl p-4 mb-2">' +
          '<div class="flex flex-wrap items-center justify-between gap-2">' +
            '<div>' +
              '<p class="text-sm font-semibold text-gray-800">' + escHtml(c.nombre) + '</p>' +
              '<p class="text-xs text-gray-400 mt-0.5">' + segmentoLabel(c.segmento) + ' · ' + (c.total_destinatarios || 0) + ' destinatarios · ' + escHtml(c.creado_en || '') + '</p>' +
            '</div>' +
            '<div class="flex flex-wrap gap-1">' + chips + '</div>' +
          '</div>' +
          '<p class="text-xs text-gray-500 mt-2 whitespace-pre-wrap">' + escHtml(c.mensaje) + '</p>' +
          '<button class="text-xs text-blue-600 hover:underline mt-2" onclick="toggleEnvios(' + c.id_campana + ', this)">Ver envíos</button>' +
          '<div class="msg-envios hidden mt-2" data-campana="' + c.id_campana + '"></div>' +
        '</div>';
      }).join('');
    })
    .catch(() => { cont.innerHTML = '<p class="text-sm text-red-500">Error al cargar el historial.</p>'; });
}

function toggleEnvios(idCampana, btn) {
  const cont = btn.parentNode.querySelector('.msg-envios');
  if (!cont) return;
  if (!cont.classList.contains('hidden')) {
    cont.classList.add('hidden');
    return;
  }

  if (!cont.dataset.loaded) {
    fetch(BASE_URL + '/admin/crm/mensajes/' + idCampana + '/detalle')
      .then(r => r.json())
      .then(envios => {
        if (!Array.isArray(envios)) {
          cont.innerHTML = '<p class="text-xs text-red-500">Error al cargar envíos.</p>';
        } else if (envios.length === 0) {
          cont.innerHTML = '<p class="text-xs text-gray-400">Sin envíos registrados.</p>';
        } else {
          cont.innerHTML = '<div class="max-h-56 overflow-y-auto divide-y divide-gray-100 border border-gray-100 rounded-lg">' +
            envios.map(e =>
              '<div class="flex items-center justify-between px-3 py-2 text-xs">' +
                '<span class="text-gray-700 truncate">' + escHtml(e.contacto_nombre || 'Contacto') + ' (' + escHtml(e.whatsapp || '—') + ')</span>' +
                '<span class="ml-2 px-2 py-0.5 rounded-full ' + estadoClase(e.estado) + '">' + e.estado.replace('_', ' ') + '</span>' +
              '</div>'
            ).join('') + '</div>';
        }
        cont.dataset.loaded = '1';
        cont.classList.remove('hidden');
      })
      .catch(() => { cont.innerHTML = '<p class="text-xs text-red-500">Error de red.</p>'; });
    return;
  }
  cont.classList.remove('hidden');
}
</script>

<style>
  .label { @apply block text-sm font-medium text-gray-700 mb-1; }
  .input  { @apply w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition; }
</style>
<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>