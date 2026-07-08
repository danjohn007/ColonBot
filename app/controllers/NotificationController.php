<?php
class NotificationController extends Controller
{
    private NotificationModel $notifications;

    public function __construct()
    {
        $this->notifications = new NotificationModel();
    }

    public function index(): void
    {
        $this->requireAuth(); // Permite cualquier usuario loggeado (visitante, prestador, colaborador_admin, superadmin)
        $user          = currentUser();
        $notifications = $this->notifications->forUser((int)$user['id']);
        $unread        = $this->notifications->unreadCount((int)$user['id']);
        $csrf          = $this->csrf();
        $routePrefix   = $this->pathForCurrentPrefix('');
        $this->view('notifications.index', compact('notifications', 'user', 'unread', 'csrf', 'routePrefix'));
    }

    public function markRead(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $user = currentUser();
        $this->notifications->markRead((int)$id, (int)$user['id']);
        $this->json(['ok' => true]);
    }

    public function markAllRead(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $user = currentUser();
        $this->notifications->markAllRead((int)$user['id']);
        $this->redirectForCurrentPrefix('admin/notificaciones');
    }
}
