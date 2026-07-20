<?php
class PromotionController extends Controller
{
    private PromotionModel $promotions;
    private BusinessModel $businesses;
    private ContactModel $contacts;
    private NotificationModel $notifications;
    private UserModel $users;

    public function __construct()
    {
        $this->promotions = new PromotionModel();
        $this->businesses = new BusinessModel();
        $this->contacts = new ContactModel();
        $this->notifications = new NotificationModel();
        $this->users = new UserModel();
    }

    public function index(): void
    {
        $this->requireAuth('prestador');
        $user = currentUser();
        $businesses = $user['role'] === 'superadmin'
            ? $this->businesses->allWithCategory()
            : $this->businesses->byUser((int)$user['id']);

        // Filtrar por negocio específico si se pasa business_id en la URL
        $filterBusinessId = (int)($_GET['business_id'] ?? 0);
        
        $promotions = [];
        if ($filterBusinessId > 0) {
            // Only show promotions for that specific business
            $promotions = $this->promotions->byBusiness($filterBusinessId);
            // Filter businesses to only show the selected one
            $businesses = array_filter($businesses, fn($b) => (int)$b['id'] === $filterBusinessId);
        } else {
            foreach ($businesses as $b) {
                $promotions = array_merge($promotions, $this->promotions->byBusiness((int)$b['id']));
            }
        }

        $this->view('promotions.index', compact('promotions', 'businesses', 'user') + ['csrf' => $this->csrf()]);
    }

    public function list(string $businessId): void
    {
        $this->requireAuth('prestador');
        $business = $this->businesses->find((int)$businessId);
        if (!$business) { $this->json(['error' => 'not found'], 404); }

        $promotions = $this->promotions->byBusiness((int)$businessId);
        $this->json($promotions);
    }

    public function create(): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $businessId = (int)($_POST['business_id'] ?? 0);
        
        // Admin users cannot create public events (without business_id)
        $user = currentUser();
        if ($businessId <= 0 && $user['role'] === 'admin') {
            $this->json(['error' => 'No tienes permiso para crear eventos públicos'], 403);
        }
        
        if ($businessId > 0) {
            $business = $this->businesses->find($businessId);
            if (!$business) { $this->json(['error' => 'not found'], 404); }
            $this->ownerOrAdmin($business);
        }

