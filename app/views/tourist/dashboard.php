<?php
$pageTitle = 'Visitante - ' . APP_NAME;
$phone = $user['phone'] ?? '';
$prefix = $routePrefix ?? '';

$iconForPlace = static function (array $item): string {
    $icon = strtolower((string)($item['category_icon'] ?? ''));
    $category = strtolower((string)($item['category_name'] ?? ''));
    $has = static function (string $needle) use ($icon, $category): bool {
        return strpos($icon, $needle) !== false || strpos($category, $needle) !== false;
    };
    if ($has('hotel')) return '&#127976;';
    if ($has('wine') || $has('vino')) return '&#127863;';
    if ($has('utensils') || $has('restaurante')) return '&#127869;';
    if ($has('landmark') || $has('cultural')) return '&#127963;';
    if ($has('mountain') || $has('aventura')) return '&#9968;';
    if ($has('spa')) return '&#128134;';
    return '&#128205;';
};

$priceLabel = static function (array $promo): string {
    $promoPrice = $promo['presale_price'] ?? null;
    $price = $promoPrice !== null && $promoPrice !== '' ? $promoPrice : ($promo['price'] ?? null);
    return $price !== null && $price !== '' ? '$' . number_format((float)$price, 2) : '';
};

$dateStatus = static function (array $promo): array {
    $now = time();
    $start = !empty($promo['start_date']) ? strtotime((string)$promo['start_date']) : null;
    $end = !empty($promo['end_date']) ? strtotime((string)$promo['end_date']) : null;
    if ($start && $start > $now) {
        return ['Proximamente', 'bg-blue-50 text-blue-700 border-blue-100'];
    }
    if ($end && $end < $now) {
        return ['Finalizada', 'bg-gray-50 text-gray-500 border-gray-100'];
    }
    return ['Disponible', 'bg-emerald-50 text-emerald-700 border-emerald-100'];
};

$withReturnTo = static function (string $href) use ($prefix): string {
    $returnTo = rawurlencode($prefix . 'turista');
    $separator = str_contains($href, '?') ? '&' : '?';
    return $href . $separator . 'return_to=' . $returnTo;
};

