<?php
class NotificationModel extends Model
{
    protected string $table = 'notifications';

    public function forUser(int $userId): array
    {
        try {
            return $this->query(
                'SELECT n.*, b.name AS business_name, e.title AS event_title
                 FROM notifications n
                 LEFT JOIN businesses b ON b.id = n.business_id
                 LEFT JOIN events e ON e.id = n.event_id
                 WHERE n.user_id = ?
                 ORDER BY n.created_at DESC',
                [$userId]
            );
        } catch (PDOException $e) {
            return $this->query(
                'SELECT n.*, b.name AS business_name, NULL AS event_title
                 FROM notifications n
                 LEFT JOIN businesses b ON b.id = n.business_id
                 WHERE n.user_id = ?
                 ORDER BY n.created_at DESC',
                [$userId]
            );
        }
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

    /**
     * Create a notification.
     * Supports both array parameter format and individual parameters for backward compatibility.
     */
    public function create(array|int $params, string $title = '', string $message = '', string $type = 'system', ?int $businessId = null, ?int $eventId = null): void
    {
        if (is_array($params)) {
            // Array format
            $userId = $params['user_id'] ?? 0;
            $title = $params['title'] ?? '';
            $message = $params['message'] ?? '';
            $type = $params['type'] ?? 'system';
            $businessId = $params['business_id'] ?? null;
            $eventId = $params['event_id'] ?? null;
            
            try {
                $this->execute(
                    'INSERT INTO notifications (user_id, business_id, event_id, type, title, message) VALUES (?,?,?,?,?,?)',
                    [$userId, $businessId, $eventId, $type, $title, $message]
                );
            } catch (PDOException $e) {
                $this->execute(
                    'INSERT INTO notifications (user_id, business_id, type, title, message) VALUES (?,?,?,?,?)',
                    [$userId, $businessId, $type, $title, $message]
                );
            }
        } else {
            // Individual parameters format (backward compatible)
            $userId = $params;
            $this->execute(
                'INSERT INTO notifications (user_id, business_id, type, title, message) VALUES (?,?,?,?,?)',
                [$userId, $businessId, $type, $title, $message]
            );
        }
    }
}
