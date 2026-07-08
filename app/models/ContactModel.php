<?php
class ContactModel extends Model
{
    protected string $table = 'contacts';

    public function byBusiness(int $businessId, string $category = ''): array
    {
        $sql = 'SELECT c.*, 
                (SELECT COUNT(*) FROM contact_purchases cp WHERE cp.contact_id = c.id) AS purchase_count,
                (SELECT SUM(cp.amount) FROM contact_purchases cp WHERE cp.contact_id = c.id) AS total_spent
                FROM contacts c WHERE c.business_id = ?';
        $params = [$businessId];

        if ($category && in_array($category, ['prospecto', 'cliente', 'lovemark', 'prospecto_sin_historial', 'prospecto_recurrente', 'cliente_frecuente'])) {
            if ($category === 'prospecto_sin_historial') {
                $sql .= " AND c.category = 'prospecto_sin_historial'";
            } elseif ($category === 'prospecto_recurrente') {
                $sql .= " AND c.category = 'prospecto_recurrente'";
            } elseif ($category === 'cliente_frecuente') {
                $sql .= " AND (c.category = 'lovemark' OR (SELECT COUNT(*) FROM contact_purchases cp WHERE cp.contact_id = c.id) >= 3)";
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
        // Try to find by wa_id first, then phone
        $existing = null;
        if ($waId) {
            $existing = $this->findByWaId($businessId, $waId);
        }
        if (!$existing && $phone) {
            $existing = $this->findByPhone($businessId, $phone);
        }
        // Try by email
        if (!$existing && $email) {
            $existing = $this->queryOne(
                'SELECT * FROM contacts WHERE business_id = ? AND email = ? LIMIT 1',
                [$businessId, $email]
            );
        }

        if ($existing) {
            $this->update($existing['id'], [
                'name' => $name,
                'phone' => $phone ?: $existing['phone'],
                'email' => $email ?: $existing['email'],
                'last_contact_at' => date('Y-m-d H:i:s'),
            ]);
            return $this->find($existing['id']);
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
        $this->execute(
            'INSERT INTO contact_purchases (contact_id, business_id, amount, products, notes) VALUES (?,?,?,?,?)',
            [$contactId, $businessId, $amount, $products, $notes]
        );

        // Update contact totals
        $this->execute(
            'UPDATE contacts SET total_visits = total_visits + 1, total_purchases = total_purchases + ? WHERE id = ?',
            [$amount, $contactId]
        );

        // Check if should upgrade based on purchase count
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM contact_purchases WHERE contact_id = ?');
        $stmt->execute([$contactId]);
        $purchaseCount = (int)$stmt->fetchColumn();

        $contact = $this->find($contactId);
        if ($contact) {
            if ($purchaseCount >= 3 && $contact['category'] !== 'lovemark') {
                $this->update($contactId, ['category' => 'lovemark']);
            } elseif ($purchaseCount >= 1 && $contact['category'] !== 'cliente' && $contact['category'] !== 'lovemark') {
                $this->update($contactId, ['category' => 'cliente']);
            }
        }
    }

    public function upgradeToCliente(int $contactId, float $amount = 0, string $products = '', string $notes = ''): void
    {
        $this->update($contactId, ['category' => 'cliente']);
        if ($amount > 0) {
            $contact = $this->find($contactId);
            $this->addPurchase($contactId, $contact['business_id'], $amount, $products, $notes);
        }
    }

    /**
     * Clasificar usuarios web basado en businesses.visits
     */
    public function classifyWebUser(int $businessId): array
    {
        $db = Database::getInstance();
        // Obtener visitantes de la tabla businesses (columna visits)
        $stmt = $db->prepare('SELECT visits FROM businesses WHERE id = ?');
        $stmt->execute([$businessId]);
        $business = $stmt->fetch();

        $visits = $business ? (int)$business['visits'] : 0;

        // Crear o actualizar contacto basado en visitas
        $existing = $this->queryOne(
            'SELECT * FROM contacts WHERE business_id = ? AND source = "mapa" ORDER BY updated_at DESC LIMIT 1',
            [$businessId]
        );

        $category = 'prospecto_sin_historial';
        if ($visits > 0) {
            $category = 'prospecto_recurrente';
        }
        if ($visits > 0) {
            // Check if there are purchases
            if ($existing) {
                $stmt = $db->prepare('SELECT COUNT(*) FROM contact_purchases WHERE contact_id = ?');
                $stmt->execute([$existing['id']]);
                $purchaseCount = (int)$stmt->fetchColumn();

                if ($purchaseCount >= 3) {
                    $category = 'lovemark';
                } elseif ($purchaseCount >= 1) {
                    $category = 'cliente';
                }
            }
        }

        return [
            'category' => $category,
            'visits' => $visits,
            'source' => 'mapa',
        ];
    }

    /**
     * Clasificar contactos basado en sesiones de chatbot WhatsApp.
     * Utiliza los datos de chatbot_sessions (session_count, purchase_count, has_purchased, category)
     * para determinar automáticamente la categoría de cada contacto en el CRM.
     *
     * Clasificación:
     * - purchase_count >= 3  → lovemark
     * - has_purchased = 1 o purchase_count >= 1  → cliente
     * - session_count > 1 y sin compras  → prospecto_recurrente
     * - session_count = 1 y sin compras  → prospecto_sin_historial
     *
     * @param int $businessId ID del negocio
     * @return array Lista de contactos clasificados con datos de chatbot
     */
    public function classifyByChatbotSessions(int $businessId): array
    {
        $db = Database::getInstance();

        // Obtener todos los contactos existentes para este negocio con wa_id
        $existingContacts = $this->query(
            "SELECT c.id, c.wa_id, c.phone, c.category, c.name, c.email, c.source
             FROM contacts c
             WHERE c.business_id = ? AND (c.wa_id IS NOT NULL AND c.wa_id != '')",
            [$businessId]
        );

        $results = [];

        // 1) Procesar contactos existentes que tienen wa_id - clasificar según sus sesiones
        foreach ($existingContacts as $contact) {
            $waId = $contact['wa_id'];
            $stmt = $db->prepare(
                "SELECT session_count, purchase_count, has_purchased, category
                 FROM chatbot_sessions WHERE wa_id = ? LIMIT 1"
            );
            $stmt->execute([$waId]);
            $session = $stmt->fetch();

            if ($session) {
                $newCategory = $this->determineCategoryFromSession($session);
                $results[] = [
                    'id'               => $contact['id'],
                    'wa_id'            => $waId,
                    'name'             => $contact['name'],
                    'email'            => $contact['email'] ?? '',
                    'phone'            => $contact['phone'] ?? $waId,
                    'category'         => $newCategory,
                    'total_visits'     => (int)$session['session_count'],
                    'total_spent'      => 0,
                    'source'           => 'whatsapp',
                    'last_contact_at'  => null,
                    'purchase_count'   => (int)$session['purchase_count'],
                    'chatbot_session_id' => 0,
                    'is_chatbot'       => true,
                ];

                // Actualizar la categoría en la tabla contacts si cambió
                if ($newCategory !== $contact['category']) {
                    $this->update((int)$contact['id'], ['category' => $newCategory]);
                }
            } else {
                // Contacto con wa_id pero sin sesión de chatbot - mantener su categoría actual
                $results[] = [
                    'id'               => $contact['id'],
                    'wa_id'            => $waId,
                    'name'             => $contact['name'],
                    'email'            => $contact['email'] ?? '',
                    'phone'            => $contact['phone'] ?? $waId,
                    'category'         => $contact['category'] ?: 'prospecto_sin_historial',
                    'total_visits'     => 0,
                    'total_spent'      => 0,
                    'source'           => $contact['source'] ?: 'manual',
                    'last_contact_at'  => null,
                    'purchase_count'   => 0,
                    'chatbot_session_id' => 0,
                    'is_chatbot'       => false,
                ];
            }
        }

        // 2) Obtener sesiones de chatbot sin contacto asociado (nuevos prospectos)
        $stmt = $db->prepare(
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
        $stmt->execute([(int)$businessId, (int)$businessId]);
        $chatbotOnlySessions = $stmt->fetchAll();

        foreach ($chatbotOnlySessions as $session) {
            $newCategory = $this->determineCategoryFromSession($session);
            $results[] = [
                'id'               => $session['id'],
                'wa_id'            => $session['wa_id'],
                'name'             => $session['session_category'] ?: 'Prospecto WhatsApp',
                'email'            => '',
                'phone'            => $session['wa_id'],
                'category'         => $newCategory,
                'total_visits'     => (int)$session['session_count'],
                'total_spent'      => 0,
                'source'           => 'whatsapp',
                'last_contact_at'  => $session['updated_at'],
                'purchase_count'   => (int)$session['purchase_count'],
                'chatbot_session_id' => (int)$session['id'],
                'is_chatbot'       => true,
            ];
        }

        return $results;
    }

    /**
     * Determinar la categoría del cliente basado en datos de sesión del chatbot.
     */
    private function determineCategoryFromSession(array $session): string
    {
        $purchaseCount = (int)($session['purchase_count'] ?? 0);
        $hasPurchased  = (bool)($session['has_purchased'] ?? false);
        $sessionCount  = (int)($session['session_count'] ?? 1);

        if ($purchaseCount >= 3) {
            return 'lovemark';
        }
        if ($hasPurchased || $purchaseCount >= 1) {
            return 'cliente';
        }
        if ($sessionCount > 1) {
            return 'prospecto_recurrente';
        }
        return 'prospecto_sin_historial';
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
            "SELECT COUNT(*) FROM contacts c WHERE c.business_id = $businessId AND c.category = 'prospecto_sin_historial' $dateFilter"
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
                    SUM(CASE WHEN c.category = 'prospecto_sin_historial' THEN 1 ELSE 0 END) AS prospectos_sin_historial,
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
