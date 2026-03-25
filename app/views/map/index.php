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
      <!-- Mis Favoritos -->
      <button id="btn-mis-favoritos" onclick="openFavoritos()"
        class="flex items-center gap-1.5 text-sm px-3 py-2 rounded-full bg-pink-50 text-pink-600 border border-pink-200 hover:bg-pink-100 transition font-medium whitespace-nowrap">
        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        Mis Favoritos
        <span id="fav-count" class="hidden bg-pink-500 text-white text-xs rounded-full px-1.5 py-0.5 font-bold leading-none"></span>
      </button>
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

<!-- Mis Favoritos modal -->
<div id="favoritos-modal" class="fixed inset-0 z-50 hidden" style="display:none;">
  <div class="absolute inset-0 bg-black bg-opacity-40 flex items-start justify-center pt-16 px-4">
    <div class="absolute inset-0" onclick="closeFavoritos()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[80vh] flex flex-col z-10">
      <div class="flex items-center justify-between p-4 border-b">
        <h2 class="font-bold text-gray-900 flex items-center gap-2">
          <svg class="w-5 h-5 text-pink-500 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
          Mis Favoritos
        </h2>
        <button onclick="closeFavoritos()" class="text-gray-400 hover:text-gray-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
      <div id="favoritos-list" class="overflow-y-auto p-4 space-y-3 flex-1"></div>
    </div>
  </div>
</div>

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
const PRELOAD_ID  = <?= (int)($preloadId ?? 0) ?>;
const PRELOAD_CAT = '<?= e($preloadCat ?? '') ?>';

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
let allPois = [];
let currentCat = PRELOAD_CAT;
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
      allPois = pois;
      pois.forEach(poi => {
        if (!poi.lat || !poi.lng) return;
        const m = L.marker([poi.lat, poi.lng], { icon: createIcon(poi.category_color || '#3B82F6') });
        m.addTo(map);
        m.on('click', () => showPOI(poi));
        markers.push(m);
      });
      if (PRELOAD_ID) {
        const target = pois.find(p => p.id === PRELOAD_ID);
        if (target) showPOI(target);
      }
    });
}

function showPOI(poi) {
  history.pushState({ poiId: poi.id }, '', `${BASE_URL}/mapa/${poi.id}`);

  // Track event
  fetch(`${BASE_URL}/api/analitica`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `event=map_view&business_id=${poi.id}`,
  });

  const isFav = isFavorito(poi.id);
  const html = `
    <img src="${poi.cover}" class="w-full h-40 object-cover rounded-xl mb-3" onerror="this.src='/assets/img/placeholder.svg'">
    <div class="flex items-start justify-between gap-2 mb-2">
      <h3 class="font-bold text-gray-900 text-base leading-tight">${poi.name}</h3>
      <div class="flex items-center gap-1 shrink-0">
        <span class="text-xs px-2 py-1 rounded-full text-white font-medium" style="background:${poi.category_color}">${poi.category}</span>
        <button onclick="toggleFavorito(${poi.id})" id="fav-btn-${poi.id}"
          class="p-1.5 rounded-full hover:bg-pink-50 transition" title="${isFav ? 'Quitar de favoritos' : 'Añadir a favoritos'}">
          <svg class="w-5 h-5 ${isFav ? 'text-pink-500 fill-current' : 'text-gray-300'}" viewBox="0 0 24 24">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"
              ${isFav ? '' : 'stroke="currentColor" stroke-width="1.5" fill="none"'}/>
          </svg>
        </button>
      </div>
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

function resetMapUrl() {
  const url = currentCat ? `${BASE_URL}/mapa/${currentCat}` : `${BASE_URL}/mapa`;
  history.pushState({ poiId: null }, '', url);
}

function closePanel() {
  resetMapUrl();
  document.getElementById('poi-panel').classList.add('translate-x-full');
}

function closeBottomSheet() {
  resetMapUrl();
  document.getElementById('bottom-sheet').classList.add('translate-y-full');
  document.getElementById('bottom-sheet-overlay').classList.add('hidden');
}

function setActiveCatButton(cat) {
  document.querySelectorAll('.cat-btn').forEach(b => {
    const active = b.dataset.cat === cat;
    b.classList.toggle('bg-blue-600', active);
    b.classList.toggle('text-white', active);
    b.classList.toggle('bg-gray-100', !active);
    b.classList.toggle('text-gray-700', !active);
  });
}

function filterCat(cat) {
  currentCat = cat;
  const url = cat ? `${BASE_URL}/mapa/${cat}` : `${BASE_URL}/mapa`;
  history.pushState({ cat }, '', url);
  setActiveCatButton(cat);
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

// ── Favorites (localStorage) ──────────────────────────────────────────────────
const FAV_KEY = 'colonbot_favoritos';

function getFavoritos() {
  try { return JSON.parse(localStorage.getItem(FAV_KEY) || '[]'); } catch (e) { console.error('Favoritos parse error:', e); return []; }
}

function saveFavoritos(favs) {
  localStorage.setItem(FAV_KEY, JSON.stringify(favs));
  updateFavCount();
}

function isFavorito(id) {
  return getFavoritos().some(f => f.id === id);
}

function toggleFavorito(id) {
  let favs = getFavoritos();
  if (isFavorito(id)) {
    favs = favs.filter(f => f.id !== id);
  } else {
    const poi = allPois.find(p => p.id === id);
    if (poi) favs.push(poi);
  }
  saveFavoritos(favs);
  // Update all heart buttons for this POI (desktop panel + mobile sheet)
  document.querySelectorAll(`#fav-btn-${id}`).forEach(btn => {
    const isFav = isFavorito(id);
    const svg = btn.querySelector('svg');
    svg.className = `w-5 h-5 ${isFav ? 'text-pink-500 fill-current' : 'text-gray-300'}`;
    const path = svg.querySelector('path');
    if (isFav) {
      path.removeAttribute('stroke');
      path.removeAttribute('stroke-width');
      path.setAttribute('fill', 'currentColor');
    } else {
      path.setAttribute('stroke', 'currentColor');
      path.setAttribute('stroke-width', '1.5');
      path.setAttribute('fill', 'none');
    }
    btn.title = isFav ? 'Quitar de favoritos' : 'Añadir a favoritos';
  });
}

