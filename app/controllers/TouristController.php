<?php
class TouristController extends Controller
{
    private BusinessModel $businesses;
    private PromotionModel $promotions;
    private EventModel $events;
    private UserModel $users;

    public function __construct()
    {
        $this->businesses = new BusinessModel();
        $this->promotions = new PromotionModel();
        $this->events = new EventModel();
        $this->users = new UserModel();
    }

    public function dashboard(): void
    {
        $this->requireAuth('visitor');
        $user = currentUser();
        $user = $this->users->find((int)$user['id']) ?: $user;

        $topVisited = $this->businesses->topVisited(10);

        $activePromotions = $this->promotions->active();
        $activeEvents = $this->events->active();
        try {
            $visitedPlaces = $this->businesses->visitedByUser((int)$user['id']);
            $myReviews = $this->businesses->reviewsByUser((int)$user['id']);
        } catch (PDOException $e) {
            error_log('Visitor dashboard data error: ' . $e->getMessage());
            $visitedPlaces = [];
            $myReviews = [];
            $this->flash('warning', 'Tu panel de visitante está activo. Falta aplicar la migración de historial y reseñas para ver toda la información.');
        }

        $this->view('tourist.dashboard', compact('user', 'topVisited', 'activePromotions', 'activeEvents', 'visitedPlaces', 'myReviews') + ['csrf' => $this->csrf()]);
    }

    public function register(): void
    {
        $this->requireAuth('visitor');
        $this->verifyCsrf();

        $user = currentUser();
        $fullUser = $this->users->find((int)$user['id']) ?: $user;
        $name = trim($_POST['name'] ?? $user['name']);
        $whatsapp = trim($_POST['whatsapp'] ?? '');
        $email = trim($_POST['email'] ?? $user['email']);

        // Update user record
        $this->users->update((int)$user['id'], [
            'name' => $name,
            'email' => $email ?: $user['email'],
            'phone' => $whatsapp ?: ($fullUser['phone'] ?? ''),
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

        $this->businesses->addReview($businessId, $user['name'], $comment, min(5, max(1, $rating)), (int)$user['id']);
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
