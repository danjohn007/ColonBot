<?php
/**
 * Punto de entrada principal – Plataforma Turística Colón
 * Enrutador MVC frontal (Front Controller)
 */

define('ROOT_PATH', __DIR__);

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';

// ─── Helpers & base ───────────────────────────────────────────────────────
require_once ROOT_PATH . '/app/helpers.php';
require_once ROOT_PATH . '/app/core/Controller.php';
require_once ROOT_PATH . '/app/core/Model.php';
require_once ROOT_PATH . '/app/core/Router.php';

// ─── Cargar modelos ───────────────────────────────────────────────────────
foreach (glob(ROOT_PATH . '/app/models/*.php') as $model) {
    require_once $model;
}

// ─── Cargar controladores ─────────────────────────────────────────────────
foreach (glob(ROOT_PATH . '/app/controllers/*.php') as $ctrl) {
    require_once $ctrl;
}

// ─── Definir rutas ────────────────────────────────────────────────────────
$router = new Router();

// Públicas
$router->get('',                        'MapController',       'index');
$router->get('mapa',                    'MapController',       'index');
$router->get('mapa/poi',                'MapController',       'poi');
$router->get('lugar/{slug}',            'MapController',       'detail');
$router->post('lugar/{slug}/contactar', 'MapController',       'contact');

// Autenticación
$router->get('login',                   'AuthController',      'loginForm');
$router->post('login',                  'AuthController',      'login');
$router->get('logout',                  'AuthController',      'logout');
$router->get('olvide-contrasena',       'AuthController',      'forgotPasswordForm');
$router->post('olvide-contrasena',      'AuthController',      'forgotPassword');

// Admin de Negocio
$router->get('admin',                   'BusinessController',  'dashboard');
$router->get('admin/negocio',           'BusinessController',  'index');
$router->get('admin/negocio/crear',     'BusinessController',  'create');
$router->post('admin/negocio/crear',    'BusinessController',  'store');
$router->get('admin/negocio/{id}',      'BusinessController',  'edit');
$router->post('admin/negocio/{id}',     'BusinessController',  'update');
$router->post('admin/negocio/{id}/eliminar', 'BusinessController', 'destroy');
$router->post('admin/upload',           'BusinessController',  'upload');

// SuperAdmin
$router->get('superadmin',              'DashboardController', 'index');
$router->get('superadmin/usuarios',     'DashboardController', 'users');
$router->post('superadmin/usuarios/crear',  'DashboardController', 'createUser');
$router->post('superadmin/usuarios/{id}',   'DashboardController', 'updateUser');
$router->post('superadmin/usuarios/{id}/eliminar', 'DashboardController', 'deleteUser');
$router->get('superadmin/negocios',     'DashboardController', 'businesses');
$router->post('superadmin/negocios/{id}/aprobar',  'DashboardController', 'approveBusiness');
$router->post('superadmin/negocios/{id}/rechazar', 'DashboardController', 'rejectBusiness');
$router->post('superadmin/negocios/{id}/eliminar', 'DashboardController', 'deleteBusiness');
$router->get('superadmin/categorias',   'DashboardController', 'categories');
$router->post('superadmin/categorias/crear', 'DashboardController', 'createCategory');
$router->post('superadmin/categorias/{id}/eliminar', 'DashboardController', 'deleteCategory');
$router->get('superadmin/analitica',    'DashboardController', 'analytics');
$router->get('superadmin/bitacora',     'DashboardController', 'actionLog');
$router->get('superadmin/errores',      'DashboardController', 'errorLog');

// Configuraciones globales
$router->get('configuraciones',         'SettingsController',  'index');
$router->post('configuraciones/guardar','SettingsController',  'save');
$router->get('configuraciones/hikvision',    'SettingsController', 'hikvision');
$router->post('configuraciones/hikvision/crear', 'SettingsController', 'createHikvision');
$router->post('configuraciones/hikvision/{id}/eliminar', 'SettingsController', 'deleteHikvision');
$router->get('configuraciones/shelly',       'SettingsController', 'shelly');
$router->post('configuraciones/shelly/crear','SettingsController', 'createShelly');
$router->post('configuraciones/shelly/{id}/eliminar', 'SettingsController', 'deleteShelly');
$router->get('configuraciones/gps',          'SettingsController', 'gps');
$router->post('configuraciones/gps/crear',   'SettingsController', 'createGps');
$router->post('configuraciones/gps/{id}/eliminar', 'SettingsController', 'deleteGps');

// Chatbot – webhook WhatsApp
$router->get('chatbot/webhook',         'ChatbotController',   'verify');
$router->post('chatbot/webhook',        'ChatbotController',   'receive');

// API interna (JSON)
$router->get('api/negocios',            'ApiController',       'businesses');
$router->get('api/negocios/{id}',       'ApiController',       'business');
$router->post('api/analitica',          'ApiController',       'trackEvent');
$router->get('api/hikvision/{id}/stream', 'ApiController',     'hikStream');
$router->get('api/shelly/{id}/estado',  'ApiController',       'shellyStatus');
$router->post('api/shelly/{id}/toggle', 'ApiController',       'shellyToggle');
$router->post('api/gps/actualizar',     'ApiController',       'gpsUpdate');

// Despachar
$router->dispatch();
