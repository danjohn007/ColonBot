<!-- Bottom Mobile Navigation (app-style) -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-40 safe-area-bottom">
  <div class="flex items-center justify-around h-16">
    <a href="<?= url('mapa') ?>" class="flex flex-col items-center gap-1 text-xs text-gray-500 hover:text-blue-600 transition px-3">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
      </svg>
      Mapa
    </a>
    <?php if (isLoggedIn()): ?>
    <a href="<?= url('admin') ?>" class="flex flex-col items-center gap-1 text-xs text-gray-500 hover:text-blue-600 transition px-3">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
      </svg>
      Negocio
    </a>
    <?php if (hasRole('superadmin')): ?>
    <a href="<?= url('superadmin') ?>" class="flex flex-col items-center gap-1 text-xs text-gray-500 hover:text-blue-600 transition px-3">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
      </svg>
      Admin
    </a>
    <?php endif; ?>
    <?php endif; ?>
    <a href="<?= url('login') ?>" class="flex flex-col items-center gap-1 text-xs text-gray-500 hover:text-blue-600 transition px-3">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
      </svg>
      <?= isLoggedIn() ? e(currentUser()['name']) : 'Ingresar' ?>
    </a>
  </div>
</nav>
<div class="md:hidden h-16"></div><!-- spacer for bottom nav -->
