# 🧪 GUÍA DE TESTING - CAMBIOS COLONBOT

**Documento:** Verificación de cambios realizados  
**Fecha:** 6 de Julio, 2026

## 📋 CHECKLIST DE TESTING

### 1️⃣ REGISTRO Y AUTENTICACIÓN

#### Test: Registro Visitante Limpio
- [ ] Navega a `/registro-visitante`
- [ ] Verifica que **NO aparezca** el navbar lateral
- [ ] Verifica que solo veas el formulario de registro
- [ ] Completa el registro con:
  - Nombre: "Juan García"
  - Email: "juan.garcia@test.com"
  - Teléfono: "123456789"
  - Contraseña: "Test123456!"
  - Confirmar: "Test123456!"
- [ ] Haz clic en "Registrar"
- [ ] **Esperado:** Redirect a `/turista` (dashboard visitante)
- **Estado:** ✅ Visitante debe ver dashboard con top negocios y promociones

#### Test: Registro Prestador Limpio
- [ ] Navega a `/registro-prestador`
- [ ] Verifica que **NO aparezca** el navbar lateral
- [ ] Verifica que solo veas el formulario de registro
- [ ] Completa el registro con:
  - Nombre negocio: "Restaurante El Sabor"
  - Email: "sabor@test.com"
  - WhatsApp: "+5214419876543"
  - Categoría: "Restaurante"
  - Contraseña: "Test123456!"
- [ ] Haz clic en "Registrar"
- [ ] **Esperado:** Redirect a `/admin/crm` (CRM del prestador)
- **Estado:** ✅ Prestador debe ver panel de CRM

#### Test: Login Visitante
- [ ] Navega a `/iniciar-sesion`
- [ ] Ingresa: `juan.garcia@test.com` / `Test123456!`
- [ ] **Esperado:** Redirect a `/turista` (dashboard visitante)
- [ ] Verifica que veas:
  - Top 10 negocios más visitados
  - Promociones activas
  - Botón "Calificar" en cada negocio
  
#### Test: Login Prestador
- [ ] Navega a `/iniciar-sesion`
- [ ] Ingresa: `sabor@test.com` / `Test123456!`
- [ ] **Esperado:** Redirect a `/admin/crm`
- [ ] Verifica que veas:
  - Panel de CRM
  - Contactos por categoría
  - Opción de crear evento

### 2️⃣ CORRECCIONES DE RUTAS

#### Test: Notificaciones Accesibles
- [ ] Login como visitante
- [ ] Navega a `/admin/notificaciones`
- [ ] **Esperado:** Página carga sin error HTTP 500
- [ ] Verifica que veas notificaciones (si las hay)
- **Nota:** Antes fallaba porque requireAuth era too restrictive

#### Test: Eventos Accesibles
- [ ] Login como colaborador_admin
- [ ] Navega a `/admin/eventos`
- [ ] **Esperado:** Página carga sin error HTTP 500
- [ ] Verifica que veas listado de eventos
- **Nota:** Antes fallaba porque solo aceptaba 'prestador'

### 3️⃣ INTERFAZ MAPA PÚBLICO

#### Test: Botón de Reserva (en `/mapa`)
- [ ] Abre la página `/mapa` sin login
- [ ] Busca un negocio en el mapa (ej: restaurante)
- [ ] Verifica el popup del negocio
- [ ] **Busca el botón** que **debería decir:** "🛒 Reservar por Whatsapp"
- [ ] **NO debe haber** un botón verde separado que diga "🟢 WhatsApp"
- [ ] Haz clic en "Reservar por Whatsapp"
- [ ] **Esperado:** Se abre WhatsApp con mensaje personalizado
- **Color esperado del botón:** Púrpura/Blue (similar a otros botones de CTA)

#### Test: Sin Duplicación de Botones
- [ ] En el popup del lugar verifica:
  - [ ] "Ver detalle" (botón azul)
  - [ ] "Reservar por Whatsapp" (botón púrpura) ← Único botón de WhatsApp
  - [ ] "Cómo llegar" (botón naranja)
