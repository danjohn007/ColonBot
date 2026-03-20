<?php
/**
 * API interna JSON
 */
class ApiController extends Controller
{
    private BusinessModel  $businesses;
    private AnalyticsModel $analytics;
    private IotModel       $iot;
    private SettingModel   $settings;

    public function __construct()
    {
        $this->businesses = new BusinessModel();
        $this->analytics  = new AnalyticsModel();
        $this->iot        = new IotModel();
        $this->settings   = new SettingModel();
    }

    public function businesses(): void
    {
        $filters = [
            'category' => $_GET['category'] ?? '',
            'search'   => $_GET['q'] ?? '',
        ];
        $list = $this->businesses->withFilters($filters);
        $this->json($list);
    }

    public function business(string $id): void
    {
        $b = $this->businesses->find((int)$id);
        if (!$b) { $this->json(['error' => 'not found'], 404); }
        $b['images']    = $this->businesses->images((int)$id);
        $b['amenities'] = $this->businesses->amenities((int)$id);
        $b['services']  = $this->businesses->services((int)$id);
        $b['products']  = $this->businesses->products((int)$id);
        $this->json($b);
    }

    public function trackEvent(): void
    {
        $event      = trim($_POST['event']       ?? '');
        $businessId = (int)($_POST['business_id'] ?? 0);
        if (!$event) { $this->json(['error' => 'missing event'], 422); }
        $this->analytics->track($event, $businessId ?: null);
        $this->json(['ok' => true]);
    }

    // ── HikVision ─────────────────────────────────────────────────────────

    public function hikStream(string $id): void
    {
        $this->requireAuth('admin');
        $device = $this->iot->findHikvision((int)$id);
        if (!$device) { $this->json(['error' => 'not found'], 404); }

        // Construir URL RTSP
        $rtsp = $device['stream_url'] ?: sprintf(
            'rtsp://%s:%s@%s:%d/Streaming/Channels/101',
            $device['username'], $device['password'], $device['ip'], $device['port']
        );

        $this->json(['stream_url' => $rtsp, 'device' => $device['name']]);
    }

    // ── Shelly Cloud ──────────────────────────────────────────────────────

    public function shellyStatus(string $id): void
    {
        $this->requireAuth('admin');
        $device = $this->iot->findShelly((int)$id);
        if (!$device) { $this->json(['error' => 'not found'], 404); }

        $url = rtrim($device['server_uri'], '/') . '/device/status';
        $res = $this->shellyRequest($url, $device['auth_key'], ['id' => $device['device_id']]);
        $this->json($res);
    }

    public function shellyToggle(string $id): void
    {
        $this->requireAuth('admin');
        $device = $this->iot->findShelly((int)$id);
        if (!$device) { $this->json(['error' => 'not found'], 404); }

        $channel = (int)($_POST['channel'] ?? 0);
        $turn    = $_POST['turn'] ?? 'toggle';
        $url     = rtrim($device['server_uri'], '/') . '/device/relay/control';
        $res     = $this->shellyRequest($url, $device['auth_key'], [
            'id'      => $device['device_id'],
            'channel' => $channel,
            'turn'    => $turn,
        ]);
        $this->json($res);
    }

    // ── GPS ────────────────────────────────────────────────────────────────

    public function gpsUpdate(): void
    {
        $apiKey = $this->settings->get('gps_api_key');
        $reqKey = $_POST['api_key'] ?? $_SERVER['HTTP_X_API_KEY'] ?? '';

        if ($apiKey && !hash_equals($apiKey, $reqKey)) {
            $this->json(['error' => 'unauthorized'], 401);
        }

        $imei = trim($_POST['imei'] ?? '');
        $lat  = (float)($_POST['lat'] ?? 0);
        $lng  = (float)($_POST['lng'] ?? 0);

        if (!$imei || !$lat || !$lng) {
            $this->json(['error' => 'missing parameters'], 422);
        }

        $this->iot->updateGpsPosition($imei, $lat, $lng);
        $this->json(['ok' => true]);
    }

    // ── Privados ──────────────────────────────────────────────────────────

    private function shellyRequest(string $url, string $authKey, array $data): mixed
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query(array_merge(['auth_key' => $authKey], $data)),
            CURLOPT_TIMEOUT        => 10,
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true) ?? ['raw' => $result];
    }
}
