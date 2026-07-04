# Mejoras Opcionales - ColonBot

## 1. Slideboards/Carousel Avanzado en Mapa

Si deseas agregar un carousel tipo colon360.mx en el mapa, agrega esta sección antes de la sección "Contenido informativo":

```html
<!-- Carousel de destinos destacados -->
<div class="relative bg-gradient-to-r from-purple-600 to-blue-600 overflow-hidden">
  <div id="banner-carousel" class="flex transition-transform duration-500 ease-out"
       style="transform: translateX(0);">
    <div class="min-w-full aspect-video bg-cover bg-center flex items-end"
         style="background-image: url('<?= asset('img/banner-1.jpg') ?>');">
      <div class="w-full bg-gradient-to-t from-black/80 to-transparent p-6">
        <h2 class="text-white text-3xl font-bold">Viñedos de Colón</h2>
        <p class="text-white/80 mt-2">Descubre nuestras bodegas centenarias</p>
      </div>
    </div>
    <div class="min-w-full aspect-video bg-cover bg-center flex items-end"
         style="background-image: url('<?= asset('img/banner-2.jpg') ?>');">
      <div class="w-full bg-gradient-to-t from-black/80 to-transparent p-6">
        <h2 class="text-white text-3xl font-bold">Turismo Gastronómico</h2>
        <p class="text-white/80 mt-2">Vive la experiencia culinaria más auténtica</p>
      </div>
    </div>
    <div class="min-w-full aspect-video bg-cover bg-center flex items-end"
         style="background-image: url('<?= asset('img/banner-3.jpg') ?>');">
      <div class="w-full bg-gradient-to-t from-black/80 to-transparent p-6">
        <h2 class="text-white text-3xl font-bold">Patrimonio Cultural</h2>
        <p class="text-white/80 mt-2">Recorre nuestros sitios históricos</p>
      </div>
    </div>
  </div>
  <button class="absolute left-4 top-1/2 -translate-y-1/2 z-10 bg-white/30 hover:bg-white/50 
                  text-white rounded-full w-10 h-10 flex items-center justify-center transition"
          onclick="slideCarousel(-1)">❮</button>
  <button class="absolute right-4 top-1/2 -translate-y-1/2 z-10 bg-white/30 hover:bg-white/50 
                  text-white rounded-full w-10 h-10 flex items-center justify-center transition"
          onclick="slideCarousel(1)">❯</button>
</div>

<script>
let carouselIndex = 0;
function slideCarousel(n) {
  const carousel = document.getElementById('banner-carousel');
  carouselIndex += n;
  if (carouselIndex >= 3) carouselIndex = 0;
  if (carouselIndex < 0) carouselIndex = 2;
  carousel.style.transform = `translateX(-${carouselIndex * 100}%)`;
}

// Auto-slide cada 5 segundos (opcional)
setInterval(() => slideCarousel(1), 5000);
</script>
```

**Archivos de imagen requeridos:**
- `public/img/banner-1.jpg` (Viñedos)
- `public/img/banner-2.jpg` (Gastronomía)
- `public/img/banner-3.jpg` (Patrimonio)

---

## 2. Modal Mejorado para Crear Eventos

Si necesitas agregar campos adicionales o mejorar validación, edita `app/views/events/index.php`.

**Campos recomendados:**
```html
<!-- Vigencia (ya existe como 'validity') -->
<input type="date" id="event-validity-start" name="validity_start" class="w-full px-3 py-2 border rounded-lg">
<input type="date" id="event-validity-end" name="validity_end" class="w-full px-3 py-2 border rounded-lg">

<!-- Aforo (ya existe como 'capacity') -->
<input type="number" id="event-capacity" name="capacity" placeholder="Capacidad" class="w-full px-3 py-2 border rounded-lg">

<!-- Precio y Precio Preventa (ya existen) -->
<input type="number" id="event-price" name="price" step="0.01" placeholder="Precio normal" class="w-full px-3 py-2 border rounded-lg">
<input type="number" id="event-presale-price" name="presale_price" step="0.01" placeholder="Precio preventa" class="w-full px-3 py-2 border rounded-lg">
```

---

## 3. Sistema de Notificaciones

Ya implementado. Para verificar:

```php
// En EventController::create() después de crear evento
$notification = new NotificationModel();
$notification->create([
    'user_id' => $visitorIds, // array de IDs de visitantes
    'type' => 'evento_nuevo',
    'title' => 'Nuevo evento: ' . $_POST['title'],
    'message' => 'Mira nuestro nuevo evento ' . $_POST['title'],
    'url' => '/evento/' . $eventId,
    'created_at' => date('Y-m-d H:i:s')
]);
```

---

## 4. Testing en Móviles - Checklist

### Usando Chrome DevTools
1. Abre DevTools (F12)
2. Haz clic en Device Toolbar (Ctrl+Shift+M)
3. Selecciona dispositivo: iPhone 12 Pro (390x844)
4. Prueba estos flujos:

