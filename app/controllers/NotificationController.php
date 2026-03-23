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
        $this->requireAuth('admin');
        $user          = currentUser();
        $notifications = $this->notifications->forUser((int)$user['id']);
        $unread        = $this->notifications->unreadCount((int)$user['id']);
        $csrf          = $this->csrf();
        $this->view('notifications.index', compact('notifications', 'user', 'unread', 'csrf'));
    }

    public function markRead(string $id): void
    {
        $this->requireAuth('admin');
        $this->verifyCsrf();
        $user = currentUser();
        $this->notifications->markRead((int)$id, (int)$user['id']);
        $this->json(['ok' => true]);
    }

    public function markAllRead(): void
    {
        $this->requireAuth('admin');
        $this->verifyCsrf();
        $user = currentUser();
        $this->notifications->markAllRead((int)$user['id']);
        $this->redirect('admin/notificaciones');
    }
}
