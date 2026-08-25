<?php
class CrmController extends Controller
{
    private ContactModel $contacts;
    private BusinessModel $businesses;
    private MensajeModel $mensajes;

    public function __construct()
    {
        $this->contacts = new ContactModel();
        $this->businesses = new BusinessModel();
        $this->mensajes = new MensajeModel();
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

        $contacts = $this->segmentContacts((int)$businessId, $category);

        $this->json($this->uniqueContacts($contacts));
    }

    /**
     * Contactos de un negocio para un segmento del CRM.
     * Reutiliza la clasificación dinámica (compra + chatbot).
     */
    private function segmentContacts(int $businessId, string $category = ''): array
    {
        // 1. Get contacts directly from contact_purchases data via LEFT JOIN
        //    dynamic_category is computed from contact_purchases:
        //    0 purchases → uses c.category static value
        //    1-2 purchases → 'cliente'
        //    3+ purchases → 'lovemark'
        $contacts = $this->contacts->byBusiness($businessId, $category);

        // 2. Get chatbot contacts for prospect classification (prospecto_sin_historial, prospecto_recurrente)
        $chatbotContacts = $this->contacts->classifyByChatbotSessions($businessId);

        // 3. Merge: database contacts FIRST so they take priority for clients/lovemarks already in DB
        if (empty($category)) {
            $contacts = array_merge($contacts, $chatbotContacts);
        } elseif (in_array($category, ['prospecto', 'prospecto_sin_historial', 'prospecto_recurrente', 'cliente', 'lovemark', 'cliente_frecuente'])) {
            $filteredChatbot = array_filter($chatbotContacts, function($c) use ($category) {
                if ($category === 'cliente_frecuente') {
                    return $c['category'] === 'lovemark' || ($c['purchase_count'] ?? 0) >= 3;
                }
                return $c['category'] === $category;
            });
            $contacts = array_merge($contacts, $filteredChatbot);
        }

        return $contacts;
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
            $validCategories = ['prospecto', 'prospecto_sin_historial', 'prospecto_recurrente', 'cliente', 'lovemark'];
            if (in_array($requestedCategory, $validCategories, true)) {
                if ($requestedCategory === 'prospecto') {
                    $data['category'] = 'prospecto_sin_historial';
                } elseif ($requestedCategory === 'lovemark') {
                    if ($this->contacts->purchaseCount((int)$id) < 3) {
                        $this->json(['error' => 'Cliente recurrente requiere al menos 3 compras registradas.'], 422);
                    }
                    $data['category'] = 'lovemark';
                } else {
                    $data['category'] = $requestedCategory;
                }
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
        // CASO 1: No se encontró en contacts (ID viene de chatbot_sessions)
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

            // Determinar el negocio: primero desde POST, luego desde consultas
            $businessId = (int)($_POST['business_id'] ?? 0);
            if ($businessId <= 0 && $waId !== '') {
                $stmtBusiness = $db->prepare(
                    'SELECT business_id FROM consultas WHERE wa_id = ? AND business_id IS NOT NULL LIMIT 1'
                );
                $stmtBusiness->execute([$waId]);
                $businessRow = $stmtBusiness->fetch();
                if ($businessRow && !empty($businessRow['business_id'])) {
                    $businessId = (int)$businessRow['business_id'];
                }
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

            // Buscar contacto del MISMO negocio por wa_id (business_id ya definido arriba)
            if ($waId !== '') {
                $stmtContact = $db->prepare('SELECT id, business_id, name, email FROM contacts WHERE wa_id = ? AND business_id = ? LIMIT 1');
                $stmtContact->execute([$waId, $businessId]);
                $existingContact = $stmtContact->fetch();

                if ($existingContact) {
                    $contactId = (int)$existingContact['id'];
                    $this->contacts->update($contactId, ['name' => $name, 'email' => $email]);
                    $this->contacts->upgradeToCliente($contactId, $amount, $products, $notes);
                    $this->logAction('upgrade_contact', 'contacts', $contactId, "Contacto {$name} upgrade a cliente");
                    $this->json(['ok' => true]);
                    return;
                }
            }

            // No existe contacto de este wa_id en este negocio → crear nuevo contacto y registrar compra
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

    // ── Mensajes (campañas WhatsApp) ─────────────────────────────────────────

    /** Buscar contacto individual por nombre o WhatsApp (para envío único). */
    public function buscarContacto(): void
    {
        $this->requireAuth('prestador');
        $businessId = (int)($_GET['business_id'] ?? 0);
        $business = $this->businesses->find($businessId);
        if (!$business) { $this->json(['error' => 'not found'], 404); }
        $this->ownerOrAdmin($business);

        $q = (string)($_GET['q'] ?? '');
        $this->json($this->contacts->search($businessId, $q, 30));
    }

    /** Crea una campaña y encola los envíos. NO llama directamente a Meta. */
    public function crearCampana(): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $businessId = (int)($_POST['business_id'] ?? 0);
        $business = $this->businesses->find($businessId);
        if (!$business) { $this->json(['error' => 'not found'], 404); }
        $this->ownerOrAdmin($business);

        $nombre    = trim((string)($_POST['nombre'] ?? ''));
        $mensaje   = trim((string)($_POST['mensaje'] ?? ''));
        $segmento  = trim((string)($_POST['segmento'] ?? 'todos'));
        $contactId = (int)($_POST['contact_id'] ?? 0);

        if (!$nombre) { $this->json(['error' => 'El nombre de la campaña es obligatorio.'], 422); }
        if (!$mensaje) { $this->json(['error' => 'El mensaje es obligatorio.'], 422); }
        if (!$this->mensajePermitido($nombre) || !$this->mensajePermitido($mensaje)) {
            $this->json(['error' => 'El nombre o mensaje contiene palabras no permitidas (no se admiten mensajes de prueba).'], 422);
        }

        $segmentosValidos = ['todos','prospecto_sin_historial','prospecto_recurrente','cliente','cliente_frecuente','individual'];
        if (!in_array($segmento, $segmentosValidos, true)) {
            $segmento = 'todos';
        }

        $destinatarios = [];
        $tipoEnvio     = 'masivo';

        if ($segmento === 'individual') {
            $tipoEnvio = 'individual';
            $contact = $this->contacts->find($contactId);
            if (!$contact || (int)$contact['business_id'] !== $businessId) {
                $this->json(['error' => 'Contacto individual inválido.'], 422);
            }
            $destinatarios[] = $contact;
        } else {
            $tipoEnvio = $segmento === 'todos' ? 'masivo' : 'segmento';
            $destinatarios = $this->segmentContacts($businessId, $segmento === 'todos' ? '' : $segmento);
        }

        // Solo destinatarios con WhatsApp/wa_id/phone
        $conWhatsapp = array_values(array_filter($destinatarios, fn($c) => $this->whatsappDeContacto($c) !== ''));

        if (empty($conWhatsapp)) {
            $this->json(['error' => 'No hay destinatarios con WhatsApp en el segmento seleccionado.'], 422);
        }

        $total = count($conWhatsapp);

        $idCampana = $this->mensajes->createCampana([
            'nombre'              => $nombre,
            'mensaje'             => $mensaje,
            'segmento'            => $segmento,
            'total_destinatarios' => $total,
            'creado_por'          => (int)(currentUser()['id'] ?? 0),
            'creado_en'           => date('Y-m-d H:i:s'),
        ]);

        $settings = new SettingModel();
        $template  = $settings->get('whatsapp_template_marketing', 'marketing_colonbot_texto');

        $envios = [];
        foreach ($conWhatsapp as $contact) {
            $waId = trim((string)($contact['wa_id'] ?? ''));
            $window = $this->mensajes->sessionWindow($waId !== '' ? $waId : $this->whatsappDeContacto($contact));

            // id_contacto solo si existe una fila real en contacts (evita violación de FK)
            $contactoReal = $this->contacts->find((int)($contact['id'] ?? 0));
            $idContacto = $contactoReal ? (int)$contactoReal['id'] : null;

            $envios[] = [
                'id_campana'               => $idCampana,
                'id_contacto'              => $idContacto,
                'whatsapp'                 => $this->whatsappDeContacto($contact),
                'mensaje'                  => $mensaje,
                'estado'                   => 'pendiente',
                'tipo_envio'               => $tipoEnvio,
                'ventana_24h_abierta'      => $window['ventana_24h_abierta'],
                'ultimo_mensaje_usuario_en'=> $window['ultimo_mensaje_usuario_en'],
                'template_nombre'          => $template,
                'intentos'                 => 0,
                'programado_en'            => date('Y-m-d H:i:s'),
            ];
        }

        $this->mensajes->addEnvios($envios);
        $this->logAction('crear_campana_mensajes', 'mensajes_campanas', $idCampana, "Campaña '{$nombre}' ({$segmento}) con {$total} destinatarios");

        $this->json(['ok' => true, 'id_campana' => $idCampana, 'total' => $total]);
    }

    /** Historial de campañas/envíos de un negocio (omitiendo "de prueba"). */
    public function historialCampanas(): void
    {
        $this->requireAuth('prestador');
        $businessId = (int)($_GET['business_id'] ?? 0);
        $business = $this->businesses->find($businessId);
        if (!$business) { $this->json(['error' => 'not found'], 404); }
        $this->ownerOrAdmin($business);

        $campanas = $this->mensajes->historialCampanas($businessId);
        $campanas = array_values(array_filter($campanas, fn($c) =>
            $this->mensajePermitido((string)($c['nombre'] ?? '')) && $this->mensajePermitido((string)($c['mensaje'] ?? ''))
        ));

        $this->json($campanas);
    }

    /** Detalle de envíos de una campaña. */
    public function detalleCampana(string $idCampana): void
    {
        $this->requireAuth('prestador');

        $envios = $this->mensajes->enviosDeCampana((int)$idCampana);

        // Verificar acceso: el usuario debe poder gestionar al menos un negocio
        // relacionado con los contactos de la campaña.
        $db = Database::getInstance();
        $tieneAcceso = false;
        foreach ($envios as $envio) {
            if (empty($envio['id_contacto'])) {
                continue;
            }
            $stmt = $db->prepare('SELECT business_id FROM contacts WHERE id = ? LIMIT 1');
            $stmt->execute([(int)$envio['id_contacto']]);
            $negocio = $stmt->fetch();
            if (!$negocio) {
                continue;
            }
            $business = $this->businesses->find((int)$negocio['business_id']);
            if ($business && $this->canManageBusiness($business)) {
                $tieneAcceso = true;
                break;
            }
        }

        if (!$tieneAcceso) {
            $this->json(['error' => 'No tienes permiso para ver esta campaña.'], 403);
        }

        $this->json($envios);
    }

    /**
     * Valida que el texto no contenga palabras tipo "prueba"/"test".
     * Retorna true si es un mensaje permitido.
     */
    private function mensajePermitido(string $texto): bool
    {
        $normalizado = mb_strtolower(trim($texto), 'UTF-8');
        $normalizado = strtr($normalizado, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        ]);

        // Coincidencias como subcadena (frases de prueba)
        $frasesProhibidas = [
            'mensaje de prueba',
            'mensaje de test',
            'mensajeprueba',
            'prueba',
            'pruebas',
            'probando',
        ];

        foreach ($frasesProhibidas as $frase) {
            if (str_contains($normalizado, $frase)) {
                return false;
            }
        }

        // "test" / "testing" como palabra completa
        if (preg_match('/\btest(?:ing)?\b/', $normalizado)) {
            return false;
        }

        return true;
    }

    /** Devuelve el WhatsApp (phone o wa_id) del contacto, o '' si no tiene. */
    private function whatsappDeContacto(array $contact): string
    {
        $phone = preg_replace('/\D/', '', (string)($contact['phone'] ?? ''));
        if ($phone !== '') {
            return $phone;
        }
        return preg_replace('/\D/', '', (string)($contact['wa_id'] ?? ''));
    }
    private function ownerOrAdmin(array $business): void
    {
        if (!$this->canManageBusiness($business)) {
            http_response_code(403);
            $this->json(['error' => 'No tienes permiso para gestionar contactos de este negocio.'], 403);
        }
    }

    /** true si el usuario actual puede gestionar el negocio (superadmin, colaborador_admin o dueño). */
    private function canManageBusiness(array $business): bool
    {
        $user = currentUser();
        $userRole = $this->normalizeRole($user['role'] ?? '');

        // SuperAdmin y Colaborador Admin tienen acceso completo a todos los negocios
        if ($userRole === 'superadmin' || $userRole === 'colaborador_admin') {
            return true;
        }

        // Prestador (o cualquier otro rol) solo gestiona sus propios negocios
        return (int)$business['user_id'] === (int)($user['id'] ?? 0);
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
