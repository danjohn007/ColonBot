<?php
$pageTitle = 'Mapa Turístico de Colón';
require APP_PATH . '/views/layout/head.php';
?>
<style>
  .map-title {
    font-family: 'Georgia', 'Times New Roman', serif;
    background: linear-gradient(135deg, #6B21A8, #2563EB, #059669);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-size: 1.75rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    text-shadow: none;
    display: inline-block;
  }
  .map-title-wrapper {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .map-title-wrapper::before {
    content: '🗺️';
    font-size: 1.5rem;
    -webkit-text-fill-color: initial;
  }
</style>
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
        <?php if ($cat['slug'] === 'punto-de-referencia') continue; ?>
        <button onclick="filterCat('<?= e($cat['slug']) ?>')" data-cat="<?= e($cat['slug']) ?>"
          class="cat-btn text-xs px-3 py-1.5 rounded-full bg-gray-100 text-gray-700 hover:bg-blue-100 hover:text-blue-700 transition font-medium"
          style="--cat-color: <?= e($cat['color']) ?>">
          <span class="cat-icon"><?= getCategoryEmoji($cat['icon'] ?? '') ?></span>
          <?= e($cat['name']) ?>
        </button>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Tipo de viaje Filter -->
    <div class="max-w-7xl mx-auto flex flex-wrap gap-2 items-center mt-2">
      <span class="text-xs font-semibold text-gray-500 mr-1">Tipo de viaje:</span>
      <button onclick="filterTripType('')" data-trip-type=""
        class="trip-type-btn text-xs px-3 py-1.5 rounded-full bg-blue-600 text-white transition font-medium">
        Todos
      </button>
      <button onclick="filterTripType('familiar')" data-trip-type="familiar"
        class="trip-type-btn text-xs px-3 py-1.5 rounded-full bg-gray-100 text-gray-700 hover:bg-blue-100 hover:text-blue-700 transition font-medium">
        👨‍👩‍👧‍👦 Familiar
      </button>
      <button onclick="filterTripType('amigos')" data-trip-type="amigos"
        class="trip-type-btn text-xs px-3 py-1.5 rounded-full bg-gray-100 text-gray-700 hover:bg-blue-100 hover:text-blue-700 transition font-medium">
        🧑‍🤝‍🧑 Amigos
      </button>
      <button onclick="filterTripType('pareja')" data-trip-type="pareja"
        class="trip-type-btn text-xs px-3 py-1.5 rounded-full bg-gray-100 text-gray-700 hover:bg-blue-100 hover:text-blue-700 transition font-medium">
        💑 Pareja
      </button>
      <button onclick="filterTripType('petfriendly')" data-trip-type="petfriendly"
        class="trip-type-btn text-xs px-3 py-1.5 rounded-full bg-gray-100 text-gray-700 hover:bg-blue-100 hover:text-blue-700 transition font-medium">
        🐾 Petfriendly
      </button>
    </div>
  </div>

  <!-- Map + Sidebar layout -->
  <div class="flex-1 flex relative overflow-hidden" style="height: calc(100vh - 130px);">
    <!-- Map -->
    <div id="map" class="flex-1 z-0"></div>

    <!-- Route Legend Boxes (right side of map) -->
    <div id="route-legend" class="hidden md:block absolute top-4 right-4 z-20 bg-white/90 backdrop-blur-sm rounded-xl shadow-lg border border-gray-200 p-3 max-w-[200px]">
      <h4 class="text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">Rutas Turísticas</h4>
      <div class="space-y-1.5">
        <?php foreach ($categories as $cat): ?>
        <?php if ($cat['slug'] === 'punto-de-referencia') continue; ?>
        <div class="flex items-center gap-2 text-xs">
          <span class="inline-block w-3 h-3 rounded-sm shrink-0" style="background-color: <?= e($cat['color']) ?>"></span>
          <span class="text-gray-600 truncate"><?= e($cat['name']) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

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

<!-- Contenido informativo -->
<div class="max-w-7xl mx-auto px-4 py-6">
  <div class="prose prose-sm max-w-none">
    <h2 class="text-xl font-bold text-gray-900 mb-3">Colón te conquistará</h2>
    <p class="text-sm text-gray-600 mb-3">Descubre nuestros maravillosos atractivos turísticos que tenemos en nuestro mapa interactivo el cual permite encontrar el mejor destino de acuerdo al estilo de visita que quieras realizar a nuestro municipio: <strong>Familiar, en pareja, con tus mejores amigos, pet friendly</strong>.</p>
    <p class="text-sm text-gray-600 mb-3">Disfruta de los mejores atractivos turísticos públicos o privados y arma tu ruta de visita, contamos con 5 opciones:</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
      <div class="bg-white rounded-xl p-3 border border-gray-100">
        <p class="font-semibold text-sm text-gray-900">🏛️ Turismo Cultural</p>
        <p class="text-xs text-gray-500">Corredores artesanales, museos, mercados, haciendas, recorridos turísticos</p>
      </div>
      <div class="bg-white rounded-xl p-3 border border-gray-100">
        <p class="font-semibold text-sm text-gray-900">⭐ Turismo de Experiencias</p>
        <p class="text-xs text-gray-500">Viñedos, productos locales y nativos, restaurantes gourmet, queserías, miradores, balnearios, paseos a caballo</p>
      </div>
      <div class="bg-white rounded-xl p-3 border border-gray-100">
        <p class="font-semibold text-sm text-gray-900">🌲 Ecoturismo y Aventura</p>
        <p class="text-xs text-gray-500">Senderismo, pesca, Mountain Bike (MTB), crosstrail, camping</p>
      </div>
      <div class="bg-white rounded-xl p-3 border border-gray-100">
        <p class="font-semibold text-sm text-gray-900">⛪ Turismo religioso</p>
        <p class="text-xs text-gray-500">Catedral de Soriano, peregrinaciones, fiestas patronales, iglesias, conventos</p>
      </div>
      <div class="bg-white rounded-xl p-3 border border-gray-100 md:col-span-2">
        <p class="font-semibold text-sm text-gray-900">🍽️ Turismo gastronómico</p>
        <p class="text-xs text-gray-500">Desde una fonda hasta lo mejor de la gastronomía en los mejores restaurantes del municipio</p>
      </div>
    </div>
    <p class="text-sm text-gray-600 mb-3">Combina el estilo de visita que quieras realizar, con el tipo de ruta a explorar y encuentra la mejor hospitalidad de los Colonenses.</p>
    <div class="bg-purple-50 rounded-xl p-4 border border-purple-100 mb-3">
      <h3 class="font-semibold text-sm text-gray-900 mb-2">🤖 Conoce a CristobalBot, nuestro anfitrión a través de WhatsApp</h3>
      <p class="text-xs text-gray-600 mb-2">Te proporciona la ubicación exacta de todos nuestros atractivos turísticos, información detallada de los productos y servicios creados para nuestros visitantes, mándale un WhatsApp y:</p>
      <ul class="text-xs text-gray-600 list-disc list-inside space-y-1 mb-3">
        <li>Haz una reservación</li>
        <li>Contacta directamente al prestador de servicios turísticos</li>
        <li>Imágenes de los lugares de tu interés</li>
        <li>Las mejores rutas para llegar a tu destino a través de Waze o Google Maps</li>
      </ul>
      <?php if (setting('chatbot_wa_number', '')): ?>
      <a href="https://wa.me/<?= e(preg_replace('/\D/', '', setting('chatbot_wa_number', ''))) ?>" target="_blank" class="inline-flex items-center gap-2 bg-green-500 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-green-600 transition">
        📱 Contáctalo aquí
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Emergency Numbers Section -->
<div class="border-t border-gray-200 bg-gradient-to-r from-red-50 to-orange-50">
  <div class="max-w-7xl mx-auto px-4 py-6">
    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">🆘 Números de emergencia</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
      <a href="tel:911" class="flex items-center gap-3 p-3 bg-white rounded-xl border border-red-200 hover:shadow-md hover:border-red-400 transition group">
        <span class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 text-lg shrink-0 group-hover:scale-110 transition">📞</span>
        <div class="min-w-0">
          <p class="font-semibold text-gray-900 text-sm leading-tight">Emergencias</p>
          <p class="text-red-600 text-sm font-bold">911</p>
          <p class="text-gray-400 text-xs">Policía, bomberos, ambulancia y protección civil</p>
        </div>
      </a>
      <a href="tel:089" class="flex items-center gap-3 p-3 bg-white rounded-xl border border-red-200 hover:shadow-md hover:border-red-400 transition group">
        <span class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 text-lg shrink-0 group-hover:scale-110 transition">📞</span>
        <div class="min-w-0">
          <p class="font-semibold text-gray-900 text-sm leading-tight">Denuncia Anónima</p>
          <p class="text-red-600 text-sm font-bold">089</p>
        </div>
      </a>
      <a href="tel:4192920296" class="flex items-center gap-3 p-3 bg-white rounded-xl border border-red-200 hover:shadow-md hover:border-red-400 transition group">
        <span class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 text-lg shrink-0 group-hover:scale-110 transition">📞</span>
        <div class="min-w-0">
          <p class="font-semibold text-gray-900 text-sm leading-tight">Protección Civil Colón</p>
          <p class="text-red-600 text-sm font-bold">419 292 0296</p>
        </div>
      </a>
      <a href="tel:4192920061" class="flex items-center gap-3 p-3 bg-white rounded-xl border border-red-200 hover:shadow-md hover:border-red-400 transition group">
        <span class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 text-lg shrink-0 group-hover:scale-110 transition">📞</span>
        <div class="min-w-0">
          <p class="font-semibold text-gray-900 text-sm leading-tight">Presidencia Municipal de Colón</p>
          <p class="text-red-600 text-sm font-bold">419 292 0061</p>
        </div>
      </a>
    </div>
  </div>
</div>

<!-- Banners de registro público entre mapa y footer -->
<div class="bg-gradient-to-r from-purple-50 to-blue-50 border-t border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 py-6 grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Banner 1: Visitante -->
    <a href="<?= url('registro/visitante') ?>" class="block bg-white rounded-2xl shadow-sm border border-purple-100 p-6 hover:shadow-md hover:border-purple-300 transition group">
      <div class="flex items-start gap-4">
        <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition">👤</div>
        <div>
          <h3 class="font-bold text-gray-900 text-sm mb-1">Regístrate como visitante y obtén descuentos exclusivos</h3>
          <p class="text-xs text-gray-500">Califica y deja comentarios de nuestros atractivos turísticos, aquí</p>
        </div>
      </div>
    </a>
    <!-- Banner 2: Prestador -->
    <a href="<?= url('registro/prestador') ?>" class="block bg-white rounded-2xl shadow-sm border border-blue-100 p-6 hover:shadow-md hover:border-blue-300 transition group">
      <div class="flex items-start gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition">🏪</div>
        <div>
          <h3 class="font-bold text-gray-900 text-sm mb-1">¿Eres prestador de servicio?</h3>
          <p class="text-xs text-gray-500">Da de alta tu negocio aquí</p>
        </div>
      </div>
    </a>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const BASE_URL = '<?= BASE_URL ?>';
const MAP_LAT  = <?= setting('map_lat','20.2862') ?>;
const MAP_LNG  = <?= setting('map_lng','-99.7242') ?>;
const MAP_ZOOM = <?= setting('map_zoom','13') ?>;
const PRELOAD_ID  = <?= (int)($preloadId ?? 0) ?>;
const PRELOAD_CAT = '<?= e($preloadCat ?? '') ?>';
const BOUNDARY_DATA = <?= $boundaryData ?: '[]' ?>;
const CHATBOT_ACTIVE    = <?= setting('chatbot_active', '0') === '1' ? 'true' : 'false' ?>;
const CHATBOT_WA_NUMBER = '<?= e(setting('chatbot_wa_number', '')) ?>';

// ─── Icon name → emoji mapping for category symbols (panels/modals) ────
const ICON_MAP = {
  'utensils':      '\u{1F37D}\u{FE0F}',
  'hotel':         '\u{1F3E8}',
  'wine':          '\u{1F377}',
  'landmark':      '\u{1F3DB}\u{FE0F}',
  'star':          '\u{2B50}',
  'waves':         '\u{1F30A}',
  'shopping-bag':  '\u{1F6CD}\u{FE0F}',
  'map-pin':       '\u{1F4CD}',
  'cross':         '\u{271D}\u{FE0F}',
  'ecoturismo':    '\u{2B50}',
  'default':       '\u{1F4CD}',
};

const ISOTIPO_ICON_MAP = {
  'restaurante':       '\u{1F37D}\u{FE0F}',
  'lugares_historicos': '\u{1F3DB}\u{FE0F}',
  'viniedo':           '\u{1F347}',
  'hotel':             '\u{1F3E8}',
  'paisaje_cerro':     '\u{1F332}',
  'lago_presa':        '\u{1F30A}',
  'lugar_compras':     '\u{1F6CD}\u{FE0F}',
  'pena_bernal':       '\u{1F3D4}\u{FE0F}',
  'aeropuerto':        '\u{2708}\u{FE0F}',
  'zoologico_wameru':  '\u{1F981}',
  'arcos_queretaro':   '\u{1F309}',
  'estacion_tren':     '\u{1F682}',
  'lugar_religioso':   '\u{26EA}',
  'apicultura':        '\u{1F41D}',
};

function iconToEmoji(iconName) {
  return ICON_MAP[iconName] || ICON_MAP['default'];
}

function isotipoToEmoji(isotipo) {
  return ISOTIPO_ICON_MAP[isotipo] || ICON_MAP['default'];
}

// Initialise map
const map = L.map('map', { zoomControl: true }).setView([MAP_LAT, MAP_LNG], MAP_ZOOM);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '\u00A9 <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
  maxZoom: 18,
}).addTo(map);

