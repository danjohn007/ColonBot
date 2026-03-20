# 🗺️ Plataforma Turística Interactiva – Municipio de Colón, Querétaro

Sistema digital completo para explorar los lugares turísticos del Municipio de Colón, con mapa interactivo, gestión de negocios, chatbot de WhatsApp e integración con dispositivos IoT.

## 📋 Módulos implementados

| Módulo | Estado |
|--------|--------|
| 🗺️ Mapa Interactivo (Leaflet.js) | ✅ |
| 🔐 Autenticación con sesiones y password_hash() | ✅ |
| 🏢 Gestión de Negocios (Admin Local) | ✅ |
| 🤖 ChatBot WhatsApp (Meta API) | ✅ |
| 📊 Dashboard SuperAdmin + Analítica | ✅ |
| ⚙️ Configuraciones Globales | ✅ |
| 📹 Dispositivos IoT – HikVision | ✅ |
| 💡 Dispositivos IoT – Shelly Cloud | ✅ |
| 📡 GPS Trackers | ✅ |
| 📋 Bitácora de Acciones | ✅ |
| 🚨 Registro de Errores | ✅ |
| 🎨 Colores personalizables | ✅ |
| 📱 Mobile-First / PWA | ✅ |
| 🔗 URLs amigables | ✅ |

## 🚀 Instalación en servidor Apache

### Requisitos

- PHP 7.4 o superior (recomendado PHP 8.1+)
- MySQL 5.7 o superior
- Apache con mod_rewrite habilitado
- Extensiones PHP: pdo, pdo_mysql, mbstring, json, curl, fileinfo

### Paso 1 – Clonar el proyecto

```bash
git clone https://github.com/danjohn007/ColonBot.git /var/www/html/colonbot
```

### Paso 2 – Crear la base de datos

```bash
mysql -u root -p < /var/www/html/colonbot/database/schema.sql
mysql -u root -p colonbot < /var/www/html/colonbot/database/seed.sql
```

### Paso 3 – Configurar credenciales

Edita `config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'colonbot');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
```

### Paso 4 – Habilitar mod_rewrite

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

VirtualHost con AllowOverride All:

```apache
<VirtualHost *:80>
    DocumentRoot /var/www/html/colonbot
    <Directory /var/www/html/colonbot>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Paso 5 – Permisos de uploads

```bash
chmod 755 /var/www/html/colonbot/public/uploads
chown www-data:www-data /var/www/html/colonbot/public/uploads
```

### Paso 6 – Verificar instalación

Abre en el navegador: `http://tu-servidor/colonbot/test_connection.php`

### Paso 7 – Primer acceso

| Rol | Correo | Contraseña |
|-----|--------|------------|
| Super Administrador | superadmin@colonbot.mx | Admin@2024 |
| Admin de Negocio | admin.mirador@colonbot.mx | Admin@2024 |

⚠️ Cambia las contraseñas después del primer acceso. Elimina test_connection.php en producción.

## 📁 Estructura del proyecto

```
colonbot/
├── index.php                   # Front Controller
├── .htaccess                   # URL rewriting
├── test_connection.php         # Diagnóstico
├── config/
│   ├── config.php              # Config + URL Base automática
│   └── database.php            # Conexión PDO Singleton
├── app/
│   ├── helpers.php
│   ├── core/Router.php
│   ├── core/Controller.php
│   ├── core/Model.php
│   ├── controllers/
│   ├── models/
│   └── views/
├── database/
│   ├── schema.sql
│   └── seed.sql
└── public/
    ├── css/app.css
    ├── js/app.js
    ├── manifest.json
    └── uploads/
```

## 🔗 URLs principales

| URL | Descripción |
|-----|-------------|
| `/mapa` | Mapa turístico público |
| `/lugar/{slug}` | Detalle de lugar |
| `/login` | Inicio de sesión |
| `/admin` | Panel Admin Negocio |
| `/superadmin` | Dashboard SuperAdmin |
| `/configuraciones` | Configuraciones globales |
| `/chatbot/webhook` | Webhook WhatsApp |
| `/api/negocios` | API JSON negocios |

## 🛠️ Tecnologías

- PHP 7.4+ sin framework, MVC propio
- MySQL 5.7
- Tailwind CSS
- Leaflet.js (mapa)
- ApexCharts (analítica)
- Meta WhatsApp Business API
- PDO + password_hash()

## 🔒 Seguridad

- Sesiones PHP + bcrypt cost 12
- CSRF tokens en todos los formularios
- Prepared statements PDO
- Headers de seguridad HTTP
- Validación de uploads
