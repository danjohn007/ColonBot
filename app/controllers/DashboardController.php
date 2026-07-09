<?php
class DashboardController extends Controller
{
    private UserModel      $users;
    private BusinessModel  $businesses;
    private CategoryModel  $categories;
    private AnalyticsModel $analytics;

    public function __construct()
    {
        $this->users      = new UserModel();
        $this->businesses = new BusinessModel();
        $this->categories = new CategoryModel();
        $this->analytics  = new AnalyticsModel();
    }

    public function index(): void
    {
        $this->requireAuth('superadmin');
        $summary     = $this->analytics->summary();
        $topBiz      = $this->analytics->topBusinesses(5);
        $dailyEvents = $this->analytics->dailyEvents(30);
        $userCount   = $this->users->count();
        $bizCount    = $this->businesses->count();
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
            'superadmin_top_sites'
        );
        $topByCategory = $this->metricRows(
            'SELECT c.name AS category, b.name, b.rating, b.visits, b.slug,
                    (
                      SELECT COUNT(*)
                      FROM businesses b2
                      WHERE b2.category_id = b.category_id
                        AND b2.status = "published"
                        AND (
                          b2.rating > b.rating
                          OR (b2.rating = b.rating AND b2.id <= b.id)
                        )
                    ) AS rn
             FROM businesses b
             JOIN categories c ON c.id = b.category_id
             WHERE b.status = "published"
             ORDER BY c.name, rn',
            'superadmin_top_by_category'
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
            'superadmin_recent_top_reviews'
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
            'superadmin_top_routes'
        );
        $newProviders = $this->metricRows(
            "SELECT b.id, b.name, b.slug, b.created_at, b.status, u.name AS owner_name,
                    c.name AS category_name
             FROM businesses b
             JOIN users u ON u.id = b.user_id
             JOIN categories c ON c.id = b.category_id
             ORDER BY b.created_at DESC
             LIMIT 50",
            'superadmin_new_providers'
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
            'superadmin_provider_visits'
        );
        $dailyVisits = $this->metricRows(
            "SELECT DATE(created_at) AS period, COUNT(*) AS total
             FROM analytics
             WHERE event = 'map_view' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY period
             ORDER BY period DESC",
            'superadmin_daily_visits'
        );
        $weeklyVisits = $this->metricRows(
            "SELECT YEAR(created_at) AS year, WEEK(created_at, 1) AS week, COUNT(*) AS total
             FROM analytics
             WHERE event = 'map_view' AND created_at >= DATE_SUB(NOW(), INTERVAL 16 WEEK)
             GROUP BY year, week
             ORDER BY year DESC, week DESC",
            'superadmin_weekly_visits'
        );
        $monthlyVisits = $this->metricRows(
            "SELECT YEAR(created_at) AS year, MONTH(created_at) AS month, COUNT(*) AS total
             FROM analytics
             WHERE event = 'map_view' AND created_at >= DATE_SUB(NOW(), INTERVAL 18 MONTH)
             GROUP BY year, month
             ORDER BY year DESC, month DESC",
            'superadmin_monthly_visits'
        );
        $seasonalData = $this->metricRows(
            "SELECT MONTH(a.created_at) AS month,
                    COUNT(*) AS total,
                    WEEK(a.created_at) AS week
             FROM analytics a
             WHERE a.event = 'map_view' AND a.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
             GROUP BY month, week
             ORDER BY month",
            'superadmin_seasonal_data'
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
            'superadmin_event_seasonality'
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
            'superadmin_routes_vs_tourism'
        );
        $topSitesByTripType = $this->metricRows(
            "SELECT tt.trip_type, b.name, c.name AS category_name, b.visits,
                    COALESCE(COUNT(a.id), 0) AS tracked_visits
             FROM business_trip_types tt
             JOIN businesses b ON b.id = tt.business_id
             JOIN categories c ON c.id = b.category_id
             LEFT JOIN analytics a ON a.business_id = b.id AND a.event = 'map_view'
             WHERE tt.trip_type IN ('familiar', 'pareja', 'adultos_mayores', 'amigos')
               AND b.status = 'published'
             GROUP BY tt.trip_type, b.id
             ORDER BY tt.trip_type, tracked_visits DESC, b.visits DESC
             LIMIT 100",
            'superadmin_top_sites_by_trip_type'
        );