- [ ] **NO debe haber** segundo botón verde de "Whatsapp"

### 4️⃣ PROMOCIONES PÚBLICAS

#### Test: Vista de Promoción (/promocion/{id})
- [ ] Login como prestador
- [ ] Crea una promoción con:
  - Nombre: "Descuento Verano"
  - Precio regular: $500
  - Precio promocional: $299
  - Vigencia: 01/07/2026 - 31/07/2026
  - Condiciones: "Válido para grupos de 5+ personas"
- [ ] Haz clic en "Ver publicación"
- [ ] **Esperado en la vista pública:**
  - [ ] **Sección "PRECIO DE LISTA":** $500 (tachado, color gris)
  - [ ] **Sección "PRECIO PROMOCIONAL":** $299 (verde, destacado)
  - [ ] **Sección "Vigencia":** 
    - Inicia: 01/07/2026
    - Finaliza: 31/07/2026
  - [ ] **Sección "Condiciones":** Válido para grupos...
  - [ ] Botón de contacto/consulta

#### Test: Información Completa
- [ ] Verifica que la promoción muestre:
  - [ ] Imagen principal
  - [ ] Título
  - [ ] Descripción
  - [ ] Precios comparativos
  - [ ] Vigencia
  - [ ] Condiciones
  - [ ] Formulario de contacto
  - [ ] Contador de vistas

### 5️⃣ DETALLE DEL LUGAR

#### Test: Botón "En línea" (/lugar/{slug})
- [ ] Login como prestador propietario del negocio
- [ ] Navega a `/lugar/nombre-negocio`
- [ ] Busca el **botón verde** que dice: "🟢 En línea"
- [ ] Verifica el texto: "(Visible en CristobalBot)"
- [ ] Haz clic en el botón
- [ ] **Esperado:** El botón cambia a "🔴 Fuera de línea" (gris)
- [ ] Vuelve a hacer clic
- [ ] **Esperado:** Vuelve a "🟢 En línea" (verde)
- [ ] Verifica en CristobalBot que el negocio aparezca/desaparezca

#### Test: Toda la Información
- [ ] En la página de detalle del lugar verifica:
  - [ ] Galería de imágenes
  - [ ] Nombre y categoría del lugar
  - [ ] Calificación en estrellas
  - [ ] Descripción
  - [ ] Dirección con ícono
  - [ ] Horarios de servicio
  - [ ] Sección "Amenidades" con iconos
  - [ ] Sección "Servicios" con precios
  - [ ] Sección "Productos" con imágenes
  - [ ] Sección "Eventos" vigentes
  - [ ] Sección "Calificaciones y Comentarios"
  - [ ] Botón "Contactar por WhatsApp"
  - [ ] Botón "Cómo llegar"
  - [ ] Mini mapa de ubicación
  - [ ] **NUEVO:** Botón "En línea/Fuera de línea"

#### Test: Comentar y Calificar
- [ ] En la sección de valoraciones:
  - [ ] Ingresa tu nombre
  - [ ] Selecciona 5 estrellas
  - [ ] Escribe un comentario: "Excelente experiencia"
  - [ ] Haz clic en "Enviar valoración"
- [ ] **Esperado:** 
  - [ ] Mensaje de éxito
  - [ ] Página se recarga
  - [ ] Tu comentario aparece en la lista

### 6️⃣ TESTING EN MÓVIL

#### Test: Responsividad (iPhone 12 Pro)
- [ ] Abre DevTools (F12)
- [ ] Cambia a Device Toolbar (Ctrl+Shift+M)
- [ ] Selecciona "iPhone 12 Pro" (390x844)
- [ ] Navega a `/mapa`
- [ ] Verifica:
  - [ ] Mapa es responsive
  - [ ] Popup se ve correctamente
  - [ ] Botón "Reservar por Whatsapp" es clickeable
  - [ ] No hay overflow horizontal
  
#### Test: Responsividad Promoción (iPhone)
- [ ] Navega a una promoción (/promocion/{id})
- [ ] Verifica:
  - [ ] Precios se muestran lado a lado (grid 2 cols)
  - [ ] Texto es legible
  - [ ] Botones son grandes (tap-friendly)

