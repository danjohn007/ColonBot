# Cambios Solicitados del PDF - Estado y Soluciones

## ✅ COMPLETADO

1. **Eliminar navbar de vistas registro**
   - ✅ register_prestador.php - DONE
   - ✅ register_visitor.php - DONE

## ⚠️ EN REVISIÓN

### 1. Vista Detalle del Lugar (/sistema/lugar/{slug})
**Estado:** Existe MapController::detail() 
**Pendiente:** Verificar que map/detail.php tenga todos los campos:
- [ ] Botón "en línea" (mostrar/no en chatbot)
- [ ] Promociones vigentes
- [ ] Eventos vigentes
- [ ] Productos/servicios
- [ ] Horario de servicio
- [ ] Calificaciones/valoraciones
- [ ] Botón WhatsApp

**Código verificado:** MapController::detail() línea 32-77 obtiene todos estos datos
- `$images` - imágenes
- `$amenities` - amenidades
- `$services` - servicios
- `$products` - productos
- `$events` - eventos
- `$reviews` - reseñas/calificaciones
- `$tripTypes` - tipos de viaje

### 2. Vista Mapa (/sistema/mapa/{id})
**Cambios solicitados:**
- [ ] Botón "Reservar/Comprar" → "Reservar por Whatsapp"
- [ ] Quitar botón verde "Whatsapp"

**Ubicación:** map/index.php línea ~850-900 (panel lateral)

### 3. Landing Page Promociones (/sistema/promocion/{id})
**Estado:** Existe promotions/public_view.php pero INCOMPLETA
**Pendiente:** Agregar a la vista estos campos:
- [x] Imagen
- [x] Título
- [x] Negocio
- [x] Descripción
- [ ] Precio de lista (comparar con presale_price)
- [ ] Precio promocional
- [ ] Vigencia (start_date/end_date)
- [ ] Restricciones (conditions)

**Solución:** Modificar promotions/public_view.php para mostrar precio_lista vs precio_promocional

### 4. Vista Notificaciones (/sistema/admin/notificaciones)
**Estado:** Error HTTP 500
**Causa probable:** Ruta routing o acceso de roles

**Solución verificada:**
- NotificationController::index() requiere 'admin' role
- ⚠️ PROBLEMA: visitantes registrados no tienen rol 'admin'
- Debería usar 'cualquier usuario loggeado' en lugar de 'admin'

**FIX necesario:**
```php
// Cambiar en NotificationController.php
public function index(): void
{
    // $this->requireAuth('admin'); ← PROBLEMA
    $this->requireAuth(); // ← Permitir cualquier usuario loggeado
```

### 5. Vista Eventos (/sistema/admin/eventos)
**Estado:** Error HTTP 500
**Causa probable:** Similar a notificaciones

**FIX necesario:**
```php
// Cambiar en EventController.php línea 18
public function index(): void
{
    $this->requireAuth('prestador');
    // ↑ Cambiar a permitir: prestador, colaborador_admin, superadmin
}
```

### 6. Login Doble Colaborador_admin
**Estado:** Requiere 2 veces la información
**Causa probable:** CSRF token o redirección

**Investigación:** auth/login.php no muestra duplicación. Posible:
- Session no se crea correctamente
- Redirección falla y vuelve al login

**FIX potencial:** En AuthController.php línea 74
```php
// Agregar verificación:
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [...];
}
```

### 7. BusinessController - Crear Negocios
**Estado:** Vista /sistema/admin/negocio/crear está "rota"
**Causa probable:** Falta implementación o ruta missing

**Solución:** Verificar que BusinessController tenga método `createForm()` y ruta en index.php
```php
// Falta en BusinessController probablemente:
public function createForm(): void { 
    $this->requireAuth('prestador');
    $this->view('business.create', ...);
}
```

## 🔧 CAMBIOS NECESARIOS DETALLADOS

### OPCIÓN A: Cambios rápidos de texto (SQL-like)

**1. NotificationController.php**
```php
// Línea 11: Cambiar de
public function index(): void
{
    $this->requireAuth('admin');
    
// A:
public function index(): void
{
    $this->requireAuth('prestador', 'visitante', 'colaborador_admin', 'superadmin');
```