// ─── Divisi\u00F3n territorial (l\u00EDmite municipal de Col\u00F3n) ──────────────────
if (BOUNDARY_DATA && BOUNDARY_DATA.length > 0) {
  // Si es array de arrays (m\u00FAltiples anillos), dibujar cada uno como polyline
  if (Array.isArray(BOUNDARY_DATA[0])) {
    BOUNDARY_DATA.forEach(ring => {
      L.polyline(ring, {
        color: '#8B5CF6',
        weight: 2,
        opacity: 0.8,
        dashArray: '8, 8',
      }).addTo(map);
    });
  } else {
    // Compatibilidad con formato plano anterior
    L.polyline(BOUNDARY_DATA, {
      color: '#8B5CF6',
      weight: 2,
      opacity: 0.8,
      dashArray: '8, 8',
    }).addTo(map);
  }
}

// ─── Geolocalizaci\u00F3n ─────────────────────────────────────────────────────
if (navigator.geolocation) {
  navigator.geolocation.getCurrentPosition(pos => {
    const { latitude: lat, longitude: lng } = pos.coords;
    L.circleMarker([lat, lng], {
      radius: 8, color: '#3B82F6', fillColor: '#3B82F6', fillOpacity: 0.8, weight: 2,
    }).addTo(map).bindPopup('\u{1F4CD} Tu ubicaci\u00F3n');
  });
}

