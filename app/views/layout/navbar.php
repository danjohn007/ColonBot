<?php
$user = currentUser();
$flash = flash();
$navPrefix = routePrefix();
$showSidebar = $user !== null;
$loginUrl = url($navPrefix . 'login');
?>
<!-- Top Navigation Bar (slim - only logo + hamburger) -->
<nav class="shadow-sm sticky top-0 z-50 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      <!-- Hamburger button (always visible on desktop too now) -->
      <?php if ($showSidebar): ?>
      <button id="sidebar-toggle" class="p-2 rounded-lg hover:bg-gray-100 transition text-gray-700 focus:outline-none">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
      <?php endif; ?>

      <!-- Logo -->
      <a href="https://colon.click/sistema/mapa" class="flex items-center gap-3 font-bold text-lg">
        <img src="<?= asset('img/colon.png') ?>" alt="Colón te conquistará" class="h-12 w-auto">
        <img src="<?= asset('img/cristo-bot-nino.png') ?>" alt="Cristo Bot Colón" class="h-10 w-auto rounded-full">
        <img src="<?= asset('img/logo-header-nuevo.jpeg') ?>" alt="Ayuntamiento de Colón" class="h-12 w-auto hidden sm:inline-block">
        <span class="text-base font-semibold whitespace-nowrap hidden sm:inline site-title-brand"><?= e(setting('site_name', 'CristobalBot: Mapa interactivo del turismo en Colón')) ?></span>
      </a>

      <!-- User / Login on the right -->
      <div class="flex items-center gap-2">
        <a href="https://www.facebook.com/share/1LUHTDWc5t/" target="_blank" rel="noopener noreferrer"
           class="flex items-center gap-1.5 bg-purple-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-medium hover:bg-purple-700 transition whitespace-nowrap">
          <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
          <span class="hidden sm:inline">Turismo</span>
        </a>
        <?php if ($user): ?>
          <a href="<?= url('logout') ?>" class="text-xs text-red-600 hover:text-red-700 transition hidden sm:inline">Salir</a>
        <?php else: ?>
          <a href="<?= e($loginUrl) ?>" class="inline-flex items-center gap-2 rounded-full bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/>
            </svg>
            <span>Ingresar / Registrarse</span>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<?php if ($showSidebar): ?>
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
      <a href="<?= url($navPrefix . 'mapa') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
        <span class="text-lg">🗺️</span> Mapa Turístico
      </a>

      <?php if ($user):
        $role = match ($user['role'] ?? '') {
          'admin_colaborador', 'colaborador', 'admin' => 'colaborador_admin',
          'turista' => 'visitor',
          default => $user['role'] ?? '',
        }; // Strict role check - no hasRole() to avoid overlaps
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
          <span class="text-lg">🏷️</span> Promociones
        </a>
        <a href="<?= url('admin/eventos') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">🎉</span> Eventos
        </a>
        <a href="<?= url('admin/crm') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">📇</span> CRM
        </a>
        <a href="<?= url('admin/notificaciones') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">🔔</span> Notificaciones
        </a>
        <div class="pt-4 pb-1 text-xs uppercase tracking-wide text-gray-400 font-semibold px-3">Sistema</div>
        <a href="<?= url('superadmin/usuarios') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">👥</span> Usuarios
        </a>
        <a href="<?= url('superadmin/negocios') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">🏢</span> Negocios
        </a>
        <a href="<?= url('superadmin/categorias') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">🏷️</span> Categorias
        </a>
        <a href="<?= url('superadmin/analitica') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">📈</span> Analitica
        </a>
        <a href="<?= url('superadmin/bitacora') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">📋</span> Bitacora
        </a>
        <a href="<?= url('superadmin/errores') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">⚠️</span> Errores
        </a>
        <a href="<?= url('configuraciones') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">⚙️</span> Configuraciones
        </a>
        <?php endif; ?>

        <!-- COLABORADOR_ADMIN (strict) - merged admin + colaborador -->
        <?php if ($role === 'colaborador_admin'): ?>
        <div class="pt-3 pb-1 text-xs uppercase tracking-wide text-gray-400 font-semibold px-3">Gestión</div>
        <a href="<?= url('admin') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">🏢</span> Mis Negocios
        </a>
        <a href="<?= url('admin/promociones') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">🏷️</span> Promociones
        </a>
        <a href="<?= url('admin/eventos') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">🎉</span> Eventos
        </a>
        <a href="<?= url('admin/crm') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">📇</span> CRM
        </a>
        <a href="<?= url('admin/notificaciones') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">🔔</span> Notificaciones
        </a>
        <a href="<?= url($navPrefix . 'configuraciones') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">⚙️</span> Configuraciones
        </a>
        <a href="<?= url($navPrefix . 'colaborador') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">📊</span> Turismo
        </a>
        <a href="<?= url($navPrefix . 'mi-perfil') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
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
          <span class="text-lg">🏷️</span> Promociones
        </a>
        <a href="<?= url('admin/eventos') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">🎉</span> Eventos
        </a>
        <a href="<?= url('admin/crm') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">📇</span> CRM
        </a>
        <a href="<?= url($navPrefix . 'mi-perfil') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">👤</span> Mi Perfil
        </a>
        <?php endif; ?>

        <!-- COLABORADOR (strict) - kept for backward compatibility but same as colaborador_admin -->
        <?php if ($role === 'colaborador'): ?>
        <div class="pt-3 pb-1 text-xs uppercase tracking-wide text-gray-400 font-semibold px-3">Turismo</div>
        <a href="<?= url($navPrefix . 'colaborador') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">📊</span> Dashboard Turismo
        </a>
        <a href="<?= url($navPrefix . 'colaborador/eventos') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">🎉</span> Eventos
        </a>
        <a href="<?= url($navPrefix . 'colaborador/metricas') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">📈</span> Métricas
        </a>
        <a href="<?= url($navPrefix . 'mi-perfil') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">👤</span> Mi Perfil
        </a>
        <?php endif; ?>

        <!-- VISITOR (strict) - merged from turista -->
        <?php if ($role === 'visitor'): ?>
        <a href="<?= url($navPrefix . 'turista') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">🏠</span> Inicio
        </a>
        <a href="<?= url($navPrefix . 'turista/perfil') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">👤</span> Mi Perfil Visitante
        </a>
        <a href="<?= url($navPrefix . 'notificaciones') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition">
          <span class="text-lg">🔔</span> Notificaciones
        </a>
        <?php endif; ?>

        <!-- Divider -->
        <div class="border-t border-gray-200 my-3"></div>

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

  function syncSidebarToggle() {
    if (!toggleBtn || !sidebar) return;
    const sidebarIsOpen = !sidebar.classList.contains('-translate-x-full') && !sidebar.classList.contains('hidden');
    toggleBtn.style.display = sidebarIsOpen ? 'none' : '';
  }

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

  syncSidebarToggle();

  function toggleSidebar() {
    const currentlyPinned = document.cookie.split('; ').find(row => row.startsWith('sidebar_pinned='));
    const isCurrentlyPinned = currentlyPinned ? currentlyPinned.split('=')[1] === 'true' : false;

    if (isCurrentlyPinned) {
      sidebar.classList.add('-translate-x-full');
      document.cookie = 'sidebar_pinned=false; path=/; max-age=' + (60*60*24*365);
      overlay.classList.add('hidden');
      document.body.style.marginLeft = '';
      syncSidebarToggle();
    } else {
      sidebar.classList.remove('-translate-x-full');
      document.cookie = 'sidebar_pinned=true; path=/; max-age=' + (60*60*24*365);
      overlay.classList.add('hidden');
      document.body.style.marginLeft = '18rem';
      syncSidebarToggle();
    }
  }

  function closeSidebar() {
    sidebar.classList.add('-translate-x-full');
    overlay.classList.add('hidden');
    document.body.style.marginLeft = '';
    document.cookie = 'sidebar_pinned=false; path=/; max-age=' + (60*60*24*365);
    syncSidebarToggle();
  }

  if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
  if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
  if (overlay) overlay.addEventListener('click', closeSidebar);
});
</script>
<?php endif; ?>

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
