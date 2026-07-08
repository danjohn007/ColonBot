<?php
class EventModel extends Model
{
    protected string $table = 'events';
    private ?array $columns = null;

    public function find(int $id): ?array
    {
        $row = parent::find($id);
        return $row ? $this->normalizeRow($row) : null;
    }

    public function insert(array $data): int
    {
        return parent::insert($this->filterWritableData($data));
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->filterWritableData($data);
        if (empty($data)) {
            return true;
        }

        return parent::update($id, $data);
    }

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
                        NULL AS presale_price, NULL AS capacity, NULL AS location,
                        NULL AS whatsapp, NULL AS validity, NULL AS conditions, 'privado' AS event_type,
                        0 AS bot_authorized, 'active' AS status,
                        NULL AS creator_name, NULL AS approver_name,
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
                        NULL AS presale_price, NULL AS capacity, NULL AS location,
                        NULL AS whatsapp, NULL AS validity, NULL AS conditions, 'privado' AS event_type,
                        0 AS bot_authorized, 'active' AS status,
                        b.name AS business_name, b.slug AS business_slug,
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

    private function filterWritableData(array $data): array
    {
        if ($this->hasColumn('name') && !isset($data['name']) && isset($data['title'])) {
            $data['name'] = $data['title'];
        }

        if ($this->hasColumn('date') && !isset($data['date']) && array_key_exists('start_date', $data)) {
            $data['date'] = $data['start_date'];
        }

        return array_intersect_key($data, array_flip($this->columns()));
    }

    private function normalizeRow(array $row): array
    {
        $row['title'] = $row['title'] ?? $row['name'] ?? '';
        $row['name'] = $row['name'] ?? $row['title'];
        $row['start_date'] = $row['start_date'] ?? $row['date'] ?? null;
        $row['date'] = $row['date'] ?? $row['start_date'];
        $row['end_date'] = $row['end_date'] ?? null;
        $row['image'] = $row['image'] ?? null;
        $row['public_url'] = $row['public_url'] ?? null;
        $row['presale_price'] = $row['presale_price'] ?? null;
        $row['capacity'] = $row['capacity'] ?? null;
        $row['location'] = $row['location'] ?? null;
        $row['whatsapp'] = $row['whatsapp'] ?? null;
        $row['validity'] = $row['validity'] ?? null;
        $row['conditions'] = $row['conditions'] ?? null;
        $row['event_type'] = $row['event_type'] ?? 'privado';
        $row['bot_authorized'] = $row['bot_authorized'] ?? 0;
        $row['status'] = $row['status'] ?? 'active';

        return $row;
    }

    private function hasColumn(string $column): bool
    {
        return in_array($column, $this->columns(), true);
    }

    private function columns(): array
    {
        if ($this->columns !== null) {
            return $this->columns;
        }

        try {
            $rows = $this->query('SHOW COLUMNS FROM `events`');
            $this->columns = array_column($rows, 'Field');
        } catch (PDOException $e) {
            error_log('Events column lookup failed: ' . $e->getMessage());
            $this->columns = [];
        }

        return $this->columns;
    }
}
