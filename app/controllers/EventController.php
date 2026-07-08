<?php
class EventController extends Controller
{
    private EventModel $events;
    private BusinessModel $businesses;
    private NotificationModel $notifications;
    private UserModel $users;

    public function __construct()
    {
        $this->events = new EventModel();
        $this->businesses = new BusinessModel();
        $this->notifications = new NotificationModel();
        $this->users = new UserModel();
    }

    public function index(): void
    {
        $this->requireAuth('prestador', 'colaborador_admin', 'superadmin');
        $user = currentUser();
        $businesses = in_array($user['role'], ['colaborador_admin', 'superadmin'])
            ? $this->businesses->allWithCategory()
            : $this->businesses->byUser((int)$user['id']);

        $events = [];
        foreach ($businesses as $b) {
            $events = array_merge($events, $this->events->byBusiness((int)$b['id']));
        }

        // Also get events created by this user directly
        $userEvents = $this->events->byUser((int)$user['id']);
        $events = array_merge($events, $userEvents);

        // Remove duplicates
        $seen = [];
        $uniqueEvents = [];
        foreach ($events as $e) {
            if (!in_array($e['id'], $seen)) {
                $seen[] = $e['id'];
                $uniqueEvents[] = $e;
            }
        }

        $this->view('events.index', compact('events', 'businesses', 'user') + ['csrf' => $this->csrf()]);
    }

    public function list(string $businessId): void
    {
        $this->requireAuth('prestador');
        $business = $this->businesses->find((int)$businessId);
        if (!$business) { $this->json(['error' => 'not found'], 404); }

        $events = $this->events->byBusiness((int)$businessId);
        $this->json($events);
    }

    public function create(): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $businessId = (int)($_POST['business_id'] ?? 0);
        
        $user = currentUser();
        if ($businessId <= 0 && !in_array($user['role'], ['colaborador_admin', 'superadmin'])) {
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

        // Determine event type: public (colaborador_admin) or private (prestador)
        $eventType = $businessId > 0 ? 'privado' : 'publico';
        if (in_array($user['role'], ['colaborador_admin', 'superadmin'])) {
            $eventType = 'publico';
        }

        $isAdminEvent = in_array($user['role'], ['colaborador_admin', 'superadmin'], true);
        $id = $this->events->insert([
            'business_id' => $businessId > 0 ? $businessId : null,
            'user_id' => currentUser()['id'],
            'title' => $title,
            'description' => trim($_POST['description'] ?? ''),
            'image' => $image ?: null,
            'price' => $_POST['price'] !== '' ? (float)$_POST['price'] : null,
            'presale_price' => $_POST['presale_price'] !== '' ? (float)$_POST['presale_price'] : null,
            'capacity' => $_POST['capacity'] !== '' ? (int)$_POST['capacity'] : null,
            'location' => trim($_POST['location'] ?? ''),
            'whatsapp' => trim($_POST['whatsapp'] ?? ''),
            'validity' => trim($_POST['validity'] ?? ''),
            'conditions' => trim($_POST['conditions'] ?? ''),
            'event_type' => $eventType,
            'target_segment' => implode(',', $_POST['target_segment'] ?? ['todos']),
            'status' => $isAdminEvent ? 'active' : 'pending',
            'approved_by' => $isAdminEvent ? (int)$user['id'] : null,
            'bot_authorized' => $isAdminEvent ? 1 : 0,
            'bot_authorized_by' => $isAdminEvent ? (int)$user['id'] : null,
            'bot_authorized_at' => $isAdminEvent ? date('Y-m-d H:i:s') : null,
            'start_date' => $this->dateTimeOrNull($_POST['start_date'] ?? null),
            'end_date' => $this->dateTimeOrNull($_POST['end_date'] ?? null),
            'presale_start' => $this->dateTimeOrNull($_POST['presale_start'] ?? null),
            'presale_end' => $this->dateTimeOrNull($_POST['presale_end'] ?? null),
        ]);

        // Generate public URL
        $publicUrl = $this->events->generatePublicUrl($id);
        $this->events->update($id, ['public_url' => $publicUrl]);

        // Notify visitors for official events, or admins for provider requests.
        if ($isAdminEvent) {
            $this->notifyVisitorsForEvent($id, $title, $businessId > 0 ? $businessId : null);
        } else {
            $this->notifyAdminsForApproval($id, $title);
        }

        $this->logAction('create_event', 'events', $id, $title);
        $this->json(['ok' => true, 'id' => $id, 'public_url' => $publicUrl]);
    }

    public function update(string $id): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $event = $this->events->find((int)$id);
        if (!$event) { $this->json(['error' => 'not found'], 404); }

        if ($event['business_id']) {
            $business = $this->businesses->find($event['business_id']);
            $this->ownerOrAdmin($business);
        }

