<?php
class AnalyticsModel extends Model
{
    protected string $table = 'analytics';

    public function track(string $event, ?int $businessId = null): void
    {
        $this->execute(
            'INSERT INTO analytics (business_id, event, ip, user_agent) VALUES (?,?,?,?)',
            [
                $businessId,
                $event,
                $_SERVER['REMOTE_ADDR'] ?? null,
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            ]
        );
    }

    public function summary(): array
    {
        return [
            'total_events'      => (int)$this->db->query('SELECT COUNT(*) FROM analytics')->fetchColumn(),
            'map_views'         => (int)$this->db->query("SELECT COUNT(*) FROM analytics WHERE event='map_view'")->fetchColumn(),
            'whatsapp_clicks'   => (int)$this->db->query("SELECT COUNT(*) FROM analytics WHERE event='whatsapp_click'")->fetchColumn(),
            'chatbot_sessions'  => (int)$this->db->query("SELECT COUNT(*) FROM analytics WHERE event='chatbot_session'")->fetchColumn(),
            'directions_clicks' => (int)$this->db->query("SELECT COUNT(*) FROM analytics WHERE event='directions_click'")->fetchColumn(),
        ];
    }

    public function topBusinesses(int $limit = 10): array
    {
        return $this->query(
            'SELECT b.name, COUNT(a.id) AS visits
             FROM analytics a
             JOIN businesses b ON b.id = a.business_id
             WHERE a.business_id IS NOT NULL
             GROUP BY a.business_id
             ORDER BY visits DESC
             LIMIT ?',
            [$limit]
        );
    }

    public function dailyEvents(int $days = 30): array
    {
        return $this->query(
            'SELECT DATE(created_at) AS day, event, COUNT(*) AS total
             FROM analytics
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY day, event
             ORDER BY day ASC',
            [$days]
        );
    }
}
