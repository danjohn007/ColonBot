<?php
class TouristController extends Controller
{
    private BusinessModel $businesses;
    private EmergencyModel $emergency;
    private TouristProfileModel $profiles;
    private PromotionModel $promotions;

    public function __construct()
    {
        $this->businesses = new BusinessModel();
        $this->emergency = new EmergencyModel();
        $this->profiles = new TouristProfileModel();
        $this->promotions = new PromotionModel();
    }

    public function dashboard(): void
    {
        // Allow access without login for public tourist page
        $user = currentUser();
        $profile = null;
        if ($user) {
            $profile = $this->profiles->findByUserId((int)$user['id']);
        }

        // Top visited & recommended
        $topVisited = $this->businesses->query(
            'SELECT b.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon
             FROM businesses b
             JOIN categories c ON c.id = b.category_id
             WHERE b.status = "published" AND b.is_open = 1
             ORDER BY b.visits DESC, b.rating DESC
             LIMIT 10'
        );

        $emergencyNumbers = $this->emergency->active();
        $activePromotions = $this->promotions->active();

        $this->view('tourist.dashboard', compact('user', 'profile', 'topVisited', 'emergencyNumbers', 'activePromotions') + ['csrf' => $this->csrf()]);
    }

    public function register(): void
    {
        $this->requireAuth('turista');
        $this->verifyCsrf();

        $user = currentUser();
        $name = trim($_POST['name'] ?? $user['name']);
        $whatsapp = trim($_POST['whatsapp'] ?? '');
        $email = trim($_POST['email'] ?? $user['email']);

        $profile = $this->profiles->createOrUpdate((int)$user['id'], $name, $whatsapp, $email);

        // Update user record too
        $userModel = new UserModel();
        $userModel->update((int)$user['id'], [
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

    public function emergency(): void
    {
        $this->requireAuth('turista');
        $numbers = $this->emergency->active();
        $this->json($numbers);
    }

    public function submitReview(): void
    {
        $this->requireAuth('turista');
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
        $this->requireAuth('turista');

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