#### Test: Responsividad Detalle (iPad)
- [ ] En DevTools, selecciona "iPad" (768x1024)
- [ ] Navega a `/lugar/nombre`
- [ ] Verifica:
  - [ ] Sidebar de contacto está sticky
  - [ ] Contenido se adapta bien
  - [ ] Imágenes son responsive

### 7️⃣ FLUJOS DE USUARIO COMPLETOS

#### Flujo: Visitante Completo
```
1. Registro visitante (sin navbar) ✓
2. Login → Dashboard /turista ✓
3. Ver mapa (/mapa) ✓
4. Hacer clic en lugar
5. Ver detalle (/lugar/slug) ✓
6. Calificar negocio ✓
7. Contactar por WhatsApp ✓
8. Ver promotiones ✓
9. Consultar promoción (/promocion/{id}) ✓
```

#### Flujo: Prestador Completo
```
1. Registro prestador (sin navbar) ✓
2. Login → CRM (/admin/crm) ✓
3. Crear evento (/admin/eventos/crear)
4. Crear promoción (/admin/promociones/crear)
5. Ver detalle de su negocio (/lugar/slug) ✓
6. Cambiar estado "En línea" ✓
```

#### Flujo: Colaborador Admin
```
1. Login como colaborador_admin ✓
2. Navegar a notificaciones (/admin/notificaciones) ✓
3. Navegar a eventos (/admin/eventos) ✓
4. Ver métricas (/admin/metricas)
5. Aprobar evento
```

## 📊 MATRIZ DE VALIDACIÓN

| Cambio | Archivo | Validación | Status |
|--------|---------|-----------|--------|
| Navbar registro visitante | register_visitor.php | No aparece navbar | ⏳ |
| Navbar registro prestador | register_prestador.php | No aparece navbar | ⏳ |
| Login visitante → /turista | PublicRegisterController.php | Redirect correcto | ⏳ |
| Login prestador → /admin/crm | PublicRegisterController.php | Redirect correcto | ⏳ |
| Notificaciones accesibles | NotificationController.php | Sin HTTP 500 | ⏳ |
| Eventos accesibles (colaborador) | EventController.php | Sin HTTP 500 | ⏳ |
| Botón "Reservar por Whatsapp" | map/index.php | Texto correcto | ⏳ |
| Botón verde WhatsApp removido | map/index.php | No aparece | ⏳ |
| Precios promocionales | promotions/public_view.php | Dos secciones visibles | ⏳ |
| Vigencia promoción | promotions/public_view.php | Fechas visibles | ⏳ |
| Botón "En línea" | map/detail.php | Funcional | ⏳ |
| Detalles completos lugar | map/detail.php | Todas secciones | ⏳ |

## 🔍 DEBUGGING

### Si hay error en registros:
```bash
1. Abre DevTools (F12)
2. Abre Console (Ctrl+Shift+K)
3. Busca errores en rojo
4. Copia el error y revisa el archivo relacionado
```

### Si registro no redirige:
```bash
1. Verifica PublicRegisterController.php líneas 65-170
2. Verifica que url('turista') y url('admin/crm') existan en index.php
3. Limpia cache del navegador (Ctrl+Shift+Del)
```

### Si botón WhatsApp no funciona:
```bash
1. Verifica que $CHATBOT_WA_NUMBER esté configurado en config.php
2. Verifica que el número sea válido (ej: 5214419876543)
3. Abre DevTools → Network y ve qué URL se abre
```

## ✅ LISTA FINAL

Antes de dar por completado, verifica:

- [ ] Todos los tests de "Checklist de Testing" realizados
- [ ] No hay errores en Console del navegador
- [ ] No hay errores HTTP 500
- [ ] Los redirects funcionan
- [ ] Las vistas son responsive en móvil
- [ ] Los botones son clickeables
- [ ] Las transacciones de datos funcionan

---

**Generado por:** AI Assistant  
**Documento:** Guía de Testing Completa  
**Última actualización:** 6/7/2026

