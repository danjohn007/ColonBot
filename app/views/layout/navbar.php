<?php
$user = currentUser();
$flash = flash();
?>
<!-- Top Navigation Bar (slim - only logo + hamburger) -->
<nav class="shadow-sm sticky top-0 z-50 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      <!-- Hamburger button (always visible on desktop too now) -->
      <button id="sidebar-toggle" class="p-2 rounded-lg hover:bg-gray-100 transition text-gray-700 focus:outline-none">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>

      <!-- Logo -->
      <a href="<?= url() ?>" class="flex items-center gap-3 font-bold text-white text-lg">
        <img src="<?= asset('img/colon.png') ?>" alt="Colón te conquistará" class="h-12 w-auto">
        <img src="<?= asset('img/ColonBotimg.png') ?>" alt="Colón Administración" class="h-10 w-auto">
        <img src="<?= asset('img/logo-header-nuevo.jpeg') ?>" alt="Ayuntamiento de Colón" class="h-12 w-auto hidden sm:inline-block">
        <span class="text-base font-medium whitespace-nowrap hidden sm:inline" style="color: #8B5CF6">CristobalBot: Mapa interactivo del turismo en Colón</span>
      </a>

      <!-- User / Login on the right -->
      <div class="flex items-center gap-2">
        <?php if ($user): ?>
          <a href="<?= url('logout') ?>" class="text-xs text-red-600 hover:text-red-700 transition hidden sm:inline">Salir</a>
        <?php else: ?>
          <a href="<?= url('login') ?>" class="bg-primary text-white px-3 py-1.5 rounded-lg text-xs hover:opacity-90 transition">Ingresar</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<!-- Left Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/40 z-40 hidden"></div>

<!-- Left Sidebar -->
<div id="sidebar" class="fixed top-0 left-0 h-full w-72 bg-white shadow-2xl z-50 overflow-y-auto
  <?php
    $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
    $isPublicMap = strpos($currentUrl, '/mapa') !== false || $currentUrl === '/' || $currentUrl === BASE_URL;
    $isSuperAdmin = strpos($currentUrl, '/superadmin') !== false;
    if ($isPublicMap) {
      echo '-translate-x-full hidden'; // Hide on public map
    } elseif ($isSuperAdmin) {
      echo ''; // Always visible on superadmin
    } else {
      echo isset($_COOKIE['sidebar_pinned']) && $_COOKIE['sidebar_pinned'] === 'true' ? '' : '-translate-x-full';
    }
  ?>
  transition-transform duration-300 ease-in-out">