        $this->view('dashboard.index', compact(
            'summary', 'topBiz', 'dailyEvents', 'userCount', 'bizCount',
            'topSites', 'topByCategory', 'recentTopReviews', 'topRoutes',
            'newProviders', 'providerVisits', 'dailyVisits', 'weeklyVisits',
            'monthlyVisits', 'seasonalData', 'eventSeasonality',
            'routesVsTourism', 'topSitesByTripType'
        ));
    }

    // ── Usuarios ──────────────────────────────────────────────────────────

    public function users(): void
    {
        $this->requireAuth('superadmin');
        $users = $this->users->all('name');
        $this->view('dashboard.users', compact('users', ) + ['csrf' => $this->csrf()]);
    }

    public function createUser(): void
    {
        $this->requireAuth('superadmin');
        $this->verifyCsrf();

        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $passwordConfirm = (string)($_POST['password_confirm'] ?? '');

        if (strlen($password) < 8) {
            $this->flash('error', 'La contraseña debe tener al menos 8 caracteres.');
            $this->redirect('superadmin/usuarios');
        }

        if ($password !== $passwordConfirm) {
            $this->flash('error', 'La confirmación de contraseña no coincide.');
            $this->redirect('superadmin/usuarios');
        }

        if ($this->users->findByEmail($email)) {
            $this->flash('error', 'El correo ya existe.');
            $this->redirect('superadmin/usuarios');
        }
        $id = $this->users->insert([
            'name'     => trim($_POST['name'] ?? ''),
            'email'    => $email,
            'password' => $this->users->hashPassword($password),
            'role'     => in_array($_POST['role'] ?? '', ['colaborador_admin','superadmin','prestador','visitor'], true) ? $_POST['role'] : 'visitor',
            'phone'    => trim($_POST['phone'] ?? ''),
            'active'   => 1,
        ]);
        $this->logAction('create_user', 'users', $id);
        $this->flash('success', 'Usuario creado.');
        $this->redirect('superadmin/usuarios');
    }

    public function updateUser(string $id): void
    {
        $this->requireAuth('superadmin');
        $this->verifyCsrf();

        $data = [
            'name'   => trim($_POST['name'] ?? ''),
            'role'   => in_array($_POST['role'] ?? '', ['colaborador_admin','superadmin','prestador','visitor'], true) ? $_POST['role'] : 'visitor',
            'active' => isset($_POST['active']) ? 1 : 0,
        ];
        if (!empty($_POST['password'])) {
            $data['password'] = $this->users->hashPassword($_POST['password']);
        }
        $this->users->update((int)$id, $data);
        $this->logAction('update_user', 'users', (int)$id);
        $this->flash('success', 'Usuario actualizado.');
        $this->redirect('superadmin/usuarios');
    }

    public function deleteUser(string $id): void
    {
        $this->requireAuth('superadmin');
        $this->verifyCsrf();

        // Prevent deleting yourself
        if ((int)$id === (int)(currentUser()['id'] ?? 0)) {
            $this->flash('error', 'No puedes eliminarte a ti mismo.');
            $this->redirect('superadmin/usuarios');
        }
        $this->users->delete((int)$id);
        $this->logAction('delete_user', 'users', (int)$id);
        $this->flash('success', 'Usuario eliminado.');
        $this->redirect('superadmin/usuarios');
    }

    // ── Negocios ──────────────────────────────────────────────────────────

    public function businesses(): void
    {
        $this->requireAuth('superadmin');
        $businesses = $this->businesses->allWithCategory();
        $this->view('dashboard.businesses', compact('businesses') + ['csrf' => $this->csrf()]);
    }

    public function approveBusiness(string $id): void
    {
        $this->requireAuth('superadmin');
        $this->verifyCsrf();
        $this->businesses->update((int)$id, ['status' => 'published']);
        $this->logAction('approve_business', 'businesses', (int)$id);
        $this->flash('success', 'Negocio aprobado.');
        $this->redirect('superadmin/negocios');
    }

    public function rejectBusiness(string $id): void
    {
        $this->requireAuth('superadmin');
        $this->verifyCsrf();
        $this->businesses->update((int)$id, ['status' => 'rejected']);
        $this->logAction('reject_business', 'businesses', (int)$id);
        $this->flash('error', 'Negocio rechazado.');
        $this->redirect('superadmin/negocios');
    }

    public function deleteBusiness(string $id): void
    {
        $this->requireAuth('superadmin');
        $this->verifyCsrf();
        $this->businesses->delete((int)$id);
        $this->logAction('delete_business', 'businesses', (int)$id);
        $this->flash('success', 'Negocio eliminado.');
        $this->redirect('superadmin/negocios');
    }

    // ── Categorías ────────────────────────────────────────────────────────

    public function categories(): void
    {
        $this->requireAuth('superadmin');
        $categories = $this->categories->all('sort_order');
        $this->view('dashboard.categories', compact('categories') + ['csrf' => $this->csrf()]);
    }

    public function createCategory(): void
    {
        $this->requireAuth('superadmin');
        $this->verifyCsrf();

        $name = trim($_POST['name'] ?? '');
        $id   = $this->categories->insert([
            'name'       => $name,
            'slug'       => slugify($name),
            'icon'       => trim($_POST['icon'] ?? 'map-pin'),
            'color'      => trim($_POST['color'] ?? '#3B82F6'),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'active'     => 1,
        ]);
        $this->logAction('create_category', 'categories', $id, $name);
        $this->flash('success', 'Categoría creada.');
        $this->redirect('superadmin/categorias');
    }

    public function updateCategory(string $id): void
    {
        $this->requireAuth('superadmin');
        $this->verifyCsrf();

        $name = trim($_POST['name'] ?? '');
        $this->categories->update((int)$id, [
            'name'       => $name,
            'slug'       => slugify($name),
            'icon'       => trim($_POST['icon'] ?? 'map-pin'),
            'color'      => trim($_POST['color'] ?? '#3B82F6'),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'active'     => isset($_POST['active']) ? 1 : 0,
        ]);
        $this->logAction('update_category', 'categories', (int)$id, $name);
        $this->flash('success', 'Categoría actualizada.');
        $this->redirect('superadmin/categorias');
    }

    public function deleteCategory(string $id): void
    {
        $this->requireAuth('superadmin');
        $this->verifyCsrf();
        $this->categories->delete((int)$id);
        $this->logAction('delete_category', 'categories', (int)$id);
        $this->flash('success', 'Categoría eliminada.');
        $this->redirect('superadmin/categorias');
    }

    // ── Analytics ─────────────────────────────────────────────────────────

    public function analytics(): void
    {
        $this->requireAuth('superadmin');
        $summary     = $this->analytics->summary();
        $topBiz      = $this->analytics->topBusinesses(10);
        $dailyEvents = $this->analytics->dailyEvents(30);
        $this->view('dashboard.analytics', compact('summary','topBiz','dailyEvents'));
    }

    // ── Logs ──────────────────────────────────────────────────────────────

    public function actionLog(): void
    {
        $this->requireAuth('superadmin');
        $db  = Database::getInstance();
        $logs = $db->query(
            'SELECT al.*, u.name AS user_name FROM action_log al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC LIMIT 200'
        )->fetchAll();
        $this->view('dashboard.action_log', compact('logs'));
    }

    public function errorLog(): void
    {
        $this->requireAuth('superadmin');
        $db   = Database::getInstance();
        $logs = $db->query(
            'SELECT * FROM error_log ORDER BY created_at DESC LIMIT 200'
        )->fetchAll();
        $this->view('dashboard.error_log', compact('logs'));
    }

    private function metricRows(string $sql, string $label): array
    {
        try {
            return Database::getInstance()->query($sql)->fetchAll();
        } catch (Throwable $e) {
            error_log("Dashboard metric {$label} skipped: " . $e->getMessage());
            return [];
        }
    }
}
