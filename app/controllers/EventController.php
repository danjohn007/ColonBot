<?php
class EventController extends Controller
{
    private EventModel $events;
    private BusinessModel $businesses;

    public function __construct()
    {
        $this->events = new EventModel();
        $this->businesses = new BusinessModel();
    }

    public function index(): void
    {
        $this->requireAuth('prestador');
        $user = currentUser();
        $businesses = $user['role'] === 'superadmin'
            ? $this->businesses->allWithCategory()
            : $this->businesses->byUser((int)$user['id']);

        $events = [];
        foreach ($businesses as $b) {
            $events = array_merge($events, $this->events->byBusiness((int)$b['id']));
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

        $id = $this->events->insert([
            'business_id' => $businessId > 0 ? $businessId : null,
            'user_id' => currentUser()['id'],
            'title' => $title,
            'description' => trim($_POST['description'] ?? ''),
            'image' => $image ?: null,
            'price' => $_POST['price'] !== '' ? (float)$_POST['price'] : null,
            'presale_price' => $_POST['presale_price'] !== '' ? (float)$_POST['presale_price'] : null,
            'conditions' => trim($_POST['conditions'] ?? ''),
            'public_url' => trim($_POST['public_url'] ?? ''),
            'target_segment' => implode(',', $_POST['target_segment'] ?? ['todos']),
            'status' => 'pending',
            'start_date' => $_POST['start_date'] ?? null,
            'end_date' => $_POST['end_date'] ?? null,
            'presale_start' => $_POST['presale_start'] ?? null,
            'presale_end' => $_POST['presale_end'] ?? null,
        ]);

        $this->logAction('create_event', 'events', $id, $title);
        $this->json(['ok' => true, 'id' => $id]);
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
            'conditions' => trim($_POST['conditions'] ?? ''),
            'public_url' => trim($_POST['public_url'] ?? ''),
            'target_segment' => implode(',', $_POST['target_segment'] ?? explode(',', $event['target_segment'])),
            'start_date' => $_POST['start_date'] ?? $event['start_date'],
            'end_date' => $_POST['end_date'] ?? $event['end_date'],
            'presale_start' => $_POST['presale_start'] ?? $event['presale_start'],
            'presale_end' => $_POST['presale_end'] ?? $event['presale_end'],
        ];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = $this->saveUpload($_FILES['image']);
            if ($image) $data['image'] = $image;
        }

        if (isset($_POST['status'])) {
            $data['status'] = in_array($_POST['status'], ['pending', 'active', 'inactive', 'expired']) ? $_POST['status'] : $event['status'];
        }

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
        if (!in_array($newStatus, ['pending', 'active', 'inactive', 'expired'])) {
            $this->json(['error' => 'Invalid status'], 422);
        }

        $this->events->update((int)$id, ['status' => $newStatus]);
        $this->json(['ok' => true]);
    }

    public function approve(string $id): void
    {
        $this->requireAuth('colaborador');
        $this->verifyCsrf();

        $this->events->approve((int)$id, (int)currentUser()['id']);
        $this->logAction('approve_event', 'events', (int)$id);
        $this->json(['ok' => true]);
    }

    /**
     * Vista pública de un evento
     */
    public function publicView(string $id): void
    {
        $event = $this->events->find((int)$id);
        if (!$event || $event['status'] !== 'active') {
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

    private function ownerOrAdmin(array $business): void
    {
        $user = currentUser();
        if ($user['role'] !== 'superadmin' && (int)$business['user_id'] !== (int)$user['id']) {
            http_response_code(403);
            $this->json(['error' => 'No tienes permiso'], 403);
        }
    }
}