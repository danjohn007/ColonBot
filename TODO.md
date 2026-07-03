# Plan de implementación - Progreso

## COMPLETADO ✅

### Tarea 1: Menú lateral - Separar Promociones y Eventos ✅
- [x] Separar enlace "Promociones" y "Eventos" en el menú lateral (navbar.php)
- [x] Agregar rutas para `admin/eventos` en index.php
- [x] Crear vistas de eventos (separada de promociones)

### Tarea 2: Modal de Promociones (tabla 'promotions') ✅
- [x] Crear modal separada solo para Promociones
- [x] Cambiar "Precio preventa" → "Precio promocional"
- [x] Cambiar "Condiciones" → "Restricciones de la Promoción"
- [x] Quitar campo "TIPO"
- [x] Agregar botón de autorización (aprobación) para publicar en chatbot
- [x] Crear URL pública de la promoción

### Tarea 3: Modal de Eventos (tabla 'events') ✅
- [x] Crear modelo EventModel
- [x] Crear EventController (con crear, editar, toggle, aprobar)
- [x] Crear vista de eventos con modal separada
- [x] Título de modal "Eventos"
- [x] Botón de autorización para eventos (superadmin/colaborador)
- [x] Vista pública de eventos con datos del negocio

### Tarea 4: Configuraciones - Conectar DB con título del sitio ✅
- [x] Modificar navbar.php para usar setting('site_name') en el título del header

### Tarea 5: Botón "Promos" por negocio específico ✅
- [x] Modificar dashboard.php para pasar business_id a promociones
- [x] Modificar PromotionController.index para filtrar por negocio
- [x] Mostrar solo promos del negocio específico al hacer clic en "Promos"

### Tarea 6: Dashboard de negocio - Corregir vista rota ✅
- [x] Agregar canvas faltante para contactsChart en microsite_dashboard.php

### Tarea 7: Mapas - Iconos de TIPO DE LUGAR en vez de PUNTO DE REFERENCIA ✅
- [x] Modificar loadPOIs() para usar isotipo (tipo de lugar) en todos los íconos
- [x] Modificar filterTripType() para usar isotipo en reference points
- [x] Usar isotipo para íconos en lugar de category_icon

## PENDIENTE ❌

### Tarea 8: Diseño del sidebar del mapa
- [ ] Rediseñar el panel lateral para que coincida con colon360.mx (diseño sideboard)

### Tarea 9: Registro Visitante - Login y Dashboard
- [ ] Agregar formulario de login en registro/visitante
- [ ] Crear dashboard del visitor

### Tarea 10: Registro Prestador - Login y Dashboard
- [ ] Agregar formulario de login en registro/prestador
- [ ] Crear dashboard del prestador con CRM, promos, negocios