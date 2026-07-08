<?php
class EventModel extends Model
{
    protected string $table = 'events';

    public function byBusiness(int $businessId): array
    {
        try {
            return $this->query(
                'SELECT e.*, u.name AS creator_name, a.name AS approver_name,
                        b.name AS business_name
                 FROM events e
                 LEFT JOIN users u ON u.id = e.user_id
                 LEFT JOIN users a ON a.id = e.approved_by
                 LEFT JOIN businesses b ON b.id = e.business_id
                 WHERE e.business_id = ?
                 ORDER BY e.created_at DESC',
                [$businessId]
            );
        } catch (PDOException $e) {
            return $this->legacyByBusiness($businessId);
        }
    }

    public function byUser(int $userId): array
    {
        try {
            return $this->query(
                'SELECT e.*, u.name AS creator_name, a.name AS approver_name,
                        b.name AS business_name
                 FROM events e
                 LEFT JOIN users u ON u.id = e.user_id
                 LEFT JOIN users a ON a.id = e.approved_by
                 LEFT JOIN businesses b ON b.id = e.business_id
                 WHERE e.user_id = ?
                 ORDER BY e.created_at DESC',
                [$userId]
            );
        } catch (PDOException $e) {
            return [];
        }
    }

    public function pendingForApproval(): array
    {
        try {
            return $this->query(
                "SELECT e.*, u.name AS creator_name, b.name AS business_name
                 FROM events e
                 LEFT JOIN users u ON u.id = e.user_id
                 LEFT JOIN businesses b ON b.id = e.business_id
                 WHERE e.status = 'pending'
                 ORDER BY e.created_at ASC"
            );
        } catch (PDOException $e) {
            return [];
        }
    }

    public function pendingBotAuthorization(): array
    {
        try {
            return $this->query(
                "SELECT e.*, u.name AS creator_name, b.name AS business_name
                 FROM events e
                 LEFT JOIN users u ON u.id = e.user_id
                 LEFT JOIN businesses b ON b.id = e.business_id
                 WHERE e.status IN ('active', 'approved') AND e.bot_authorized = 0
                 ORDER BY e.created_at ASC"
            );
        } catch (PDOException $e) {
            return [];
        }
    }

    public function approve(int $id, int $approvedBy): void
    {
        $this->update($id, [
            'status' => 'active',
            'approved_by' => $approvedBy,
        ]);
    }

    public function authorizeBot(int $id, int $authorizedBy): void
    {
        $this->update($id, [
            'bot_authorized' => 1,
            'bot_authorized_by' => $authorizedBy,
            'bot_authorized_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function active(): array
    {
        try {
            return $this->query(
                "SELECT e.*, b.name AS business_name, b.slug AS business_slug,
                        b.address, b.whatsapp, b.phone, b.lat, b.lng,
                        b.google_maps_link, b.name AS business_name_full
                 FROM events e
                 LEFT JOIN businesses b ON b.id = e.business_id
                 WHERE e.status IN ('active', 'approved')
                 AND (e.end_date IS NULL OR e.end_date >= NOW())
                 ORDER BY e.start_date ASC"
            );
        } catch (PDOException $e) {
            return $this->legacyActive();
        }
    }

    public function activeForChatbot(): array
    {
        try {
            return $this->query(
                "SELECT e.*, b.name AS business_name, b.slug AS business_slug,
                        b.address, b.whatsapp, b.phone
                 FROM events e
                 LEFT JOIN businesses b ON b.id = e.business_id
                 WHERE e.status IN ('active', 'approved')
                 AND e.bot_authorized = 1
                 AND (e.end_date IS NULL OR e.end_date >= NOW())
                 ORDER BY e.start_date ASC"
            );
        } catch (PDOException $e) {
            return $this->legacyActive();
        }
    }

    public function allWithBusiness(): array
    {
        return $this->query(
            'SELECT e.*, b.name AS business_name, b.slug AS business_slug,
                    b.address, b.whatsapp, b.phone, b.lat, b.lng,
                    b.google_maps_link
             FROM events e
             LEFT JOIN businesses b ON b.id = e.business_id
             ORDER BY e.created_at DESC'
        );
    }

    public function generatePublicUrl(int $id): string
    {
        $event = $this->find($id);
        if (!$event) return '';

        $slug = slugify($event['title'] ?? $event['name'] ?? 'evento');
        return BASE_URL . '/evento/' . $id . '/' . $slug;
    }

    private function legacyByBusiness(int $businessId): array
    {
        try {
            return $this->query(
                "SELECT e.*, e.name AS title, e.date AS start_date, e.date AS date,
                        NULL AS end_date, NULL AS image, NULL AS public_url,
                        'active' AS status, NULL AS creator_name, NULL AS approver_name,
                        b.name AS business_name
                 FROM events e
                 LEFT JOIN businesses b ON b.id = e.business_id
                 WHERE e.business_id = ?
                 ORDER BY e.date DESC",
                [$businessId]
            );
        } catch (PDOException $e) {
            error_log('Legacy business events skipped: ' . $e->getMessage());
            return [];
        }
    }

    private function legacyActive(): array
    {
        try {
            return $this->query(
                "SELECT e.*, e.name AS title, e.name,
                        e.date AS start_date, e.date AS date,
                        NULL AS end_date, NULL AS image, NULL AS public_url,
                        'active' AS status, b.name AS business_name, b.slug AS business_slug,
                        b.address, b.whatsapp, b.phone, b.lat, b.lng,
                        b.google_maps_link, b.name AS business_name_full
                 FROM events e
                 LEFT JOIN businesses b ON b.id = e.business_id
                 WHERE e.date IS NULL OR e.date >= NOW()
                 ORDER BY e.date ASC"
            );
        } catch (PDOException $e) {
            error_log('Legacy events skipped: ' . $e->getMessage());
            return [];
        }
    }
}
