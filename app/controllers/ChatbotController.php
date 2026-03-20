<?php
/**
 * Chatbot WhatsApp – Webhook Meta Platforms
 */
class ChatbotController extends Controller
{
    private BusinessModel $businesses;
    private CategoryModel $categories;
    private SettingModel  $settings;

    public function __construct()
    {
        $this->businesses = new BusinessModel();
        $this->categories = new CategoryModel();
        $this->settings   = new SettingModel();
    }

    /** Verificación del webhook (GET) */
    public function verify(): void
    {
        $verifyToken = $this->settings->get('wa_verify_token', 'colonbot_verify_2024');
        $mode        = $_GET['hub_mode']          ?? '';
        $token       = $_GET['hub_verify_token']  ?? '';
        $challenge   = $_GET['hub_challenge']      ?? '';

        if ($mode === 'subscribe' && hash_equals($verifyToken, $token)) {
            echo $challenge;
            exit;
        }
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }

    /** Recepción de mensajes (POST) */
    public function receive(): void
    {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!$data) {
            http_response_code(200);
            exit;
        }

        try {
            $entry   = $data['entry'][0]   ?? null;
            $changes = $entry['changes'][0] ?? null;
            $value   = $changes['value']    ?? null;
            $msg     = $value['messages'][0] ?? null;

            if (!$msg) {
                http_response_code(200);
                exit;
            }

            $from    = $msg['from'] ?? '';
            $type    = $msg['type'] ?? 'text';
            $text    = '';

            if ($type === 'text') {
                $text = strtolower(trim($msg['text']['body'] ?? ''));
            } elseif ($type === 'interactive') {
                $text = strtolower(trim(
                    $msg['interactive']['button_reply']['id'] ??
                    $msg['interactive']['list_reply']['id'] ?? ''
                ));
            }

            $response = $this->buildResponse($from, $text);
            $this->sendMessage($from, $response);

        } catch (Throwable $e) {
            logError($e->getMessage(), $e->getFile(), $e->getLine());
        }

        http_response_code(200);
        exit;
    }

    // ── Lógica de respuestas ──────────────────────────────────────────────

    private function buildResponse(string $from, string $text): array
    {
        // Menú principal
        if ($text === '' || in_array($text, ['hola', 'inicio', 'menu', 'menú', '0'], true)) {
            return $this->menuPrincipal();
        }

        // Submenús por palabra clave
        if (str_contains($text, 'restaurante') || $text === 'cat_restaurantes') {
            return $this->listByCategory('restaurantes', '🍽️ Restaurantes en Colón');
        }
        if (str_contains($text, 'hotel') || str_contains($text, 'hosped') || $text === 'cat_hoteles') {
            return $this->listByCategory('hoteles', '🏨 Hospedaje en Colón');
        }
        if (str_contains($text, 'viñedo') || str_contains($text, 'vino') || $text === 'cat_vinedos') {
            return $this->listByCategory('vinedos', '🍷 Viñedos en Colón');
        }
        if (str_contains($text, 'histori') || str_contains($text, 'monumento') || $text === 'cat_historicos') {
            return $this->listByCategory('historicos', '🏛️ Sitios Históricos');
        }
        if (str_contains($text, 'experiencia') || $text === 'cat_experiencias') {
            return $this->listByCategory('experiencias', '⭐ Experiencias Turísticas');
        }

        // Default
        return $this->menuPrincipal();
    }

    private function menuPrincipal(): array
    {
        return [
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => [
                    'text' => "¡Hola! 👋 Bienvenido a la *Plataforma Turística de Colón, Querétaro*.\n\n¿Qué deseas explorar?"
                ],
                'action' => [
                    'buttons' => [
                        ['type' => 'reply', 'reply' => ['id' => 'cat_restaurantes',  'title' => '🍽️ Restaurantes']],
                        ['type' => 'reply', 'reply' => ['id' => 'cat_hoteles',       'title' => '🏨 Hospedaje']],
                        ['type' => 'reply', 'reply' => ['id' => 'cat_experiencias',  'title' => '⭐ Experiencias']],
                    ]
                ]
            ]
        ];
    }

    private function listByCategory(string $catSlug, string $title): array
    {
        $businesses = $this->businesses->withFilters(['category' => $catSlug]);

        if (empty($businesses)) {
            return ['type' => 'text', 'text' => ['body' => "No encontré resultados para esa categoría. Escribe *menú* para regresar."]];
        }

        $lines = ["*{$title}*\n"];
        foreach (array_slice($businesses, 0, 5) as $b) {
            $wa = $b['whatsapp'] ? "\n   ↪ " . waLink($b['whatsapp'], 'Hola, vi tu perfil en Colón Turismo') : '';
            $lines[] = "📍 *{$b['name']}*\n   {$b['address']}{$wa}";
        }
        $lines[] = "\nEscribe *menú* para regresar al inicio.";

        return ['type' => 'text', 'text' => ['body' => implode("\n\n", $lines)]];
    }

    // ── Envío de mensajes via API de Meta ─────────────────────────────────

    private function sendMessage(string $to, array $payload): void
    {
        $token   = $this->settings->get('wa_token');
        $phoneId = $this->settings->get('wa_phone_id');

        if (!$token || !$phoneId) {
            error_log('WhatsApp API not configured.');
            return;
        }

        $body = array_merge(['messaging_product' => 'whatsapp', 'to' => $to], $payload);
        $waVersion = $this->settings->get('wa_api_version', 'v19.0');
        $url  = "https://graph.facebook.com/{$waVersion}/{$phoneId}/messages";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
            CURLOPT_TIMEOUT        => 15,
        ]);
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 400) {
            logError('WhatsApp API error: ' . $response, __FILE__, __LINE__, 'warning');
        }
    }
}
