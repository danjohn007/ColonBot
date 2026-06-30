<?php
class ColaboradorController extends Controller
{
    private BusinessModel $businesses;
    private PromotionModel $promotions;
    private AnalyticsModel $analytics;
    private UserModel $users;

    public function __construct()
    {
        $this->businesses = new BusinessModel();
        $this->promotions = new PromotionModel();
        $this->analytics = new AnalyticsModel();
        $this->users = new UserModel();
    }

    public function dashboard(): void
    {
        $this->requireAuth('colaborador');

        // Metrics
        $totalBiz = $this->businesses->count();
        $totalUsers = $this->users->count();
        $pendingPromos = $this->promotions->pendingForApproval();
        $summary = $this->analytics->summary();
        $topBusinesses = $this->analytics->topBusinesses(50);
        $dailyEvents = $this->analytics->dailyEvents(30);

        // Top visited this month
        $db = Database::getInstance();
        $topByCategory = $db->query(
            'SELECT c.name AS category, b.name, b.rating, b.visits,
                    ROW_NUMBER() OVER (PARTITION BY b.category_id ORDER BY b.rating DESC) AS rn
             FROM businesses b
             JOIN categories c ON c.id = b.category_id
             WHERE b.status = "published"
             ORDER BY c.name, b.rating DESC'
        )->fetchAll();

        // New providers this month
        $newProviders = $db->query(
            "SELECT b.*, u.name AS owner_name FROM businesses b
             JOIN users u ON u.id = b.user_id
             WHERE b.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
             ORDER BY b.created_at DESC"
        )->fetchAll();

        // Visits per day/week/month
        $visitsByDay = $db->query(
            "SELECT DATE(created_at) AS day, COUNT(*) AS total
             FROM analytics
             WHERE event = 'map_view' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY day ORDER BY day"
        )->fetchAll();

        $visitsByWeek = $db->query(
            "SELECT WEEK(created_at) AS week, COUNT(*) AS total
             FROM analytics
             WHERE event = 'map_view' AND created_at >= DATE_SUB(NOW(), INTERVAL 4 WEEK)
             GROUP BY week ORDER BY week"
        )->fetchAll();

        $this->view('colaborador.dashboard', compact(
            'totalBiz', 'totalUsers', 'pendingPromos', 'summary',
            'topBusinesses', 'dailyEvents', 'topByCategory',
            'newProviders', 'visitsByDay', 'visitsByWeek'
        ));
    }

    public function events(): void
    {
        $this->requireAuth('colaborador');

        $pendingPromos = $this->promotions->pendingForApproval();
        $activePromos = $this->promotions->active();

        $this->view('colaborador.events', compact('pendingPromos', 'activePromos') + ['csrf' => $this->csrf()]);
    }

    public function approvePromotion(string $id): void
    {
        $this->requireAuth('colaborador');
        $this->verifyCsrf();

        $this->promotions->approve((int)$id, (int)currentUser()['id']);
        $this->logAction('approve_promotion', 'promotions', (int)$id);
        $this->json(['ok' => true]);
    }

    public function createGlobalEvent(): void
    {
        $this->requireAuth('colaborador');
        $this->verifyCsrf();

        $title = trim($_POST['title'] ?? '');
        if (!$title) { $this->flash('error', 'El título es requerido'); $this->redirect('colaborador/eventos'); }

        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ALLOWED_IMG_EXT, true) && $_FILES['image']['size'] <= MAX_FILE_SIZE) {
                $filename = bin2hex(random_bytes(16)) . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_PATH . '/' . $filename)) {
                    $image = $filename;
                }
            }
        }

        $this->promotions->insert([
            'business_id' => null,
            'user_id' => currentUser()['id'],
            'title' => $title,
            'description' => trim($_POST['description'] ?? ''),
            'image' => $image ?: null,
            'price' => null,
            'presale_price' => null,
            'conditions' => trim($_POST['conditions'] ?? ''),
            'public_url' => trim($_POST['public_url'] ?? ''),
            'type' => 'evento',
            'target_segment' => 'todos',
            'status' => 'active',
            'start_date' => $_POST['start_date'] ?? null,
            'end_date' => $_POST['end_date'] ?? null,
        ]);

        $this->flash('success', 'Evento público creado exitosamente.');
        $this->redirect('colaborador/eventos');
    }

    public function resetRatings(string $businessId): void
    {
        $this->requireAuth('colaborador');
        $this->verifyCsrf();

        $db = Database::getInstance();
        $db->execute('DELETE FROM reviews WHERE business_id = ?', [(int)$businessId]);
        $db->execute('UPDATE businesses SET rating = 0 WHERE id = ?', [(int)$businessId]);

        $this->logAction('reset_ratings', 'businesses', (int)$businessId);
        $this->json(['ok' => true]);
    }

    public function contactProvider(string $businessId): void
    {
        $this->requireAuth('colaborador');

        $business = $this->businesses->find((int)$businessId);
        if (!$business) { $this->json(['error' => 'not found'], 404); }

        $this->json([
            'ok' => true,
            'business' => [
                'name' => $business['name'],
                'whatsapp' => $business['whatsapp'] ? waLink($business['whatsapp']) : null,
                'email' => $business['email'],
                'phone' => $business['phone'],
            ]
        ]);
    }

    public function metrics(): void
    {
        $this->requireAuth('colaborador');

        $db = Database::getInstance();

        // Top 20, 50, 100 by category
        $topByCategory = $db->query(
            'SELECT c.name AS category, b.name, b.rating, b.visits, b.slug,
                    @rn := IF(@cat = c.name, @rn + 1, 1) AS rn,
                    @cat := c.name
             FROM businesses b
             JOIN categories c ON c.id = b.category_id, (SELECT @rn := 0, @cat := "") AS vars
             WHERE b.status = "published"
             ORDER BY c.name, b.rating DESC'
        )->fetchAll();

        // Most visited routes (by subcategory or trip_type)
        $topRoutes = $db->query(
            "SELECT tt.trip_type, COUNT(DISTINCT b.id) AS total_businesses,
                    SUM(b.visits) AS total_visits,
                    AVG(b.rating) AS avg_rating
             FROM business_trip_types tt
             JOIN businesses b ON b.id = tt.business_id
             WHERE b.status = 'published'
             GROUP BY tt.trip_type
             ORDER BY total_visits DESC"
        )->fetchAll();

        // Seasonal comparison
        $seasonalData = $db->query(
            "SELECT MONTH(a.created_at) AS month,
                    COUNT(*) AS total,
                    WEEK(a.created_at) AS week
             FROM analytics a
             WHERE a.event = 'map_view' AND a.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
             GROUP BY month, week
             ORDER BY month"
        )->fetchAll();

        // Most visited by trip type
        $byTripType = $db->query(
            "SELECT tt.trip_type,
                    COUNT(DISTINCT cp.contact_id) AS total_visitors
             FROM business_trip_types tt
             JOIN businesses b ON b.id = tt.business_id
             LEFT JOIN contacts c ON c.business_id = b.id
             LEFT JOIN contact_purchases cp ON cp.contact_id = c.id
             GROUP BY tt.trip_type
             ORDER BY total_visitors DESC"
        )->fetchAll();

        $this->view('colaborador.metrics', compact('topByCategory', 'topRoutes', 'seasonalData', 'byTripType'));
    }
}