        $data = [
            'title' => trim($_POST['title'] ?? $event['title']),
            'description' => trim($_POST['description'] ?? ''),
            'price' => $_POST['price'] !== '' ? (float)$_POST['price'] : null,
            'presale_price' => $_POST['presale_price'] !== '' ? (float)$_POST['presale_price'] : null,
            'capacity' => $_POST['capacity'] !== '' ? (int)$_POST['capacity'] : null,
            'location' => trim($_POST['location'] ?? ''),
            'whatsapp' => trim($_POST['whatsapp'] ?? ''),
            'validity' => trim($_POST['validity'] ?? ''),
            'conditions' => trim($_POST['conditions'] ?? ''),
            'target_segment' => implode(',', $_POST['target_segment'] ?? explode(',', $event['target_segment'])),
            'start_date' => $this->dateTimeOrNull($_POST['start_date'] ?? $event['start_date']),
            'end_date' => $this->dateTimeOrNull($_POST['end_date'] ?? $event['end_date']),
            'presale_start' => $this->dateTimeOrNull($_POST['presale_start'] ?? $event['presale_start']),
            'presale_end' => $this->dateTimeOrNull($_POST['presale_end'] ?? $event['presale_end']),
        ];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = $this->saveUpload($_FILES['image']);
            if ($image) $data['image'] = $image;
        }

        if (isset($_POST['status'])) {
            $data['status'] = in_array($_POST['status'], ['pending', 'approved', 'active', 'inactive', 'expired']) ? $_POST['status'] : $event['status'];
        }

        // Regenerate public URL if title changed
        $data['public_url'] = $this->events->generatePublicUrl((int)$id);

        $this->events->update((int)$id, $data);
        $this->logAction('update_event', 'events', (int)$id, $data['title']);
        $this->json(['ok' => true]);
    }

    public function toggleStatus(string $id): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $event = $this->events->find((int)$id);
        if (!$event) { $this->json(['error' => 'not found'], 404); }

        if ($event['business_id']) {
            $business = $this->businesses->find($event['business_id']);
            $this->ownerOrAdmin($business);
        }

        $newStatus = $_POST['status'] ?? '';
        if (!in_array($newStatus, ['pending', 'approved', 'active', 'inactive', 'expired'])) {
            $this->json(['error' => 'Invalid status'], 422);
        }

        $this->events->update((int)$id, ['status' => $newStatus]);
        if ($newStatus === 'active') {
            $this->notifyVisitorsForEvent((int)$id, $event['title'], $event['business_id'] ? (int)$event['business_id'] : null);
        }
        $this->json(['ok' => true]);
    }

    public function approve(string $id): void
    {
        $this->requireAuth('colaborador_admin');
        $this->verifyCsrf();

        $this->events->approve((int)$id, (int)currentUser()['id']);
        $event = $this->events->find((int)$id);
        if ($event) {
            $this->notifyVisitorsForEvent((int)$id, $event['title'], $event['business_id'] ? (int)$event['business_id'] : null);
        }
        $this->logAction('approve_event', 'events', (int)$id);
        $this->json(['ok' => true]);
    }

    /**
     * Authorize event for chatbot publication (1-click)
     */
    public function authorizeBot(string $id): void
    {
        $this->requireAuth('colaborador_admin');
        $this->verifyCsrf();

        $event = $this->events->find((int)$id);
        if (!$event) { $this->json(['error' => 'not found'], 404); }

        if (!in_array($event['status'], ['active', 'approved'], true)) {
            $this->json(['error' => 'Primero aprueba el evento antes de publicarlo en chatbot'], 422);
        }

        $this->events->authorizeBot((int)$id, (int)currentUser()['id']);
        $this->logAction('authorize_event_bot', 'events', (int)$id, $event['title']);
        $this->json(['ok' => true, 'message' => 'Evento autorizado para publicación en chatbot']);
    }

    /**
     * Vista pública de un evento
     */
    public function publicView(string $id): void
    {
        $event = $this->events->find((int)$id);
        if (!$event || $event['status'] === 'expired') {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        // Get business data
        $business = null;
        if ($event['business_id']) {
            $business = $this->businesses->find((int)$event['business_id']);
        }

        $this->view('events.public_view', compact('event', 'business'));
    }

    private function notifyAdminsForApproval(int $eventId, string $title): void
    {
        $admins = $this->users->admins();
        foreach ($admins as $admin) {
            $this->notifications->create([
                'user_id' => (int)$admin['id'],
                'event_id' => $eventId,
                'type' => 'system',
                'title' => 'Nuevo evento pendiente de aprobación',
                'message' => "El evento \"{$title}\" requiere aprobación para su publicación.",
            ]);
        }
    }

    private function notifyVisitorsForEvent(int $eventId, string $title, ?int $businessId): void
    {
        foreach ($this->users->visitors() as $visitor) {
            $this->notifications->create([
                'user_id' => (int)$visitor['id'],
                'business_id' => $businessId,
                'event_id' => $eventId,
                'type' => 'system',
                'title' => 'Nuevo evento disponible',
                'message' => "Ya puedes consultar el evento \"{$title}\" en tu panel de visitante.",
            ]);
        }
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

    private function dateTimeOrNull(?string $value): ?string
    {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }

        return str_replace('T', ' ', $value);
    }

    private function ownerOrAdmin(array $business): void
    {
        $user = currentUser();
        if (!in_array($user['role'], ['superadmin', 'colaborador_admin']) && (int)$business['user_id'] !== (int)$user['id']) {
            http_response_code(403);
            $this->json(['error' => 'No tienes permiso'], 403);
        }
    }
}
