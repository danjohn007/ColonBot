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
      <button onclick="filterTripType('adultos_mayores')" data-trip-type="adultos_mayores"
        class="trip-type-btn text-xs px-3 py-1.5 rounded-full bg-gray-100 text-gray-700 hover:bg-blue-100 hover:text-blue-700 transition font-medium">
        👴 Adultos Mayores
      </button>
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
const CHATBOT_ACTIVE    = <?= setting('chatbot_active', '0') === '1' ? 'true' : 'false' ?>;
const CHATBOT_WA_NUMBER = '<?= e(setting('chatbot_wa_number', '')) ?>';

// ─── Icon name → emoji mapping for category symbols (panels/modals) ────
const ICON_MAP = {
  'utensils':      '🍽️',
  'hotel':         '🏨',
  'wine':          '🍷',
  'landmark':      '🏛️',
  'star':          '⭐',
  'waves':         '🌊',
  'shopping-bag':  '🛍️',
  'map-pin':       '📍',
  'cross':         '✝️',     // Turismo religioso
  'ecoturismo':    '⭐',     // Ecoturismo y Aventura (estrella)
  'default':       '📍',
};

const ISOTIPO_ICON_MAP = {
  'restaurante':       '🍽️',
  'lugares_historicos': '🏛️',
  'viniedo':           '🍷',
  'hotel':             '🏨',
  'paisaje_cerro':     '⭐',
  'lago_presa':        '🌊',
  'lugar_compras':     '🛍️',
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
  attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
  maxZoom: 18,
}).addTo(map);

// ─── Límite municipal de Colón, Querétaro ──────────────────────────────
// Colores súper vibrantes para destacar el municipio al máximo
const BOUNDARY_COLOR       = '#DC2626';      // Rojo intenso
const BOUNDARY_COLOR_ALT   = '#FF6B00';      // Naranja vibrante
const BOUNDARY_GLOW_COLOR  = '#FF6B6B';      // Rojo claro para glow exterior
const BOUNDARY_DASH_COLOR  = '#FFFFFF';      // Blanco puro para la línea discontinua
const BOUNDARY_ACCENT      = '#FFD700';      // Dorado para capa de acento
const BOUNDARY_FILL_COLOR  = '#DC2626';      // Color de relleno

// Capa 1 - Relleno del área municipal (más notorio) - AUMENTADO
const BOUNDARY_FILL_STYLE = {
  color: BOUNDARY_FILL_COLOR,
  weight: 2,
  opacity: 0.5,
  fillColor: BOUNDARY_FILL_COLOR,
  fillOpacity: 0.18,
  className: 'colon-boundary-fill',
};

// Capa 2 - Glow exterior MEGA amplio (efecto de resplandor enorme) - MEJORADO
const BOUNDARY_GLOW_STYLE = {
  color: BOUNDARY_GLOW_COLOR,
  weight: 80,
  opacity: 0.35,
  fill: false,
  className: 'colon-boundary-glow',
};

// Capa 3 - Línea sólida principal MUY gruesa y visible - AUMENTADA
const BOUNDARY_STYLE = {
  color: BOUNDARY_COLOR,
  weight: 16,
  opacity: 1.0,
  fillColor: BOUNDARY_COLOR,
  fillOpacity: 0.08,
  className: 'colon-boundary-layer',
};

// Capa 4 - Línea discontinua animada encima del borde principal - MEJORADA
const BOUNDARY_DASH_STYLE = {
  color: BOUNDARY_DASH_COLOR,
  weight: 7,
  opacity: 0.95,
  fill: false,
  dashArray: '10, 8',
  className: 'colon-boundary-dash',
};

// Capa 5 - Línea de acento dorado para dar más énfasis - MEJORADA
const BOUNDARY_INNER_STYLE = {
  color: BOUNDARY_ACCENT,
  weight: 20,
  opacity: 0.30,
  fill: false,
  className: 'colon-boundary-glow',
};

// Capa 6 - Sombra exterior adicional para efecto 3D - MEJORADA
const BOUNDARY_SHADOW_STYLE = {
  color: '#000000',
  weight: 100,
  opacity: 0.15,
  fill: false,
  className: 'colon-boundary-glow',
};

// Centro geográfico aproximado de Colón para la etiqueta
const COLON_CENTER_LAT = 20.786;
const COLON_CENTER_LNG = -100.050;

/**
 * Dibuja el límite municipal en el mapa a partir de un arreglo de coordenadas [lat, lng].
 */
function drawBoundary(latlngs) {
  if (!latlngs || latlngs.length < 3) {
    console.error('Coordenadas insuficientes para dibujar el límite');
    return;
  }

  // --- Capa 1: Sombra exterior (efecto 3D) ---
  L.polygon(latlngs, BOUNDARY_SHADOW_STYLE).addTo(map).bringToBack();

  // --- Capa 2: Relleno del área (base) ---
  L.polygon(latlngs, BOUNDARY_FILL_STYLE).addTo(map).bringToBack();

  // --- Capa 3: Glow exterior MEGA amplio ---
  L.polygon(latlngs, BOUNDARY_GLOW_STYLE).addTo(map).bringToBack();

  // --- Capa 4: Línea principal sólida ---
  const mainBoundary = L.polygon(latlngs, BOUNDARY_STYLE).addTo(map);

  // --- Capa 5: Línea discontinua animada encima ---
  L.polygon(latlngs, BOUNDARY_DASH_STYLE).addTo(map);

  // --- Capa 6: Línea de acento dorado ---
  L.polygon(latlngs, BOUNDARY_INNER_STYLE).addTo(map).bringToBack();

  // --- Etiqueta con el nombre del municipio ---
  const labelIcon = L.divIcon({
    className: 'colon-boundary-label',
    html: '<div>MUNICIPIO DE COLÓN</div><small>QUERÉTARO, MÉXICO</small>',
    iconSize: [260, 70],
    iconAnchor: [130, 35],
  });
  L.marker([COLON_CENTER_LAT, COLON_CENTER_LNG], {
    icon: labelIcon,
    interactive: false,
    keyboard: false,
  }).addTo(map);

  console.log('✅ Límite municipal de Colón dibujado correctamente');
}

