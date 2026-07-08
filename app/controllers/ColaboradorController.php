<?php
class ColaboradorController extends Controller
{
    private BusinessModel $businesses;
    private PromotionModel $promotions;
    private EventModel $eventModel;
    private NotificationModel $notifications;
    private AnalyticsModel $analytics;
    private UserModel $users;

    public function __construct()
    {
        $this->businesses = new BusinessModel();
        $this->promotions = new PromotionModel();
        $this->eventModel = new EventModel();
        $this->notifications = new NotificationModel();
        $this->analytics = new AnalyticsModel();
        $this->users = new UserModel();
    }

    public function dashboard(): void
    {
        $this->requireAuth('colaborador_admin');

        // Metrics
        $totalBiz = $this->safeCount('businesses');
        $totalUsers = $this->safeCount('users');
        $pendingPromos = $this->safeRows(
            'SELECT p.*, u.name AS creator_name, b.name AS business_name
             FROM promotions p
             LEFT JOIN users u ON u.id = p.user_id
             LEFT JOIN businesses b ON b.id = p.business_id
             WHERE p.status = "pending"
             ORDER BY p.created_at ASC',
            'dashboard_pending_promos'
        );
        $summary = $this->safeSummary();
        $topBusinesses = $this->safeRows(
            'SELECT b.name, COUNT(a.id) AS visits
             FROM analytics a
             JOIN businesses b ON b.id = a.business_id
             WHERE a.business_id IS NOT NULL
             GROUP BY a.business_id
             ORDER BY visits DESC
             LIMIT 50',
            'dashboard_top_businesses'
        );
        $dailyEvents = $this->safeRows(
            'SELECT DATE(created_at) AS day, event, COUNT(*) AS total
             FROM analytics
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY day, event
             ORDER BY day ASC',
            'dashboard_daily_events'
        );

        // Top visited this month
        $topByCategory = $this->safeRows(
            'SELECT ranked.category, ranked.name, ranked.rating, ranked.visits, ranked.rn
             FROM (
                SELECT c.name AS category, b.name, b.rating, b.visits,
                       @rn := IF(@cat = c.name, @rn + 1, 1) AS rn,
                       @cat := c.name
                FROM businesses b
                JOIN categories c ON c.id = b.category_id
                CROSS JOIN (SELECT @rn := 0, @cat := "") vars
                WHERE b.status = "published"
                ORDER BY c.name, b.rating DESC
             ) ranked
             ORDER BY ranked.category, ranked.rn',
            'dashboard_top_by_category'
        );

        // New providers this month
        $newProviders = $this->safeRows(
            "SELECT b.*, u.name AS owner_name FROM businesses b
             JOIN users u ON u.id = b.user_id
             WHERE b.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
             ORDER BY b.created_at DESC",
            'dashboard_new_providers'
        );

        $providers = $this->safeRows(
            "SELECT b.*, u.name AS owner_name, u.email AS owner_email
             FROM businesses b
             JOIN users u ON u.id = b.user_id
             ORDER BY b.name ASC",
            'dashboard_providers'
        );

        // Visits per day/week/month
        $visitsByDay = $this->safeRows(
            "SELECT DATE(created_at) AS day, COUNT(*) AS total
             FROM analytics
             WHERE event = 'map_view' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY day ORDER BY day",
            'dashboard_visits_by_day'
        );

        $visitsByWeek = $this->safeRows(
            "SELECT WEEK(created_at) AS week, COUNT(*) AS total
             FROM analytics
             WHERE event = 'map_view' AND created_at >= DATE_SUB(NOW(), INTERVAL 4 WEEK)
             GROUP BY week ORDER BY week",
            'dashboard_visits_by_week'
        );

        $this->view('colaborador.dashboard', compact(
            'totalBiz', 'totalUsers', 'pendingPromos', 'summary',
            'topBusinesses', 'dailyEvents', 'topByCategory',
            'newProviders', 'providers', 'visitsByDay', 'visitsByWeek'
        ) + ['csrf' => $this->csrf()]);
    }