REPLACE
  <div class="p-4">
    <!-- Sidebar header -->
    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
      <span class="font-bold text-gray-900 text-lg">Menú</span>
      <button id="sidebar-close" class="p-1 rounded-lg hover:bg-gray-100 transition text-gray-500">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- User info -->
    <?php if ($user): ?>
    <div class="mb-4 px-3 py-2 bg-gray-50 rounded-xl">
      <p class="text-sm font-medium text-gray-900"><?= e($user['name']) ?></p>
      <p class="text-xs text-gray-500"><?= e($user['role']) ?></p>
    </div>
    <?php endif; ?>

    <!-- Menu items -->
    <div class="space-y-1 text-sm font-medium text-gray-700">

      <!-- Map - always visible -->
      <a href="<?= url('mapa') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
        <span class="text-lg">🗺️</span> Mapa Turístico
      </a>

      <?php if ($user):
        $role = $user['role']; // Strict role check - no hasRole() to avoid overlaps
      ?>

        <!-- SUPERADMIN (strict) -->
        <?php if ($role === 'superadmin'): ?>
        <div class="pt-3 pb-1 text-xs uppercase tracking-wide text-gray-400 font-semibold px-3">Administración</div>
        <a href="<?= url('superadmin') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">📊</span> Dashboard
        </a>
        <a href="<?= url('admin') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">🏢</span> Mis Negocios
        </a>
        <a href="<?= url('admin/promociones') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">🎉</span> PROMOCIONES y EVENTOS
        </a>
        <a href="<?= url('admin/crm') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">📇</span> CRM
        </a>
        <a href="<?= url('admin/notificaciones') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">🔔</span> Notificaciones
        </a>
        <?php endif; ?>

        <!-- ADMIN (strict) -->
        <?php if ($role === 'admin'): ?>
        <div class="pt-3 pb-1 text-xs uppercase tracking-wide text-gray-400 font-semibold px-3">Gestión</div>
        <a href="<?= url('admin') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">🏢</span> Mis Negocios
        </a>
        <a href="<?= url('admin/promociones') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">🎉</span> PROMOCIONES y EVENTOS
        </a>
        <a href="<?= url('admin/crm') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">📇</span> CRM
        </a>
        <a href="<?= url('admin/notificaciones') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">🔔</span> Notificaciones
        </a>
        <a href="<?= url('colaborador') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">📊</span> Turismo
        </a>
        <a href="<?= url('mi-perfil') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">👤</span> Mi Perfil
        </a>
        <?php endif; ?>

        <!-- PRESTADOR (strict) -->
        <?php if ($role === 'prestador'): ?>
        <div class="pt-3 pb-1 text-xs uppercase tracking-wide text-gray-400 font-semibold px-3">Gestión</div>
        <a href="<?= url('admin') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">🏢</span> Mis Negocios
        </a>
        <a href="<?= url('admin/promociones') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">🎉</span> PROMOCIONES y EVENTOS
        </a>
        <a href="<?= url('admin/crm') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">📇</span> CRM
        </a>
        <a href="<?= url('mi-perfil') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">👤</span> Mi Perfil
        </a>
        <?php endif; ?>

        <!-- COLABORADOR (strict) -->
        <?php if ($role === 'colaborador'): ?>
        <div class="pt-3 pb-1 text-xs uppercase tracking-wide text-gray-400 font-semibold px-3">Turismo</div>
        <a href="<?= url('colaborador') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">📊</span> Dashboard Turismo
        </a>
        <a href="<?= url('colaborador/eventos') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">🎉</span> Eventos
        </a>
        <a href="<?= url('colaborador/metricas') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">📈</span> Métricas
        </a>
        <a href="<?= url('mi-perfil') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">👤</span> Mi Perfil
        </a>
        <?php endif; ?>

        <!-- TURISTA (strict) - only for actual turista role -->
        <?php if ($role === 'turista'): ?>
        <a href="<?= url('turista') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">👤</span> Mi Perfil Turista
        </a>
        <?php endif; ?>

        <!-- Divider -->
        <div class="border-t border-gray-200 my-3"></div>

        <!-- Configuraciones above logout -->
        <?php if (in_array($role, ['superadmin', 'admin', 'prestador', 'colaborador'])): ?>
        <a href="<?= url('configuraciones') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">⚙️</span> Configuraciones
        </a>
        <?php endif; ?>

        <!-- Logout -->
        <a href="<?= url('logout') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-red-600 hover:bg-red-50 transition">
          <span class="text-lg">🚪</span> Cerrar sesión
        </a>

      <?php else: ?>
        <!-- Not logged in -->
        <div class="border-t border-gray-200 my-3"></div>
        <a href="<?= url('login') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition justify-center">
          Ingresar / Registrarse
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Sidebar Toggle Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebar-overlay');
  const toggleBtn = document.getElementById('sidebar-toggle');
  const closeBtn = document.getElementById('sidebar-close');
  const currentUrl = window.location.pathname || '';

  // Check if we're on superadmin pages (always pinned)
  const isSuperAdmin = currentUrl.indexOf('/superadmin') !== -1 || currentUrl.indexOf('/admin/') !== -1;
  const isPublicMap = currentUrl.indexOf('/mapa') !== -1 || currentUrl === '/' || currentUrl.endsWith('/');

  if (isSuperAdmin) {
    // Superadmin: sidebar always visible, no overlay
    sidebar.classList.remove('-translate-x-full');
    overlay.classList.add('hidden');
    document.cookie = 'sidebar_pinned=true; path=/; max-age=' + (60*60*24*365);
    document.body.style.marginLeft = '18rem'; // 72 * 4 = 288px = 18rem
    if (toggleBtn) toggleBtn.style.display = 'none';
  } else if (!isPublicMap) {
    // Normal behavior for other pages
    const isPinned = document.cookie.split('; ').find(row => row.startsWith('sidebar_pinned='));
    const pinned = isPinned ? isPinned.split('=')[1] === 'true' : false;

    if (pinned) {
      sidebar.classList.remove('-translate-x-full');
      overlay.classList.add('hidden');
      document.body.style.marginLeft = '18rem';
    }
  }

  function toggleSidebar() {
    if (isSuperAdmin) return; // No toggle on superadmin

    const currentlyPinned = document.cookie.split('; ').find(row => row.startsWith('sidebar_pinned='));
    const isCurrentlyPinned = currentlyPinned ? currentlyPinned.split('=')[1] === 'true' : false;

    if (isCurrentlyPinned) {
      sidebar.classList.add('-translate-x-full');
      document.cookie = 'sidebar_pinned=false; path=/; max-age=' + (60*60*24*365);
      overlay.classList.add('hidden');
      document.body.style.marginLeft = '';
    } else {
      sidebar.classList.remove('-translate-x-full');
      document.cookie = 'sidebar_pinned=true; path=/; max-age=' + (60*60*24*365);
      overlay.classList.add('hidden');
      document.body.style.marginLeft = '18rem';
    }
  }

  function closeSidebar() {
    if (isSuperAdmin) return;
    const currentlyPinned = document.cookie.split('; ').find(row => row.startsWith('sidebar_pinned='));
    const isCurrentlyPinned = currentlyPinned ? currentlyPinned.split('=')[1] === 'true' : false;
    if (!isCurrentlyPinned) {
      sidebar.classList.add('-translate-x-full');
      overlay.classList.add('hidden');
      document.body.style.marginLeft = '';
    }
  }

  if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
  if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
  if (overlay) overlay.addEventListener('click', closeSidebar);
});
</script>

<!-- Flash Messages -->
<?php if ($flash): ?>
<div id="flash-msg" class="fixed top-20 right-4 z-50 max-w-sm">
  <div class="rounded-lg shadow-lg p-4 text-sm font-medium flex items-start gap-3
    <?= $flash['type'] === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
    <span><?= $flash['type'] === 'success' ? '✅' : '❌' ?></span>
    <p><?= e($flash['msg']) ?></p>
    <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-gray-400 hover:text-gray-600">✕</button>
  </div>
</div>
<script>setTimeout(()=>{ const m=document.getElementById('flash-msg'); if(m) m.remove(); }, 4000);</script>
<?php endif; ?>