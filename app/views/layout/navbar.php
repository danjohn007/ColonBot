<?php
$user = currentUser();
$flash = flash();
?>
<!-- Top Navigation -->
<nav class="shadow-sm sticky top-0 z-50" style="background-color: var(--color-primary)">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      <!-- Logo -->
      <a href="<?= url() ?>" class="flex items-center gap-2 font-bold text-white text-lg">
        <?php if (setting('site_logo')): ?>
          <img src="<?= imageUrl(setting('site_logo')) ?>" alt="Logo" class="h-8 w-auto">
        <?php else: ?>
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        <?php endif; ?>
        <span class="hidden sm:inline"><?= e(setting('site_name', APP_NAME)) ?></span>
      </a>

      <!-- Desktop menu -->
      <div class="hidden md:flex items-center gap-6 text-sm font-medium text-white">
        <a href="<?= url('mapa') ?>" class="hover:opacity-80 transition">🗺️ Mapa</a>
        <?php if ($user): ?>
          <?php if (hasRole('admin')): ?>
            <a href="<?= url('admin/notificaciones') ?>" class="hover:opacity-80 transition">🔔 Notificaciones</a>
          <?php endif; ?>
          <?php if (hasRole('superadmin')): ?>
            <a href="<?= url('superadmin') ?>" class="hover:opacity-80 transition">📊 Dashboard</a>
            <a href="<?= url('configuraciones') ?>" class="hover:opacity-80 transition">⚙️ Config</a>
          <?php endif; ?>
          <a href="<?= url('admin') ?>" class="hover:opacity-80 transition">🏢 Mi Negocio</a>
          <a href="<?= url('logout') ?>" class="text-red-200 hover:text-red-100 transition">Salir</a>
        <?php else: ?>
          <a href="<?= url('login') ?>" class="bg-white bg-opacity-20 text-white px-4 py-2 rounded-lg hover:bg-opacity-30 transition">Ingresar</a>
        <?php endif; ?>
      </div>

      <!-- Mobile hamburger -->
      <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-white hover:bg-opacity-20 transition text-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
    </div>
  </div>

  <!-- Mobile menu -->
  <div id="mobile-menu" class="hidden md:hidden border-t border-white border-opacity-20 bg-white">
    <div class="px-4 py-3 space-y-2 text-sm font-medium text-gray-700">
      <a href="<?= url('mapa') ?>" class="block py-2 hover:opacity-80">🗺️ Mapa Turístico</a>
      <?php if ($user): ?>
        <?php if (hasRole('admin')): ?>
          <a href="<?= url('admin/notificaciones') ?>" class="block py-2 hover:opacity-80">🔔 Notificaciones</a>
        <?php endif; ?>
        <?php if (hasRole('superadmin')): ?>
          <a href="<?= url('superadmin') ?>" class="block py-2 hover:opacity-80">📊 Dashboard</a>
          <a href="<?= url('configuraciones') ?>" class="block py-2 hover:opacity-80">⚙️ Configuraciones</a>
        <?php endif; ?>
        <a href="<?= url('admin') ?>" class="block py-2 hover:opacity-80">🏢 Mi Negocio</a>
        <a href="<?= url('logout') ?>" class="block py-2 text-red-500">Cerrar sesión</a>
      <?php else: ?>
        <a href="<?= url('login') ?>" class="block py-2 font-semibold" style="color: var(--color-primary)">Ingresar</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

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
