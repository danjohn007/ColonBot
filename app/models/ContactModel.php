<?php
class ContactModel extends Model
{
    protected string $table = 'contacts';
    private const CLIENTE_RECURRENTE_MIN_PURCHASES = 3;

    public function byBusiness(int $businessId, string $category = ''): array
    {
        $sql = 'SELECT c.*,
                c.total_visits AS purchase_count,
                c.total_purchases AS total_spent,
                CASE
                    WHEN c.total_visits >= ' . self::CLIENTE_RECURRENTE_MIN_PURCHASES . ' THEN \'lovemark\'
                    WHEN c.total_visits >= 1 THEN \'cliente\'
                    ELSE c.category
                END AS dynamic_category
                FROM contacts c
                WHERE c.business_id = ?';
        $params = [$businessId];

        if ($category && in_array($category, ['prospecto', 'cliente', 'lovemark', 'prospecto_sin_historial', 'prospecto_recurrente', 'cliente_frecuente'], true)) {
            if ($category === 'prospecto_sin_historial') {
                $sql .= " AND c.category IN ('prospecto', 'prospecto_sin_historial')";
            } elseif ($category === 'prospecto_recurrente') {
                $sql .= " AND c.category = 'prospecto_recurrente'";
            } elseif ($category === 'cliente_frecuente') {
                $sql .= " AND c.total_visits >= " . self::CLIENTE_RECURRENTE_MIN_PURCHASES;
            } elseif ($category === 'cliente') {
                $sql .= " AND c.total_visits BETWEEN 1 AND 2";
            } elseif ($category === 'lovemark') {
                $sql .= " AND c.total_visits >= " . self::CLIENTE_RECURRENTE_MIN_PURCHASES;
            } else {
                $sql .= ' AND c.category = ?';
                $params[] = $category;
            }
        }

        $sql .= ' ORDER BY c.updated_at DESC';
        return $this->query($sql, $params);
    }

    public function prospectos(int $businessId): array
    {
        return $this->byBusiness($businessId, 'prospecto_sin_historial');
    }

    public function clientes(int $businessId): array
    {
        return $this->byBusiness($businessId, 'cliente');
    }

    public function lovemarks(int $businessId): array
    {
        return $this->byBusiness($businessId, 'lovemark');
    }

    public function findByWaId(int $businessId, string $waId): ?array
    {
        return $this->queryOne(
            'SELECT * FROM contacts WHERE business_id = ? AND wa_id = ? LIMIT 1',
            [$businessId, $waId]
        );
    }

    public function findByPhone(int $businessId, string $phone): ?array
    {
        return $this->queryOne(
            'SELECT * FROM contacts WHERE business_id = ? AND phone = ? LIMIT 1',
            [$businessId, $phone]
        );
    }