let markers = [];
let allPois = [];
let allRefPoints = []; // Puntos de referencia siempre visibles
let currentCat = PRELOAD_CAT;
let currentSearch = '';
let currentIsotipo = '';
let currentTripType = '';

function createIcon(color, emoji) {
  return L.divIcon({
    className: '',
    html: `<div style="background:${color};width:34px;height:34px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center">
      <span style="transform:rotate(45deg);font-size:17px;line-height:1">${emoji}</span>
    </div>`,
    iconSize: [34, 34], iconAnchor: [17, 34], popupAnchor: [0, -38],
  });
}

function loadPOIs() {
  const params = new URLSearchParams({ category: currentCat, q: currentSearch });
  fetch(`${BASE_URL}/mapa/poi?${params}`)
    .then(r => r.json())
    .then(pois => {
      // Separate regular POIs from reference points
      allPois = pois.filter(p => p.category_slug !== 'punto-de-referencia');
      allRefPoints = pois.filter(p => p.category_slug === 'punto-de-referencia');

      markers.forEach(m => map.removeLayer(m));
      markers = [];

      // Always add reference points with their own TIPO DE LUGAR (isotipo) icon
      allRefPoints.forEach(poi => {
        if (!poi.lat || !poi.lng) return;
        const refEmoji = isotipoToEmoji(poi.isotipo) || iconToEmoji(poi.category_icon);
        const m = L.marker([poi.lat, poi.lng], { icon: createIcon(poi.category_color || '#8B5CF6', refEmoji) });
        m.addTo(map);
        m.on('click', () => showPOI(poi));
        markers.push(m);
      });

      // Add filtered POIs - also use TIPO DE LUGAR (isotipo) icon
      allPois.forEach(poi => {
        if (!poi.lat || !poi.lng) return;
        const poiEmoji = isotipoToEmoji(poi.isotipo);
        const m = L.marker([poi.lat, poi.lng], { icon: createIcon(poi.category_color || '#3B82F6', poiEmoji) });
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

  // Trip type labels with emojis
  const TRIP_TYPE_MAP = {
    'familiar':       '\u{1F468}\u200D\u{1F469}\u200D\u{1F467}\u200D\u{1F466} Familiar',
    'amigos':         '\u{1F9D1}\u200D\u{1F91D}\u200D\u{1F9D1} Amigos',
    'pareja':         '\u{1F491} Pareja',
    'petfriendly':    '\u{1F43E} Petfriendly',
  };

  // Isotipo labels with emojis
  const ISOTIPO_LABEL_MAP = {
    'restaurante':       '\u{1F37D}\u{FE0F} Restaurante',
    'lugares_historicos':'\u{1F3DB}\u{FE0F} Lugares hist\u00F3ricos',
    'viniedo':           '\u{1F347} Vi\u00F1edo',
    'hotel':             '\u{1F3E8} Hotel',
  'paisaje_cerro':     '\u{1F332} Paisaje/cerro',
    'lago_presa':        '\u{1F30A} Lago/presa',
    'lugar_compras':     '\u{1F6CD}\u{FE0F} Lugar de compras',
    'pena_bernal':       '\u{1F3D4}\u{FE0F} Pe\u00F1a de Bernal',
    'aeropuerto':        '\u{2708}\u{FE0F} Aeropuerto',
    'zoologico_wameru':  '\u{1F981} Zool\u00F3gico Wamer\u00FA',
    'arcos_queretaro':   '\u{1F309} Los Arcos de Quer\u00E9taro',
    'estacion_tren':     '\u{1F682} Estaci\u00F3n del tren M\u00E9xico-Quer\u00E9taro',
    'lugar_religioso':   '\u{26EA} Lugar religioso',
    'apicultura':        '\u{1F41D} Apicultura',
  };

  const isFav = isFavorito(poi.id);
  const categoryEmoji = iconToEmoji(poi.category_icon);
  const isPuntoReferencia = poi.category_slug === 'punto-de-referencia';

  // Build trip type badges HTML
  const tripTypeBadges = (poi.trip_types && poi.trip_types.length > 0)
    ? poi.trip_types.map(tt => {
        const label = TRIP_TYPE_MAP[tt] || tt;
        return `<span class="inline-block text-xs px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 border border-purple-200 font-medium">${label}</span>`;
      }).join(' ')
    : '';

  // Build isotipo badge HTML
  const isotipoLabel = ISOTIPO_LABEL_MAP[poi.isotipo] || poi.isotipo || '';
  const isotipoBadge = isotipoLabel
    ? `<span class="inline-block text-xs px-2 py-0.5 rounded-full bg-orange-50 text-orange-700 border border-orange-200 font-medium">${isotipoLabel}</span>`
    : '';

  const html = `
    <img src="${poi.cover}" class="w-full h-40 object-cover rounded-xl mb-3" onerror="this.src='/assets/img/placeholder.svg'">
    <div class="flex items-start justify-between gap-2 mb-2">
      <h3 class="font-bold text-gray-900 text-base leading-tight">${poi.name}</h3>
      <div class="flex items-center gap-1 shrink-0">
        <span class="text-xs px-2 py-1 rounded-full text-white font-medium" style="background:${poi.category_color}">${categoryEmoji} ${poi.category}</span>
        <button onclick="toggleFavorito(${poi.id})" id="fav-btn-${poi.id}"
          class="p-1.5 rounded-full hover:bg-pink-50 transition" title="${isFav ? 'Quitar de favoritos' : 'A\u00F1adir a favoritos'}">
          <svg class="w-5 h-5 ${isFav ? 'text-pink-500 fill-current' : 'text-gray-300'}" viewBox="0 0 24 24">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"
              ${isFav ? '' : 'stroke="currentColor" stroke-width="1.5" fill="none"'}/>
          </svg>
        </button>
      </div>
    </div>
    ${!isPuntoReferencia ? `
    <div class="flex items-center gap-1 text-yellow-400 text-sm mb-3">
      ${'\u2605'.repeat(Math.round(poi.rating))}${'\u2606'.repeat(5-Math.round(poi.rating))}
      <span class="text-gray-500 ml-1">${poi.rating.toFixed(1)}</span>
    </div>
    ` : ''}
    ${!isPuntoReferencia && isotipoBadge ? `<div class="flex items-center gap-2 mb-3">${isotipoBadge}</div>` : ''}
    ${!isPuntoReferencia && tripTypeBadges ? `<div class="flex flex-wrap gap-1 mb-3">${tripTypeBadges}</div>` : ''}
    <div class="grid grid-cols-2 gap-2">
      ${!isPuntoReferencia ? `
      <a href="${poi.url}" class="col-span-2 flex items-center justify-center gap-2 bg-blue-600 text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
        Ver detalle
      </a>
      ` : ''}
      ${!isPuntoReferencia ? (CHATBOT_ACTIVE && CHATBOT_WA_NUMBER
        ? `<a href="https://wa.me/${CHATBOT_WA_NUMBER}?text=${encodeURIComponent('Hola, quiero ver las opciones para ' + poi.name)}" target="_blank"
            class="col-span-2 flex items-center justify-center gap-2 bg-purple-600 text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-purple-700 transition">
            \u{1F4AC} Reservar por Whatsapp
          </a>`
        : `<button type="button" onclick="toggleReservarMenu(this)"
            class="col-span-2 flex items-center justify-center gap-2 bg-purple-600 text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-purple-700 transition">
            \u{1F4AC} Reservar por Whatsapp
          </button>
          <div class="col-span-2 hidden reservar-menu">
            <div class="grid grid-cols-2 gap-2 mt-1">
              <a href="${poi.url}#productos" class="flex items-center justify-center gap-1.5 bg-blue-50 text-blue-700 border border-blue-200 py-2 rounded-xl text-sm font-medium hover:bg-blue-100 transition">
                \u{1F6CD}\u{FE0F} Productos
              </a>
              <a href="${poi.url}#servicios" class="flex items-center justify-center gap-1.5 bg-green-50 text-green-700 border border-green-200 py-2 rounded-xl text-sm font-medium hover:bg-green-100 transition">
                \u{1F4CB} Servicios
              </a>
              <a href="${poi.url}#amenidades" class="flex items-center justify-center gap-1.5 bg-orange-50 text-orange-700 border border-orange-200 py-2 rounded-xl text-sm font-medium hover:bg-orange-100 transition">
                \u{1F6CE}\u{FE0F} Amenidades
              </a>
              <a href="${poi.url}#eventos" class="flex items-center justify-center gap-1.5 bg-purple-50 text-purple-700 border border-purple-200 py-2 rounded-xl text-sm font-medium hover:bg-purple-100 transition">
                \u{1F389} Eventos
              </a>
            </div>
          </div>`) : ''}

      <a href="https://www.google.com/maps/dir/?api=1&destination=${poi.lat},${poi.lng}" target="_blank"
        class="flex items-center justify-center gap-1.5 bg-orange-500 text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-orange-600 transition">
        \u{1F5FA}\u{FE0F} C\u00F3mo llegar
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

function setActiveIsotipoButton(isotipo) {
  document.querySelectorAll('.isotipo-btn').forEach(b => {
    const active = b.dataset.isotipo === isotipo;
    b.classList.toggle('bg-blue-600', active);
    b.classList.toggle('text-white', active);
    b.classList.toggle('bg-gray-100', !active);
    b.classList.toggle('text-gray-700', !active);
  });
}

function setActiveTripTypeButton(tripType) {
  document.querySelectorAll('.trip-type-btn').forEach(b => {
    const active = b.dataset.tripType === tripType;
    b.classList.toggle('bg-blue-600', active);
    b.classList.toggle('text-white', active);
    b.classList.toggle('bg-gray-100', !active);
    b.classList.toggle('text-gray-700', !active);
  });
}

function filterTripType(tripType) {
  currentTripType = tripType;
  setActiveTripTypeButton(tripType);
  // Filter POIs client-side based on the trip type
  let filtered = allPois;
  if (currentTripType) {
    filtered = allPois.filter(poi => poi.trip_types && poi.trip_types.includes(currentTripType));
  }
  // Clear existing markers
  markers.forEach(m => map.removeLayer(m));
  markers = [];

  // Always add reference points with their own TIPO DE LUGAR (isotipo) icon
  allRefPoints.forEach(poi => {
    if (!poi.lat || !poi.lng) return;
    const refEmoji = isotipoToEmoji(poi.isotipo) || iconToEmoji(poi.category_icon);
    const m = L.marker([poi.lat, poi.lng], { icon: createIcon(poi.category_color || '#8B5CF6', refEmoji) });
    m.addTo(map);
    m.on('click', () => showPOI(poi));
    markers.push(m);
  });

  // Add filtered POIs
  filtered.forEach(poi => {
    if (!poi.lat || !poi.lng) return;
    const poiEmoji = isotipoToEmoji(poi.isotipo);
    const m = L.marker([poi.lat, poi.lng], { icon: createIcon(poi.category_color || '#3B82F6', poiEmoji) });
    m.addTo(map);
    m.on('click', () => showPOI(poi));
    markers.push(m);
  });
}

function filterIsotipo(isotipo) {
  currentIsotipo = isotipo;
  setActiveIsotipoButton(isotipo);
  // Filter POIs client-side based on the isotipo
  const filtered = currentIsotipo
    ? allPois.filter(poi => poi.isotipo === currentIsotipo)
    : allPois;
  // Clear existing markers
  markers.forEach(m => map.removeLayer(m));
  markers = [];
  filtered.forEach(poi => {
    if (!poi.lat || !poi.lng) return;
    const poiEmoji = isotipoToEmoji(poi.isotipo);
    const m = L.marker([poi.lat, poi.lng], { icon: createIcon(poi.category_color || '#3B82F6', poiEmoji) });
    m.addTo(map);
    m.on('click', () => showPOI(poi));
    markers.push(m);
  });
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
    btn.title = isFav ? 'Quitar de favoritos' : 'A\u00F1adir a favoritos';
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
    list.innerHTML = '<p class="text-center text-gray-400 py-8">A\u00FAn no tienes favoritos.<br>Toca el coraz\u00F3n en cualquier negocio para a\u00F1adirlo.</p>';
  } else {
    list.innerHTML = favs.map(poi => {
      const categoryEmoji = iconToEmoji(poi.category_icon);
      return `
      <div class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl hover:bg-gray-50 cursor-pointer fav-item" data-poi-id="${poi.id}">
        <img src="${poi.cover}" class="w-14 h-14 object-cover rounded-lg shrink-0" onerror="this.src='/assets/img/placeholder.svg'">
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-gray-900 text-sm truncate">${poi.name}</p>
          <span class="text-xs px-2 py-0.5 rounded-full text-white font-medium" style="background:${poi.category_color}">${categoryEmoji} ${poi.category}</span>
        </div>
        <button data-remove-id="${poi.id}" class="p-1.5 text-pink-500 hover:bg-pink-50 rounded-full transition shrink-0 fav-remove-btn" title="Quitar de favoritos">
          <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        </button>
      </div>`;
    }).join('');
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