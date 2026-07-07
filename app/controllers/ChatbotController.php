<?php
/**
 * Chatbot WhatsApp – Webhook Meta Platforms
 */
class ChatbotController extends Controller
{
    private BusinessModel $businesses;
    private CategoryModel $categories;
    private SettingModel  $settings;
    private ConsultaModel $consultas;
    private ContactModel  $contacts;

    public function __construct()
    {
        $this->businesses = new BusinessModel();
        $this->categories = new CategoryModel();
        $this->settings   = new SettingModel();
        $this->consultas  = new ConsultaModel();
        $this->contacts   = new ContactModel();
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

        // Categorías
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
        if (str_contains($text, 'emergencia') || str_contains($text, 'urgencia') || str_contains($text, 'policia') || str_contains($text, 'bombero') || str_contains($text, 'ambulancia') || $text === 'emergencias') {
            return $this->emergencyNumbers();
        }

        // Ver negocio específico: formato "ver_N" donde N es el business_id
        if (preg_match('/^ver_(\d+)$/', $text, $matches)) {
            $businessId = (int)$matches[1];
            return $this->showBusinessDetail($from, $businessId);
        }

        // Reservar en negocio: formato "reservar_N" donde N es el business_id
        if (preg_match('/^reservar_(\d+)$/', $text, $matches)) {
            $businessId = (int)$matches[1];
            return $this->reserveBusiness($from, $businessId);
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
        // Only show published AND open businesses in chatbot
        $businesses = $this->businesses->publishedForChatbot();
        // Filter by category
        if ($catSlug) {
            $businesses = array_filter($businesses, function($b) use ($catSlug) {
                return ($b['category_slug'] ?? '') === $catSlug;
            });
        }

        if (empty($businesses)) {
            return ['type' => 'text', 'text' => ['body' => "No encontré resultados para esa categoría.\n\n🗺️ Explora el mapa interactivo: " . url('mapa') . "\n\nEscribe *menú* para regresar."]];
        }

        // Show first 5 businesses as interactive buttons so users can click to see details
        $firstBatch = array_slice($businesses, 0, 10);
        
        $lines = ["*{$title}*\nSelecciona un lugar para ver más detalles:\n"];
        $buttons = [];
        
        foreach ($firstBatch as $idx => $b) {
            $btnId = 'ver_' . $b['id'];
            $btnTitle = mb_substr($b['name'], 0, 20);
            $buttons[] = ['type' => 'reply', 'reply' => ['id' => $btnId, 'title' => $btnTitle]];
            
            // Also add to text list with map link as fallback
            $mapLink = url('mapa/' . $b['id']);
            $lines[] = "\n📍 *{$b['name']}*";
            $lines[] = "   🗺️ " . $mapLink;
        }
        $lines[] = "\nEscribe *menú* para regresar al inicio.";

        // If we have buttons, send interactive message; otherwise text
        if (!empty($buttons)) {
            // Split into groups of 3 (Meta's max buttons per group)
            $buttonGroups = array_chunk($buttons, 3);
            $firstGroup = $buttonGroups[0] ?? [];
            
            // Store the rest in session for later
            $_SESSION['chatbot_pending_' . $from] = array_slice($buttonGroups, 1);
            
            return [
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => ['text' => implode("\n", $lines)],
                    'action' => ['buttons' => $firstGroup]
                ]
            ];
        }
        
        return ['type' => 'text', 'text' => ['body' => implode("\n", $lines)]];
    }

