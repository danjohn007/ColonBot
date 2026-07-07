<?php
class CrmController extends Controller
{
    private ContactModel $contacts;
    private BusinessModel $businesses;

    public function __construct()
    {
        $this->contacts = new ContactModel();
        $this->businesses = new BusinessModel();
    }

    public function index(): void
    {
        $this->requireAuth('prestador');
        $user = currentUser();
        $businesses = $user['role'] === 'superadmin'
            ? $this->businesses->allWithCategory()
            : $this->businesses->byUser((int)$user['id']);

        $this->view('crm.index', compact('businesses', 'user') + ['csrf' => $this->csrf()]);
    }

    /**
     * Obtener contactos del CRM directamente desde chatbot_sessions y consultas.
     * NO usa la tabla contacts.
     *
     * Clasificación:
     * A) prospecto_sin_historial: en chatbot_sessions pero NO en consultas
     * B) prospecto_recurrente: en consultas con SOLO 'solicitar_informacion'
     * C) cliente: en consultas con 'compra_reservacion'
     * D) lovemark: en consultas con 3+ 'compra_reservacion' en mismo negocio
     */
    public function list(string $businessId): void
    {
        $this->requireAuth('prestador');
        $business = $this->businesses->find((int)$businessId);
        if (!$business) { $this->json(['error' => 'not found'], 404); }
        $this->ownerOrAdmin($business);

        $categoryFilter = $_GET['category'] ?? '';
        $db = Database::getInstance();

        // 1) Get ALL chatbot sessions
        $allSessions = $db->query(
            "SELECT wa_id, updated_at FROM chatbot_sessions ORDER BY updated_at DESC"
        );

        if (empty($allSessions)) {
            $this->json([]);
            return;
        }

        $results = [];

        foreach ($allSessions as $session) {
            $waId = $session['wa_id'];

            // Count actions in consultas for this wa_id AND this business
            $infoCount = (int)$db->query(
                "SELECT COUNT(*) FROM consultas WHERE wa_id = ? AND business_id = ? AND tipo_accion = 'solicitar_informacion'",
                [$waId, (int)$businessId]
            )->fetchColumn();

            $compraCount = (int)$db->query(
                "SELECT COUNT(*) FROM consultas WHERE wa_id = ? AND business_id = ? AND tipo_accion = 'compra_reservacion'",
                [$waId, (int)$businessId]
            )->fetchColumn();

            // Also check if they have compra_reservacion in ANY business
            $hasCompraAnyBusiness = (int)$db->query(
                "SELECT COUNT(*) FROM consultas WHERE wa_id = ? AND tipo_accion = 'compra_reservacion' AND business_id = ?",
                [$waId, (int)$businessId]
            )->fetchColumn();

            // Determine category
            if ($compraCount >= 3) {
                $category = 'lovemark';
            } elseif ($compraCount >= 1) {
                $category = 'cliente';
            } elseif ($infoCount >= 1) {
                $category = 'prospecto_recurrente';
            } else {
                $category = 'prospecto_sin_historial';
            }

            // Apply category filter if set
            if ($categoryFilter) {
                if ($categoryFilter === 'cliente_frecuente') {
                    if ($category !== 'lovemark') continue;
                } else if ($category !== $categoryFilter) {
                    continue;
                }
            }

            // Get business name for the place they consulted about
            $businessName = null;
            if ($infoCount > 0 || $compraCount > 0) {
                $bizRow = $db->query(
                    "SELECT b.name FROM consultas c JOIN businesses b ON b.id = c.business_id WHERE c.wa_id = ? AND c.business_id = ? LIMIT 1",
                    [$waId, (int)$businessId]
                );
                if (!empty($bizRow)) {
                    $businessName = $bizRow[0]['name'];
                }
            }

            $results[] = [
                'id' => 0,
                'wa_id' => $waId,
                'name' => 'Usuario ' . substr($waId, -4),
                'email' => '',
                'phone' => $waId,
                'category' => $category,
                'total_visits' => $infoCount + $compraCount,
                'total_spent' => 0,
                'source' => 'whatsapp',
                'last_contact_at' => $session['updated_at'],
                'purchase_count' => $compraCount,
                'is_chatbot' => true,
                'business_name' => $businessName,
            ];
        }

        $this->json($results);
    }

    public function add(): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $businessId = (int)($_POST['business_id'] ?? 0);
        $business = $this->businesses->find($businessId);
        if (!$business) { $this->json(['error' => 'not found'], 404); }
        $this->ownerOrAdmin($business);

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $category = $_POST['category'] ?? 'prospecto';

        if (!$name) { $this->json(['error' => 'El nombre es requerido'], 422); }

        $contact = $this->contacts->createOrUpdate($businessId, $name, '', $phone, $email, 'manual');
        if ($notes) {
            $this->contacts->update($contact['id'], [
                'notes' => $notes,
                'category' => in_array($category, ['prospecto', 'prospecto_sin_historial', 'prospecto_recurrente', 'cliente', 'lovemark']) ? $category : 'prospecto_sin_historial',
            ]);
        }

