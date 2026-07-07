<?php
class TouristController extends Controller
{
    private BusinessModel $businesses;
    private PromotionModel $promotions;
    private UserModel $users;
    private NotificationModel $notifications;

    public function __construct()
    {
        $this->businesses = new BusinessModel();
        $this->promotions = new PromotionModel();
        $this->users = new UserModel();
        $this->notifications = new NotificationModel();
    }

    public function dashboard(): void
    {
        // Allow access without login for public tourist page
        $user = currentUser();

        // Top visited & recommended
        $topVisited = $this->businesses->query(
            'SELECT b.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon
             FROM businesses b
             JOIN categories c ON c.id = b.category_id
             WHERE b.status = "published" AND b.is_open = 1
             ORDER BY b.visits DESC, b.rating DESC
             LIMIT 10'
        );

        $activePromotions = $this->promotions->active();

        // C) Visit history for logged-in user
        $visitHistory = [];
        if ($user) {
            $db = Database::getInstance();
            $visitHistory = $db->query(
                'SELECT b.id, b.name, b.slug, a.created_at AS visited_at
                 FROM analytics a
                 JOIN businesses b ON b.id = a.business_id
                 WHERE a.user_id = ? AND a.event = "map_view"
                 ORDER BY a.created_at DESC
                 LIMIT 20',
                [(int)$user['id']]
            )->fetchAll();
        }

        // D) Notifications for visitor (promotions & events from prestadores and colaborador_admin)
        $notifications = [];
        if ($user) {
            $notifications = $this->notifications->byUser((int)$user['id']);
        }

        $this->view('tourist.dashboard', compact('user', 'topVisited', 'activePromotions', 'visitHistory', 'notifications') + ['csrf' => $this->csrf()]);
    }

    public function register(): void
    {
        $this->requireAuth('visitor');
        $this->verifyCsrf();

        $user = currentUser();
        $name = trim($_POST['name'] ?? $user['name']);
        $whatsapp = trim($_POST['whatsapp'] ?? '');
        $email = trim($_POST['email'] ?? $user['email']);

        // Update user record
        $this->users->update((int)$user['id'], [
            'name' => $name,
            'email' => $email ?: $user['email'],
            'phone' => $whatsapp ?: $user['phone'],
        ]);

        // Refresh session
        $_SESSION['user']['name'] = $name;
        if ($email) $_SESSION['user']['email'] = $email;

        $this->flash('success', 'Perfil actualizado correctamente.');
        $this->redirect('turista');
    }

    public function submitReview(): void
    {
        $this->requireAuth('visitor');
        $this->verifyCsrf();

        $businessId = (int)($_POST['business_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 5);
        $comment = trim($_POST['comment'] ?? '');

        if ($businessId <= 0) {
            $this->flash('error', 'Negocio no válido.');
            $this->redirect('turista');
        }

        $user = currentUser();
        $business = $this->businesses->find($businessId);
        if (!$business) {
            $this->flash('error', 'Negocio no encontrado.');
            $this->redirect('turista');
        }

        $this->businesses->addReview($businessId, $user['name'], $comment, min(5, max(1, $rating)));
        $this->businesses->updateRating($businessId);

        $this->flash('success', '¡Gracias por tu valoración!');
        $this->redirect('turista');
    }

    public function makeReservation(string $businessId): void
    {
        $this->requireAuth('visitor');

        $business = $this->businesses->find((int)$businessId);
        if (!$business) { $this->json(['error' => 'not found'], 404); }

        $whatsapp = $business['whatsapp'] ?: $business['phone'];
        if (!$whatsapp) { $this->json(['error' => 'El negocio no tiene WhatsApp configurado'], 422); }

        $user = currentUser();
        $msg = urlencode("Hola, soy {$user['name']} y quiero hacer una reservación en su negocio.");

        $this->json([
            'ok' => true,
            'url' => waLink($whatsapp, $msg),
        ]);
    }
}