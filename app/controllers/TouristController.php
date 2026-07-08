<?php
class TouristController extends Controller
{
    private BusinessModel $businesses;
    private PromotionModel $promotions;
    private EventModel $events;
    private UserModel $users;
    private NotificationModel $notifications;

    public function __construct()
    {
        $this->businesses = new BusinessModel();
        $this->promotions = new PromotionModel();
        $this->events = new EventModel();
        $this->users = new UserModel();
        $this->notifications = new NotificationModel();
    }

    public function dashboard(): void
    {
        $this->requireAuth('visitor');
        $user = currentUser();
        $user = $this->users->find((int)$user['id']) ?: $user;

        $topVisited = [];
        $activePromotions = [];
        $activeEvents = [];
        $visitedPlaces = [];
        $myReviews = [];

        try {
            $topVisited = $this->businesses->topVisited(10);
        } catch (Throwable $e) {
            error_log('Visitor dashboard topVisited error: ' . $e->getMessage());
        }

        try {
            $activePromotions = $this->promotions->active();
        } catch (Throwable $e) {
            error_log('Visitor dashboard promotions error: ' . $e->getMessage());
        }

        try {
            $activeEvents = array_merge($this->events->active(), $this->promotions->activeEvents());
        } catch (Throwable $e) {
            error_log('Visitor dashboard events error: ' . $e->getMessage());
        }

        try {
            $visitedPlaces = $this->businesses->visitedByUser((int)$user['id']);
            $myReviews = $this->businesses->reviewsByUser((int)$user['id']);
        } catch (Throwable $e) {
            error_log('Visitor dashboard data error: ' . $e->getMessage());
            $this->flash('warning', 'Tu panel de visitante está activo. Falta aplicar la migración de historial y reseñas para ver toda la información.');
        }

        try {
            $routePrefix = $this->pathForCurrentPrefix('');
            $this->view('tourist.dashboard', compact('user', 'topVisited', 'activePromotions', 'activeEvents', 'visitedPlaces', 'myReviews') + ['csrf' => $this->csrf(), 'routePrefix' => $routePrefix]);
        } catch (Throwable $e) {
            error_log('Visitor dashboard view error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            throw $e;
        }
    }

    public function profile(): void
    {
        $this->requireAuth('visitor');
        $user = currentUser();
        $user = $this->users->find((int)$user['id']) ?: $user;
        $routePrefix = $this->pathForCurrentPrefix('');

        $this->view('tourist.profile', compact('user', 'routePrefix') + ['csrf' => $this->csrf()]);
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
        $_SESSION['user']['phone'] = $whatsapp ?: ($fullUser['phone'] ?? '');

        $this->flash('success', 'Perfil actualizado correctamente.');
        $returnTo = $this->visitorReturnPath((string)($_POST['return_to'] ?? ''));
        if ($returnTo !== '') {
            $this->redirect($returnTo);
        }
        $this->redirectForCurrentPrefix('turista');
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
            $this->redirectForCurrentPrefix('turista');
        }

        $user = currentUser();
        $business = $this->businesses->find($businessId);
        if (!$business) {
            $this->flash('error', 'Negocio no encontrado.');
            $this->redirectForCurrentPrefix('turista');
        }

        $rating = min(5, max(1, $rating));
        try {
            $this->businesses->addReview($businessId, $user['name'], $comment, $rating, (int)$user['id']);
            $this->businesses->updateRating($businessId);
        } catch (Throwable $e) {
            error_log('Visitor review submit failed: ' . $e->getMessage());
            $this->flash('error', 'No se pudo guardar la valoracion. Revisa que la migracion de reseñas este aplicada.');
            $this->redirectForCurrentPrefix('turista');
        }

        try {
            if (!empty($business['user_id'])) {
                $this->notifications->create([
                    'user_id' => (int)$business['user_id'],
                    'business_id' => $businessId,
                    'type' => 'review',
                    'title' => 'Nueva valoracion de visitante',
                    'message' => "{$user['name']} califico tu negocio con {$rating}/5.",
                ]);
            }
        } catch (Throwable $e) {
            error_log('Visitor review notification skipped: ' . $e->getMessage());
        }

        $this->flash('success', 'Gracias, tu opinion se guardo correctamente.');
        $this->redirectForCurrentPrefix('turista');
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

    private function visitorReturnPath(string $returnTo): string
    {
        $returnTo = trim($returnTo);
        return preg_match('/^(landing\/)?turista(\/perfil)?$/', $returnTo) ? $returnTo : '';
    }
}