require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<style>
  .visitor-shell { max-width: 1180px; }
  .visitor-hero {
    background:
      linear-gradient(110deg, rgba(234, 88, 12, .96), rgba(249, 115, 22, .88)),
      url("<?= asset('img/landing/queso-vino.jpeg') ?>") center / cover;
  }
  .visitor-hero h1 { color: #fff; }
  .media-tile {
    display: grid;
    place-items: center;
    background: linear-gradient(135deg, #fff7ed, #e0f2fe);
    color: #c2410c;
    font-size: 1.55rem;
    font-weight: 800;
  }
  .media-tile.place { background: linear-gradient(135deg, #ecfeff, #fff7ed); color: #0f766e; }
  .media-tile.event { background: linear-gradient(135deg, #eef2ff, #fff7ed); color: #4f46e5; }
  .rank-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2rem;
    height: 2rem;
    border-radius: 999px;
    background: #111827;
    color: #fff;
    font-weight: 800;
    font-size: .8rem;
  }
</style>

<script>
function swapImageFallback(img) {
  const fallback = document.createElement('div');
  fallback.className = img.dataset.fallbackClass || 'media-tile w-16 h-16 rounded-lg shrink-0';
  fallback.innerHTML = img.dataset.fallback || '&#128205;';
  img.replaceWith(fallback);
}
</script>

<main class="visitor-shell mx-auto px-4 py-6 md:py-8 mb-24">
  <section class="visitor-hero rounded-2xl shadow-sm p-6 md:p-8 mb-6 text-white overflow-hidden">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-5">
      <div>
        <p class="text-xs font-bold uppercase tracking-[.22em] text-orange-100">Panel de visitante</p>
        <h1 class="text-2xl md:text-4xl font-extrabold mt-2 leading-tight text-white">Hola, <?= e($user['name']) ?></h1>
        <p class="text-orange-50 mt-3 max-w-2xl text-sm md:text-base">Encuentra promociones, eventos y lugares para tu siguiente visita en Colon.</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <a href="<?= url($prefix . 'mapa') ?>" class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-white text-orange-700 text-sm font-bold hover:bg-orange-50 transition">Ver mapa</a>
        <a href="<?= url($prefix . 'notificaciones') ?>" class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-orange-700/55 text-white text-sm font-bold hover:bg-orange-700 transition">Notificaciones</a>
      </div>
    </div>
  </section>

  <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <div class="flex items-center justify-between gap-4">
      <div>
        <h2 class="font-bold text-gray-900 text-lg">Mi perfil</h2>
        <p class="text-sm text-gray-500 mt-1"><?= e($user['email'] ?? '') ?><?= $phone ? ' / WhatsApp: ' . e($phone) : '' ?></p>
      </div>
      <a href="<?= url($prefix . 'turista/perfil') ?>" class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-orange-50 text-orange-700 text-sm font-bold hover:bg-orange-100 transition">Editar perfil</a>
    </div>
  </section>

  <div class="grid grid-cols-1 xl:grid-cols-[1.05fr_.95fr] gap-6 mb-6">
    <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
      <div class="flex items-center justify-between gap-4 mb-4">
        <h2 class="font-bold text-gray-900 text-lg">Promociones para ti</h2>
        <span class="text-xs font-semibold text-gray-400"><?= count($activePromotions ?? []) ?> disponibles</span>
      </div>
      <div class="space-y-3">
        <?php if (empty($activePromotions)): ?>
        <div class="rounded-xl bg-orange-50 border border-orange-100 p-4 text-sm text-orange-900">Aun no hay promociones publicadas para visitantes.</div>
        <?php endif; ?>
        <?php foreach (array_slice($activePromotions, 0, 6) as $p): ?>
        <?php [$statusText, $statusClass] = $dateStatus($p); ?>
        <?php $promoHref = $withReturnTo($p['public_url'] ?: url('promocion/' . (int)$p['id'])); ?>
        <a href="<?= e($promoHref) ?>" class="flex gap-4 p-3 border border-gray-100 rounded-xl hover:border-orange-200 hover:bg-orange-50/50 transition">
          <?php if (!empty($p['image'])): ?>
          <img src="<?= imageUrl($p['image']) ?>" alt="" class="w-20 h-20 object-cover rounded-lg shrink-0" data-fallback="&#127991;" data-fallback-class="media-tile w-20 h-20 rounded-lg shrink-0" onerror="swapImageFallback(this)">
          <?php else: ?>
          <div class="media-tile w-20 h-20 rounded-lg shrink-0">&#127991;</div>
          <?php endif; ?>
          <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-2">
              <p class="font-bold text-gray-900 text-sm md:text-base truncate"><?= e($p['title']) ?></p>
              <span class="shrink-0 text-[11px] px-2 py-1 rounded-full border <?= $statusClass ?>"><?= $statusText ?></span>
            </div>
            <p class="text-sm text-gray-600 mt-1 line-clamp-2"><?= e($p['description'] ?? '') ?></p>
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2 text-xs text-gray-500">
              <?php if ($priceLabel($p)): ?><span class="font-bold text-orange-700"><?= e($priceLabel($p)) ?></span><?php endif; ?>
              <?php if (!empty($p['business_name'])): ?><span><?= e($p['business_name']) ?></span><?php endif; ?>
              <?php if (!empty($p['end_date'])): ?><span>Hasta <?= e(date('d/m/Y', strtotime($p['end_date']))) ?></span><?php endif; ?>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
      <div class="flex items-center justify-between gap-4 mb-4">
        <h2 class="font-bold text-gray-900 text-lg">Eventos publicados</h2>
        <span class="text-xs font-semibold text-gray-400"><?= count($activeEvents ?? []) ?> eventos</span>
      </div>
      <div class="space-y-3 max-h-[520px] overflow-y-auto pr-1">
        <?php if (empty($activeEvents)): ?>
        <div class="rounded-xl bg-blue-50 border border-blue-100 p-4 text-sm text-blue-900">Aun no hay eventos publicados.</div>
        <?php endif; ?>
        <?php foreach (array_slice($activeEvents, 0, 8) as $event): ?>
        <?php $eventHref = ($event['type'] ?? '') === 'evento'
            ? ($event['public_url'] ?: url('promocion/' . (int)$event['id']))
            : url('evento/' . (int)$event['id'] . '/' . slugify($event['title'])); ?>
        <a href="<?= e($withReturnTo($eventHref)) ?>" class="flex gap-4 p-3 border border-gray-100 rounded-xl hover:border-blue-200 hover:bg-blue-50/50 transition">
          <?php if (!empty($event['image'])): ?>
          <img src="<?= imageUrl($event['image']) ?>" alt="" class="w-20 h-20 object-cover rounded-lg shrink-0" data-fallback="&#127881;" data-fallback-class="media-tile event w-20 h-20 rounded-lg shrink-0" onerror="swapImageFallback(this)">
          <?php else: ?>
          <div class="media-tile event w-20 h-20 rounded-lg shrink-0">&#127881;</div>
          <?php endif; ?>
          <div class="min-w-0">
            <p class="font-bold text-gray-900 text-sm truncate"><?= e($event['title']) ?></p>
            <p class="text-xs text-gray-500 line-clamp-2 mt-1"><?= e($event['description'] ?? '') ?></p>
            <?php if (!empty($event['start_date'])): ?><p class="text-xs text-orange-600 font-semibold mt-2"><?= e(date('d/m/Y H:i', strtotime($event['start_date']))) ?></p><?php endif; ?>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </section>
  </div>

  <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <div class="flex items-center justify-between gap-4 mb-4">
      <h2 class="font-bold text-gray-900 text-lg">Lugares visitados</h2>
      <a href="<?= url($prefix . 'mapa') ?>" class="text-sm text-orange-600 font-bold hover:underline">Explorar mas</a>
    </div>
    <?php if (empty($visitedPlaces)): ?>
    <div class="rounded-xl bg-gray-50 border border-gray-100 p-4 text-sm text-gray-600">Tu historial aparecera cuando abras el detalle de un lugar desde el mapa.</div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      <?php foreach ($visitedPlaces as $b): ?>
      <article class="border border-gray-100 rounded-xl p-3 hover:border-orange-200 transition">
        <a href="<?= url($prefix . 'lugar/' . $b['slug']) ?>" class="flex items-center gap-3">
          <?php if (!empty($b['cover_image'])): ?>
          <img src="<?= imageUrl($b['cover_image']) ?>" alt="" class="w-16 h-16 object-cover rounded-lg shrink-0" data-fallback="<?= e($iconForPlace($b)) ?>" data-fallback-class="media-tile place w-16 h-16 rounded-lg shrink-0" onerror="swapImageFallback(this)">
          <?php else: ?>
          <div class="media-tile place w-16 h-16 rounded-lg shrink-0"><?= $iconForPlace($b) ?></div>
          <?php endif; ?>
          <div class="min-w-0">
            <p class="font-bold text-gray-900 text-sm truncate"><?= e($b['name']) ?></p>
            <p class="text-xs text-gray-500"><?= e($b['category_name']) ?></p>
            <p class="text-xs text-orange-600 mt-1"><?= (int)($b['visit_count'] ?? 1) ?> visita<?= (int)($b['visit_count'] ?? 1) === 1 ? '' : 's' ?></p>
          </div>
        </a>
        <div class="flex items-center justify-between gap-2 mt-3 pt-3 border-t border-gray-100">
          <span class="text-xs text-gray-400"><?= e(date('d/m/Y', strtotime($b['last_visited_at']))) ?></span>
          <a href="<?= url($prefix . 'lugar/' . $b['slug']) ?>#valoraciones" class="px-3 py-2 rounded-lg bg-orange-600 text-white text-xs font-bold hover:bg-orange-700 transition">Calificar</a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </section>

  <div class="grid grid-cols-1 xl:grid-cols-[1fr_.85fr] gap-6">
    <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
      <h2 class="font-bold text-gray-900 text-lg mb-4">Lugares mas visitados</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach (array_slice($topVisited, 0, 8) as $index => $b): ?>
        <article class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl hover:border-orange-200 transition">
          <a href="<?= url($prefix . 'lugar/' . $b['slug']) ?>" class="contents">
            <?php if (!empty($b['cover_image'])): ?>
            <img src="<?= imageUrl($b['cover_image']) ?>" alt="" class="w-16 h-16 object-cover rounded-lg shrink-0" data-fallback="<?= e($iconForPlace($b)) ?>" data-fallback-class="media-tile place w-16 h-16 rounded-lg shrink-0" onerror="swapImageFallback(this)">
            <?php else: ?>
            <div class="media-tile place w-16 h-16 rounded-lg shrink-0"><?= $iconForPlace($b) ?></div>
            <?php endif; ?>
            <div class="min-w-0 flex-1">
              <p class="font-bold text-gray-900 text-sm truncate"><?= e($b['name']) ?></p>
              <p class="text-xs text-gray-500"><?= e($b['category_name']) ?></p>
              <p class="text-xs text-amber-600 mt-1"><?= number_format((float)$b['rating'], 1) ?>/5</p>
            </div>
            <span class="rank-badge shrink-0">#<?= (int)$index + 1 ?></span>
          </a>
        </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
      <h2 class="font-bold text-gray-900 text-lg mb-4">Opiniones enviadas</h2>
      <?php if (empty($myReviews)): ?>
      <div class="rounded-xl bg-gray-50 border border-gray-100 p-4 text-sm text-gray-600">Aun no has calificado negocios.</div>
      <?php else: ?>
      <div class="space-y-3 max-h-[460px] overflow-y-auto pr-1">
        <?php foreach ($myReviews as $review): ?>
        <a href="<?= url($prefix . 'lugar/' . $review['business_slug']) ?>#valoraciones" class="block p-4 border border-gray-100 rounded-xl hover:bg-gray-50 transition">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="font-bold text-gray-900 text-sm truncate"><?= e($review['business_name']) ?></p>
              <p class="text-xs text-gray-500"><?= e($review['category_name'] ?? '') ?></p>
            </div>
            <span class="text-amber-400 text-sm whitespace-nowrap"><?= str_repeat('&#9733;', (int)$review['rating']) ?></span>
          </div>
          <?php if (!empty($review['comment'])): ?><p class="text-sm text-gray-600 mt-2 line-clamp-3"><?= e($review['comment']) ?></p><?php endif; ?>
          <p class="text-xs text-gray-400 mt-2"><?= e(date('d/m/Y', strtotime($review['created_at']))) ?></p>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </section>
  </div>
</main>

<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