function updateFavCount() {
  const count = getFavoritos().length;
  const el = document.getElementById('fav-count');
  if (count > 0) {
    el.textContent = count;
    el.classList.remove('hidden');
  } else {
    el.classList.add('hidden');
  }
}

function openFavoritos() {
  const favs = getFavoritos();
  const list = document.getElementById('favoritos-list');
  if (favs.length === 0) {
    list.innerHTML = '<p class="text-center text-gray-400 py-8">Aún no tienes favoritos.<br>Toca el corazón en cualquier negocio para añadirlo.</p>';
  } else {
    list.innerHTML = favs.map(poi => `
      <div class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl hover:bg-gray-50 cursor-pointer fav-item" data-poi-id="${poi.id}">
        <img src="${poi.cover}" class="w-14 h-14 object-cover rounded-lg shrink-0" onerror="this.src='/assets/img/placeholder.svg'">
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-gray-900 text-sm truncate">${poi.name}</p>
          <span class="text-xs px-2 py-0.5 rounded-full text-white font-medium" style="background:${poi.category_color}">${poi.category}</span>
        </div>
        <button data-remove-id="${poi.id}" class="p-1.5 text-pink-500 hover:bg-pink-50 rounded-full transition shrink-0 fav-remove-btn" title="Quitar de favoritos">
          <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        </button>
      </div>
    `).join('');
    list.querySelectorAll('.fav-item').forEach(item => {
      item.addEventListener('click', function(e) {
        if (e.target.closest('.fav-remove-btn')) return;
        const id = parseInt(this.dataset.poiId, 10);
        const poi = getFavoritos().find(f => f.id === id);
        if (poi) { closeFavoritos(); showPOI(poi); }
      });
    });
    list.querySelectorAll('.fav-remove-btn').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleFavoritoFromList(parseInt(this.dataset.removeId, 10));
      });
    });
  }
  document.getElementById('favoritos-modal').style.display = 'block';
}

function closeFavoritos() {
  document.getElementById('favoritos-modal').style.display = 'none';
}

function toggleFavoritoFromList(id) {
  toggleFavorito(id);
  openFavoritos(); // refresh list
}

if (PRELOAD_CAT) {
  setActiveCatButton(PRELOAD_CAT);
}
updateFavCount();
loadPOIs();
</script>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
