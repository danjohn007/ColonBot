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

        // Add chatbot contacts with automatic classification based on chatbot sessions
        $chatbotContacts = $this->contacts->classifyByChatbotSessions((int)$businessId);

        // Merge chatbot contacts
        if (empty($category)) {
            // Show all: merge chatbot contacts with regular contacts
            $contacts = array_merge($chatbotContacts, $contacts);
        } elseif (in_array($category, ['prospecto', 'prospecto_sin_historial', 'prospecto_recurrente', 'cliente', 'lovemark', 'cliente_frecuente'])) {
            // Filter chatbot contacts by the requested category
            $filteredChatbot = array_filter($chatbotContacts, function($c) use ($category) {
                if ($category === 'cliente_frecuente') {
                    return $c['category'] === 'lovemark' || ($c['purchase_count'] ?? 0) >= 4;
                }
                return $c['category'] === $category;
            });
            $contacts = array_merge($filteredChatbot, $contacts);
        }

        $this->json($this->uniqueContacts($contacts));
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
        $category = $_POST['category'] ?? 'prospecto_sin_historial';

        if (!$name) { $this->json(['error' => 'El nombre es requerido'], 422); }

        $contact = $this->contacts->createOrUpdate($businessId, $name, '', $phone, $email, 'manual');
        $this->contacts->update($contact['id'], [
            'notes' => $notes,
            'category' => in_array($category, ['prospecto', 'prospecto_sin_historial', 'prospecto_recurrente'], true)
                ? ($category === 'prospecto' ? 'prospecto_sin_historial' : $category)
                : 'prospecto_sin_historial',
        ]);

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
        if (isset($_POST['category'])) {
            $requestedCategory = $_POST['category'];
            if (in_array($requestedCategory, ['prospecto', 'prospecto_sin_historial', 'prospecto_recurrente'], true)) {
                $data['category'] = $requestedCategory === 'prospecto' ? 'prospecto_sin_historial' : $requestedCategory;
            } elseif ($requestedCategory === 'cliente') {
                if ($this->contacts->purchaseCount((int)$id) < 1) {
                    $this->json(['error' => 'Para convertir a cliente primero registra una compra.'], 422);
                }
                $data['category'] = 'cliente';
            } elseif ($requestedCategory === 'lovemark') {
                if ($this->contacts->purchaseCount((int)$id) < 4) {
                    $this->json(['error' => 'Cliente recurrente requiere mas de 3 compras registradas.'], 422);
                }
                $data['category'] = 'lovemark';
            }
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

        if ($products === '') {
            $this->json(['error' => 'Captura el producto o servicio vendido para convertirlo en cliente.'], 422);
        }

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
     * Si acumula mas de 3 compras, se convierte en cliente recurrente / Lovemark
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

        if ($products === '') {
            $this->json(['error' => 'Captura el producto o servicio vendido.'], 422);
        }

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

    private function uniqueContacts(array $contacts): array
    {
        $unique = [];
        foreach ($contacts as $contact) {
            $sessionId = (int)($contact['chatbot_session_id'] ?? 0);
            $key = $sessionId > 0 ? 'chatbot_session:' . $sessionId : 'contact:' . (int)$contact['id'];
            if (!isset($unique[$key])) {
                $unique[$key] = $contact;
            }
        }
        return array_values($unique);
    }
}