#### Flow 1: Visitante Registrado
```
[ ] Ir a /sistema/registro/visitante
[ ] Completar registro
[ ] Confirmar email
[ ] Login
[ ] Ver dashboard
[ ] Ver eventos públicos
[ ] Dejar reseña en un negocio
[ ] Recibir promociones en bandeja
```

#### Flow 2: Prestador
```
[ ] Ir a /sistema/registro/prestador
[ ] Completar registro
[ ] Confirmar como prestador
[ ] Login
[ ] Ver /admin/crm (nuevos prospectos)
[ ] Ver /admin/eventos
[ ] Crear evento con todos los campos
[ ] Copiar URL pública del evento
[ ] Compartir evento (WhatsApp, email)
```

#### Flow 3: Colaborador
```
[ ] Login como colaborador_admin
[ ] Ver /colaborador (dashboard)
[ ] Ir a Eventos y Promociones
[ ] Autorizar evento privado (1-click)
[ ] Ver /colaborador/metricas
[ ] Verificar gráficas y tablas en móvil
```

#### Flow 4: Mapa Público
```
[ ] Ir a /mapa
[ ] Filtrar por categoría
[ ] Filtrar por tipo de viaje
[ ] Ver favoritos
[ ] Tocar un pin en el mapa
[ ] Ver panel lateral con información
[ ] En móvil, ver bottom-sheet en lugar de panel
[ ] Ver banners de registro al bajar
```

### Velocidad de Carga
- [ ] Primeras imágenes cargan en < 2s
- [ ] Modal "Crear evento" aparece en < 1s
- [ ] Búsqueda responde en < 500ms

### Orientación
- [ ] Funciona en portrait (vertical)
- [ ] Funciona en landscape (horizontal)
- [ ] No hay overflow de contenido

---

## 5. Configuración Final en Base de Datos

Verifica que estos valores estén configurados:

```sql
-- Login a tu base de datos
USE colonbot;

-- Ver configuración actual
SELECT * FROM settings WHERE `key` IN (
  'site_name',
  'map_lat',
  'map_lng', 
  'map_zoom',
  'chatbot_wa_number',
  'chatbot_active',
  'uploads_path',
  'uploads_url'
);

-- Si falta UPLOAD_URL, agregar:
INSERT INTO settings (`key`, `value`) VALUES
('uploads_url', '/uploads/'),
('uploads_path', './uploads/')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
```

---

## 6. Deployment en Servidor

Antes de publicar en producción:

### 1. Variables de Entorno
```php
// En config/config.php
define('BASE_URL', 'https://colon.click');
define('APP_DEBUG', false);
define('APP_ENV', 'production');
```

### 2. Permisos de Carpetas
```bash
# En servidor
chmod 755 uploads/
chmod 755 logs/
chmod 644 uploads/*
```

### 3. HTTPS Obligatorio
```php
// Asegurar redirección HTTP → HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}
```

### 4. Backup Automático
```bash
# Cron diario (agregar a crontab)
0 3 * * * mysqldump -u user -p colonbot > /backups/colonbot_$(date +\%Y\%m\%d).sql
```

---

## 7. Solución de Problemas Comunes

### Las imágenes no se ven
```php
// Verificar en helper imageUrl() 
// Debe estar en helpers.php

function imageUrl($path) {
    if (strpos($path, 'http') === 0) return $path; // URL completa
    return BASE_URL . '/uploads/' . ltrim($path, '/');
}
```

### Modal no se abre
```javascript
// Verificar que exista en app.js:
function openCreateModal() {
    document.getElementById('event-modal').classList.remove('hidden');
}
```

### URLs públicas no funcionan
```php
// Verificar route en index.php:
$router->get('/evento/{id}', 'EventController::publicView');
$router->get('/evento/{id}/{slug}', 'EventController::publicView');
```

---

## 8. Resumen de Estado Final

| Feature | Status | Archivo | Verificado |
|---------|--------|---------|-----------|
| Login Prestador → /admin/crm | ✅ DONE | AuthController | SÍ |
| Crear Eventos | ✅ DONE | EventController | SÍ |
| URL Pública Eventos | ✅ DONE | EventModel | SÍ |
| Crear Promociones | ✅ DONE | PromotionController | SÍ |
| URL Pública Promociones | ✅ DONE | PromotionModel | SÍ |
| Dashboard Colaborador | ✅ DONE | ColaboradorController | SÍ |
| Métricas Avanzadas | ✅ DONE | metrics.php | SÍ |
| Visitante Dashboard | ✅ DONE | TouristController | SÍ |
| Mapa Interactivo | ✅ DONE | MapController | SÍ |
| Banners Registro | ✅ DONE | map/index.php | SÍ |
| Testing Móviles | ⚠️ PENDING | - | - |
| Slideboards Adicionales | ⚠️ OPTIONAL | map/index.php | - |

---

**Contacto para dudas:**  
Si necesitas más información sobre cualquier módulo, revisa los comentarios en cada archivo PHP.