**2. EventController.php**
```php
// Línea 18: Similar fix
```

**3. map/index.php**
```php
// Buscar y reemplazar:
// "Reservar/Comprar" → "Reservar por Whatsapp"
// Quitar <button ... class="green">Whatsapp</button>
```

**4. promotions/public_view.php**
```php
// Agregar sección de precios comparativos:
<?php if ($promo['price']): ?>
<div class="flex gap-4">
  <div>
    <p class="text-xs text-gray-500">Precio de lista</p>
    <p class="text-lg font-bold line-through text-gray-400">
      $<?= number_format((float)$promo['price'], 2) ?>
    </p>
  </div>
  <?php if ($promo['presale_price']): ?>
  <div>
    <p class="text-xs text-gray-500">Precio promocional</p>
    <p class="text-lg font-bold text-green-600">
      $<?= number_format((float)$promo['presale_price'], 2) ?>
    </p>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>
```

### OPCIÓN B: Cambios de controlador

**TouristController - Crear Dashboard Visitante**
```php
// En PublicRegisterController.php
public function visitorLogin(): void
{
    // ... auth code ...
    if ($user['role'] !== 'visitor') {
        $this->flash('error', 'Esta cuenta no es de tipo visitante.');
        $this->redirect('registro/visitante');
        return;
    }
    
    // Cambiar redirección de:
    // $this->redirect('mapa');
    // A:
    $this->redirect('dashboard/visitante'); // ← Nueva ruta
}

// En TouristController
public function dashboard(): void
{
    $this->requireAuth('visitor');
    $user = currentUser();
    $promotions = $this->promotions->activeForVisitor();
    $visitedPlaces = $this->businesses->visitedByUser((int)$user['id']);
    $notifications = $this->notifications->forUser((int)$user['id']);
    
    $this->view('tourist.dashboard', compact('promotions', 'visitedPlaces', 'notifications', 'user'));
}
```

**BusinessController - CreateForm**
```php
public function createForm(): void
{
    $this->requireAuth('prestador');
    $categories = $this->categories->active();
    $this->view('business.create_form', compact('categories'));
}
```

## 📋 RESUMEN DE CAMBIOS POR ARCHIVO

| Archivo | Cambio | Líneas | Tipo |
|---------|--------|--------|------|
| register_prestador.php | Eliminar navbar | 3-4 | ✅ DONE |
| register_visitor.php | Eliminar navbar | 3-4 | ✅ DONE |
| NotificationController.php | Cambiar requireAuth | 11 | 🔧 TODO |
| EventController.php | Cambiar requireAuth | 18 | 🔧 TODO |
| map/index.php | Botón WhatsApp → Reservar | ~850 | 🔧 TODO |
| promotions/public_view.php | Precios comparativos | ~25 | 🔧 TODO |
| PublicRegisterController.php | Redirección visitante | ~70 | 🔧 TODO |
| TouristController.php | Nuevo dashboard | NEW | 🔧 TODO |
| BusinessController.php | CreateForm method | NEW | 🔧 TODO |
| business/create.php | Nueva vista | NEW | 🔧 TODO |
| map/detail.php | Verificar completitud | ~50 | ✔️ CHECK |

## 🎯 PRIORIDAD DE EJECUCIÓN

**CRÍTICO (Bloquea funcionalidad):**
1. Arreglar NotificationController.php requireAuth
2. Arreglar EventController.php requireAuth
3. Arreglar PublicRegisterController visitante redirect

**ALTO (Falta funcionalidad visible):**
4. Crear TouristController dashboard
5. Crear BusinessController createForm
6. Cambiar botones en map

**MEDIO (Mejoras UI):**
7. Precios en promotions/public_view.php
8. Verificar map/detail.php completitud
9. Agregar banners en mapa

## 💡 NOTAS

- Las vistas "rotas" probablemente tienen un error 500 por requireAuth incorrecto
- El login doble podría ser un refresh de página tras redirección
- Las landing pages de promociones Y eventos necesitan mejoras visuales

