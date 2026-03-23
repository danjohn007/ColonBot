<?php
$pageTitle = 'Mapa Turístico – ' . e(setting('site_name', APP_NAME));
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="flex-1 flex flex-col">
  <!-- Filter Bar -->
  <div class="bg-white border-b shadow-sm px-4 py-3">
    <div class="max-w-7xl mx-auto flex flex-wrap gap-3 items-center">
      <!-- Search -->
      <div class="flex-1 min-w-48 relative">
        <input type="text" id="search-input" placeholder="🔍 Buscar lugares..."
          class="w-full pl-4 pr-10 py-2 text-sm border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
        <button onclick="doSearch()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </button>
      </div>
      <!-- Category filters -->
      <div class="flex gap-2 flex-wrap">
        <button onclick="filterCat('')" data-cat=""
          class="cat-btn text-xs px-3 py-1.5 rounded-full bg-gray-100 text-gray-700 hover:bg-blue-100 hover:text-blue-700 transition font-medium active-cat">
          Todos
        </button>
        <?php foreach ($categories as $cat): ?>
        <button onclick="filterCat('<?= e($cat['slug']) ?>')" data-cat="<?= e($cat['slug']) ?>"
          class="cat-btn text-xs px-3 py-1.5 rounded-full bg-gray-100 text-gray-700 hover:bg-blue-100 hover:text-blue-700 transition font-medium"
          style="--cat-color: <?= e($cat['color']) ?>">
          <?= e($cat['name']) ?>
        </button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Map + Sidebar layout -->
  <div class="flex-1 flex relative overflow-hidden" style="height: calc(100vh - 130px);">
    <!-- Map -->
    <div id="map" class="flex-1 z-0"></div>

    <!-- Sidebar panel -->
    <div id="poi-panel" class="hidden md:block w-80 bg-white border-l shadow-lg overflow-y-auto z-10 absolute right-0 top-0 bottom-0 transform translate-x-full transition-transform duration-300">
      <div class="p-4 border-b flex items-center justify-between">
        <h2 class="font-semibold text-gray-800" id="panel-title">Información</h2>
        <button onclick="closePanel()" class="text-gray-400 hover:text-gray-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
      <div id="panel-content" class="p-4 space-y-4"></div>
    </div>
  </div>
</main>

<!-- Mobile bottom sheet -->
<div id="bottom-sheet" class="md:hidden fixed bottom-16 left-0 right-0 bg-white rounded-t-2xl shadow-2xl z-40 transform translate-y-full transition-transform duration-300" style="max-height: 70vh; overflow-y: auto;">
  <div class="flex justify-center py-2">
    <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
  </div>
  <div id="bottom-sheet-content" class="px-4 pb-6"></div>
</div>
<div id="bottom-sheet-overlay" class="md:hidden fixed inset-0 bg-black bg-opacity-30 z-30 hidden" onclick="closeBottomSheet()"></div>

<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const BASE_URL = '<?= BASE_URL ?>';
const MAP_LAT  = <?= setting('map_lat','20.2862') ?>;
const MAP_LNG  = <?= setting('map_lng','-99.7242') ?>;
const MAP_ZOOM = <?= setting('map_zoom','13') ?>;

// Initialise map
const map = L.map('map', { zoomControl: true }).setView([MAP_LAT, MAP_LNG], MAP_ZOOM);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
  maxZoom: 18,
}).addTo(map);

// Geolocation
if (navigator.geolocation) {
  navigator.geolocation.getCurrentPosition(pos => {
    const { latitude: lat, longitude: lng } = pos.coords;
    L.circleMarker([lat, lng], {
      radius: 8, color: '#3B82F6', fillColor: '#3B82F6', fillOpacity: 0.8, weight: 2,
    }).addTo(map).bindPopup('📍 Tu ubicación');
  });
}

let markers = [];
let currentCat = '';
let currentSearch = '';

function createIcon(color) {
  return L.divIcon({
    className: '',
    html: `<div style="background:${color};width:32px;height:32px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,.3)"></div>`,
    iconSize: [32, 32], iconAnchor: [16, 32], popupAnchor: [0, -36],
  });
}

function loadPOIs() {
  const params = new URLSearchParams({ category: currentCat, q: currentSearch });
  fetch(`${BASE_URL}/mapa/poi?${params}`)
    .then(r => r.json())
    .then(pois => {
      markers.forEach(m => map.removeLayer(m));
      markers = [];
      pois.forEach(poi => {
        if (!poi.lat || !poi.lng) return;
        const m = L.marker([poi.lat, poi.lng], { icon: createIcon(poi.category_color || '#3B82F6') });
        m.addTo(map);
        m.on('click', () => showPOI(poi));
        markers.push(m);
      });
    });
}

