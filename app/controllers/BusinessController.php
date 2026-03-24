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
        $this->requireAuth('admin');
        $user      = currentUser();
        $businesses = $this->businesses->byUser((int)$user['id']);
        $this->view('business.dashboard', compact('businesses', 'user'));
    }

    public function index(): void
    {
        $this->requireAuth('admin');
        $user      = currentUser();
        $businesses = $this->businesses->byUser((int)$user['id']);
        $this->view('business.index', compact('businesses'));
    }

    public function create(): void
    {
        $this->requireAuth('admin');
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
        $this->requireAuth('admin');
        $this->verifyCsrf();

        $categoryIds = array_map('intval', $_POST['categories'] ?? []);
        if (empty($categoryIds)) {
            $this->flash('error', 'Selecciona al menos una categoría.');
            $this->redirect('admin/negocio/crear');
            return;
        }

        $data = $this->buildData();
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

        // Imagen de portada
        $this->handleCoverUpload($id);

        $this->logAction('create_business', 'businesses', $id, $data['name']);
        $this->flash('success', 'Negocio creado exitosamente.');
        $this->redirect('admin/negocio');
    }

    public function edit(string $id): void
    {
        $this->requireAuth('admin');
        $business = $this->businesses->find((int)$id);
        if (!$business) { http_response_code(404); return; }

        $this->ownerOrAdmin($business);

        $categories         = $this->categories->active();
        $amenities          = $this->amenities->active();
        $businessAmen       = array_column($this->businesses->amenities((int)$id), 'id');
        $businessCategoryIds = array_column($this->businesses->businessCategories((int)$id), 'id');
        $images             = $this->businesses->images((int)$id);
        $services           = $this->businesses->allServices((int)$id);
        $products           = $this->businesses->allProducts((int)$id);
        $events             = $this->businesses->allEvents((int)$id);

        $this->view('business.form', [
            'business'           => $business,
            'categories'         => $categories,
            'amenities'          => $amenities,
            'businessAmenIds'    => $businessAmen,
            'businessCategoryIds' => $businessCategoryIds,
            'images'             => $images,
            'services'           => $services,
            'products'           => $products,
            'events'             => $events,
            'csrf'               => $this->csrf(),
        ]);
    }

    public function update(string $id): void
    {
        $this->requireAuth('admin');
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
        // The first selected category is stored as primary for display/map joins
        $data['category_id'] = $categoryIds[0];
        $this->businesses->syncCategories((int)$id, $categoryIds);
        $this->businesses->update((int)$id, $data);

        $amenityIds = array_map('intval', $_POST['amenities'] ?? []);
        $this->businesses->syncAmenities((int)$id, $amenityIds);

        $this->handleCoverUpload((int)$id);

        $this->logAction('update_business', 'businesses', (int)$id, $data['name']);
        $this->flash('success', 'Negocio actualizado.');
        $this->redirect('admin/negocio');
    }

    public function destroy(string $id): void
    {
        $this->requireAuth('admin');
        $this->verifyCsrf();

        $business = $this->businesses->find((int)$id);
        if (!$business) { $this->redirect('admin/negocio'); }

        $this->ownerOrAdmin($business);

        $this->businesses->delete((int)$id);
        $this->logAction('delete_business', 'businesses', (int)$id, $business['name']);
        $this->flash('success', 'Negocio eliminado.');
        $this->redirect('admin/negocio');
    }

    public function upload(): void
    {
        $this->requireAuth('admin');
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
        $this->requireAuth('admin');
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
        $this->requireAuth('admin');
        $this->verifyCsrf();

        $business = $this->businesses->find((int)$id);
        if (!$business) { $this->json(['error' => 'not found'], 404); }

        $this->ownerOrAdmin($business);

        $this->businesses->deleteService((int)$sid, (int)$id);
        $this->json(['ok' => true]);
    }

    public function saveProduct(string $id): void
    {
        $this->requireAuth('admin');
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
        $this->requireAuth('admin');
        $this->verifyCsrf();

        $business = $this->businesses->find((int)$id);
        if (!$business) { $this->json(['error' => 'not found'], 404); }

        $this->ownerOrAdmin($business);

        $this->businesses->deleteProduct((int)$pid, (int)$id);
        $this->json(['ok' => true]);
    }

    public function saveEvent(string $id): void
    {
        $this->requireAuth('admin');
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

        $this->businesses->upsertEvent((int)$id, [
            'name'        => $name,
            'description' => $desc,
            'price'       => $price,
            'date'        => $date,
        ], $eid);

        $events = $this->businesses->allEvents((int)$id);
        $this->json(['ok' => true, 'events' => $events]);
    }

    public function deleteEvent(string $id, string $eid): void
    {
        $this->requireAuth('admin');
        $this->verifyCsrf();

        $business = $this->businesses->find((int)$id);
        if (!$business) { $this->json(['error' => 'not found'], 404); }

        $this->ownerOrAdmin($business);

        $this->businesses->deleteEvent((int)$eid, (int)$id);
        $this->json(['ok' => true]);
    }

    // ── Privados ──────────────────────────────────────────────────────────

    private function buildData(): array
    {
        return [
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
            'schedule'    => trim($_POST['schedule'] ?? ''),
            'status'      => in_array($_POST['status'] ?? '', ['draft','pending','published'], true)
                             ? $_POST['status'] : 'draft',
        ];
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
        if ($user['role'] !== 'superadmin' && (int)$business['user_id'] !== (int)$user['id']) {
            http_response_code(403);
            die('No tienes permiso para editar este negocio.');
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
}
