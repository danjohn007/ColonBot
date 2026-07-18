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
        $userRole = $this->normalizeRole($user['role'] ?? '');
        $businesses = ($userRole === 'superadmin' || $userRole === 'colaborador_admin')
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

        $contactId = (int)$id;
        $contact = $this->contacts->find($contactId);
        $contactName = trim($_POST['name'] ?? '');
        $contactEmail = trim($_POST['email'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);
        $products = trim($_POST['products'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if ($products === '') {
            $this->json(['error' => 'Captura el producto o servicio vendido para convertirlo en cliente.'], 422);
            return;
        }

        // ────────────────────────────────────────────────────────
        // CASO 1: No se encontró en contacts → buscar en chatbot_sessions
        // ────────────────────────────────────────────────────────
        if (!$contact) {
            $db = Database::getInstance();

            $stmt = $db->prepare('SELECT wa_id, category, session_count FROM chatbot_sessions WHERE id = ? LIMIT 1');
            $stmt->execute([$contactId]);
            $session = $stmt->fetch();

            if (!$session) {
                $this->json(['error' => 'not found'], 404);
                return;
            }

            $waId = trim($session['wa_id'] ?? '');

            // 1a) Buscar si ya existe un contacto con ese mismo wa_id
            if ($waId !== '') {
                $stmtContact = $db->prepare('SELECT id, business_id, name, email FROM contacts WHERE wa_id = ? LIMIT 1');
                $stmtContact->execute([$waId]);
                $existingContact = $stmtContact->fetch();

                if ($existingContact) {
                    $contactId = (int)$existingContact['id'];
                    $business = $this->businesses->find((int)$existingContact['business_id']);
                    $this->ownerOrAdmin($business);

                    $name = $contactName ?: $existingContact['name'];
                    $email = $contactEmail ?: $existingContact['email'] ?? '';

                    $this->contacts->update($contactId, ['name' => $name, 'email' => $email]);
                    $this->contacts->upgradeToCliente($contactId, $amount, $products, $notes);
                    $this->logAction('upgrade_contact', 'contacts', $contactId, "Contacto {$name} upgrade a cliente");
                    $this->json(['ok' => true]);
                    return;
                }

                // 1b) Obtener business_id desde consultas (tabla que sí tiene business_id + wa_id)
                $stmtBusiness = $db->prepare(
                    'SELECT business_id FROM consultas WHERE wa_id = ? AND business_id IS NOT NULL LIMIT 1'
                );
                $stmtBusiness->execute([$waId]);
                $businessRow = $stmtBusiness->fetch();

                if ($businessRow && !empty($businessRow['business_id'])) {
                    $businessId = (int)$businessRow['business_id'];
                } else {
                    $businessId = (int)($_POST['business_id'] ?? 0);
                }
            } else {
                $businessId = (int)($_POST['business_id'] ?? 0);
            }

            if ($businessId <= 0) {
                $this->json(['error' => 'No se pudo determinar el negocio para este contacto de chatbot.'], 422);
                return;
            }

            $business = $this->businesses->find($businessId);
            if (!$business) { $this->json(['error' => 'not found'], 404); return; }
            $this->ownerOrAdmin($business);

            $name = $contactName ?: 'Prospecto WhatsApp';
            $email = $contactEmail ?: '';

            // Crear el contacto y registrar compra
            $newContact = $this->contacts->createOrUpdate($businessId, $name, $waId, $waId, $email, 'whatsapp');
            $contactId = (int)$newContact['id'];
            $this->contacts->addPurchase($contactId, $businessId, $amount, $products, $notes);

            $this->logAction('upgrade_contact', 'contacts', $contactId, "Contacto {$name} upgrade a cliente desde chatbot");
            $this->json(['ok' => true]);
            return;
        }

        // ────────────────────────────────────────────────────────
        // CASO 2: Contacto normal encontrado en la tabla contacts
        // ────────────────────────────────────────────────────────
        $business = $this->businesses->find($contact['business_id']);
        $this->ownerOrAdmin($business);

        $name = $contactName ?: $contact['name'];
        $email = $contactEmail ?: $contact['email'] ?? '';

        $this->contacts->update($contactId, ['name' => $name, 'email' => $email]);
        $this->contacts->upgradeToCliente($contactId, $amount, $products, $notes);

        $this->logAction('upgrade_contact', 'contacts', $contactId, "Contacto {$name} upgrade a cliente");
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
        $userRole = $this->normalizeRole($user['role'] ?? '');
        
        // SuperAdmin tiene acceso completo a todos los negocios
        if ($userRole === 'superadmin') {
            return;
        }
        
        // Colaborador Admin tiene acceso completo a todos los negocios
        if ($userRole === 'colaborador_admin') {
            return;
        }
        
        // Prestador solo puede gestionar contactos de sus propios negocios
        if ($userRole === 'prestador') {
            if ((int)$business['user_id'] !== (int)$user['id']) {
                http_response_code(403);
                $this->json(['error' => 'No tienes permiso para gestionar contactos de este negocio.'], 403);
            }
            return;
        }

        // Cualquier otro rol: verificar si es propietario
        if ((int)$business['user_id'] !== (int)$user['id']) {
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