    public function createOrUpdate(int $businessId, string $name, string $waId = '', string $phone = '', string $email = '', string $source = 'manual'): array
    {
        $existing = null;
        if ($waId) {
            $existing = $this->findByWaId($businessId, $waId);
        }
        if (!$existing && $phone) {
            $existing = $this->findByPhone($businessId, $phone);
        }
        if (!$existing && $email) {
            $existing = $this->queryOne(
                'SELECT * FROM contacts WHERE business_id = ? AND email = ? LIMIT 1',
                [$businessId, $email]
            );
        }

        if ($existing) {
            $this->update((int)$existing['id'], [
                'name' => $name,
                'phone' => $phone ?: $existing['phone'],
                'email' => $email ?: $existing['email'],
                'last_contact_at' => date('Y-m-d H:i:s'),
            ]);
            return $this->find((int)$existing['id']);
        }

        $id = $this->insert([
            'business_id' => $businessId,
            'wa_id' => $waId ?: null,
            'name' => $name,
            'phone' => $phone ?: null,
            'email' => $email ?: null,
            'category' => 'prospecto_sin_historial',
            'source' => $source,
            'last_contact_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->find($id);
    }

    public function addPurchase(int $contactId, int $businessId, float $amount, string $products = '', string $notes = ''): void
    {
        // Get current contact data to determine new total_visits
        $contact = $this->find($contactId);
        if (!$contact) return;

        $newVisits = (int)$contact['total_visits'] + 1;
        $newTotalPurchases = (float)$contact['total_purchases'] + $amount;

        // Determine new category based on visit count
        $newCategory = 'cliente';
        if ($newVisits >= self::CLIENTE_RECURRENTE_MIN_PURCHASES) {
            $newCategory = 'lovemark';
        }

        $this->update($contactId, [
            'total_visits' => $newVisits,
            'total_purchases' => $newTotalPurchases,
            'products' => $products ?: $contact['products'],
            'notes' => $notes ?: $contact['notes'],
            'category' => $newCategory,
            'last_contact_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function upgradeToCliente(int $contactId, float $amount = 0, string $products = '', string $notes = ''): void
    {
        $contact = $this->find($contactId);
        if ($contact) {
            $this->addPurchase($contactId, (int)$contact['business_id'], $amount, $products, $notes);
        }
    }

    public function purchaseCount(int $contactId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM contact_purchases WHERE contact_id = ?');
        $stmt->execute([$contactId]);
        return (int)$stmt->fetchColumn();
    }

    public function classifyWebUser(int $businessId): array
    {
        $stmt = $this->db->prepare('SELECT visits FROM businesses WHERE id = ?');
        $stmt->execute([$businessId]);
        $business = $stmt->fetch();

        $visits = $business ? (int)$business['visits'] : 0;
        $category = $visits > 0 ? 'prospecto_recurrente' : 'prospecto_sin_historial';

        $existing = $this->queryOne(
            'SELECT * FROM contacts WHERE business_id = ? AND source = "mapa" ORDER BY updated_at DESC LIMIT 1',
            [$businessId]
        );

        if ($existing) {
            $purchaseCount = $this->purchaseCount((int)$existing['id']);
            if ($purchaseCount > 0) {
                $category = $this->categoryFromPurchaseCount($purchaseCount);
            }
        }

        return [
            'category' => $category,
            'visits' => $visits,
            'source' => 'mapa',
        ];
    }

    public function syncWebVisitorProspect(int $businessId, array $user): void
    {
        $userId = (int)($user['id'] ?? 0);
        $name = trim($user['name'] ?? '') ?: 'Visitante web';
        $email = trim($user['email'] ?? '');
        $phone = trim($user['phone'] ?? '');

        if ($userId <= 0 || ($email === '' && $phone === '')) {
            return;
        }

        $existing = null;
        if ($email !== '') {
            $existing = $this->queryOne(
                'SELECT * FROM contacts WHERE business_id = ? AND email = ? LIMIT 1',
                [$businessId, $email]
            );
        }
        if (!$existing && $phone !== '') {
            $existing = $this->findByPhone($businessId, $phone);
        }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM visitor_place_visits WHERE user_id = ? AND business_id = ?');
        $stmt->execute([$userId, $businessId]);
        $visitCount = (int)$stmt->fetchColumn();
        $prospectCategory = $visitCount > 1 ? 'prospecto_recurrente' : 'prospecto_sin_historial';

        if ($existing) {
            $purchaseCount = $this->purchaseCount((int)$existing['id']);
            $this->update((int)$existing['id'], [
                'name' => $name,
                'email' => $email ?: $existing['email'],
                'phone' => $phone ?: $existing['phone'],
                'category' => $purchaseCount > 0 ? $this->categoryFromPurchaseCount($purchaseCount) : $prospectCategory,
                'source' => $existing['source'] ?: 'mapa',
                'total_visits' => max($visitCount, (int)($existing['total_visits'] ?? 0)),
                'last_contact_at' => date('Y-m-d H:i:s'),
            ]);
            return;
        }

        $this->insert([
            'business_id' => $businessId,
            'name' => $name,
            'email' => $email ?: null,
            'phone' => $phone ?: null,
            'category' => $prospectCategory,
            'source' => 'mapa',
            'total_visits' => $visitCount,
            'last_contact_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function classifyByChatbotSessions(int $businessId): array
    {
        $existingContacts = $this->query(
            "SELECT c.id, c.wa_id, c.phone, c.category, c.name, c.email, c.source
             FROM contacts c
             WHERE c.business_id = ? AND (c.wa_id IS NOT NULL AND c.wa_id != '')",
            [$businessId]
        );

        $results = [];

        foreach ($existingContacts as $contact) {
            $waId = (string)$contact['wa_id'];
            $session = $this->chatbotSession($waId);
            $consultas = $this->consultationCounts($waId, $businessId);
            $purchaseCount = $this->purchaseCount((int)$contact['id']);
            $newCategory = $purchaseCount > 0
                ? $this->categoryFromPurchaseCount($purchaseCount)
                : $this->determineProspectCategory($session, $consultas);

            $results[] = [
                'id' => (int)$contact['id'],
                'wa_id' => $waId,
                'name' => $contact['name'],
                'email' => $contact['email'] ?? '',
                'phone' => $contact['phone'] ?? $waId,
                'category' => $newCategory,
                'total_visits' => max((int)($session['session_count'] ?? 0), (int)$consultas['total_acciones']),
                'total_spent' => $this->totalSpent((int)$contact['id']),
                'source' => 'whatsapp',
                'last_contact_at' => $session['updated_at'] ?? null,
                'purchase_count' => $purchaseCount,
                'chatbot_session_id' => 0,
                'is_chatbot' => true,
            ];

            if ($newCategory !== $contact['category']) {
                $this->update((int)$contact['id'], ['category' => $newCategory]);
            }
        }

        $stmt = $this->db->prepare(
            "SELECT cs.id, cs.wa_id, cs.last_message AS notes,
                    cs.category AS session_category,
                    cs.session_count, cs.purchase_count, cs.has_purchased,
                    cs.updated_at
             FROM chatbot_sessions cs
             WHERE cs.wa_id NOT IN (
                SELECT COALESCE(c.wa_id, '') FROM contacts c WHERE c.business_id = ?
             )
             AND cs.wa_id NOT IN (
                SELECT COALESCE(c.phone, '') FROM contacts c WHERE c.business_id = ?
             )
             ORDER BY cs.updated_at DESC
             LIMIT 50"
        );
        $stmt->execute([$businessId, $businessId]);

        foreach ($stmt->fetchAll() as $session) {
            $waId = (string)$session['wa_id'];
            $consultas = $this->consultationCounts($waId, $businessId);
            $results[] = [
                'id' => (int)$session['id'],
                'wa_id' => $waId,
                'name' => $session['session_category'] ?: 'Prospecto WhatsApp',
                'email' => '',
                'phone' => $waId,
                'category' => $this->determineProspectCategory($session, $consultas),
                'total_visits' => max((int)$session['session_count'], (int)$consultas['total_acciones']),
                'total_spent' => 0,
                'source' => 'whatsapp',
                'last_contact_at' => $session['updated_at'],
                'purchase_count' => 0,
                'chatbot_session_id' => (int)$session['id'],
                'is_chatbot' => true,
            ];
        }

        return $results;
    }

    private function chatbotSession(string $waId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT session_count, purchase_count, has_purchased, category, updated_at
             FROM chatbot_sessions WHERE wa_id = ? LIMIT 1"
        );
        $stmt->execute([$waId]);
        return $stmt->fetch() ?: null;
    }

    private function consultationCounts(string $waId, int $businessId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT
                    COUNT(*) AS total_acciones,
                    SUM(CASE WHEN tipo_accion = 'solicitar_informacion' THEN 1 ELSE 0 END) AS info_count,
                    SUM(CASE WHEN tipo_accion = 'compra_reservacion' THEN 1 ELSE 0 END) AS reserva_count
                 FROM consultas
                 WHERE wa_id = ? AND business_id = ?"
            );
            $stmt->execute([$waId, $businessId]);
            $row = $stmt->fetch() ?: [];
        } catch (PDOException $e) {
            $row = [];
        }

        return [
            'total_acciones' => (int)($row['total_acciones'] ?? 0),
            'info_count' => (int)($row['info_count'] ?? 0),
            'reserva_count' => (int)($row['reserva_count'] ?? 0),
        ];
    }

    private function determineProspectCategory(?array $session, array $consultas): string
    {
        $sessionCount = (int)($session['session_count'] ?? 0);
        $consultationActions = (int)$consultas['total_acciones'];

        if ($sessionCount > 1 || $consultationActions > 0) {
            return 'prospecto_recurrente';
        }
        return 'prospecto_sin_historial';
    }

    private function categoryFromPurchaseCount(int $purchaseCount): string
    {
        if ($purchaseCount >= self::CLIENTE_RECURRENTE_MIN_PURCHASES) {
            return 'lovemark';
        }
        if ($purchaseCount >= 1) {
            return 'cliente';
        }
        return 'prospecto_sin_historial';
    }

    private function refreshCategoryFromPurchases(int $contactId): void
    {
        $purchaseCount = $this->purchaseCount($contactId);
        if ($purchaseCount <= 0 || !$this->find($contactId)) {
            return;
        }

        $this->update($contactId, ['category' => $this->categoryFromPurchaseCount($purchaseCount)]);
    }

    private function totalSpent(int $contactId): float
    {
        $stmt = $this->db->prepare('SELECT COALESCE(SUM(amount), 0) FROM contact_purchases WHERE contact_id = ?');
        $stmt->execute([$contactId]);
        return (float)$stmt->fetchColumn();
    }

    public function getMetrics(int $businessId, string $period = 'all'): array
    {
        $dateFilter = '';
        switch ($period) {
            case 'day':   $dateFilter = 'AND c.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)'; break;
            case 'week':  $dateFilter = 'AND c.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)'; break;
            case 'month': $dateFilter = 'AND c.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)'; break;
            case 'year':  $dateFilter = 'AND c.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)'; break;
        }

        $total = (int)$this->db->query(
            "SELECT COUNT(*) FROM contacts c WHERE c.business_id = $businessId $dateFilter"
        )->fetchColumn();

        $prospectosSinHistorial = (int)$this->db->query(
            "SELECT COUNT(*) FROM contacts c WHERE c.business_id = $businessId AND c.category IN ('prospecto', 'prospecto_sin_historial') $dateFilter"
        )->fetchColumn();

        $prospectosRecurrentes = (int)$this->db->query(
            "SELECT COUNT(*) FROM contacts c WHERE c.business_id = $businessId AND c.category = 'prospecto_recurrente' $dateFilter"
        )->fetchColumn();

        $clientes = (int)$this->db->query(
            "SELECT COUNT(*) FROM contacts c WHERE c.business_id = $businessId AND c.category = 'cliente' $dateFilter"
        )->fetchColumn();

        $lovemarks = (int)$this->db->query(
            "SELECT COUNT(*) FROM contacts c WHERE c.business_id = $businessId AND c.category = 'lovemark' $dateFilter"
        )->fetchColumn();

        $totalSales = (float)$this->db->query(
            "SELECT COALESCE(SUM(cp.amount), 0) FROM contact_purchases cp
             JOIN contacts c ON c.id = cp.contact_id
             WHERE cp.business_id = $businessId $dateFilter"
        )->fetchColumn();

        $reservations = (int)$this->db->query(
            "SELECT COUNT(*) FROM contact_purchases cp
             JOIN contacts c ON c.id = cp.contact_id
             WHERE cp.business_id = $businessId $dateFilter"
        )->fetchColumn();

        $topProducts = $this->query(
            "SELECT cp.products, COUNT(*) AS total FROM contact_purchases cp
             JOIN contacts c ON c.id = cp.contact_id
             WHERE cp.business_id = ? AND cp.products IS NOT NULL AND cp.products != ''
             GROUP BY cp.products ORDER BY total DESC LIMIT 10",
            [$businessId]
        );

        return [
            'total' => $total,
            'prospectos_sin_historial' => $prospectosSinHistorial,
            'prospectos_recurrentes' => $prospectosRecurrentes,
            'prospectos' => $prospectosSinHistorial + $prospectosRecurrentes,
            'clientes' => $clientes,
            'lovemarks' => $lovemarks,
            'total_sales' => $totalSales,
            'reservations' => $reservations,
            'top_products' => $topProducts,
        ];
    }

    public function getChartData(int $businessId, string $period = 'month'): array
    {
        $format = $period === 'year' ? '%Y-%m' : '%Y-%m-%d';
        $interval = $period === 'year' ? 12 : ($period === 'month' ? 30 : 7);

        return $this->query(
            "SELECT DATE_FORMAT(c.created_at, '$format') AS label,
                    COUNT(*) AS total,
                    SUM(CASE WHEN c.category IN ('prospecto', 'prospecto_sin_historial') THEN 1 ELSE 0 END) AS prospectos_sin_historial,
                    SUM(CASE WHEN c.category = 'prospecto_recurrente' THEN 1 ELSE 0 END) AS prospectos_recurrentes,
                    SUM(CASE WHEN c.category = 'cliente' THEN 1 ELSE 0 END) AS clientes,
                    SUM(CASE WHEN c.category = 'lovemark' THEN 1 ELSE 0 END) AS lovemarks
             FROM contacts c
             WHERE c.business_id = ?
             AND c.created_at >= DATE_SUB(NOW(), INTERVAL $interval $period)
             GROUP BY label
             ORDER BY label ASC",
            [$businessId]
        );
    }
}
