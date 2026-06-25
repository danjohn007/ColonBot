  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <!-- App JS -->
  <script src="<?= asset('js/app.js') ?>"></script>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v7.0.0/css/all.css">
  <script>
    // Mobile menu toggle
    const btn = document.getElementById('mobile-menu-btn');
    const menu = document.getElementById('mobile-menu');
    if (btn && menu) {
      btn.addEventListener('click', () => menu.classList.toggle('hidden'));
    }
  </script>
  <!-- Footer -->
  <footer>
    <div style="background-color: #322662; color: #ffffff; padding: 2.5rem 1rem 1.5rem;">
      <div class="max-w-7xl mx-auto">
        <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap; margin-top: 1rem; justify-content: center;">
          <a href="https://colon.gob.mx/inicio/?page_id=3" target="_blank" rel="noopener" style="color: #d288ed; text-decoration: underline; display: flex; align-items: center; gap: 8px; font-weight: 500; font-size: 14px; transition: color 0.3s ease;" onmouseover="this.style.color='#ff7f41'" onmouseout="this.style.color='#d288ed'">
            <i class="fas fa-shield-alt" style="font-size: 14px;"></i>
            Aviso de privacidad
          </a>
          <a href="https://colon.gob.mx/inicio/aviso-de-privacidad-siaaps/" target="_blank" rel="noopener" style="color: #d288ed; text-decoration: underline; display: flex; align-items: center; gap: 8px; font-weight: 500; font-size: 14px; transition: color 0.3s ease;" onmouseover="this.style.color='#ff7f41'" onmouseout="this.style.color='#d288ed'">
            <i class="fas fa-file-contract" style="font-size: 14px;"></i>
            Aviso de privacidad SIAAPS
          </a>
        </div>
        <div style="text-align: center; margin-top: 1.5rem; font-size: 13px; opacity: 0.9;">
          &copy; <?= date('Y') ?> Municipio de Colón, Querétaro. Todos los derechos reservados.
        </div>
      </div>
    </div>
  </footer>
</body>
</html>
