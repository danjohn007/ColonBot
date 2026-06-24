  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <!-- App JS -->
  <script src="<?= asset('js/app.js') ?>"></script>
  <script>
    // Mobile menu toggle
    const btn = document.getElementById('mobile-menu-btn');
    const menu = document.getElementById('mobile-menu');
    if (btn && menu) {
      btn.addEventListener('click', () => menu.classList.toggle('hidden'));
    }
  </script>
  <!-- Footer institucional -->
  <footer style="background-color: var(--color-primary)" class="text-white py-8 px-4">
    <div class="max-w-7xl mx-auto">
      <div class="flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-4">
          <img src="<?= asset('img/ssc.png') ?>" alt="SSC" class="h-14 w-auto">
          <div class="text-left">
            <p class="font-bold text-lg">Secretaría de Seguridad Ciudadana</p>
            <p class="text-sm opacity-80">Municipio de Colón</p>
          </div>
        </div>
        <div class="flex items-center gap-4">
          <img src="<?= asset('img/logo-header.png') ?>" alt="Colón" class="h-12 w-auto">
          <div class="text-left">
            <p class="font-bold text-lg">Colón Turismo</p>
            <p class="text-sm opacity-80">Plataforma Turística Interactiva</p>
          </div>
        </div>
      </div>
      <div class="mt-6 pt-4 border-t border-white border-opacity-20 text-center text-sm opacity-70">
        <p>&copy; <?= date('Y') ?> Municipio de Colón, Querétaro. Todos los derechos reservados.</p>
      </div>
    </div>
  </footer>
</body>
</html>