    public function events(): void
    {
        $this->requireAuth('colaborador_admin');

        $pendingPromos = $this->promotions->pendingForApproval();
        $pendingEvents = $this->eventModel->pendingForApproval();
        $pendingBotEvents = $this->eventModel->pendingBotAuthorization();
        $activePromos = array_merge($this->eventModel->active(), $this->promotions->active(), $this->promotions->activeEvents());
        $routePrefix = $this->pathForCurrentPrefix('');

        $this->view('colaborador.events', compact('pendingPromos', 'pendingEvents', 'pendingBotEvents', 'activePromos', 'routePrefix') + ['csrf' => $this->csrf()]);
    }

    public function approvePromotion(string $id): void
    {
        $this->requireAuth('colaborador_admin');
        $this->verifyCsrf();

        $this->promotions->approve((int)$id, (int)currentUser()['id']);
        $this->logAction('approve_promotion', 'promotions', (int)$id);
        $this->json(['ok' => true]);
    }

    public function createGlobalEvent(): void
    {
        $this->requireAuth('colaborador_admin');
        $this->verifyCsrf();

        $title = trim($_POST['title'] ?? '');
        if (!$title) {
            $this->flash('error', 'El titulo es requerido');
            $this->redirectForCurrentPrefix('colaborador/eventos');
            return;
        }

        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = $this->saveUpload($_FILES['image']);
        }

        $user = currentUser();
        $id = $this->eventModel->insert([
            'business_id' => null,
            'user_id' => (int)$user['id'],
            'title' => $title,
            'description' => trim($_POST['description'] ?? ''),
            'image' => $image ?: null,
            'price' => $_POST['price'] !== '' ? (float)$_POST['price'] : null,
            'presale_price' => $_POST['presale_price'] !== '' ? (float)$_POST['presale_price'] : null,
            'capacity' => $_POST['capacity'] !== '' ? (int)$_POST['capacity'] : null,
            'location' => trim($_POST['location'] ?? ''),
            'validity' => trim($_POST['validity'] ?? ''),
            'conditions' => trim($_POST['conditions'] ?? ''),
            'event_type' => 'publico',
            'target_segment' => 'todos',
            'status' => 'active',
            'approved_by' => (int)$user['id'],
            'bot_authorized' => 1,
            'bot_authorized_by' => (int)$user['id'],
            'bot_authorized_at' => date('Y-m-d H:i:s'),
            'start_date' => $this->dateTimeOrNull($_POST['start_date'] ?? null),
            'end_date' => $this->dateTimeOrNull($_POST['end_date'] ?? null),
            'presale_start' => $this->dateTimeOrNull($_POST['presale_start'] ?? null),
            'presale_end' => $this->dateTimeOrNull($_POST['presale_end'] ?? null),
        ]);

        $publicUrl = $this->eventModel->generatePublicUrl($id);
        $this->eventModel->update($id, ['public_url' => $publicUrl]);

        foreach ($this->users->visitors() as $visitor) {
            $this->notifications->create([
                'user_id' => (int)$visitor['id'],
                'event_id' => $id,
                'type' => 'system',
                'title' => 'Nuevo evento disponible',
                'message' => "Ya puedes consultar el evento \"{$title}\": {$publicUrl}",
            ]);
        }

