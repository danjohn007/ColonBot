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
        $this->view('dashboard.index', compact('summary','topBiz','dailyEvents','userCount','bizCount'));
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
        if ($this->users->findByEmail($email)) {
            $this->flash('error', 'El correo ya existe.');
            $this->redirect('superadmin/usuarios');
        }
        $id = $this->users->insert([
            'name'     => trim($_POST['name'] ?? ''),
            'email'    => $email,
            'password' => $this->users->hashPassword($_POST['password'] ?? ''),
            'role'     => in_array($_POST['role'] ?? '', ['admin','superadmin'], true) ? $_POST['role'] : 'admin',
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
            'role'   => in_array($_POST['role'] ?? '', ['admin','superadmin'], true) ? $_POST['role'] : 'admin',
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
}