        $this->logAction('create_contact', 'contacts', $contact['id'], "Contacto $name en negocio {$business['name']}");
        $this->json(['ok' => true, 'contact' => $contact]);
    }

    public function update(string $id): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $contact = $this->contacts->find((int)$id);
        if (!$contact) { $this->json(['error' => 'not found'], 404); }

        $business = $this->businesses->find($contact['business_id']);
        $this->ownerOrAdmin($business);

        $data = [];
        if (isset($_POST['name'])) $data['name'] = trim($_POST['name']);
        if (isset($_POST['email'])) $data['email'] = trim($_POST['email']);
        if (isset($_POST['phone'])) $data['phone'] = trim($_POST['phone']);
        if (isset($_POST['notes'])) $data['notes'] = trim($_POST['notes']);
        if (isset($_POST['category']) && in_array($_POST['category'], ['prospecto', 'prospecto_sin_historial', 'prospecto_recurrente', 'cliente', 'lovemark'])) {
            $data['category'] = $_POST['category'];
        }

        if (!empty($data)) {
            $this->contacts->update((int)$id, $data);
        }

        $this->json(['ok' => true]);
    }

    /**
     * Convertir PROSPECTO a CLIENTE (Customer Journey etapa B)
     */
    public function upgradeToCliente(string $id): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $contact = $this->contacts->find((int)$id);
        if (!$contact) { $this->json(['error' => 'not found'], 404); }

        $business = $this->businesses->find($contact['business_id']);
        $this->ownerOrAdmin($business);

        $name = trim($_POST['name'] ?? $contact['name']);
        $amount = (float)($_POST['amount'] ?? 0);
        $products = trim($_POST['products'] ?? '');
        $email = trim($_POST['email'] ?? $contact['email'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        // Update contact info
        $this->contacts->update((int)$id, [
            'name' => $name,
            'email' => $email ?: $contact['email'],
        ]);

        $this->contacts->upgradeToCliente((int)$id, $amount, $products, $notes);

        $this->logAction('upgrade_contact', 'contacts', (int)$id, "Contacto {$name} upgrade a cliente");
        $this->json(['ok' => true]);
    }

    /**
     * Agregar compra (Customer Journey - seguimiento)
     * Si acumula 3+ compras, automáticamente se convierte a Lovemark
     */
    public function addPurchase(string $id): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $contact = $this->contacts->find((int)$id);
        if (!$contact) { $this->json(['error' => 'not found'], 404); }

        $business = $this->businesses->find($contact['business_id']);
        $this->ownerOrAdmin($business);

        $name = trim($_POST['name'] ?? $contact['name']);
        $amount = (float)($_POST['amount'] ?? 0);
        $products = trim($_POST['products'] ?? '');
        $email = trim($_POST['email'] ?? $contact['email'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        // Update contact info
        $this->contacts->update((int)$id, [
            'name' => $name,
            'email' => $email ?: $contact['email'],
        ]);

        $this->contacts->addPurchase((int)$id, (int)$contact['business_id'], $amount, $products, $notes);

        $this->logAction('add_purchase', 'contacts', (int)$id, "Compra de {$products} por \${$amount}");
        $this->json(['ok' => true]);
    }

    public function sendWhatsapp(string $id): void
    {
        $this->requireAuth('prestador');

        $contact = $this->contacts->find((int)$id);
        if (!$contact) { $this->json(['error' => 'not found'], 404); }

        $business = $this->businesses->find($contact['business_id']);
        $this->ownerOrAdmin($business);

        $phone = $contact['phone'] ?: $contact['wa_id'];
        if (!$phone) { $this->json(['error' => 'El contacto no tiene teléfono'], 422); }

        $msg = urlencode(trim($_POST['message'] ?? 'Hola, te contactamos desde nuestra plataforma turística.'));
        $url = waLink($phone, $msg);

        $this->json(['ok' => true, 'url' => $url]);
    }

    public function metrics(string $businessId): void
    {
        $this->requireAuth('prestador');

        $business = $this->businesses->find((int)$businessId);
        if (!$business) { $this->json(['error' => 'not found'], 404); }
        $this->ownerOrAdmin($business);

        $period = $_GET['period'] ?? 'all';
        $metrics = $this->contacts->getMetrics((int)$businessId, $period);
        $chartData = $this->contacts->getChartData((int)$businessId, $_GET['chart_period'] ?? 'month');

        $this->json([
            'metrics' => $metrics,
            'chart' => $chartData,
        ]);
    }

    private function ownerOrAdmin(array $business): void
    {
        $user = currentUser();
        if ($user['role'] !== 'superadmin' && (int)$business['user_id'] !== (int)$user['id']) {
            http_response_code(403);
            $this->json(['error' => 'No tienes permiso'], 403);
        }
    }
}