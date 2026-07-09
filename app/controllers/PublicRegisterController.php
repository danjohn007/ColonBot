<?php
/**
 * Controlador de registro público para Visitantes y Prestadores
 * Con verificación: Email o SMS (solo un método requerido)
 */
class PublicRegisterController extends Controller
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    /**
     * Formulario de registro para Visitante
     */
    public function visitorForm(): void
    {
        $returnTo = $this->rememberVisitorReturnTo();
        if (isLoggedIn()) {
            $user = currentUser();
            if (($user['role'] ?? '') === 'visitor' && $returnTo !== '') {
                unset($_SESSION['visitor_return_to']);
                $this->redirect($returnTo);
            }
            $this->redirectForCurrentPrefix(($user['role'] ?? '') === 'visitor' ? 'turista' : 'mapa');
        }
        $routePrefix = $this->pathForCurrentPrefix('');
        $this->view('public.register_visitor', ['csrf' => $this->csrf(), 'routePrefix' => $routePrefix, 'returnTo' => $returnTo]);
    }

    /**
     * Inicio de sesión para Visitante
     */
    public function visitorLogin(): void
    {
        $this->verifyCsrf();

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $returnTo = $this->rememberVisitorReturnTo();

        if (!$email || !$password) {
            $this->flash('error', 'Ingresa correo y contraseña.');
            $this->redirectToVisitorForm();
            return;
        }

        $user = $this->users->findByEmail($email);
        if (!$user || !$this->users->verifyPassword($password, $user['password'])) {
            $this->flash('error', 'Credenciales incorrectas.');
            $this->redirectToVisitorForm();
            return;
        }

        if (!$user['active']) {
            $this->flash('error', 'Tu cuenta está desactivada.');
            $this->redirectToVisitorForm();
            return;
        }

        if ($user['role'] !== 'visitor') {
            $this->flash('error', 'Esta cuenta no es de tipo visitante.');
            $this->redirectToVisitorForm();
            return;
        }

        $_SESSION['user'] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];

        $this->logAction('visitor_login', 'users', $user['id']);
        unset($_SESSION['visitor_return_to']);
        if ($returnTo !== '') {
            $this->redirect($returnTo);
        }
        $this->redirectForCurrentPrefix('turista'); // Redirigir al dashboard del visitante
    }

    /**
     * Procesar registro de Visitante
     */
    public function visitorRegister(): void
    {
        $this->verifyCsrf();

        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $returnTo = $this->rememberVisitorReturnTo();

        if (!$name || !$email || !$password || !$passwordConfirm) {
            $this->flash('error', 'Nombre, email y contrasena son requeridos.');
            $this->redirectToVisitorForm();
            return;
        }

        if (strlen($password) < 8) {
            $this->flash('error', 'La contrasena debe tener al menos 8 caracteres.');
            $this->redirectToVisitorForm();
            return;
        }

        if ($password !== $passwordConfirm) {
            $this->flash('error', 'Las contrasenas no coinciden.');
            $this->redirectToVisitorForm();
            return;
        }

        // Check if user already exists
        $existing = $this->users->findByEmail($email);
        if ($existing) {
            $this->flash('error', 'El email ya está registrado.');
            $this->redirectToVisitorForm();
            return;
        }

        // Generate verification code
        $emailCode = random_int(100000, 999999);

        // Store pending registration in session
        $_SESSION['pending_register'] = [
            'type'       => 'visitor',
            'name'       => $name,
            'email'      => $email,
            'phone'      => '',
            'password_hash' => $this->users->hashPassword($password),
            'email_code' => $emailCode,
            'return_to' => $returnTo,
            'created_at' => time(),
        ];

        // Send verification email
        $this->sendVerificationEmail($email, $emailCode, $name);

        $this->flash('success', 'Te hemos enviado un código de verificación a tu email.');
        $this->redirectForCurrentPrefix('registro/verificar');
    }

    /**
     * Formulario de registro para Prestador
     */
    public function prestadorForm(): void
    {
        if (isLoggedIn()) {
            $role = $this->normalizeRole(currentUser()['role'] ?? '');
            $this->redirect($role === 'prestador' ? 'admin/crm' : 'admin');
        }
        $routePrefix = $this->pathForCurrentPrefix('');
        $this->view('public.register_prestador', ['csrf' => $this->csrf(), 'routePrefix' => $routePrefix]);
    }

    /**
     * Inicio de sesión para Prestador
     */
    public function prestadorLogin(): void
    {
        $this->verifyCsrf();

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $this->flash('error', 'Ingresa correo y contraseña.');
            $this->redirectForCurrentPrefix('registro/prestador');
            return;
        }

        $user = $this->users->findByEmail($email);
        if (!$user || !$this->users->verifyPassword($password, $user['password'])) {
            $this->flash('error', 'Credenciales incorrectas.');
            $this->redirectForCurrentPrefix('registro/prestador');
            return;
        }

        if (!$user['active']) {
            $this->flash('error', 'Tu cuenta está desactivada.');
            $this->redirectForCurrentPrefix('registro/prestador');
            return;
        }

        $role = $this->normalizeRole($user['role']);
        if (!in_array($role, ['prestador', 'colaborador_admin', 'superadmin'])) {
            $this->flash('error', 'Esta cuenta no es de tipo prestador.');
            $this->redirectForCurrentPrefix('registro/prestador');
            return;
        }

        $_SESSION['user'] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $role,
            'phone' => $user['phone'] ?? '',
        ];

        $this->logAction('prestador_login', 'users', $user['id']);
        $redirect = match ($role) {
            'superadmin' => 'superadmin',
            'colaborador_admin' => 'colaborador',
            'prestador' => 'admin/crm',
            default => 'admin/crm',
        };
        $this->redirect($redirect);
    }

    private function rememberVisitorReturnTo(): string
    {
        $returnTo = $this->sanitizeReturnTo((string)($_POST['return_to'] ?? $_GET['return_to'] ?? $_SESSION['visitor_return_to'] ?? ''));
        if ($returnTo !== '') {
            $_SESSION['visitor_return_to'] = $returnTo;
        }
        return $returnTo;
    }

    private function redirectToVisitorForm(): void
    {
        $path = 'registro/visitante';
        $returnTo = (string)($_SESSION['visitor_return_to'] ?? '');
        if ($returnTo !== '') {
            $path .= '?return_to=' . rawurlencode($returnTo);
        }
        $this->redirectForCurrentPrefix($path);
    }

    private function sanitizeReturnTo(string $returnTo): string
    {
        $returnTo = trim(str_replace('\\', '/', $returnTo));
        $basePath = trim((string)(parse_url(BASE_URL, PHP_URL_PATH) ?? ''), '/');

        if ($returnTo === '' || strpos($returnTo, '//') === 0 || preg_match('/^[a-z][a-z0-9+.-]*:/i', $returnTo)) {
            return '';
        }

        $returnTo = ltrim($returnTo, '/');
        if ($basePath !== '' && ($returnTo === $basePath || strpos($returnTo, $basePath . '/') === 0)) {
            $returnTo = trim(substr($returnTo, strlen($basePath)), '/');
        }

        if (strpos($returnTo, '..') !== false) {
            return '';
        }

        return preg_match('/^(landing\/)?(lugar\/[A-Za-z0-9._~%-]+|mapa(\/[A-Za-z0-9._~%-]+)?|turista)(#[A-Za-z0-9_-]+|\?.*)?$/', $returnTo) ? $returnTo : '';
    }

    /**
     * Procesar registro de Prestador
     */
    public function prestadorRegister(): void
    {
        $this->verifyCsrf();

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $negocio  = trim($_POST['business_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if (!$name || !$email || !$password || !$passwordConfirm) {
            $this->flash('error', 'Nombre, email y contrasena son requeridos.');
            $this->redirectForCurrentPrefix('registro/prestador');
            return;
        }

        if (strlen($password) < 8) {
            $this->flash('error', 'La contrasena debe tener al menos 8 caracteres.');
            $this->redirectForCurrentPrefix('registro/prestador');
            return;
        }

        if ($password !== $passwordConfirm) {
            $this->flash('error', 'Las contrasenas no coinciden.');
            $this->redirectForCurrentPrefix('registro/prestador');
            return;
        }

        // Check if user already exists
        $existing = $this->users->findByEmail($email);
        if ($existing) {
            $this->flash('error', 'El email ya está registrado.');
            $this->redirectForCurrentPrefix('registro/prestador');
            return;
        }

        // Generate verification code
        $emailCode = random_int(100000, 999999);

        // Store pending registration in session
        $_SESSION['pending_register'] = [
            'type'          => 'prestador',
            'name'          => $name,
            'email'         => $email,
            'phone'         => '',
            'business_name' => $negocio,
            'password_hash' => $this->users->hashPassword($password),
            'email_code'    => $emailCode,
            'created_at'    => time(),
        ];

        // Send verification email
        $this->sendVerificationEmail($email, $emailCode, $name);

        $this->flash('success', 'Te hemos enviado un código de verificación a tu email.');
        $this->redirectForCurrentPrefix('registro/verificar');
    }

    /**
     * Formulario de verificación de código
     */
    public function verifyForm(): void
    {
        if (!isset($_SESSION['pending_register'])) {
            $this->redirectForCurrentPrefix('mapa');
            return;
        }
        $routePrefix = $this->pathForCurrentPrefix('');
        $this->view('public.verify_code', [
            'csrf'        => $this->csrf(),
            'email'       => $_SESSION['pending_register']['email'],
            'routePrefix' => $routePrefix,
        ]);
    }

    /**
     * Verificar el código ingresado por el usuario
     * Solo verificación por email
     */
    public function verifyCode(): void
    {
        $this->verifyCsrf();

        $pending = $_SESSION['pending_register'] ?? null;
        if (!$pending) {
            $this->flash('error', 'No hay registro pendiente.');
            $this->redirectForCurrentPrefix('mapa');
            return;
        }

        $code = trim($_POST['code'] ?? '');

        $expectedCode = $pending['email_code'];

        if ((string)$code !== (string)$expectedCode) {
            $this->flash('error', 'El código ingresado no es correcto.');
            $this->redirectForCurrentPrefix('registro/verificar');
            return;
        }

        // Complete registration immediately
        $this->completeRegistration($pending);
    }

    /**
     * Reenviar código de verificación
     */
    public function resendCode(): void
    {
        $this->verifyCsrf();

        $pending = $_SESSION['pending_register'] ?? null;
        if (!$pending) {
            $this->json(['error' => 'No hay registro pendiente'], 400);
            return;
        }

        $newCode = random_int(100000, 999999);
        $_SESSION['pending_register']['email_code'] = $newCode;
        $this->sendVerificationEmail($pending['email'], $newCode, $pending['name']);

        $this->json(['ok' => true, 'message' => 'Código reenviado.']);
    }

    /**
     * Completa el registro creando el usuario en la base de datos
     */
    private function completeRegistration(array $pending): void
    {
        $passwordHash = $pending['password_hash'] ?? password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
        $role = $pending['type'] === 'prestador' ? 'prestador' : 'visitor';

        $userId = $this->users->insert([
            'name'     => $pending['name'],
            'email'    => $pending['email'],
            'password' => $passwordHash,
            'phone'    => $pending['phone'] ?? '',
            'role'     => $role,
            'active'   => 1,
        ]);

        // Auto-login
        $_SESSION['user'] = [
            'id'    => $userId,
            'name'  => $pending['name'],
            'email' => $pending['email'],
            'role'  => $role,
        ];

        unset($_SESSION['pending_register']);

        $this->logAction('public_register', 'users', $userId, "Registro público como $role");

        $returnTo = $this->sanitizeReturnTo((string)($pending['return_to'] ?? $_SESSION['visitor_return_to'] ?? ''));
        unset($_SESSION['visitor_return_to']);

        $redirect = $role === 'prestador' ? 'admin/crm' : ($returnTo ?: $this->pathForCurrentPrefix('turista'));
        $this->flash('success', 'Registro completado exitosamente. Bienvenido a CristobalBot.');
        $this->redirect($redirect);
    }

    /**
     * Enviar email de verificación
     */
    private function sendVerificationEmail(string $email, string $code, string $name): void
    {
        $subject = 'CristobalBot - Código de verificación';
        $message = "Hola $name,\n\n";
        $message .= "Tu código de verificación para registrarte en CristobalBot es: $code\n\n";
        $message .= "Ingresa este código en la página de verificación para completar tu registro.\n\n";
        $message .= "Si no solicitaste este registro, ignora este mensaje.\n\n";
        $message .= "Saludos,\nEquipo de Turismo Colón";

        $headers = 'From: no-reply@colon.click' . "\r\n" .
                   'Reply-To: no-reply@colon.click' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();

        @mail($email, $subject, $message, $headers);
    }
}
