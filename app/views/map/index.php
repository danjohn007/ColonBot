<?php
$pageTitle = 'Colón te conquistará | Mapa Interactivo';
$extraHead = '<link rel="preconnect" href="https://fonts.googleapis.com">' . PHP_EOL
  . '  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . PHP_EOL
  . '  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">' . PHP_EOL
  . '  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">' . PHP_EOL
  . '  <link rel="stylesheet" href="' . asset('css/landing-map.css') . '">';
$viewer = currentUser();
$landingProfiles = [
  'turismo-de-experiencias' => [
    'eyebrow' => 'Turismo de experiencias',
    'title' => 'Vive Colón con todos los sentidos',
    'copy' => 'Queserías, viñedos, miradores y prestadores locales para armar una ruta memorable.',
    'intro' => 'Explora experiencias, productos locales y anfitriones listos para recibirte.',
    'routes_title' => 'Momentos para saborear, caminar y compartir.',
    'routes_copy' => 'Combina recorridos guiados, productos regionales y pausas escénicas en una ruta hecha a tu ritmo.',
    'slides' => ['queso-vino.jpeg', 'noche-restaurante.jpeg', 'el-chino.jpeg', 'gordita.jpeg'],
  ],
  'turismo-cultural' => [
    'eyebrow' => 'Turismo cultural',
    'title' => 'Historia viva, oficios y tradiciones',
    'copy' => 'Recorre mercados, artesanías, museos, haciendas y espacios que cuentan la identidad de Colón.',
    'intro' => 'Encuentra puntos culturales, corredores artesanales y lugares con memoria.',
    'routes_title' => 'Una ruta para mirar Colón con calma.',
    'routes_copy' => 'Conecta arquitectura, oficios locales, historia y encuentros con la comunidad.',
    'slides' => ['dulces-tradicionales.jpeg', 'el-chino.jpeg', 'noche-restaurante.jpeg', 'queso-vino.jpeg'],
  ],
  'ecoturismo-y-aventura' => [
    'eyebrow' => 'Ecoturismo y aventura',
    'title' => 'Aire libre, paisaje y adrenalina suave',
    'copy' => 'Senderismo, cerros, presas, pesca, camping y rutas para conectar con la naturaleza.',
    'intro' => 'Filtra lugares para aventura, descanso al aire libre y paisajes abiertos.',
    'routes_title' => 'Aventura con horizonte colonense.',
    'routes_copy' => 'Planea salidas familiares, con amigos o en pareja entre senderos, agua y vistas amplias.',
    'slides' => ['noche-restaurante.jpeg', 'queso-vino.jpeg', 'dulces-tradicionales.jpeg', 'gordita.jpeg'],
  ],
  'turismo-religioso' => [
    'eyebrow' => 'Turismo religioso',
    'title' => 'Fe, peregrinación y patrimonio',
    'copy' => 'Iglesias, celebraciones, conventos y recorridos con valor espiritual e histórico.',
    'intro' => 'Ubica espacios religiosos, fiestas patronales y puntos cercanos para completar tu visita.',
    'routes_title' => 'Tradición espiritual en cada parada.',
    'routes_copy' => 'Crea una ruta serena entre templos, plazas, servicios y experiencias locales.',
    'slides' => ['el-chino.jpeg', 'dulces-tradicionales.jpeg', 'noche-restaurante.jpeg', 'queso-vino.jpeg'],
  ],
  'turismo-gastronomico' => [
    'eyebrow' => 'Turismo gastronómico',
    'title' => 'Sabores locales, de la fonda al restaurante',
    'copy' => 'Antojitos, dulces tradicionales, vino, queso y propuestas de cocina para descubrir Colón por el paladar.',
    'intro' => 'Encuentra restaurantes, productos locales y paradas ideales para comer bien.',
    'routes_title' => 'Una ruta para probar Colón.',
    'routes_copy' => 'Arma el día entre desayunos, antojitos, sobremesa, dulces y cenas con identidad local.',
    'slides' => ['gordita.jpeg', 'el-chino.jpeg', 'queso-vino.jpeg', 'dulces-tradicionales.jpeg'],
  ],
];
$landingDefault = [
  'eyebrow' => 'Mapa Interactivo',
  'title' => 'Colón te conquistará',
  'copy' => 'Combina el estilo de visita que quieras realizar con la ruta ideal y encuentra la mejor hospitalidad de los colonenses.',
  'intro' => 'Explora atractivos, sabores y experiencias desde el mapa interactivo del turismo en Colón.',
  'routes_title' => 'Elige tu forma de vivir Colón.',
  'routes_copy' => 'Descubre atractivos públicos y privados de acuerdo al estilo de visita que quieras realizar: familiar, en pareja, con amigos o pet friendly.',
  'slides' => ['noche-restaurante.jpeg', 'dulces-tradicionales.jpeg', 'queso-vino.jpeg', 'el-chino.jpeg'],
];
$landingRouteUrls = [
  'turismo-de-experiencias' => 'https://colon.click/landing/mapa/turismo-de-experiencias',
  'turismo-cultural' => 'https://colon.click/landing/mapa/turismo-cultural',
  'ecoturismo-y-aventura' => 'https://colon.click/landing/mapa/ecoturismo-y-aventura',
  'turismo-religioso' => 'https://colon.click/landing/mapa/turismo-religioso',
  'turismo-gastronomico' => 'https://colon.click/landing/mapa/turismo-gastronomico',
];
$landingContent = $landingProfiles[$preloadCat ?? ''] ?? $landingDefault;
$routeCardsBySlug = [
  'turismo-de-experiencias' => [
    ['label' => 'Producto local', 'title' => 'Queso, vino y sabores de origen', 'copy' => 'Paradas para probar, comprar y conversar con anfitriones locales.', 'img' => 'queso-vino.jpeg'],
    ['label' => 'Plan de tarde', 'title' => 'Miradores, sobremesa y noche', 'copy' => 'Experiencias para bajar el ritmo y disfrutar Colón sin prisa.', 'img' => 'noche-restaurante.jpeg'],
    ['label' => 'Ruta memorable', 'title' => 'Momentos hechos para compartir', 'copy' => 'Combina gastronomia, servicios y atractivos cercanos en una sola salida.', 'img' => 'gordita.jpeg', 'wide' => true],
  ],
  'turismo-cultural' => [
    ['label' => 'Tradición', 'title' => 'Mercados, dulces y oficios', 'copy' => 'Encuentra lugares donde la identidad local se vive en cada detalle.', 'img' => 'dulces-tradicionales.jpeg'],
    ['label' => 'Historia', 'title' => 'Haciendas, plazas y memoria', 'copy' => 'Recorridos para conectar arquitectura, relatos y vida cotidiana.', 'img' => 'el-chino.jpeg'],
    ['label' => 'Comunidad', 'title' => 'Cultura con anfitriones locales', 'copy' => 'Haz una ruta con paradas utiles para comer, comprar y aprender.', 'img' => 'noche-restaurante.jpeg', 'wide' => true],
  ],
  'ecoturismo-y-aventura' => [
    ['label' => 'Aire libre', 'title' => 'Senderos y paisajes abiertos', 'copy' => 'Opciones para caminar, respirar y descubrir vistas del municipio.', 'img' => 'noche-restaurante.jpeg'],
    ['label' => 'Naturaleza', 'title' => 'Presas, cerros y descanso', 'copy' => 'Lugares para convivir con amigos, familia o pareja.', 'img' => 'queso-vino.jpeg'],
    ['label' => 'Aventura suave', 'title' => 'Rutas para moverte a tu ritmo', 'copy' => 'Planea una salida con servicios cercanos y puntos de interes.', 'img' => 'dulces-tradicionales.jpeg', 'wide' => true],
  ],
  'turismo-religioso' => [
    ['label' => 'Fe', 'title' => 'Templos y celebraciones', 'copy' => 'Ubica espacios religiosos y fiestas patronales de Colón.', 'img' => 'el-chino.jpeg'],
    ['label' => 'Patrimonio', 'title' => 'Arquitectura y tradición', 'copy' => 'Recorridos serenos con valor histórico y espiritual.', 'img' => 'dulces-tradicionales.jpeg'],
    ['label' => 'Peregrinacion', 'title' => 'Una visita cuidada de principio a fin', 'copy' => 'Complementa la ruta con servicios, comida y puntos cercanos.', 'img' => 'noche-restaurante.jpeg', 'wide' => true],
  ],
  'turismo-gastronomico' => [
    ['label' => 'Antojitos', 'title' => 'Sabores de fonda y mercado', 'copy' => 'Paradas casuales para probar recetas locales y antojos de la región.', 'img' => 'gordita.jpeg'],
    ['label' => 'Sobremesa', 'title' => 'Restaurantes y cocina local', 'copy' => 'Opciones para comer bien y convertir la visita en experiencia.', 'img' => 'el-chino.jpeg'],
    ['label' => 'Producto regional', 'title' => 'Queso, vino y dulces tradicionales', 'copy' => 'Arma una ruta para llevarte Colón también a casa.', 'img' => 'queso-vino.jpeg', 'wide' => true],
  ],
];
$defaultRouteCards = [
  ['label' => 'Experiencias', 'title' => 'Queserías, viñedos y miradores', 'copy' => 'Productos locales y nativos, restaurantes gourmet, balnearios y paseos a caballo.', 'img' => 'queso-vino.jpeg', 'href' => $landingRouteUrls['turismo-de-experiencias']],
  ['label' => 'Turismo Cultural', 'title' => 'Mercados, artesanías e historia', 'copy' => 'Corredores artesanales, museos, mercados, haciendas y recorridos turísticos.', 'img' => 'dulces-tradicionales.jpeg', 'href' => $landingRouteUrls['turismo-cultural']],
  ['label' => 'Ecoturismo y aventura', 'title' => 'Senderos, naturaleza y aire libre', 'copy' => 'Cerros, presas, pesca, camping y rutas para conectar con el paisaje.', 'img' => 'noche-restaurante.jpeg', 'href' => $landingRouteUrls['ecoturismo-y-aventura']],
  ['label' => 'Turismo religioso', 'title' => 'Fe, peregrinación y patrimonio', 'copy' => 'Iglesias, celebraciones, conventos y recorridos con valor espiritual e histórico.', 'img' => 'el-chino.jpeg', 'href' => $landingRouteUrls['turismo-religioso']],
  ['label' => 'Gastronomía', 'title' => 'De la fonda al restaurante', 'copy' => 'Sabores locales, antojitos y lo mejor de la gastronomía del municipio.', 'img' => 'gordita.jpeg', 'href' => $landingRouteUrls['turismo-gastronomico'], 'wide' => true],
];
$routeCards = $routeCardsBySlug[$preloadCat ?? ''] ?? $defaultRouteCards;
if (!empty($preloadCat) && isset($landingRouteUrls[$preloadCat])) {
  foreach ($routeCards as &$card) {
    $card['href'] = $card['href'] ?? $landingRouteUrls[$preloadCat];
  }
  unset($card);
}
require APP_PATH . '/views/layout/head.php';
?>
<style>
  .map-title {
    font-family: 'Georgia', 'Times New Roman', serif;
    background: linear-gradient(135deg, #f97316, #ea580c, #c2410c);
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

<section class="colon-hero" id="inicio" aria-label="Colón te conquistará">
  <div class="colon-hero-bg" aria-hidden="true">
    <?php foreach ($landingContent['slides'] as $i => $slide): ?>
    <img class="colon-hero-slide <?= $i === 0 ? 'is-active' : '' ?>" src="<?= asset('img/landing/' . $slide) ?>" alt="">
    <?php endforeach; ?>
  </div>
  <div class="colon-hero-shade" aria-hidden="true"></div>
  <div class="colon-hero-content">
    <p class="colon-eyebrow reveal-up colon-dynamic-copy"><?= e($landingContent['eyebrow']) ?></p>
    <h1 class="colon-hero-title reveal-up colon-dynamic-copy"><?= e($landingContent['title']) ?></h1>
    <p class="colon-hero-copy reveal-up colon-dynamic-copy"><?= e($landingContent['copy']) ?></p>
    <p class="colon-eyebrow reveal-up">Mapa Interactivo</p>
    <h1 class="colon-hero-title reveal-up">Colón te conquistará</h1>
    <p class="colon-hero-copy reveal-up">Combina el estilo de visita que quieras realizar con la ruta ideal y encuentra la mejor hospitalidad de los Colonenses.</p>
    <div class="colon-hero-actions reveal-up">
      <a href="#explorar-mapa" class="colon-btn colon-btn-primary">Explorar rutas</a>
      <a href="#cristo-bot" class="colon-btn colon-btn-ghost">Conocer a CristoBot</a>
    </div>
  </div>
  <div class="colon-slide-dots" aria-label="Controles del carrusel">
    <button class="colon-dot is-active" type="button" data-colon-dot="0" aria-label="Imagen 1"></button>
    <button class="colon-dot" type="button" data-colon-dot="1" aria-label="Imagen 2"></button>
    <button class="colon-dot" type="button" data-colon-dot="2" aria-label="Imagen 3"></button>
    <button class="colon-dot" type="button" data-colon-dot="3" aria-label="Imagen 4"></button>
  </div>
</section>

<main class="flex-1 flex flex-col" id="explorar-mapa">
  <section class="colon-map-shell">
    <div class="colon-map-intro reveal-up">
      <p class="colon-eyebrow colon-dynamic-copy">Diseña tu ruta</p>
      <h2 class="colon-dynamic-copy"><?= e($landingContent['intro']) ?></h2>
      <p class="colon-eyebrow">Diseña tu ruta</p>
      <h2>Explora atractivos, sabores y experiencias desde el mapa interactivo del turismo en Colón.</h2>
    </div>
  <!-- Filter Bar -->
  <div class="colon-filter-bar">
    <div class="colon-filter-row">
      <!-- Search -->
      <div class="colon-search-box">
        <input type="text" id="search-input" placeholder="🔍 Buscar lugares..."
          class="colon-filter-input">
        <button onclick="doSearch()" class="colon-search-btn" aria-label="Buscar">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </button>
      </div>
      <!-- Mis Favoritos -->
      <button id="btn-mis-favoritos" onclick="openFavoritos()"
        class="colon-favorites-btn">
        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        Mis Favoritos
        <span id="fav-count" class="hidden bg-orange-600 text-white text-xs rounded-full px-1.5 py-0.5 font-bold leading-none"></span>
      </button>
      <!-- Category filters -->
      <div class="colon-chip-row">
        <button onclick="filterCat('')" data-cat=""
          class="cat-btn colon-chip text-xs px-3 py-1.5 rounded-full bg-orange-600 text-white transition font-medium active-cat">
          Todos
        </button>
<?php foreach ($categories as $cat): ?>
        <?php if ($cat['slug'] === 'punto-de-referencia') continue; ?>
        <button onclick="filterCat('<?= e($cat['slug']) ?>')" data-cat="<?= e($cat['slug']) ?>"
          class="cat-btn colon-chip text-xs px-3 py-1.5 rounded-full transition font-medium"
          style="--cat-color: <?= e($cat['color']) ?>">
          <span class="cat-icon"><?= getCategoryEmoji($cat['icon'] ?? '') ?></span>
          <?= e($cat['name']) ?>
        </button>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Tipo de viaje Filter -->
    <div class="colon-trip-row">
      <span class="colon-trip-label">Tipo de viaje:</span>
      <button onclick="filterTripType('')" data-trip-type=""
        class="trip-type-btn text-xs px-3 py-1.5 rounded-full bg-orange-600 text-white transition font-medium">
        Todos
      </button>
      <button onclick="filterTripType('familiar')" data-trip-type="familiar"
        class="trip-type-btn colon-chip text-xs px-3 py-1.5 rounded-full transition font-medium">
        👨‍👩‍👧‍👦 Familiar
      </button>
      <button onclick="filterTripType('amigos')" data-trip-type="amigos"
        class="trip-type-btn colon-chip text-xs px-3 py-1.5 rounded-full transition font-medium">
        🧑‍🤝‍🧑 Amigos
      </button>
      <button onclick="filterTripType('pareja')" data-trip-type="pareja"
        class="trip-type-btn colon-chip text-xs px-3 py-1.5 rounded-full transition font-medium">
        💑 Pareja
      </button>
      <button onclick="filterTripType('adultos_mayores')" data-trip-type="adultos_mayores"
        class="trip-type-btn colon-chip text-xs px-3 py-1.5 rounded-full transition font-medium">
        Adultos mayores
      </button>
      <button onclick="filterTripType('petfriendly')" data-trip-type="petfriendly"
        class="trip-type-btn colon-chip text-xs px-3 py-1.5 rounded-full transition font-medium">
        🐾 Petfriendly
      </button>
    </div>
  </div>

  <!-- Map + Sidebar layout -->
  <div class="flex-1 flex relative overflow-hidden colon-map-frame" style="height: calc(100vh - 130px);">
    <!-- Map -->
    <div id="map" class="flex-1 z-0"></div>

    <!-- Route Legend Boxes (top corner of map) -->
    <div id="route-legend" class="hidden md:block absolute top-4 left-4 z-20 bg-white/90 backdrop-blur-sm rounded-xl shadow-lg border border-gray-200 p-3 max-w-[200px]">
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
  </section>
</main>

<!-- Mis Favoritos modal -->
<div id="favoritos-modal" class="fixed inset-0 z-50 hidden" style="display:none;">
  <div class="absolute inset-0 bg-black bg-opacity-40 flex items-start justify-center pt-16 px-4">
    <div class="absolute inset-0" onclick="closeFavoritos()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[80vh] flex flex-col z-10">
      <div class="flex items-center justify-between p-4 border-b">
        <h2 class="font-bold text-gray-900 flex items-center gap-2">
          <svg class="w-5 h-5 text-orange-500 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
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

<section class="colon-routes-section">
  <div class="colon-section-head">
    <div>
      <p class="colon-eyebrow reveal-up colon-dynamic-copy">Rutas sugeridas</p>
      <h2 class="colon-display reveal-up colon-dynamic-copy"><?= e($landingContent['routes_title']) ?></h2>
      <p class="colon-section-copy reveal-up colon-dynamic-copy"><?= e($landingContent['routes_copy']) ?></p>
      <p class="colon-eyebrow reveal-up">Rutas sugeridas</p>
      <h2 class="colon-display reveal-up">Elige tu forma de vivir Colón.</h2>
    </div>
    <p class="colon-section-copy reveal-up">Descubre atractivos públicos y privados de acuerdo al estilo de visita que quieras realizar: familiar, en pareja, con amigos o pet friendly.</p>
  </div>

  <div class="colon-route-grid colon-dynamic-routes">
    <?php foreach ($routeCards as $card): ?>
    <a href="<?= e($card['href'] ?? '#explorar-mapa') ?>" class="colon-route-card <?= !empty($card['wide']) ? 'colon-route-wide' : '' ?> reveal-up" style="--route-img: url('<?= asset('img/landing/' . $card['img']) ?>')">
      <span><?= e($card['label']) ?></span>
      <h3><?= e($card['title']) ?></h3>
      <p><?= e($card['copy']) ?></p>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="colon-route-grid">
    <article class="colon-route-card reveal-up" style="--route-img: url('<?= asset('img/landing/dulces-tradicionales.jpeg') ?>')">
      <span>Turismo Cultural</span>
      <h3>Mercados, artesanías e historia</h3>
      <p>Corredores artesanales, museos, mercados, haciendas y recorridos turísticos.</p>
    </article>
    <article class="colon-route-card reveal-up" style="--route-img: url('<?= asset('img/landing/queso-vino.jpeg') ?>')">
      <span>Experiencias</span>
      <h3>Queserías, viñedos y miradores</h3>
      <p>Productos locales y nativos, restaurantes gourmet, balnearios y paseos a caballo.</p>
    </article>
    <article class="colon-route-card reveal-up" style="--route-img: url('<?= asset('img/landing/noche-restaurante.jpeg') ?>')">
      <span>Ecoturismo</span>
      <h3>Aventura al aire libre</h3>
      <p>Senderismo, pesca, Mountain Bike, crosstrail y camping.</p>
    </article>
    <article class="colon-route-card reveal-up" style="--route-img: url('<?= asset('img/landing/el-chino.jpeg') ?>')">
      <span>Religioso</span>
      <h3>Soriano y fiestas patronales</h3>
      <p>Catedral, peregrinaciones, iglesias, conventos y celebraciones tradicionales.</p>
    </article>
    <article class="colon-route-card colon-route-wide reveal-up" style="--route-img: url('<?= asset('img/landing/gordita.jpeg') ?>')">
      <span>Gastronomía</span>
      <h3>De la fonda al restaurante</h3>
      <p>Sabores locales, antojitos y lo mejor de la gastronomía del municipio.</p>
    </article>
  </div>
</section>

<section id="cristo-bot" class="colon-bot-section">
  <div class="colon-bot-visual reveal-scale" aria-hidden="true">
    <span class="colon-bot-badge"><img src="<?= asset('img/cristo-bot-nino-small.png') ?>" alt="" loading="lazy" decoding="async"></span>
  </div>
  <div class="colon-bot-content reveal-up">
    <p class="colon-eyebrow">WhatsApp</p>
    <h2>Conoce a CristoBot Colón, nuestro anfitrión.</h2>
    <p>Te proporciona la ubicación exacta de los atractivos turísticos, información detallada de productos y servicios, imágenes de lugares y rutas por Waze o Google Maps.</p>
    <div class="colon-bot-actions">
      <span>Haz una reservación</span>
      <span>Contacta prestadores turísticos</span>
      <span>Consulta imágenes de interés</span>
      <span>Llega por la mejor ruta</span>
    </div>
    <?php if (setting('chatbot_wa_number', '')): ?>
    <a href="https://wa.me/<?= e(preg_replace('/\D/', '', setting('chatbot_wa_number', ''))) ?>" target="_blank" class="colon-btn colon-btn-whatsapp">
      Contáctalo aquí
    </a>
    <?php endif; ?>
  </div>
</section>

<section class="colon-emergency-section" aria-label="Números de emergencia">
  <div class="colon-emergency-inner">
    <div class="colon-emergency-head">
      <p class="colon-eyebrow">Asistencia</p>
      <h2>Números de emergencia</h2>
    </div>
    <div class="colon-emergency-grid">
      <a href="tel:911" class="colon-emergency-card colon-emergency-primary">
        <span class="colon-emergency-icon" aria-hidden="true">911</span>
        <div>
          <strong>Emergencias</strong>
          <b>911</b>
          <small>Policía, bomberos, ambulancia y protección civil</small>
        </div>
      </a>
      <a href="tel:089" class="colon-emergency-card">
        <span class="colon-emergency-icon" aria-hidden="true">089</span>
        <div>
          <strong>Denuncia Anónima</strong>
          <b>089</b>
          <small>Reporte seguro y confidencial</small>
        </div>
      </a>
      <a href="tel:4192920296" class="colon-emergency-card">
        <span class="colon-emergency-icon" aria-hidden="true">PC</span>
        <div>
          <strong>Protección Civil Colón</strong>
          <b>419 292 0296</b>
          <small>Apoyo municipal inmediato</small>
        </div>
      </a>
      <a href="tel:4192920061" class="colon-emergency-card">
        <span class="colon-emergency-icon" aria-hidden="true">COL</span>
        <div>
          <strong>Presidencia Municipal</strong>
          <b>419 292 0061</b>
          <small>Atención ciudadana Colón</small>
        </div>
      </a>
    </div>
  </div>
</section>

<!-- Banners de registro público entre mapa y footer -->
<section class="colon-public-band" aria-label="Registro público">
  <div class="colon-public-grid">
    <a href="<?= url(($routePrefix ?? '') . 'registro/visitante') ?>" class="colon-public-banner">
      <img src="<?= asset('img/cristo-bot-nino.png') ?>" alt="Visitante de Colón" loading="lazy" decoding="async">
      <span class="colon-public-overlay" aria-hidden="true"></span>
      <div class="colon-public-banner-content">
        <span>Visitantes</span>
        <h3>Regístrate como visitante y obtén descuentos exclusivos</h3>
        <p>Califica y deja comentarios de nuestros atractivos turísticos.</p>
      </div>
    </a>
    <a href="<?= url(($routePrefix ?? '') . 'registro/prestador') ?>" class="colon-public-banner">
      <img src="<?= asset('img/landing/noche-restaurante.jpeg') ?>" alt="Negocio turístico en Colón" loading="lazy" decoding="async">
      <span class="colon-public-overlay" aria-hidden="true"></span>
      <div class="colon-public-banner-content">
        <span>Prestadores</span>
        <h3>¿Eres prestador de servicio?</h3>
        <p>Da de alta tu negocio y conecta con visitantes.</p>
      </div>
    </a>
  </div>
</section>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const BASE_URL = '<?= BASE_URL ?>';
const ROUTE_PREFIX = '<?= e($routePrefix ?? '') ?>';
const ROUTE_BASE = `${BASE_URL}/${ROUTE_PREFIX}`.replace(/\/$/, '');
const IS_LOGGED_IN = <?= $viewer ? 'true' : 'false' ?>;
const CAN_REVIEW = <?= ($viewer && ($viewer['role'] ?? '') === 'visitor') ? 'true' : 'false' ?>;
const VISITOR_LOGIN_URL = '<?= url(($routePrefix ?? '') . 'registro/visitante') ?>';
const MAP_LAT  = <?= setting('map_lat','20.2862') ?>;
const MAP_LNG  = <?= setting('map_lng','-99.7242') ?>;
const MAP_ZOOM = <?= setting('map_zoom','13') ?>;
const PRELOAD_ID  = <?= (int)($preloadId ?? 0) ?>;
const PRELOAD_CAT = '<?= e($preloadCat ?? '') ?>';
const BOUNDARY_GEOJSON_URL = '<?= e($boundaryGeoJsonUrl ?? asset('data/colon-boundary.geojson')) ?>';
const CHATBOT_ACTIVE    = <?= setting('chatbot_active', '0') === '1' ? 'true' : 'false' ?>;
const CHATBOT_WA_NUMBER = '<?= e(setting('chatbot_wa_number', '')) ?>';
const MAP_LIMITS = L.latLngBounds(
  L.latLng(19.85, -100.65),
  L.latLng(21.05, -99.05)
);

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
const map = L.map('map', {
  zoomControl: true,
  scrollWheelZoom: false,
  minZoom: 9,
  maxZoom: 18,
  maxBounds: MAP_LIMITS,
  maxBoundsViscosity: 0.95,
}).setView([MAP_LAT, MAP_LNG], Math.max(MAP_ZOOM, 10));

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '\u00A9 <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
  maxZoom: 18,
  minZoom: 9,
  bounds: MAP_LIMITS,
  noWrap: true,
}).addTo(map);

