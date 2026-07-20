<?php
$pageTitle = 'CRM – ' . APP_NAME;
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-6xl mx-auto px-4 py-8 mb-24">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">CRM - Mis Contactos</h1>
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
    <button onclick="filterCategory('')" data-cat="" class="cat-filter px-4 py-2 rounded-full text-sm font-medium bg-blue-600 text-white transition">Todos</button>
    <button onclick="filterCategory('prospecto_sin_historial')" data-cat="prospecto_sin_historial" class="cat-filter px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-700 hover:bg-blue-100 transition">Prospectos sin historial</button>
    <button onclick="filterCategory('prospecto_recurrente')" data-cat="prospecto_recurrente" class="cat-filter px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-700 hover:bg-blue-100 transition">Prospectos recurrentes</button>
    <button onclick="filterCategory('cliente')" data-cat="cliente" class="cat-filter px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-700 hover:bg-blue-100 transition">Clientes</button>
    <button onclick="filterCategory('cliente_frecuente')" data-cat="cliente_frecuente" class="cat-filter px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-700 hover:bg-blue-100 transition">Clientes frecuentes</button>
  </div>

  <!-- Contacts table -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="overflow-x-auto">
      <table class="text-sm min-w-[900px] w-full" id="contacts-table">
        <thead class="bg-gray-50">
          <tr class="text-left text-xs text-gray-500 uppercase tracking-wide">
            <th class="px-4 py-3">Nombre</th>
            <th class="px-4 py-3">Telefono</th>
            <th class="px-4 py-3">Email</th>
            <th class="px-4 py-3">Categoria</th>
            <th class="px-4 py-3">Visitas</th>
            <th class="px-4 py-3">Ventas</th>
            <th class="px-4 py-3">Origen</th>
            <th class="px-4 py-3">Ultimo contacto</th>
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
    <button onclick="closeAddModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">X</button>
    <h2 class="text-lg font-bold text-gray-900 mb-4">Nuevo contacto</h2>
    <form onsubmit="saveContact(event)" class="space-y-4">
      <input type="hidden" id="add-business-id" value="">
      <div>
        <label class="label block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
        <input type="text" id="add-name" required class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Telefono</label>
          <input type="tel" id="add-phone" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input type="email" id="add-email" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
        </div>
      </div>
      <div>
        <label class="label block text-sm font-medium text-gray-700 mb-1">Categoria</label>
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

<!-- Upgrade to Cliente Modal -->
<div id="upgrade-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center px-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 relative">
    <button onclick="closeUpgradeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">X</button>
    <h2 class="text-lg font-bold text-gray-900 mb-1">Convertir PROSPECTO a CLIENTE</h2>
    <p class="text-xs text-gray-500 mb-4">Customer Journey: un prospecto se convierte en cliente solo al registrar una compra.</p>
    <p class="text-sm text-gray-500 mb-4" id="upgrade-contact-name"></p>
    <form onsubmit="return upgradeToCliente(event)" class="space-y-4">
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
        <label class="label block text-sm font-medium text-gray-700 mb-1">Notas</label>
        <textarea id="upgrade-notes" rows="2" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="Comentarios adicionales..."></textarea>
      </div>
      <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-xl font-medium hover:bg-green-700 transition">
        Convertir a Cliente
      </button>
    </form>
  </div>
</div>

<!-- Purchase Modal -->
<div id="purchase-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center px-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 relative">
    <button onclick="closePurchaseModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">X</button>
    <h2 class="text-lg font-bold text-gray-900 mb-1">Registrar Nueva Compra</h2>
    <p class="text-xs text-gray-500 mb-4">Customer Journey: mas de 3 compras = cliente recurrente / Lovemark.</p>
    <p class="text-sm text-gray-500 mb-4" id="purchase-contact-name"></p>
    <form onsubmit="return addPurchaseEvent(event)" class="space-y-4">
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
var CSRF = '<?= e($csrf) ?>';
var BASE_URL = '<?= BASE_URL ?>';
var currentCategory = '';

function escHtml(str) {
  var d = document.createElement('div');
  d.textContent = String(str);
  return d.innerHTML;
}