function showPOI(poi) {
  // Track event
  fetch(`${BASE_URL}/api/analitica`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `event=map_view&business_id=${poi.id}`,
  });

  const html = `
    <img src="${poi.cover}" class="w-full h-40 object-cover rounded-xl mb-3" onerror="this.src='/assets/img/placeholder.svg'">
    <div class="flex items-start justify-between gap-2 mb-2">
      <h3 class="font-bold text-gray-900 text-base leading-tight">${poi.name}</h3>
      <span class="text-xs px-2 py-1 rounded-full text-white font-medium shrink-0" style="background:${poi.category_color}">${poi.category}</span>
    </div>
    <div class="flex items-center gap-1 text-yellow-400 text-sm mb-4">
      ${'★'.repeat(Math.round(poi.rating))}${'☆'.repeat(5-Math.round(poi.rating))}
      <span class="text-gray-500 ml-1">${poi.rating.toFixed(1)}</span>
    </div>
    <div class="grid grid-cols-2 gap-2">
      <a href="${poi.url}" class="col-span-2 flex items-center justify-center gap-2 bg-blue-600 text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
        Ver detalle
      </a>
      <button type="button" onclick="toggleReservarMenu(this)"
        class="col-span-2 flex items-center justify-center gap-2 bg-purple-600 text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-purple-700 transition">
        🛒 Reservar/Comprar
      </button>
      <div class="col-span-2 hidden reservar-menu">
        <div class="grid grid-cols-2 gap-2 mt-1">
          <a href="${poi.url}#productos" class="flex items-center justify-center gap-1.5 bg-blue-50 text-blue-700 border border-blue-200 py-2 rounded-xl text-sm font-medium hover:bg-blue-100 transition">
            🛍️ Productos
          </a>
          <a href="${poi.url}#servicios" class="flex items-center justify-center gap-1.5 bg-green-50 text-green-700 border border-green-200 py-2 rounded-xl text-sm font-medium hover:bg-green-100 transition">
            📋 Servicios
          </a>
          <a href="${poi.url}#amenidades" class="flex items-center justify-center gap-1.5 bg-orange-50 text-orange-700 border border-orange-200 py-2 rounded-xl text-sm font-medium hover:bg-orange-100 transition">
            🛎️ Amenidades
          </a>
          <a href="${poi.url}#eventos" class="flex items-center justify-center gap-1.5 bg-purple-50 text-purple-700 border border-purple-200 py-2 rounded-xl text-sm font-medium hover:bg-purple-100 transition">
            🎉 Eventos
          </a>
        </div>
      </div>
      <a href="https://wa.me/?text=Estoy%20en%20${encodeURIComponent(poi.name)}%20Colón%20Qro" target="_blank"
        onclick="trackWA(${poi.id})"
        class="flex items-center justify-center gap-1.5 bg-green-500 text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-green-600 transition">
        💬 WhatsApp
      </a>
      <a href="https://www.google.com/maps/dir/?api=1&destination=${poi.lat},${poi.lng}" target="_blank"
        class="flex items-center justify-center gap-1.5 bg-orange-500 text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-orange-600 transition">
        🗺️ Cómo llegar
      </a>
    </div>
  `;

  // Desktop panel
  const panel = document.getElementById('poi-panel');
  document.getElementById('panel-title').textContent = poi.name;
  document.getElementById('panel-content').innerHTML = html;
  panel.classList.remove('translate-x-full');

  // Mobile bottom sheet
  document.getElementById('bottom-sheet-content').innerHTML = html;
  document.getElementById('bottom-sheet').classList.remove('translate-y-full');
  document.getElementById('bottom-sheet-overlay').classList.remove('hidden');

  map.panTo([poi.lat, poi.lng]);
}

function closePanel() {
  document.getElementById('poi-panel').classList.add('translate-x-full');
}

function closeBottomSheet() {
  document.getElementById('bottom-sheet').classList.add('translate-y-full');
  document.getElementById('bottom-sheet-overlay').classList.add('hidden');
}

function filterCat(cat) {
  currentCat = cat;
  document.querySelectorAll('.cat-btn').forEach(b => {
    const active = b.dataset.cat === cat;
    b.classList.toggle('bg-blue-600', active);
    b.classList.toggle('text-white', active);
    b.classList.toggle('bg-gray-100', !active);
    b.classList.toggle('text-gray-700', !active);
  });
  loadPOIs();
}

function doSearch() {
  currentSearch = document.getElementById('search-input').value;
  loadPOIs();
}

document.getElementById('search-input')?.addEventListener('keydown', e => {
  if (e.key === 'Enter') doSearch();
});

function trackWA(id) {
  fetch(`${BASE_URL}/api/analitica`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `event=whatsapp_click&business_id=${id}`,
  });
}

function toggleReservarMenu(btn) {
  const menu = btn.nextElementSibling;
  menu.classList.toggle('hidden');
}

loadPOIs();
</script>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
