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

    public function list(string $businessId): void
    {
        $this->requireAuth('prestador');
        $business = $this->businesses->find((int)$businessId);
        if (!$business) { $this->json(['error' => 'not found'], 404); }
        $this->ownerOrAdmin($business);

        $category = $_GET['category'] ?? '';
        $contacts = $this->contacts->byBusiness((int)$businessId, $category);

        // Add chatbot sessions as prospects (auto-imported from chatbot)
        $db = Database::getInstance();
        $chatbotContacts = $db->query(
            "SELECT cs.id AS id, cs.wa_id AS phone, cs.last_message AS notes,
                    'Chatbot' AS name, '' AS email, 'prospecto' AS category,
                    0 AS total_visits, 0 AS total_spent,
                    'chatbot' AS source, cs.updated_at AS last_contact_at,
                    0 AS purchase_count
             FROM chatbot_sessions cs
             WHERE cs.wa_id NOT IN (
                SELECT COALESCE(c.wa_id, '') FROM contacts c WHERE c.business_id = ?
             )
             AND cs.wa_id NOT IN (
                SELECT COALESCE(c.phone, '') FROM contacts c WHERE c.business_id = ?
             )
             ORDER BY cs.updated_at DESC
             LIMIT 50",
            [(int)$businessId, (int)$businessId]
        )->fetchAll();

        // Merge chatbot contacts as prospects
        if (empty($category) || $category === 'prospecto' || $category === 'prospecto_sin_historial') {
            $contacts = array_merge($chatbotContacts, $contacts);
        }

        $this->json($contacts);
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
                'category' => in_array($category, ['prospecto', 'cliente', 'lovemark']) ? $category : 'prospecto',
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
        if (isset($_POST['category']) && in_array($_POST['category'], ['prospecto', 'cliente', 'lovemark'])) {
            $data['category'] = $_POST['category'];
        }

        if (!empty($data)) {
            $this->contacts->update((int)$id, $data);
        }

        $this->json(['ok' => true]);
    }

    /**
     * Convertir PROSPECTO a CLIENTE (Customer Journey etapa B)
     * Modal solicita: Nombre del Cliente, Producto o Servicio, Monto total, Email (opcional), Notas
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