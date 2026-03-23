<?php
class NotificationModel extends Model
{
    protected string $table = 'notifications';

    public function forUser(int $userId): array
    {
        return $this->query(
            'SELECT n.*, b.name AS business_name
             FROM notifications n
             LEFT JOIN businesses b ON b.id = n.business_id
             WHERE n.user_id = ?
             ORDER BY n.created_at DESC',
            [$userId]
        );
    }

    public function unreadCount(int $userId): int
    {
        $row = $this->queryOne(
            'SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = ? AND read_at IS NULL',
            [$userId]
        );
        return (int)($row['cnt'] ?? 0);
    }

    public function markRead(int $id, int $userId): void
    {
        $this->execute(
            'UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ?',
            [$id, $userId]
        );
    }

    public function markAllRead(int $userId): void
    {
        $this->execute(
            'UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL',
            [$userId]
        );
    }

    public function create(int $userId, string $title, string $message = '', string $type = 'system', ?int $businessId = null): void
    {
        $this->execute(
            'INSERT INTO notifications (user_id, business_id, type, title, message) VALUES (?,?,?,?,?)',
            [$userId, $businessId, $type, $title, $message]
        );
    }
}
