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
  <!-- Footer -->
  <footer class="py-3 px-4 text-white text-xs flex items-center justify-between" style="background-color: var(--color-primary)">
    <span class="font-medium">Plataforma Turística de Colón</span>
    <span class="opacity-80">&copy; <?= date('Y') ?> Municipio de Colón, Querétaro. Todos los derechos reservados.</span>
  </footer>
</body>
</html>