        $title = trim($_POST['title'] ?? '');
        if (!$title) { $this->json(['error' => 'El título es requerido'], 422); }

        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = $this->saveUpload($_FILES['image']);
        }

        $id = $this->promotions->insert([
            'business_id' => $businessId > 0 ? $businessId : null,
            'user_id' => currentUser()['id'],
            'title' => $title,
            'description' => trim($_POST['description'] ?? ''),
            'image' => $image ?: null,
            'price' => $_POST['price'] !== '' ? (float)$_POST['price'] : null,
            'presale_price' => $_POST['presale_price'] !== '' ? (float)$_POST['presale_price'] : null,
            'conditions' => trim($_POST['conditions'] ?? ''),
            'public_url' => null,
            'type' => in_array($_POST['type'] ?? '', ['promocion', 'evento']) ? $_POST['type'] : 'promocion',
            'target_segment' => implode(',', $_POST['target_segment'] ?? ['todos']),
            'status' => 'pending',
            'start_date' => $_POST['start_date'] ?? null,
            'end_date' => $_POST['end_date'] ?? null,
            'presale_start' => $_POST['presale_start'] ?? null,
            'presale_end' => $_POST['presale_end'] ?? null,
        ]);

        // Update public_url with the actual ID
        $this->promotions->update($id, ['public_url' => BASE_URL . '/promocion/' . $id]);

        $this->logAction('create_promotion', 'promotions', $id, $title);
        $this->json(['ok' => true, 'id' => $id]);
    }

    public function update(string $id): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $promo = $this->promotions->find((int)$id);
        if (!$promo) { $this->json(['error' => 'not found'], 404); }

        if ($promo['business_id']) {
            $business = $this->businesses->find($promo['business_id']);
            $this->ownerOrAdmin($business);
        }

        $data = [
            'title' => trim($_POST['title'] ?? $promo['title']),
            'description' => trim($_POST['description'] ?? ''),
            'price' => $_POST['price'] !== '' ? (float)$_POST['price'] : null,
            'presale_price' => $_POST['presale_price'] !== '' ? (float)$_POST['presale_price'] : null,
            'conditions' => trim($_POST['conditions'] ?? ''),
            'public_url' => trim($_POST['public_url'] ?? ''),
            'type' => in_array($_POST['type'] ?? $promo['type'], ['promocion', 'evento']) ? $_POST['type'] : $promo['type'],
            'target_segment' => implode(',', $_POST['target_segment'] ?? explode(',', $promo['target_segment'])),
            'start_date' => $_POST['start_date'] ?? $promo['start_date'],
            'end_date' => $_POST['end_date'] ?? $promo['end_date'],
            'presale_start' => $_POST['presale_start'] ?? $promo['presale_start'],
            'presale_end' => $_POST['presale_end'] ?? $promo['presale_end'],
        ];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = $this->saveUpload($_FILES['image']);
            if ($image) $data['image'] = $image;
        }

        // If changing back to draft/pending
        if (isset($_POST['status'])) {
            $data['status'] = in_array($_POST['status'], ['pending', 'active', 'inactive', 'expired']) ? $_POST['status'] : $promo['status'];
        }

        $this->promotions->update((int)$id, $data);
        $this->logAction('update_promotion', 'promotions', (int)$id, $data['title']);
        $this->json(['ok' => true]);
    }

    public function toggleStatus(string $id): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $promo = $this->promotions->find((int)$id);
        if (!$promo) { $this->json(['error' => 'not found'], 404); }

        if ($promo['business_id']) {
            $business = $this->businesses->find($promo['business_id']);
            $this->ownerOrAdmin($business);
        }

        $newStatus = $_POST['status'] ?? '';
        if (!in_array($newStatus, ['pending', 'active', 'inactive', 'expired'])) {
            $this->json(['error' => 'Invalid status'], 422);
        }

        $this->promotions->update((int)$id, ['status' => $newStatus]);
        if ($newStatus === 'active') {
            $this->notifyVisitorsForPromotion((int)$id, $promo['title'], $promo['business_id'] ? (int)$promo['business_id'] : null);
        }
        $this->json(['ok' => true]);
    }

    public function send(string $id): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $promo = $this->promotions->find((int)$id);
        if (!$promo) { $this->json(['error' => 'not found'], 404); }

        if ($promo['business_id']) {
            $business = $this->businesses->find($promo['business_id']);
            $this->ownerOrAdmin($business);
        }

        $via = $_POST['via'] ?? 'whatsapp';

        // Build the promotion message
        $promoInfo = "{$promo['title']}";
        if ($promo['description']) {
            $promoInfo .= "\n{$promo['description']}";
        }
        if ($promo['price']) {
            $promoInfo .= "\n💰 Precio: \${$promo['price']}";
        }
        if ($promo['presale_price']) {
            $promoInfo .= "\n🏷️ Precio promocional: \${$promo['presale_price']}";
        }
        if ($promo['conditions']) {
            $promoInfo .= "\n📋 Condiciones: {$promo['conditions']}";
        }
        if ($promo['public_url']) {
            $promoInfo .= "\n\n🔗 Más información: {$promo['public_url']}";
        }
        if ($promo['start_date']) {
            $promoInfo .= "\n📅 Inicio: " . date('d/m/Y', strtotime($promo['start_date']));
        }
        if ($promo['end_date']) {
            $promoInfo .= "\n⏰ Fin: " . date('d/m/Y', strtotime($promo['end_date']));
        }

        $messageText = "Te informamos sobre nuestra gran promoción:\n\n{$promoInfo}";

        // Log the general send
        $this->promotions->logSend((int)$id, null, $via);

        // Get target contacts with chatbot_sessions wa_id
        $targets = $this->promotions->getTargetContacts((int)$id);
        $sent = 0;
        $errors = 0;

        foreach ($targets as $target) {
            // Use session_wa_id from chatbot_sessions if available, otherwise use contact wa_id or phone
            $waId = $target['session_wa_id'] ?: ($target['wa_id'] ?: $target['phone']);
            if (!$waId) continue;

            $success = $this->sendWhatsAppMessage($waId, $messageText);
            if ($success) {
                $this->promotions->logSend((int)$id, (int)$target['id'], $via);
                $sent++;
            } else {
                $errors++;
            }
        }

        $this->json([
            'ok' => true,
            'sent' => $sent,
            'errors' => $errors,
            'message' => "Mensaje enviado a {$sent} prospectos" . ($errors ? " ({$errors} errores)" : ""),
        ]);
    }

    /**
     * Send a WhatsApp text message via Meta API
     */
    private function sendWhatsAppMessage(string $to, string $message): bool
    {
        $token = setting('wa_token');
        $phoneId = setting('wa_phone_id');

        if (!$token || !$phoneId) {
            logError('WhatsApp API not configured for promotion send', __FILE__, __LINE__, 'warning');
            return false;
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $message],
        ];

        $waVersion = setting('wa_api_version', 'v19.0');
        $url = "https://graph.facebook.com/{$waVersion}/{$phoneId}/messages";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
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
            logError('WhatsApp API error sending promotion: ' . $response, __FILE__, __LINE__, 'warning');
            return false;
        }

        return true;
    }

    public function approve(string $id): void
    {
        $this->requireAuth('colaborador');
        $this->verifyCsrf();

        $this->promotions->approve((int)$id, (int)currentUser()['id']);
        $promo = $this->promotions->find((int)$id);
        if ($promo) {
            $this->notifyVisitorsForPromotion((int)$id, $promo['title'], $promo['business_id'] ? (int)$promo['business_id'] : null);
        }
        $this->logAction('approve_promotion', 'promotions', (int)$id);
        $this->json(['ok' => true]);
    }

    public function delete(string $id): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $promo = $this->promotions->find((int)$id);
        if (!$promo) { $this->json(['error' => 'not found'], 404); }

        if ($promo['business_id']) {
            $business = $this->businesses->find($promo['business_id']);
            $this->ownerOrAdmin($business);
        }

        $this->promotions->delete((int)$id);
        $this->logAction('delete_promotion', 'promotions', (int)$id, $promo['title']);
        $this->json(['ok' => true]);
    }

    public function sendHistory(string $id): void
    {
        $this->requireAuth('prestador');

        $promo = $this->promotions->find((int)$id);
        if (!$promo) { $this->json(['error' => 'not found'], 404); }

        $history = $this->promotions->getSendHistory((int)$id);
        $this->json($history);
    }

    public function apiPromotions(): void
    {
        // Public API for chatbot/map to show active promotions
        $promotions = $this->promotions->active();
        $result = array_map(function($p) {
            return [
                'id' => $p['id'],
                'title' => $p['title'],
                'description' => $p['description'],
                'image' => $p['image'] ? imageUrl($p['image']) : null,
                'price' => $p['price'],
                'presale_price' => $p['presale_price'],
                'conditions' => $p['conditions'],
                'public_url' => $p['public_url'],
                'type' => $p['type'],
                'start_date' => $p['start_date'],
                'end_date' => $p['end_date'],
                'business_name' => $p['business_name'],
                'business_slug' => $p['business_slug'],
            ];
        }, $promotions);

        $this->json($result);
    }

    /**
     * Vista pública de una promoción
     */
    public function publicView(string $id): void
    {
        $promo = $this->promotions->find((int)$id);
        if (!$promo || !in_array($promo['status'], ['active', 'approved'], true)) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        // Track view
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO promotion_views (promotion_id, ip, user_agent) VALUES (?,?,?)');
        $stmt->execute([(int)$id, $_SERVER['REMOTE_ADDR'] ?? null, substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)]);

        // Count views
        $stmt = $db->prepare('SELECT COUNT(*) FROM promotion_views WHERE promotion_id = ?');
        $stmt->execute([(int)$id]);
        $viewCount = (int)$stmt->fetchColumn();

        $stmt = $db->prepare('SELECT COUNT(*) FROM promotion_inquiries WHERE promotion_id = ?');
        $stmt->execute([(int)$id]);
        $inquiryCount = (int)$stmt->fetchColumn();

        $this->view('promotions.public_view', compact('promo', 'viewCount', 'inquiryCount'));
    }

    /**
     * Solicitar información de una promoción
     */
    public function publicInquiry(string $id): void
    {
        $promo = $this->promotions->find((int)$id);
        if (!$promo) { $this->json(['error' => 'not found'], 404); }

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $message = trim($_POST['message'] ?? '');

        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO promotion_inquiries (promotion_id, name, phone, email, message) VALUES (?,?,?,?,?)');
        $stmt->execute([(int)$id, $name, $phone, $email, $message]);

        $this->json(['ok' => true, 'message' => 'Solicitud enviada exitosamente']);
    }

    /**
     * Lista pública de eventos
     */
    public function publicEvents(): void
    {
        $events = $this->promotions->publicEvents();
        $this->view('promotions.public_events', compact('events'));
    }

    /**
     * Vista pública de un evento
     */
    public function publicEventView(string $id): void
    {
        $promo = $this->promotions->find((int)$id);
        if (!$promo || !in_array($promo['status'], ['active', 'approved'], true)) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $this->view('promotions.public_view', compact('promo'));
    }

    private function saveUpload(array $file): ?string
    {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_IMG_EXT, true)) return null;
        if ($file['size'] > MAX_FILE_SIZE) return null;

        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest = UPLOAD_PATH . '/' . $filename;

        if (!is_dir(UPLOAD_PATH)) {
            mkdir(UPLOAD_PATH, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return $filename;
        }
        return null;
    }

    private function notifyVisitorsForPromotion(int $promotionId, string $title, ?int $businessId): void
    {
        foreach ($this->users->visitors() as $visitor) {
            $this->notifications->create([
                'user_id' => (int)$visitor['id'],
                'business_id' => $businessId,
                'type' => 'system',
                'title' => 'Nueva promoción disponible',
                'message' => "Ya puedes consultar la promoción \"{$title}\" en tu panel de visitante.",
            ]);
        }
    }

    private function ownerOrAdmin(array $business): void
    {
        $user = currentUser();
        if ($user['role'] !== 'superadmin' && (int)$business['user_id'] !== (int)$user['id']) {
            http_response_code(403);
            $this->json(['error' => 'No tienes permiso'], 403);
        }
    }
}