const mapEl = map.getContainer();
mapEl.addEventListener('click', () => map.scrollWheelZoom.enable());
mapEl.addEventListener('mouseleave', () => map.scrollWheelZoom.disable());

// Municipio de Colon resaltado desde GeoJSON local.
fetch(BOUNDARY_GEOJSON_URL)
  .then(response => {
    if (!response.ok) {
      throw new Error(`No se pudo cargar el limite municipal (${response.status})`);
    }
    return response.json();
  })
  .then(boundaryGeoJson => {
    const boundaryLayer = L.geoJSON(boundaryGeoJson, {
      interactive: false,
      style: {
        color: '#ea580c',
        weight: 3,
        opacity: 0.95,
        fillColor: '#f97316',
        fillOpacity: 0.18,
      },
    }).addTo(map);

    boundaryLayer.bringToBack();
  })
  .catch(error => {
    console.warn('No se pudo cargar el limite municipal de Colon:', error);
  });

// ─── Geolocalizaci\u00F3n ─────────────────────────────────────────────────────
if (navigator.geolocation) {
  navigator.geolocation.getCurrentPosition(pos => {
    const { latitude: lat, longitude: lng } = pos.coords;
    L.circleMarker([lat, lng], {
      radius: 8, color: '#f97316', fillColor: '#f97316', fillOpacity: 0.8, weight: 2,
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
  fetch(`${ROUTE_BASE}/mapa/poi?${params}`)
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
        const m = L.marker([poi.lat, poi.lng], { icon: createIcon(poi.category_color || '#f97316', refEmoji) });
        m.addTo(map);
        m.on('click', () => showPOI(poi));
        markers.push(m);
      });

      // Add filtered POIs - also use TIPO DE LUGAR (isotipo) icon
      allPois.forEach(poi => {
        if (!poi.lat || !poi.lng) return;
        const poiEmoji = isotipoToEmoji(poi.isotipo);
        const m = L.marker([poi.lat, poi.lng], { icon: createIcon(poi.category_color || '#f97316', poiEmoji) });
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
  history.pushState({ poiId: poi.id }, '', `${ROUTE_BASE}/mapa/${poi.id}`);

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
    'adultos_mayores':'Adultos mayores',
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
        return `<span class="inline-block text-xs px-2 py-0.5 rounded-full bg-orange-50 text-orange-700 border border-orange-200 font-medium">${label}</span>`;
      }).join(' ')
    : '';

  // Build isotipo badge HTML
  const isotipoLabel = ISOTIPO_LABEL_MAP[poi.isotipo] || poi.isotipo || '';
  const isotipoBadge = isotipoLabel
    ? `<span class="inline-block text-xs px-2 py-0.5 rounded-full bg-orange-50 text-orange-700 border border-orange-200 font-medium">${isotipoLabel}</span>`
    : '';
  const reviewReturnTo = `${ROUTE_PREFIX}lugar/${poi.slug}#valoraciones`;
  const visitorReviewUrl = `${VISITOR_LOGIN_URL}?return_to=${encodeURIComponent(reviewReturnTo)}`;
  const reviewCta = !isPuntoReferencia
    ? (CAN_REVIEW
      ? `<a href="${poi.url}#valoraciones" class="block mb-3 rounded-xl border border-orange-200 bg-orange-50 px-3 py-2 text-sm font-semibold text-orange-700 hover:bg-orange-100 transition">Calificar y escribir reseña</a>`
      : (IS_LOGGED_IN
        ? `<div class="mb-3 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-600">Solo las cuentas de visitante pueden opinar sobre los lugares.</div>`
        : `<a href="${VISITOR_LOGIN_URL}" class="block mb-3 rounded-xl border border-orange-200 bg-orange-50 px-3 py-2 text-sm font-semibold text-orange-700 hover:bg-orange-100 transition">Inicia sesión como visitante para opinar</a>`))
    : '';

  const html = `
    <img src="${poi.cover}" class="w-full h-40 object-cover rounded-xl mb-3" onerror="this.src='<?= asset('img/placeholder.svg') ?>'">
    <div class="flex items-start justify-between gap-2 mb-2">
      <h3 class="font-bold text-gray-900 text-base leading-tight">${poi.name}</h3>
      <div class="flex items-center gap-1 shrink-0">
        <span class="text-xs px-2 py-1 rounded-full text-white font-medium bg-orange-600">${categoryEmoji} ${poi.category}</span>
        <button onclick="toggleFavorito(${poi.id})" id="fav-btn-${poi.id}"
          class="p-1.5 rounded-full hover:bg-orange-50 transition" title="${isFav ? 'Quitar de favoritos' : 'A\u00F1adir a favoritos'}">
          <svg class="w-5 h-5 ${isFav ? 'text-orange-500 fill-current' : 'text-gray-300'}" viewBox="0 0 24 24">
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
    ${reviewCta}
    <div class="grid grid-cols-2 gap-2">
      ${!isPuntoReferencia ? `
      <a href="${poi.url}#valoraciones" class="col-span-2 flex items-center justify-center gap-2 bg-orange-600 text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-orange-700 transition">
        Ver detalle y calificar
      </a>
      ` : ''}
      ${!isPuntoReferencia ? (CHATBOT_ACTIVE && CHATBOT_WA_NUMBER
        ? `<a href="https://wa.me/${CHATBOT_WA_NUMBER}?text=${encodeURIComponent('Hola, quiero ver las opciones para ' + poi.name)}" target="_blank"
            class="col-span-2 flex items-center justify-center gap-2 bg-orange-600 text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-orange-700 transition">
            \u{1F4AC} Reservar por Whatsapp
          </a>`
        : `<button type="button" onclick="toggleReservarMenu(this)"
            class="col-span-2 flex items-center justify-center gap-2 bg-orange-600 text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-orange-700 transition">
            \u{1F4AC} Reservar por Whatsapp
          </button>
          <div class="col-span-2 hidden reservar-menu">
            <div class="grid grid-cols-2 gap-2 mt-1">
              <a href="${poi.url}#productos" class="flex items-center justify-center gap-1.5 bg-orange-50 text-orange-700 border border-orange-200 py-2 rounded-xl text-sm font-medium hover:bg-orange-100 transition">
                \u{1F6CD}\u{FE0F} Productos
              </a>
              <a href="${poi.url}#servicios" class="flex items-center justify-center gap-1.5 bg-orange-50 text-orange-700 border border-orange-200 py-2 rounded-xl text-sm font-medium hover:bg-orange-100 transition">
                \u{1F4CB} Servicios
              </a>
              <a href="${poi.url}#amenidades" class="flex items-center justify-center gap-1.5 bg-orange-50 text-orange-700 border border-orange-200 py-2 rounded-xl text-sm font-medium hover:bg-orange-100 transition">
                \u{1F6CE}\u{FE0F} Amenidades
              </a>
              <a href="${poi.url}#eventos" class="flex items-center justify-center gap-1.5 bg-orange-50 text-orange-700 border border-orange-200 py-2 rounded-xl text-sm font-medium hover:bg-orange-100 transition">
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

  const renderedHtml = html.replace(VISITOR_LOGIN_URL, visitorReviewUrl);

  // Desktop panel
  const panel = document.getElementById('poi-panel');
  document.getElementById('panel-title').textContent = poi.name;
  document.getElementById('panel-content').innerHTML = renderedHtml;
  panel.classList.remove('translate-x-full');

  // Mobile bottom sheet
  document.getElementById('bottom-sheet-content').innerHTML = renderedHtml;
  document.getElementById('bottom-sheet').classList.remove('translate-y-full');
  document.getElementById('bottom-sheet-overlay').classList.remove('hidden');

  map.panTo([poi.lat, poi.lng]);
}

function resetMapUrl() {
  const url = currentCat ? `${ROUTE_BASE}/mapa/${currentCat}` : `${ROUTE_BASE}/mapa`;
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
    b.classList.toggle('bg-orange-600', active);
    b.classList.toggle('text-white', active);
    b.classList.toggle('bg-gray-100', !active);
    b.classList.toggle('text-gray-700', !active);
  });
}