function loadContacts() {
  var businessId = document.getElementById('business-select').value;
  var tbody = document.getElementById('contacts-tbody');
  if (!businessId) {
    tbody.innerHTML = '<tr><td colspan="9" class="text-center py-8 text-gray-400">Selecciona un negocio para ver sus contactos</td></tr>';
    document.getElementById('add-business-id').value = '';
    return;
  }

  document.getElementById('add-business-id').value = businessId;

  var url = BASE_URL + '/admin/crm/' + businessId + '/list?category=' + currentCategory;
  fetch(url)
    .then(function(r) { return r.json(); })
    .then(function(contacts) {
      if (contacts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-8 text-gray-400">No hay contactos en esta categoria</td></tr>';
        return;
      }
      var html = '';
      for (var i = 0; i < contacts.length; i++) {
        var c = contacts[i];
        var categoryLabel = c.category;
        var categoryClass = 'text-purple-600 bg-purple-50';
        if (c.category === 'lovemark') {
          categoryClass = 'text-pink-600 bg-pink-50';
        } else if (c.category === 'cliente') {
          categoryClass = 'text-green-600 bg-green-50';
        } else if (c.category === 'prospecto_recurrente') {
          categoryClass = 'text-orange-600 bg-orange-50';
        }
        var lastContact = c.last_contact_at ? new Date(c.last_contact_at).toLocaleDateString('es-MX') : '-';
        var phone = c.phone || c.wa_id || '-';

        // Build action buttons using data attributes to avoid escaping issues
        var actionsHtml = '<div class="flex gap-1">';
        if (c.category !== 'cliente' && c.category !== 'lovemark') {
          actionsHtml += '<button class="btn-upgrade" data-cid="' + c.id + '" data-cname="' + encodeURIComponent(c.name) + '" type="button" style="font-size:12px;padding:4px 8px;background:#dcfce7;color:#15803d;border-radius:8px;cursor:pointer;border:none;" title="Registrar compra y convertir a cliente">SUBIR</button>';
        }
        if (c.category === 'cliente' || c.category === 'lovemark') {
          actionsHtml += '<button class="btn-purchase" data-cid="' + c.id + '" data-cname="' + encodeURIComponent(c.name) + '" type="button" style="font-size:12px;padding:4px 8px;background:#eff6ff;color:#1d4ed8;border-radius:8px;cursor:pointer;border:none;" title="Registrar compra">COMPRA</button>';
        }
        if (phone !== '-') {
          actionsHtml += '<a href="https://wa.me/' + phone.replace(/\D/g,'') + '" target="_blank" style="font-size:12px;padding:4px 8px;background:#dcfce7;color:#15803d;border-radius:8px;text-decoration:none;display:inline-block;" title="WhatsApp">WA</a>';
        }
        actionsHtml += '</div>';

        html += '<tr class="hover:bg-gray-50">' +
          '<td class="px-4 py-3 font-medium text-gray-800">' + escHtml(c.name) + '</td>' +
          '<td class="px-4 py-3 text-gray-500">' + escHtml(phone) + '</td>' +
          '<td class="px-4 py-3 text-gray-500">' + escHtml(c.email || '-') + '</td>' +
          '<td class="px-4 py-3"><span class="text-xs px-2 py-1 rounded-full font-medium ' + categoryClass + '">' + categoryLabel + '</span></td>' +
          '<td class="px-4 py-3">' + (c.total_visits || 0) + '</td>' +
          '<td class="px-4 py-3">$' + parseFloat(c.total_spent || 0).toFixed(2) + '</td>' +
          '<td class="px-4 py-3">' + (c.source || '-') + '</td>' +
          '<td class="px-4 py-3 text-xs">' + lastContact + '</td>' +
          '<td class="px-4 py-3">' + actionsHtml + '</td>' +
          '</tr>';
      }
      tbody.innerHTML = html;

      // Attach click handlers for upgrade buttons
      var upgradeBtns = tbody.querySelectorAll('.btn-upgrade');
      for (var j = 0; j < upgradeBtns.length; j++) {
        upgradeBtns[j].addEventListener('click', function(ev) {
          var id = this.getAttribute('data-cid');
          var name = decodeURIComponent(this.getAttribute('data-cname'));
          openUpgradeModal(id, name);
        });
      }

      // Attach click handlers for purchase buttons
      var purchaseBtns = tbody.querySelectorAll('.btn-purchase');
      for (var k = 0; k < purchaseBtns.length; k++) {
        purchaseBtns[k].addEventListener('click', function(ev) {
          var id = this.getAttribute('data-cid');
          var name = decodeURIComponent(this.getAttribute('data-cname'));
          openPurchaseModal(id, name);
        });
      }
    })
    .catch(function(err) {
      console.error('CRM Error:', err);
      alert('Error al cargar contactos: ' + err.message);
    });
}

