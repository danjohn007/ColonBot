<?php
class BusinessController extends Controller
{
    private BusinessModel $businesses;
    private CategoryModel $categories;
    private AmenityModel  $amenities;

    public function __construct()
    {
        $this->businesses = new BusinessModel();
        $this->categories = new CategoryModel();
        $this->amenities  = new AmenityModel();
    }

    public function dashboard(): void
    {
        $this->requireAuth('prestador');
        $user      = currentUser();
        $businesses = $user['role'] === 'superadmin'
            ? $this->businesses->allWithCategory()
            : $this->businesses->byUser((int)$user['id']);
        $this->view('business.dashboard', compact('businesses', 'user'));
    }

    // ── Micrositio del Prestador ─────────────────────────────────────────

    public function microsite(): void
    {
        $this->requireAuth('prestador');
        // Colaborador_admin can also view microsite dashboards
        $userRole = currentUser()['role'] ?? '';
        if ($userRole === 'colaborador' || $userRole === 'colaborador_admin') {
            $this->requireAuth('colaborador_admin');
        }
        $user = currentUser();
        $businesses = $user['role'] === 'superadmin'
            ? $this->businesses->allWithCategory()
            : $this->businesses->byUser((int)$user['id']);

        if (empty($businesses)) {
            // Allow admin/superadmin to proceed even without businesses
            if (in_array($user['role'], ['colaborador_admin', 'superadmin'])) {
                $this->view('business.microsite', compact('businesses', 'user'));
                return;
            }
            $this->flash('info', 'Primero debes registrar un negocio.');
            $this->redirect('admin/negocio/crear');
        }

        $this->view('business.microsite', compact('businesses', 'user'));
    }

    public function micrositeDashboard(string $id): void
    {
        $this->requireAuth('prestador');
        // Colaborador can also view microsite dashboards
        $userRole = currentUser()['role'] ?? '';
        if ($userRole === 'colaborador') {
            $this->requireAuth('colaborador');
        }
        $business = $this->businesses->find((int)$id);
        if (!$business) { http_response_code(404); return; }
        $this->ownerOrAdmin($business);

        $user = currentUser();
        $images = $this->businesses->images((int)$id);
        $services = $this->businesses->allServices((int)$id);
        $products = $this->businesses->allProducts((int)$id);
        $events = $this->businesses->allEvents((int)$id);
        $reviews = $this->businesses->reviews((int)$id);

        // CRM metrics
        $contactModel = new ContactModel();
        $metrics = $contactModel->getMetrics((int)$id);
        $chartData = $contactModel->getChartData((int)$id, 'month');

        // Analytics from map & whatsapp
        $analyticsModel = new AnalyticsModel();
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM analytics WHERE business_id = ? AND event = 'map_view'");
        $stmt->execute([(int)$id]);
        $mapViews = (int)$stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COUNT(*) FROM analytics WHERE business_id = ? AND event = 'whatsapp_click'");
        $stmt->execute([(int)$id]);
        $waClicks = (int)$stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COUNT(*) FROM analytics WHERE business_id = ? AND event = 'directions_click'");
        $stmt->execute([(int)$id]);
        $directionsClicks = (int)$stmt->fetchColumn();

        $stmt = $db->prepare(
            'SELECT COUNT(*)
             FROM chatbot_sessions cs
             LEFT JOIN contacts c ON c.wa_id = cs.wa_id
             WHERE c.business_id = ?'
        );
        $stmt->execute([(int)$id]);
        $chatbotSessions = (int)$stmt->fetchColumn();

        $leadCount = (int)($metrics['total'] ?? 0);
        $convertedLeads = (int)($metrics['clientes'] ?? 0) + (int)($metrics['lovemarks'] ?? 0);
        $conversionRate = $leadCount > 0 ? round(($convertedLeads / $leadCount) * 100, 1) : 0;
        $campaignResponse = $this->campaignResponseMetrics((int)$id);
        $topLovemarks = $this->topLovemarks((int)$id);
        $weeklySales = $this->salesTotal((int)$id, 'week');
        $monthlySales = $this->salesTotal((int)$id, 'month');

        $this->view('business.microsite_dashboard', compact(
            'business', 'user', 'images', 'services', 'products', 'events', 'reviews',
            'metrics', 'chartData', 'mapViews', 'waClicks', 'directionsClicks', 'chatbotSessions',
            'leadCount', 'conversionRate', 'campaignResponse', 'topLovemarks',
            'weeklySales', 'monthlySales'
        ) + ['csrf' => $this->csrf()]);
    }