    /**
     * Muestra el detalle de un negocio y REGISTRA la consulta como 'solicitar_informacion'
     */
    private function showBusinessDetail(string $from, int $businessId): array
    {
        $business = $this->businesses->find($businessId);
        if (!$business) {
            return ['type' => 'text', 'text' => ['body' => "No encontré ese lugar.\n\nEscribe *menú* para regresar."]];
        }

        // ✅ REGISTRAR ACCIÓN: Solicitar información del negocio
        $this->registrarSolicitudInfo($from, $businessId);

        $whatsapp = $business['whatsapp'] ?: $business['phone'];
        $mapLink = url('mapa/' . $business['id']);
        $waLink = $whatsapp ? 'https://wa.me/' . preg_replace('/\D/', '', $whatsapp) : null;

        $lines = [
            "*📍 {$business['name']}*\n",
            "📝 *Descripción:*",
            ($business['description'] ? mb_substr($business['description'], 0, 300) : 'Sin descripción') . "\n",
            "📞 *Teléfono:* " . ($business['phone'] ?: 'No disponible'),
            "🏠 *Dirección:* " . ($business['address'] ?: 'No disponible') . "\n",
            "🗺️ Ver en el mapa: {$mapLink}",
        ];

        // Add WhatsApp link if available
        if ($waLink) {
            $lines[] = "💬 WhatsApp: {$waLink}";
        }

        $lines[] = "\nElige una opción:";
        $buttons = [];

        if ($whatsapp) {
            $buttons[] = ['type' => 'reply', 'reply' => ['id' => 'reservar_' . $businessId, 'title' => '📅 Reservar']];
        }
        $buttons[] = ['type' => 'reply', 'reply' => ['id' => 'menu', 'title' => '🔙 Menú']];

        return [
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => ['text' => implode("\n", $lines)],
                'action' => ['buttons' => $buttons]
            ]
        ];
    }

    /**
     * Procesa una reservación y REGISTRA la consulta como 'compra_reservacion'
     */
    private function reserveBusiness(string $from, int $businessId): array
    {
        $business = $this->businesses->find($businessId);
        if (!$business) {
            return ['type' => 'text', 'text' => ['body' => "No encontré ese lugar.\n\nEscribe *menú* para regresar."]];
        }

        $whatsapp = $business['whatsapp'] ?: $business['phone'];
        if (!$whatsapp) {
            return ['type' => 'text', 'text' => ['body' => "Este negocio no tiene WhatsApp configurado.\n\nEscribe *menú* para regresar."]];
        }

        // ✅ REGISTRAR ACCIÓN: Compra/Reservación en el negocio
        $this->registrarCompraReservacion($from, $businessId);

        $waLink = 'https://wa.me/' . preg_replace('/\D/', '', $whatsapp) . '?text=' . urlencode('Hola, me gustaría hacer una reservación en su negocio.');

        return [
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => [
                    'text' => "*✅ Reservación iniciada en {$business['name']}*\n\nHaz clic en el botón de abajo para comunicarte directamente con el negocio por WhatsApp.\n\nO escribe *menú* para regresar."
                ],
                'action' => [
                    'buttons' => [
                        ['type' => 'reply', 'reply' => ['id' => 'menu', 'title' => '🔙 Menú']],
                    ]
                ]
            ]
        ];
    }

    /**
     * Registrar en la tabla 'consultas' cuando un usuario solicita información
     * de un negocio específico a través del chatbot.
     */
    private function registrarSolicitudInfo(string $from, int $businessId): void
    {
        try {
            $this->consultas->registrarSolicitudInfo($from, $businessId);
            error_log("✅ CONSULTA REGISTRADA: SolicitudInfo - wa_id={$from}, business_id={$businessId}");
        } catch (Throwable $e) {
            logError('Error al registrar consulta: ' . $e->getMessage(), __FILE__, __LINE__);
        }
    }

    /**
     * Registrar en la tabla 'consultas' cuando un usuario realiza una
     * compra/reservación en un negocio a través del chatbot.
     */
    private function registrarCompraReservacion(string $from, int $businessId): void
    {
        try {
            $this->consultas->registrarCompraReservacion($from, $businessId);
            error_log("✅ CONSULTA REGISTRADA: CompraReservacion - wa_id={$from}, business_id={$businessId}");
        } catch (Throwable $e) {
            logError('Error al registrar consulta: ' . $e->getMessage(), __FILE__, __LINE__);
        }
    }

    private function emergencyNumbers(): array
    {
        $lines = [
            "*🆘 Números de Emergencia - Colón*\n",
            "🚨 *Emergencias* (Policía, bomberos, ambulancia, protección civil)",
            "   📞 911\n",
            "🤫 *Denuncia Anónima*",
            "   📞 089\n",
            "🛡️ *Protección Civil Colón*",
            "   📞 419 292 0296\n",
            "🏛️ *Presidencia Municipal de Colón*",
            "   📞 419 292 0061\n",
            "Escribe *menú* para regresar al inicio."
        ];

        return ['type' => 'text', 'text' => ['body' => implode("\n", $lines)]];
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