function filterCategory(cat) {
  currentCategory = cat;
  var filters = document.querySelectorAll('.cat-filter');
  for (var i = 0; i < filters.length; i++) {
    var b = filters[i];
    var active = b.getAttribute('data-cat') === cat;
    if (active) {
      b.classList.add('bg-blue-600');
      b.classList.add('text-white');
      b.classList.remove('bg-gray-100');
      b.classList.remove('text-gray-700');
    } else {
      b.classList.remove('bg-blue-600');
      b.classList.remove('text-white');
      b.classList.add('bg-gray-100');
      b.classList.add('text-gray-700');
    }
  }
  loadContacts();
}

function openAddModal() {
  var bid = document.getElementById('business-select').value;
  if (!bid) { alert('Primero selecciona un negocio.'); return; }
  document.getElementById('add-modal').classList.remove('hidden');
}

function closeAddModal() {
  document.getElementById('add-modal').classList.add('hidden');
}

function saveContact(e) {
  e.preventDefault();
  var businessId = document.getElementById('add-business-id').value;
  var name = document.getElementById('add-name').value.trim();
  if (!name) { alert('El nombre es requerido'); return; }

  var params = new URLSearchParams();
  params.append('_csrf', CSRF);
  params.append('business_id', businessId);
  params.append('name', name);
  params.append('phone', document.getElementById('add-phone').value.trim());
  params.append('email', document.getElementById('add-email').value.trim());
  params.append('category', document.getElementById('add-category').value);
  params.append('notes', document.getElementById('add-notes').value.trim());

  fetch(BASE_URL + '/admin/crm/crear', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: params.toString(),
  })
  .then(function(r) { return r.json(); })
  .then(function(d) {
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
  .catch(function(err) { alert('Error: ' + err.message); });
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
  var id = document.getElementById('upgrade-contact-id').value;
  if (!id) { alert('Error: ID de contacto no encontrado'); return false; }

  var products = document.getElementById('upgrade-products').value.trim();
  if (!products) { alert('Captura el producto o servicio vendido'); return false; }

  var params = new URLSearchParams();
  params.append('_csrf', CSRF);
  params.append('name', document.getElementById('upgrade-name').value.trim());
  params.append('email', document.getElementById('upgrade-email').value.trim());
  params.append('amount', document.getElementById('upgrade-amount').value || '0');
  params.append('products', products);
  params.append('notes', document.getElementById('upgrade-notes').value.trim());

  var url = BASE_URL + '/admin/crm/' + id + '/upgrade';
  fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: params.toString(),
  })
  .then(function(r) {
    if (!r.ok) {
      return r.text().then(function(t) { throw new Error(t); });
    }
    return r.json();
  })
  .then(function(d) {
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
  .catch(function(err) {
    alert('Error del servidor: ' + err.message);
  });
  return false;
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
  var id = document.getElementById('purchase-contact-id').value;
  if (!id) { alert('Error: ID de contacto no encontrado'); return false; }

  var products = document.getElementById('purchase-products').value.trim();
  if (!products) { alert('Captura el producto o servicio vendido'); return false; }

  var params = new URLSearchParams();
  params.append('_csrf', CSRF);
  params.append('name', document.getElementById('purchase-name').value.trim());
  params.append('email', document.getElementById('purchase-email').value.trim());
  params.append('amount', document.getElementById('purchase-amount').value || '0');
  params.append('products', products);
  params.append('notes', document.getElementById('purchase-notes').value.trim());

  fetch(BASE_URL + '/admin/crm/' + id + '/compra', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: params.toString(),
  })
  .then(function(r) {
    if (!r.ok) {
      return r.text().then(function(t) { throw new Error(t); });
    }
    return r.json();
  })
  .then(function(d) {
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
  .catch(function(err) {
    alert('Error del servidor: ' + err.message);
  });
  return false;
}
</script>

<style>
  .label { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
  .input { width: 100%; padding: 0.625rem 1rem; border: 1px solid #d1d5db; border-radius: 0.75rem; font-size: 0.875rem; outline: none; transition: all 0.15s; }
  .input:focus { outline: none; ring: 2px solid #3b82f6; border-color: transparent; }
</style>
<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>