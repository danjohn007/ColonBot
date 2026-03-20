<?php
class SettingsController extends Controller
{
    private SettingModel $settings;
    private IotModel     $iot;

    public function __construct()
    {
        $this->settings = new SettingModel();
        $this->iot      = new IotModel();
    }

    public function index(): void
    {
        $this->requireAuth('superadmin');
        $groups = $this->settings->allGrouped();
        $this->view('settings.index', compact('groups') + ['csrf' => $this->csrf()]);
    }

    public function save(): void
    {
        $this->requireAuth('superadmin');
        $this->verifyCsrf();

        $group = $_POST['group'] ?? 'general';
        unset($_POST['_csrf'], $_POST['group']);

        $this->settings->saveMany($_POST, $group);
        $this->logAction('update_settings', 'settings', 0, $group);
        $this->flash('success', 'Configuración guardada.');
        $this->redirect('configuraciones');
    }

    // ── HikVision ─────────────────────────────────────────────────────────

    public function hikvision(): void
    {
        $this->requireAuth('superadmin');
        $devices = $this->iot->allHikvision();
        $this->view('settings.hikvision', compact('devices') + ['csrf' => $this->csrf()]);
    }

    public function createHikvision(): void
    {
        $this->requireAuth('superadmin');
        $this->verifyCsrf();

        $this->iot->createHikvision([
            'name'       => trim($_POST['name'] ?? ''),
            'ip'         => trim($_POST['ip'] ?? ''),
            'port'       => (int)($_POST['port'] ?? 80),
            'username'   => trim($_POST['username'] ?? 'admin'),
            'password'   => trim($_POST['password'] ?? ''),
            'stream_url' => trim($_POST['stream_url'] ?? ''),
            'type'       => $_POST['type'] ?? 'camera',
            'location'   => trim($_POST['location'] ?? ''),
        ]);
        $this->logAction('create_hikvision_device');
        $this->flash('success', 'Dispositivo HikVision agregado.');
        $this->redirect('configuraciones/hikvision');
    }

    public function deleteHikvision(string $id): void
    {
        $this->requireAuth('superadmin');
        $this->verifyCsrf();
        $this->iot->deleteHikvision((int)$id);
        $this->logAction('delete_hikvision_device', 'iot_hikvision', (int)$id);
        $this->flash('success', 'Dispositivo eliminado.');
        $this->redirect('configuraciones/hikvision');
    }

    // ── Shelly ────────────────────────────────────────────────────────────

    public function shelly(): void
    {
        $this->requireAuth('superadmin');
        $devices = $this->iot->allShelly();
        $this->view('settings.shelly', compact('devices') + ['csrf' => $this->csrf()]);
    }

    public function createShelly(): void
    {
        $this->requireAuth('superadmin');
        $this->verifyCsrf();

        $this->iot->createShelly([
            'name'       => trim($_POST['name'] ?? ''),
            'device_id'  => trim($_POST['device_id'] ?? ''),
            'auth_key'   => trim($_POST['auth_key'] ?? ''),
            'server_uri' => trim($_POST['server_uri'] ?? 'https://shelly-41-eu.shelly.cloud'),
            'type'       => trim($_POST['type'] ?? 'relay'),
            'location'   => trim($_POST['location'] ?? ''),
        ]);
        $this->logAction('create_shelly_device');
        $this->flash('success', 'Dispositivo Shelly agregado.');
        $this->redirect('configuraciones/shelly');
    }

    public function deleteShelly(string $id): void
    {
        $this->requireAuth('superadmin');
        $this->verifyCsrf();
        $this->iot->deleteShelly((int)$id);
        $this->logAction('delete_shelly_device', 'iot_shelly', (int)$id);
        $this->flash('success', 'Dispositivo Shelly eliminado.');
        $this->redirect('configuraciones/shelly');
    }

    // ── GPS ────────────────────────────────────────────────────────────────

    public function gps(): void
    {
        $this->requireAuth('superadmin');
        $trackers = $this->iot->allGps();
        $this->view('settings.gps', compact('trackers') + ['csrf' => $this->csrf()]);
    }

    public function createGps(): void
    {
        $this->requireAuth('superadmin');
        $this->verifyCsrf();

        $this->iot->createGps([
            'name'     => trim($_POST['name'] ?? ''),
            'imei'     => trim($_POST['imei'] ?? ''),
            'api_key'  => trim($_POST['api_key'] ?? ''),
            'provider' => trim($_POST['provider'] ?? ''),
        ]);
        $this->logAction('create_gps_tracker');
        $this->flash('success', 'GPS Tracker agregado.');
        $this->redirect('configuraciones/gps');
    }

    public function deleteGps(string $id): void
    {
        $this->requireAuth('superadmin');
        $this->verifyCsrf();
        $this->iot->deleteGps((int)$id);
        $this->logAction('delete_gps_tracker', 'gps_trackers', (int)$id);
        $this->flash('success', 'GPS Tracker eliminado.');
        $this->redirect('configuraciones/gps');
    }
}