        $this->logAction('create_public_event', 'events', $id, $title);
        $this->flash('success', 'Evento publico creado y autorizado para chatbot.');
        $this->redirectForCurrentPrefix('colaborador/eventos');
        return;
        /*
        if (!$title) { $this->flash('error', 'El título es requerido'); $this->redirect('colaborador/eventos'); }

        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ALLOWED_IMG_EXT, true) && $_FILES['image']['size'] <= MAX_FILE_SIZE) {
                $filename = bin2hex(random_bytes(16)) . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_PATH . '/' . $filename)) {
                    $image = $filename;
                }
            }
        }

        $publicUrl = trim($_POST['public_url'] ?? '');
        $id = $this->eventModel->insert([
            'business_id' => null,
            'user_id' => currentUser()['id'],
            'title' => $title,
            'description' => trim($_POST['description'] ?? ''),
            'image' => $image ?: null,
            'price' => null,
            'presale_price' => null,
            'conditions' => trim($_POST['conditions'] ?? ''),
            'public_url' => $publicUrl ?: null,
            'type' => 'evento',
            'target_segment' => 'todos',
            'status' => 'active',
            'start_date' => $_POST['start_date'] ?? null,
            'end_date' => $_POST['end_date'] ?? null,
        ]);

        if ($publicUrl === '') {
            $this->promotions->update($id, ['public_url' => BASE_URL . '/promocion/' . $id]);
        }

        foreach ($this->users->visitors() as $visitor) {
            $this->notifications->create([
                'user_id' => (int)$visitor['id'],
                'type' => 'system',
                'title' => 'Nuevo evento disponible',
                'message' => "Ya puedes consultar el evento \"{$title}\" en tu panel de visitante.",
            ]);
        }

        $this->flash('success', 'Evento público creado exitosamente.');
        $this->redirect('colaborador/eventos');
        */
    }

    public function resetRatings(string $businessId): void
    {
        $this->requireAuth('colaborador_admin');
        $this->verifyCsrf();

        $business = $this->businesses->find((int)$businessId);
        if (!$business) { $this->json(['error' => 'not found'], 404); }

        $db = Database::getInstance();
        try {
            $stmt = $db->prepare('DELETE FROM reviews WHERE business_id = ?');
            $stmt->execute([(int)$businessId]);
        } catch (Throwable $e) {
            error_log('Reset ratings reviews delete skipped: ' . $e->getMessage());
        }
        $stmt = $db->prepare('UPDATE businesses SET rating = 0 WHERE id = ?');
        $stmt->execute([(int)$businessId]);

        $this->logAction('reset_ratings', 'businesses', (int)$businessId, $business['name']);
        $this->json(['ok' => true]);
    }

    public function contactProvider(string $businessId): void
    {
        $this->requireAuth('colaborador_admin');

        $business = $this->businesses->find((int)$businessId);
        if (!$business) { $this->json(['error' => 'not found'], 404); }

        $channel = in_array($_GET['channel'] ?? '', ['whatsapp', 'email'], true) ? $_GET['channel'] : 'contact';
        $message = trim($_GET['message'] ?? 'Contacto directo con prestador desde panel colaborador.');
        $this->logAction('contact_provider_' . $channel, 'businesses', (int)$businessId, $message . ' - ' . $business['name']);

        $this->json([
            'ok' => true,
            'business' => [
                'name' => $business['name'],
                'whatsapp' => $business['whatsapp'] ? waLink($business['whatsapp']) : null,
                'email' => $business['email'],
                'phone' => $business['phone'],
                'email_url' => $business['email'] ? 'mailto:' . $business['email'] . '?subject=' . rawurlencode('Contacto de Direccion de Turismo') : null,
            ]
        ]);
    }

    public function metrics(): void
    {
        $this->requireAuth('colaborador_admin');

        $topSites = $this->metricRows(
            "SELECT b.id, b.name, b.slug, c.name AS category, b.rating, b.visits,
                    COALESCE(COUNT(a.id), 0) AS tracked_visits
             FROM businesses b
             JOIN categories c ON c.id = b.category_id
             LEFT JOIN analytics a ON a.business_id = b.id AND a.event = 'map_view'
             WHERE b.status = 'published'
             GROUP BY b.id
             ORDER BY tracked_visits DESC, b.visits DESC
             LIMIT 100",
            'top_sites'
        );

        $topByCategory = $this->metricRows(
            'SELECT c.name AS category, b.name, b.rating, b.visits, b.slug,
                    @rn := IF(@cat = c.name, @rn + 1, 1) AS rn,
                    @cat := c.name
             FROM businesses b
             JOIN categories c ON c.id = b.category_id, (SELECT @rn := 0, @cat := "") AS vars
             WHERE b.status = "published"
             ORDER BY c.name, b.rating DESC',
            'top_by_category'
        );

        $recentTopReviews = $this->metricRows(
            "SELECT r.rating, r.comment, r.created_at, b.name AS business_name,
                    b.slug AS business_slug, c.name AS category_name
             FROM reviews r
             JOIN businesses b ON b.id = r.business_id
             JOIN categories c ON c.id = b.category_id
             WHERE r.rating >= 4
             ORDER BY r.created_at DESC
             LIMIT 30",
            'recent_top_reviews'
        );

        $topRoutes = $this->metricRows(
            "SELECT tt.trip_type, COUNT(DISTINCT b.id) AS total_businesses,
                    COALESCE(SUM(route_views.total), 0) AS total_visits,
                    AVG(b.rating) AS avg_rating
             FROM business_trip_types tt
             JOIN businesses b ON b.id = tt.business_id
             LEFT JOIN (
                SELECT business_id, COUNT(*) AS total
                FROM analytics
                WHERE event = 'map_view'
                GROUP BY business_id
             ) route_views ON route_views.business_id = b.id
             WHERE b.status = 'published'
             GROUP BY tt.trip_type
             ORDER BY total_visits DESC",
            'top_routes'
        );

        $newProviders = $this->metricRows(
            "SELECT b.id, b.name, b.slug, b.created_at, b.status, u.name AS owner_name,
                    c.name AS category_name
             FROM businesses b
             JOIN users u ON u.id = b.user_id
             JOIN categories c ON c.id = b.category_id
             ORDER BY b.created_at DESC
             LIMIT 50",
            'new_providers'
        );

        $providerVisits = $this->metricRows(
            "SELECT b.id, b.name,
                    SUM(CASE WHEN DATE(a.created_at) = CURDATE() THEN 1 ELSE 0 END) AS today,
                    SUM(CASE WHEN YEARWEEK(a.created_at, 1) = YEARWEEK(CURDATE(), 1) THEN 1 ELSE 0 END) AS this_week,
                    SUM(CASE WHEN YEAR(a.created_at) = YEAR(CURDATE()) AND MONTH(a.created_at) = MONTH(CURDATE()) THEN 1 ELSE 0 END) AS this_month,
                    COUNT(a.id) AS total
             FROM businesses b
             LEFT JOIN analytics a ON a.business_id = b.id AND a.event = 'map_view'
             GROUP BY b.id
             ORDER BY total DESC, b.name ASC
             LIMIT 100",
            'provider_visits'
        );

        $dailyVisits = $this->metricRows(
            "SELECT DATE(created_at) AS period, COUNT(*) AS total
             FROM analytics
             WHERE event = 'map_view' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY period
             ORDER BY period DESC",
            'daily_visits'
        );

        $weeklyVisits = $this->metricRows(
            "SELECT YEAR(created_at) AS year, WEEK(created_at, 1) AS week, COUNT(*) AS total
             FROM analytics
             WHERE event = 'map_view' AND created_at >= DATE_SUB(NOW(), INTERVAL 16 WEEK)
             GROUP BY year, week
             ORDER BY year DESC, week DESC",
            'weekly_visits'
        );

        $monthlyVisits = $this->metricRows(
            "SELECT YEAR(created_at) AS year, MONTH(created_at) AS month, COUNT(*) AS total
             FROM analytics
             WHERE event = 'map_view' AND created_at >= DATE_SUB(NOW(), INTERVAL 18 MONTH)
             GROUP BY year, month
             ORDER BY year DESC, month DESC",
            'monthly_visits'
        );

        $seasonalData = $this->metricRows(
            "SELECT MONTH(a.created_at) AS month,
                    COUNT(*) AS total,
                    WEEK(a.created_at) AS week
             FROM analytics a
             WHERE a.event = 'map_view' AND a.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
             GROUP BY month, week
             ORDER BY month",
            'seasonal_data'
        );

        $eventSeasonality = $this->metricRows(
            "SELECT source, month, COUNT(*) AS total
             FROM (
                SELECT 'evento' AS source, MONTH(start_date) AS month
                FROM events
                WHERE start_date IS NOT NULL
                UNION ALL
                SELECT type AS source, MONTH(start_date) AS month
                FROM promotions
                WHERE start_date IS NOT NULL
             ) x
             GROUP BY source, month
             ORDER BY month, source",
            'event_seasonality'
        );

        $byTripType = $this->metricRows(
            "SELECT tt.trip_type,
                    COUNT(DISTINCT b.id) AS total_businesses,
                    COALESCE(SUM(route_views.total), 0) AS total_visitors
             FROM business_trip_types tt
             JOIN businesses b ON b.id = tt.business_id
             LEFT JOIN (
                SELECT business_id, COUNT(*) AS total
                FROM analytics
                WHERE event = 'map_view'
                GROUP BY business_id
             ) route_views ON route_views.business_id = b.id
             GROUP BY tt.trip_type
             ORDER BY total_visitors DESC",
            'by_trip_type'
        );

        $routesVsTourism = $this->metricRows(
            "SELECT tt.trip_type, c.name AS category_name,
                    COUNT(DISTINCT b.id) AS total_businesses,
                    COALESCE(SUM(route_views.total), 0) AS total_visits,
                    AVG(b.rating) AS avg_rating
             FROM business_trip_types tt
             JOIN businesses b ON b.id = tt.business_id
             JOIN categories c ON c.id = b.category_id
             LEFT JOIN (
                SELECT business_id, COUNT(*) AS total
                FROM analytics
                WHERE event = 'map_view'
                GROUP BY business_id
             ) route_views ON route_views.business_id = b.id
             GROUP BY tt.trip_type, c.id
             ORDER BY tt.trip_type, total_visits DESC",
            'routes_vs_tourism'
        );

        $this->view('colaborador.metrics', compact(
            'topSites', 'topByCategory', 'recentTopReviews', 'topRoutes',
            'newProviders', 'providerVisits', 'dailyVisits', 'weeklyVisits',
            'monthlyVisits', 'seasonalData', 'eventSeasonality', 'byTripType',
            'routesVsTourism'
        ));
    }

    private function metricRows(string $sql, string $label): array
    {
        return $this->safeRows($sql, "Colaborador metric {$label}");
    }

    private function dateTimeOrNull(?string $value): ?string
    {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }

        return str_replace('T', ' ', $value);
    }

    private function saveUpload(array $file): ?string
    {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_IMG_EXT, true) || $file['size'] > MAX_FILE_SIZE) {
            return null;
        }

        if (!is_dir(UPLOAD_PATH)) {
            mkdir(UPLOAD_PATH, 0755, true);
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest = UPLOAD_PATH . '/' . $filename;

        return move_uploaded_file($file['tmp_name'], $dest) ? $filename : null;
    }

    private function safeRows(string $sql, string $label): array
    {
        try {
            return Database::getInstance()->query($sql)->fetchAll();
        } catch (Throwable $e) {
            error_log("Colaborador query {$label} skipped: " . $e->getMessage());
            return [];
        }
    }

    private function safeCount(string $table): int
    {
        if (!preg_match('/^[a-z_]+$/', $table)) {
            return 0;
        }

        try {
            return (int)Database::getInstance()->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
        } catch (Throwable $e) {
            error_log("Colaborador count {$table} skipped: " . $e->getMessage());
            return 0;
        }
    }

    private function safeSummary(): array
    {
        return [
            'total_events'      => $this->safeScalar('SELECT COUNT(*) FROM analytics', 'summary_total_events'),
            'map_views'         => $this->safeScalar("SELECT COUNT(*) FROM analytics WHERE event='map_view'", 'summary_map_views'),
            'whatsapp_clicks'   => $this->safeScalar("SELECT COUNT(*) FROM analytics WHERE event='whatsapp_click'", 'summary_whatsapp_clicks'),
            'chatbot_sessions'  => $this->safeScalar('SELECT COUNT(*) FROM chatbot_sessions', 'summary_chatbot_sessions'),
            'directions_clicks' => $this->safeScalar("SELECT COUNT(*) FROM analytics WHERE event='directions_click'", 'summary_directions_clicks'),
        ];
    }

    private function safeScalar(string $sql, string $label): int
    {
        try {
            return (int)Database::getInstance()->query($sql)->fetchColumn();
        } catch (Throwable $e) {
            error_log("Colaborador scalar {$label} skipped: " . $e->getMessage());
            return 0;
        }
    }
}
