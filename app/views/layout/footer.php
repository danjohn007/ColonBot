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
        <!-- Contact sections grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-bottom: 2rem;">

          <!-- Presidencia Municipal -->
          <div>
            <p style="font-size: 16px; font-weight: 700; margin-bottom: 0.75rem; color: #ffffff;">Presidencia Municipal de Colón</p>
            <ul style="list-style: none; padding: 0; margin: 0;">
              <li style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 0.5rem; font-size: 14px;">
                <i class="fas fa-map-marker-alt" style="color: #d288ed; margin-top: 3px; min-width: 16px;"></i>
                <span>C. Héroes de la Revolución No. 1, Colón, Qro.</span>
              </li>
              <li style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 0.5rem; font-size: 14px;">
                <i class="far fa-clock" style="color: #d288ed; margin-top: 3px; min-width: 16px;"></i>
                <span>Horarios de atención:</span>
              </li>
              <li style="padding-left: 26px; font-size: 13px; opacity: 0.9; margin-bottom: 0.25rem;">Lunes de 09:00 - 17:00 horas</li>
              <li style="padding-left: 26px; font-size: 13px; opacity: 0.9; margin-bottom: 0.5rem;">Mar. a Vier. de 09:00 - 16:00 horas</li>
            </ul>
          </div>

          <!-- Atención Ciudadana -->
          <div>
            <p style="font-size: 16px; font-weight: 700; margin-bottom: 0.75rem; color: #ffffff;">Atención Ciudadana</p>
            <ul style="list-style: none; padding: 0; margin: 0;">
              <li style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.5rem; font-size: 14px;">
                <i class="fas fa-phone" style="color: #d288ed; min-width: 16px;"></i>
                <a href="tel:419-234-3700" style="color: #d288ed; text-decoration: underline;">Tel: 419-234-3700 - Ext. 1302</a>
              </li>
              <li style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.5rem; font-size: 14px;">
                <i class="fas fa-envelope" style="color: #d288ed; min-width: 16px;"></i>
                <a href="mailto:atencion.ciudadana@colon.gob.mx" style="color: #d288ed; text-decoration: underline;">atencion.ciudadana@colon.gob.mx</a>
              </li>
            </ul>
          </div>

          <!-- SMDIF -->
          <div>
            <p style="font-size: 16px; font-weight: 700; margin-bottom: 0.75rem; color: #ffffff;">SMDIF</p>
            <ul style="list-style: none; padding: 0; margin: 0;">
              <li style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.5rem; font-size: 14px;">
                <i class="fas fa-phone" style="color: #d288ed; min-width: 16px;"></i>
                <a href="tel:419-690-4856" style="color: #d288ed; text-decoration: underline;">Tel: 419-690-4856</a>
              </li>
              <li style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.5rem; font-size: 14px;">
                <i class="fas fa-globe" style="color: #d288ed; min-width: 16px;"></i>
                <a href="https://colon.gob.mx/SMDIF" target="_blank" rel="noopener" style="color: #d288ed; text-decoration: underline;">www.colon.gob.mx/SMDIF</a>
              </li>
            </ul>
          </div>

        </div>

        <!-- Bottom row: Protección Civil + Social + Privacy -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; border-top: 1px solid rgba(255,255,255,0.15); padding-top: 2rem;">
          <!-- Protección Civil y Seguridad Pública -->
          <div id="protcivil">
            <p style="font-size: 16px; font-weight: 700; margin-bottom: 0.75rem; color: #ffffff;">Protección Civil y Seguridad Pública</p>
            <ul style="list-style: none; padding: 0; margin: 0;">
              <li style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.5rem; font-size: 14px;">
                <i class="fas fa-phone" style="color: #d288ed; min-width: 16px;"></i>
                <a href="tel:419-234-3700" style="color: #d288ed; text-decoration: underline;">Tel: 419-234-3700 Ext. 2802</a>
              </li>
            </ul>
          </div>

          <!-- Redes Sociales -->
          <div>
            <p style="font-size: 16px; font-weight: 700; margin-bottom: 0.75rem; color: #ffffff;">Redes Sociales Oficiales</p>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
              <a href="https://www.facebook.com/MunicipioColonOficial" target="_blank" rel="noopener" style="color: #d288ed; font-size: 24px; transition: color 0.3s ease;" onmouseover="this.style.color='#ff7f41'" onmouseout="this.style.color='#d288ed'" title="Facebook">
                <i class="fab fa-facebook-f"></i>
              </a>
              <a href="https://www.instagram.com/gobiernodecolon/" target="_blank" rel="noopener" style="color: #d288ed; font-size: 24px; transition: color 0.3s ease;" onmouseover="this.style.color='#ff7f41'" onmouseout="this.style.color='#d288ed'" title="Instagram">
                <i class="fab fa-instagram"></i>
              </a>
              <a href="https://x.com/MpioColon" target="_blank" rel="noopener" style="color: #d288ed; font-size: 24px; transition: color 0.3s ease;" onmouseover="this.style.color='#ff7f41'" onmouseout="this.style.color='#d288ed'" title="X (Twitter)">
                <i class="fab fa-x-twitter"></i>
              </a>
              <a href="https://www.youtube.com/channel/UCzxvBsAVswjwc6dRN4uglWA" target="_blank" rel="noopener" style="color: #d288ed; font-size: 24px; transition: color 0.3s ease;" onmouseover="this.style.color='#ff7f41'" onmouseout="this.style.color='#d288ed'" title="YouTube">
                <i class="fab fa-youtube"></i>
              </a>
            </div>
          </div>

          <!-- Privacy links -->
          <div>
            <p style="font-size: 16px; font-weight: 700; margin-bottom: 0.75rem; color: #ffffff;">Privacidad</p>
            <ul style="list-style: none; padding: 0; margin: 0;">
              <li style="display: flex; align-items: center; gap: 8px; margin-bottom: 0.5rem; font-size: 14px;">
                <i class="fas fa-shield-alt" style="color: #d288ed; min-width: 16px;"></i>
                <a href="https://colon.gob.mx/inicio/?page_id=3" target="_blank" rel="noopener" style="color: #d288ed; text-decoration: underline; font-weight: 500; transition: color 0.3s ease;" onmouseover="this.style.color='#ff7f41'" onmouseout="this.style.color='#d288ed'">Aviso de privacidad</a>
              </li>
              <li style="display: flex; align-items: center; gap: 8px; margin-bottom: 0.5rem; font-size: 14px;">
                <i class="fas fa-file-contract" style="color: #d288ed; min-width: 16px;"></i>
                <a href="https://colon.gob.mx/inicio/aviso-de-privacidad-siaaps/" target="_blank" rel="noopener" style="color: #d288ed; text-decoration: underline; font-weight: 500; transition: color 0.3s ease;" onmouseover="this.style.color='#ff7f41'" onmouseout="this.style.color='#d288ed'">Aviso de privacidad SIAAPS</a>
              </li>
              <li style="font-size: 13px; opacity: 0.9; margin-top: 0.75rem;">&copy; <?= date('Y') ?> Municipio de Colón, Querétaro. Todos los derechos reservados.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </footer>
</body>
</html>