// ─── Límite HARDCODEADO de Colón (respaldo si Nominatim falla) ───
// Coordenadas más precisas del perímetro de Colón, Querétaro
// Estos puntos representan aproximadamente los vértices del municipio
const COLON_BOUNDARY_FALLBACK = [
  [20.8851, -100.1853],  // Noroeste
  [20.8934, -100.1547],  // Norte
  [20.8812, -100.0987],  // Noreste
  [20.8342, -100.0234],  // Este
  [20.7894, -99.9876],   // Sureste
  [20.7456, -99.9642],   // Sur-centro
  [20.7123, -99.9912],   // Suroeste
  [20.6789, -100.0234],  // Oeste-sur
  [20.6845, -100.1234],  // Oeste-centro
  [20.7234, -100.1876],  // Oeste-norte
  [20.7689, -100.1923],  // Centro-norte
  [20.8234, -100.1943],  // Norte-oeste
  [20.8851, -100.1853],  // Cierre del polígono
];

// Función para cargar el límite - primero intenta Nominatim, luego fallback
function loadColonBoundary() {
  console.log('📍 Iniciando carga del límite de Colón...');
  
  fetch('https://nominatim.openstreetmap.org/lookup?osm_ids=R2671516&format=geojson', {
    timeout: 5000,
  })
    .then(r => {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(geojson => {
      if (!geojson || !geojson.features || geojson.features.length === 0) {
        throw new Error('Nominatim: sin datos');
      }
      const feat = geojson.features[0];
      const coords = feat.geometry.coordinates;
      let ring;
      if (feat.geometry.type === 'MultiPolygon') {
        ring = coords[0][0];
      } else {
        ring = coords[0];
      }
      const latlngs = ring.map(c => [c[1], c[0]]);
      console.log('✅ Límite cargado desde Nominatim - Puntos:', latlngs.length);
      drawBoundary(latlngs);
    })
    .catch(err => {
      console.warn('⚠️ Nominatim falló, usando límite fallback:', err.message);
      console.log('📍 Dibujando polígono fallback con', COLON_BOUNDARY_FALLBACK.length, 'puntos');
      drawBoundary(COLON_BOUNDARY_FALLBACK);
    });
}

// Ejecutar carga del límite INMEDIATAMENTE
console.log('⏳ Esperando disponibilidad de Leaflet...');
if (typeof L !== 'undefined') {
  loadColonBoundary();
} else {
  setTimeout(loadColonBoundary, 500);
}

// ─── Geolocalización ─────────────────────────────────────────────────────
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
      markers.forEach(m => map.removeLayer(m));
      markers = [];
      allPois = pois;
      pois.forEach(poi => {
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
    'familiar':       '👨‍👩‍👧‍👦 Familiar',
    'amigos':         '🧑‍🤝‍🧑 Amigos',
    'pareja':         '💑 Pareja',
    'petfriendly':    '🐾 Petfriendly',
    'adultos_mayores':'👴 Adultos Mayores',
  };

  // Isotipo labels with emojis
  const ISOTIPO_LABEL_MAP = {
    'restaurante':       '🍽️ Restaurante',
    'lugares_historicos':'🏛️ Lugares históricos',
    'viniedo':           '🍷 Viñedo',
    'hotel':             '🏨 Hotel',
    'paisaje_cerro':     '⭐ Paisaje/cerro',
    'lago_presa':        '🌊 Lago/presa',
    'lugar_compras':     '🛍️ Lugar de compras',
  };

  const isFav = isFavorito(poi.id);
  const categoryEmoji = iconToEmoji(poi.category_icon);

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
          class="p-1.5 rounded-full hover:bg-pink-50 transition" title="${isFav ? 'Quitar de favoritos' : 'Añadir a favoritos'}">
          <svg class="w-5 h-5 ${isFav ? 'text-pink-500 fill-current' : 'text-gray-300'}" viewBox="0 0 24 24">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"
              ${isFav ? '' : 'stroke="currentColor" stroke-width="1.5" fill="none"'}/>
          </svg>
        </button>
      </div>
    </div>
    <div class="flex items-center gap-1 text-yellow-400 text-sm mb-3">
      ${'★'.repeat(Math.round(poi.rating))}${'☆'.repeat(5-Math.round(poi.rating))}
      <span class="text-gray-500 ml-1">${poi.rating.toFixed(1)}</span>
    </div>
    ${isotipoBadge ? `<div class="flex items-center gap-2 mb-3">${isotipoBadge}</div>` : ''}
    ${tripTypeBadges ? `<div class="flex flex-wrap gap-1 mb-3">${tripTypeBadges}</div>` : ''}
    <div class="grid grid-cols-2 gap-2">
      <a href="${poi.url}" class="col-span-2 flex items-center justify-center gap-2 bg-blue-600 text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
        Ver detalle
      </a>
      ${CHATBOT_ACTIVE && CHATBOT_WA_NUMBER
        ? `<a href="https://wa.me/${CHATBOT_WA_NUMBER}?text=${encodeURIComponent('Hola, quiero ver las opciones para ' + poi.name)}" target="_blank"
            class="col-span-2 flex items-center justify-center gap-2 bg-purple-600 text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-purple-700 transition">
            🛒 Reservar/Comprar
          </a>`
        : `<button type="button" onclick="toggleReservarMenu(this)"
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
          </div>`
      }
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