    public function micrositeCharts(string $id): void
    {
        $this->requireAuth('prestador');
        $business = $this->businesses->find((int)$id);
        if (!$business) { $this->json(['error' => 'not found'], 404); }
        $this->ownerOrAdmin($business);

        $period = $_GET['period'] ?? 'month';
        $db = Database::getInstance();

        // Chart data: views vs whatsapp clicks over time
        $format = $period === 'year' ? '%Y-%m' : '%Y-%m-%d';
        $interval = $period === 'year' ? 12 : ($period === 'month' ? 30 : 7);

        $stmt = $db->prepare(
            "SELECT DATE_FORMAT(created_at, '$format') AS label,
                    SUM(CASE WHEN event = 'map_view' THEN 1 ELSE 0 END) AS map_views,
                    SUM(CASE WHEN event = 'whatsapp_click' THEN 1 ELSE 0 END) AS wa_clicks
             FROM analytics
             WHERE business_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL $interval $period)
             GROUP BY label
             ORDER BY label ASC"
        );
        $stmt->execute([(int)$id]);
        $chartData = $stmt->fetchAll();

        $this->json($chartData);
    }

    public function toggleOpen(string $id): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $business = $this->businesses->find((int)$id);
        if (!$business) { $this->json(['error' => 'not found'], 404); }
        $this->ownerOrAdmin($business);

        $isOpen = isset($_POST['is_open']) ? (int)$_POST['is_open'] : (int)(!$business['is_open']);
        $this->businesses->update((int)$id, [
            'is_open' => $isOpen,
            'open_for_messaging' => in_array($_POST['open_for_messaging'] ?? $business['open_for_messaging'], ['24hrs', 'schedule'])
                ? $_POST['open_for_messaging'] : $business['open_for_messaging'],
        ]);

        $this->logAction('toggle_business_open', 'businesses', (int)$id, $isOpen ? 'Abierto' : 'Cerrado');
        $this->json(['ok' => true, 'is_open' => $isOpen]);
    }

    public function index(): void
    {
        $this->requireAuth('prestador');
        $user      = currentUser();
        $businesses = $user['role'] === 'superadmin'
            ? $this->businesses->allWithCategory()
            : $this->businesses->byUser((int)$user['id']);
        $this->view('business.index', compact('businesses'));
    }

    public function create(): void
    {
        $this->requireAuth('prestador');
        $categories = $this->categories->active();
        $amenities  = $this->amenities->active();
        $this->view('business.form', [
            'business'   => null,
            'categories' => $categories,
            'amenities'  => $amenities,
            'csrf'       => $this->csrf(),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $categoryIds = array_map('intval', $_POST['categories'] ?? []);
        if (empty($categoryIds)) {
            $this->flash('error', 'Selecciona al menos una categoría.');
            $this->redirect('admin/negocio/crear');
            return;
        }

        $data = $this->buildData();
        $data['isotipo'] = trim($_POST['isotipo'] ?? '');
        // The first selected category is stored as primary for display/map joins (backward compat)
        $data['category_id'] = $categoryIds[0];
        $slug = $this->uniqueSlug($data['name']);
        $data['slug']    = $slug;
        $data['user_id'] = currentUser()['id'];

        $id = $this->businesses->insert($data);

        // Categorías
        $this->businesses->syncCategories($id, $categoryIds);

        // Amenidades
        $amenityIds = array_map('intval', $_POST['amenities'] ?? []);
        $this->businesses->syncAmenities($id, $amenityIds);

        // Tipos de viaje
        $tripTypes = $_POST['trip_types'] ?? [];
        $this->businesses->syncTripTypes($id, $tripTypes);

        // Imagen de portada
        $this->handleCoverUpload($id);

        $this->logAction('create_business', 'businesses', $id, $data['name']);
        $this->flash('success', 'Negocio creado exitosamente.');
        $this->redirect('admin/negocio');
    }

    public function edit(string $id): void
    {
        $this->requireAuth('prestador');
        $business = $this->businesses->find((int)$id);
        if (!$business) { http_response_code(404); return; }

        $this->ownerOrAdmin($business);

        $categories         = $this->categories->active();
        $amenities          = $this->amenities->active();
        $businessAmen       = array_column($this->businesses->amenities((int)$id), 'id');
        $businessCategoryIds = array_column($this->businesses->businessCategories((int)$id), 'id');
        $businessTripTypes   = array_column($this->businesses->tripTypes((int)$id), 'trip_type');
        $images             = $this->businesses->images((int)$id);
        $services           = $this->businesses->allServices((int)$id);
        $products           = $this->businesses->allProducts((int)$id);

        $this->view('business.form', [
            'business'           => $business,
            'categories'         => $categories,
            'amenities'          => $amenities,
            'businessAmenIds'    => $businessAmen,
            'businessCategoryIds' => $businessCategoryIds,
            'businessTripTypes'   => $businessTripTypes,
            'images'             => $images,
            'services'           => $services,
            'products'           => $products,
            'csrf'               => $this->csrf(),
        ]);
    }

    public function update(string $id): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $business = $this->businesses->find((int)$id);
        if (!$business) { $this->redirect('admin/negocio'); }

        $this->ownerOrAdmin($business);

        $categoryIds = array_map('intval', $_POST['categories'] ?? []);
        if (empty($categoryIds)) {
            $this->flash('error', 'Selecciona al menos una categoría.');
            $this->redirect('admin/negocio/' . $id);
            return;
        }

        $data = $this->buildData();
        $data['isotipo'] = trim($_POST['isotipo'] ?? '');
        // The first selected category is stored as primary for display/map joins
        $data['category_id'] = $categoryIds[0];
        $this->businesses->syncCategories((int)$id, $categoryIds);
        $this->businesses->update((int)$id, $data);

        $amenityIds = array_map('intval', $_POST['amenities'] ?? []);
        $this->businesses->syncAmenities((int)$id, $amenityIds);

        // Tipos de viaje
        $tripTypes = $_POST['trip_types'] ?? [];
        $this->businesses->syncTripTypes((int)$id, $tripTypes);

        $this->handleCoverUpload((int)$id);

        $this->logAction('update_business', 'businesses', (int)$id, $data['name']);
        $this->flash('success', 'Negocio actualizado.');
        $this->redirect('admin/negocio');
    }

    public function destroy(string $id): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $business = $this->businesses->find((int)$id);
        if (!$business) { $this->redirect('admin/negocio'); }

        $this->ownerOrAdmin($business);

        $this->businesses->delete((int)$id);
        $this->logAction('delete_business', 'businesses', (int)$id, $business['name']);
        $this->flash('success', 'Negocio eliminado.');
        $this->redirect('admin/negocio');
    }

    public function deleteImage(string $id): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $image = $this->businesses->findImage((int)$id);
        if (!$image) { $this->json(['error' => 'not found'], 404); }

        $business = $this->businesses->find((int)$image['business_id']);
        if (!$business) { $this->json(['error' => 'not found'], 404); }

        $this->ownerOrAdmin($business);

        $filePath = UPLOAD_PATH . '/' . $image['path'];
        if (is_file($filePath)) {
            unlink($filePath);
        }

        $this->businesses->deleteImage((int)$id);
        $this->json(['ok' => true]);
    }

    public function upload(): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $businessId = (int)($_POST['business_id'] ?? 0);
        $business   = $this->businesses->find($businessId);
        if (!$business) { $this->json(['error' => 'not found'], 404); }

        $this->ownerOrAdmin($business);

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['error' => 'upload error'], 400);
        }

        $path = $this->saveUpload($_FILES['image']);
        if (!$path) {
            $this->json(['error' => 'invalid file'], 422);
        }

        $this->businesses->addImage($businessId, $path, $_POST['caption'] ?? '');
        $this->json(['ok' => true, 'path' => imageUrl($path)]);
    }

    public function saveService(string $id): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $business = $this->businesses->find((int)$id);
        if (!$business) { $this->json(['error' => 'not found'], 404); }

        $this->ownerOrAdmin($business);

        $name  = trim($_POST['name'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $price = $this->parsePrice();
        $sid   = (int)($_POST['service_id'] ?? 0);

        if ($name === '') { $this->json(['error' => 'El nombre es requerido'], 422); }

        $this->businesses->upsertService((int)$id, [
            'name'        => $name,
            'description' => $desc,
            'price'       => $price,
        ], $sid);

        $services = $this->businesses->allServices((int)$id);
        $this->json(['ok' => true, 'services' => $services]);
    }

    public function deleteService(string $id, string $sid): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $business = $this->businesses->find((int)$id);
        if (!$business) { $this->json(['error' => 'not found'], 404); }

        $this->ownerOrAdmin($business);

        $this->businesses->deleteService((int)$sid, (int)$id);
        $this->json(['ok' => true]);
    }

    public function saveProduct(string $id): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $business = $this->businesses->find((int)$id);
        if (!$business) { $this->json(['error' => 'not found'], 404); }

        $this->ownerOrAdmin($business);

        $name      = trim($_POST['name'] ?? '');
        $desc      = trim($_POST['description'] ?? '');
        $price     = $this->parsePrice();
        $available = (int)($_POST['available'] ?? 1);
        $pid       = (int)($_POST['product_id'] ?? 0);

        if ($name === '') { $this->json(['error' => 'El nombre es requerido'], 422); }

        $this->businesses->upsertProduct((int)$id, [
            'name'        => $name,
            'description' => $desc,
            'price'       => $price,
            'available'   => $available,
        ], $pid);

        $products = $this->businesses->allProducts((int)$id);
        $this->json(['ok' => true, 'products' => $products]);
    }

    public function deleteProduct(string $id, string $pid): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $business = $this->businesses->find((int)$id);
        if (!$business) { $this->json(['error' => 'not found'], 404); }

        $this->ownerOrAdmin($business);

        $this->businesses->deleteProduct((int)$pid, (int)$id);
        $this->json(['ok' => true]);
    }

    public function saveEvent(string $id): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $business = $this->businesses->find((int)$id);
        if (!$business) { $this->json(['error' => 'not found'], 404); }

        $this->ownerOrAdmin($business);

        $name  = trim($_POST['name'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $price = $this->parsePrice();
        $date  = trim($_POST['date'] ?? '') ?: null;
        $eid   = (int)($_POST['event_id'] ?? 0);

        if ($name === '') { $this->json(['error' => 'El nombre es requerido'], 422); }

        // Use promotions table for unified event storage (same as colaborador global events)
        $promotions = new PromotionModel();
        if ($eid > 0) {
            $promotions->update($eid, [
                'title' => $name,
                'description' => $desc,
                'price' => $price,
                'start_date' => $date,
                'type' => 'evento',
            ]);
        } else {
            $promotions->insert([
                'business_id' => (int)$id,
                'user_id' => currentUser()['id'],
                'title' => $name,
                'description' => $desc,
                'price' => $price,
                'type' => 'evento',
                'target_segment' => 'todos',
                'status' => 'active',
                'start_date' => $date,
            ]);
            $this->notifyVisitorsForBusinessEvent((int)$id, $name);
        }

        $events = $this->businesses->allEvents((int)$id);
        $this->json(['ok' => true, 'events' => $events]);
    }

    public function deleteEvent(string $id, string $eid): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $business = $this->businesses->find((int)$id);
        if (!$business) { $this->json(['error' => 'not found'], 404); }

        $this->ownerOrAdmin($business);

        // Delete from promotions table (unified events)
        $promotions = new PromotionModel();
        $promotions->delete((int)$eid);
        $this->json(['ok' => true]);
    }

    // ── Privados ──────────────────────────────────────────────────────────

    private function buildData(): array
    {
        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'address'     => trim($_POST['address'] ?? ''),
            'lat'         => $_POST['lat'] !== '' ? (float)$_POST['lat'] : null,
            'lng'         => $_POST['lng'] !== '' ? (float)$_POST['lng'] : null,
            'phone'       => trim($_POST['phone'] ?? ''),
            'whatsapp'    => trim($_POST['whatsapp'] ?? ''),
            'email'       => trim($_POST['email'] ?? ''),
            'website'     => trim($_POST['website'] ?? ''),
            'facebook'    => trim($_POST['facebook'] ?? ''),
            'instagram'   => trim($_POST['instagram'] ?? ''),
            'is_open'     => isset($_POST['is_open']) ? (int)$_POST['is_open'] : 0,
            'open_for_messaging' => in_array($_POST['open_for_messaging'] ?? '24hrs', ['24hrs', 'schedule'], true)
                ? $_POST['open_for_messaging'] : '24hrs',
        ];

        // Only superadmin can change business status
        $user = currentUser();
        if ($user['role'] === 'superadmin') {
            $data['status'] = in_array($_POST['status'] ?? '', ['draft','pending','published'], true)
                             ? $_POST['status'] : 'draft';
        }

        return $data;
    }

    private function uniqueSlug(string $name): string
    {
        $base = slugify($name);
        $slug = $base;
        $i    = 1;
        $db   = Database::getInstance();
        $stmt = $db->prepare('SELECT COUNT(*) FROM businesses WHERE slug = ?');
        $stmt->execute([$slug]);
        while ((int)$stmt->fetchColumn() > 0) {
            $slug = $base . '-' . $i++;
            $stmt->execute([$slug]);
        }
        return $slug;
    }

    private function parsePrice(): ?float
    {
        $raw = $_POST['price'] ?? '';
        return $raw !== '' ? (float)$raw : null;
    }

    private function ownerOrAdmin(array $business): void
    {
        $user = currentUser();
        // Colaborador can view any business (read-only)
        if ($user['role'] === 'colaborador') {
            return;
        }
        if ($user['role'] !== 'superadmin' && (int)$business['user_id'] !== (int)$user['id']) {
            http_response_code(403);
            die('No tienes permiso para editar este negocio.');
        }
    }

    private function notifyVisitorsForBusinessEvent(int $businessId, string $title): void
    {
        $users = new UserModel();
        $notifications = new NotificationModel();

        foreach ($users->visitors() as $visitor) {
            $notifications->create([
                'user_id' => (int)$visitor['id'],
                'business_id' => $businessId,
                'type' => 'system',
                'title' => 'Nuevo evento disponible',
                'message' => "Ya puedes consultar el evento \"{$title}\" en tu panel de visitante.",
            ]);
        }
    }

    private function handleCoverUpload(int $businessId): void
    {
        if (!isset($_FILES['cover']) || $_FILES['cover']['error'] !== UPLOAD_ERR_OK) {
            return;
        }
        $path = $this->saveUpload($_FILES['cover']);
        if ($path) {
            $this->businesses->update($businessId, ['cover_image' => $path]);
        }
    }

    private function saveUpload(array $file): ?string
    {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_IMG_EXT, true)) return null;
        if ($file['size'] > MAX_FILE_SIZE) return null;

        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest     = UPLOAD_PATH . '/' . $filename;

        if (!is_dir(UPLOAD_PATH)) {
            mkdir(UPLOAD_PATH, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return $filename;
        }
        return null;
    }

    private function campaignResponseMetrics(int $businessId): array
    {
        try {
            $stmt = Database::getInstance()->prepare(
                "SELECT COUNT(DISTINCT p.id) AS campaigns,
                        COALESCE(COUNT(DISTINCT pv.id), 0) AS views,
                        COALESCE(COUNT(DISTINCT pi.id), 0) AS inquiries
                 FROM promotions p
                 LEFT JOIN promotion_views pv ON pv.promotion_id = p.id
                 LEFT JOIN promotion_inquiries pi ON pi.promotion_id = p.id
                 WHERE p.business_id = ?
                   AND p.type IN ('promocion', 'evento')"
            );
            $stmt->execute([$businessId]);
            $row = $stmt->fetch() ?: ['campaigns' => 0, 'views' => 0, 'inquiries' => 0];
        } catch (Throwable $e) {
            error_log('Provider campaign response skipped: ' . $e->getMessage());
            $row = ['campaigns' => 0, 'views' => 0, 'inquiries' => 0];
        }

        $views = (int)($row['views'] ?? 0);
        $inquiries = (int)($row['inquiries'] ?? 0);
        $row['rate'] = $views > 0 ? round(($inquiries / $views) * 100, 1) : 0;
        return $row;
    }

    private function topLovemarks(int $businessId): array
    {
        try {
            $stmt = Database::getInstance()->prepare(
                "SELECT c.name, c.phone, c.email,
                        COUNT(cp.id) AS purchases,
                        COALESCE(SUM(cp.amount), 0) AS total_spent
                 FROM contacts c
                 LEFT JOIN contact_purchases cp ON cp.contact_id = c.id
                 WHERE c.business_id = ?
                   AND c.category = 'lovemark'
                 GROUP BY c.id
                 ORDER BY total_spent DESC, purchases DESC, c.updated_at DESC
                 LIMIT 10"
            );
            $stmt->execute([$businessId]);
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            error_log('Provider top lovemarks skipped: ' . $e->getMessage());
            return [];
        }
    }

    private function salesTotal(int $businessId, string $period): float
    {
        $interval = $period === 'week' ? '7 DAY' : '1 MONTH';
        try {
            $stmt = Database::getInstance()->prepare(
                "SELECT COALESCE(SUM(amount), 0)
                 FROM contact_purchases
                 WHERE business_id = ?
                   AND purchase_date >= DATE_SUB(NOW(), INTERVAL {$interval})"
            );
            $stmt->execute([$businessId]);
            return (float)$stmt->fetchColumn();
        } catch (Throwable $e) {
            error_log("Provider sales {$period} skipped: " . $e->getMessage());
            return 0.0;
        }
    }
}