function filterCat(cat) {
  currentCat = cat;
  const url = cat ? `${ROUTE_BASE}/mapa/${cat}` : `${ROUTE_BASE}/mapa`;
  history.pushState({ cat }, '', url);
  setActiveCatButton(cat);
  loadPOIs();
}

function setActiveIsotipoButton(isotipo) {
  document.querySelectorAll('.isotipo-btn').forEach(b => {
    const active = b.dataset.isotipo === isotipo;
    b.classList.toggle('bg-orange-600', active);
    b.classList.toggle('text-white', active);
    b.classList.toggle('bg-gray-100', !active);
    b.classList.toggle('text-gray-700', !active);
  });
}

function setActiveTripTypeButton(tripType) {
  document.querySelectorAll('.trip-type-btn').forEach(b => {
    const active = b.dataset.tripType === tripType;
    b.classList.toggle('bg-orange-600', active);
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
    const m = L.marker([poi.lat, poi.lng], { icon: createIcon(poi.category_color || '#f97316', refEmoji) });
    m.addTo(map);
    m.on('click', () => showPOI(poi));
    markers.push(m);
  });

  // Add filtered POIs
  filtered.forEach(poi => {
    if (!poi.lat || !poi.lng) return;
    const poiEmoji = isotipoToEmoji(poi.isotipo);
    const m = L.marker([poi.lat, poi.lng], { icon: createIcon(poi.category_color || '#f97316', poiEmoji) });
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
    const m = L.marker([poi.lat, poi.lng], { icon: createIcon(poi.category_color || '#f97316', poiEmoji) });
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
    svg.className = `w-5 h-5 ${isFav ? 'text-orange-500 fill-current' : 'text-gray-300'}`;
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
        <img src="${poi.cover}" class="w-14 h-14 object-cover rounded-lg shrink-0" onerror="this.src='<?= asset('img/placeholder.svg') ?>'">
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-gray-900 text-sm truncate">${poi.name}</p>
          <span class="text-xs px-2 py-0.5 rounded-full text-white font-medium bg-orange-600">${categoryEmoji} ${poi.category}</span>
        </div>
        <button data-remove-id="${poi.id}" class="p-1.5 text-orange-500 hover:bg-orange-50 rounded-full transition shrink-0 fav-remove-btn" title="Quitar de favoritos">
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

(() => {
  const slides = Array.from(document.querySelectorAll('.colon-hero-slide'));
  const dots = Array.from(document.querySelectorAll('.colon-dot'));
  let currentSlide = 0;
  let sliderTimer;

  function setColonSlide(index) {
    if (!slides.length) return;
    currentSlide = (index + slides.length) % slides.length;
    slides.forEach((slide, i) => slide.classList.toggle('is-active', i === currentSlide));
    dots.forEach((dot, i) => dot.classList.toggle('is-active', i === currentSlide));
  }

  function startColonSlider() {
    clearInterval(sliderTimer);
    sliderTimer = setInterval(() => setColonSlide(currentSlide + 1), 5200);
  }

  dots.forEach(dot => {
    dot.addEventListener('click', () => {
      setColonSlide(Number(dot.dataset.colonDot || 0));
      startColonSlider();
    });
  });

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.16, rootMargin: '0px 0px -8% 0px' });

    document.querySelectorAll('.reveal-up, .reveal-scale').forEach((el, index) => {
      el.style.transitionDelay = `${Math.min(index % 4, 3) * 80}ms`;
      observer.observe(el);
    });
  } else {
    document.querySelectorAll('.reveal-up, .reveal-scale').forEach(el => el.classList.add('is-visible'));
  }

  startColonSlider();
})();
</script>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
