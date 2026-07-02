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
        $this->view('public.register_visitor', ['csrf' => $this->csrf()]);
    }

    /**
     * Procesar registro de Visitante
     */
    public function visitorRegister(): void
    {
        $this->verifyCsrf();

        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (!$name || !$email) {
            $this->flash('error', 'Nombre y email son requeridos.');
            $this->redirect('registro/visitante');
            return;
        }

        // Check if user already exists
        $existing = $this->users->findByEmail($email);
        if ($existing) {
            $this->flash('error', 'El email ya está registrado.');
            $this->redirect('registro/visitante');
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
            'email_code' => $emailCode,
            'created_at' => time(),
        ];

        // Send verification email
        $this->sendVerificationEmail($email, $emailCode, $name);

        $this->flash('success', 'Te hemos enviado un código de verificación a tu email.');
        $this->redirect('registro/verificar');
    }

    /**
     * Formulario de registro para Prestador
     */
    public function prestadorForm(): void
    {
        $this->view('public.register_prestador', ['csrf' => $this->csrf()]);
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

        if (!$name || !$email) {
            $this->flash('error', 'Nombre y email son requeridos.');
            $this->redirect('registro/prestador');
            return;
        }

        // Check if user already exists
        $existing = $this->users->findByEmail($email);
        if ($existing) {
            $this->flash('error', 'El email ya está registrado.');
            $this->redirect('registro/prestador');
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
            'email_code'    => $emailCode,
            'created_at'    => time(),
        ];

        // Send verification email
        $this->sendVerificationEmail($email, $emailCode, $name);

        $this->flash('success', 'Te hemos enviado un código de verificación a tu email.');
        $this->redirect('registro/verificar');
    }

    /**
     * Formulario de verificación de código
     */
    public function verifyForm(): void
    {
        if (!isset($_SESSION['pending_register'])) {
            $this->redirect('mapa');
            return;
        }
        $this->view('public.verify_code', [
            'csrf'  => $this->csrf(),
            'email' => $_SESSION['pending_register']['email'],
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
            $this->redirect('mapa');
            return;
        }

        $code = trim($_POST['code'] ?? '');

        $expectedCode = $pending['email_code'];

        if ((string)$code !== (string)$expectedCode) {
            $this->flash('error', 'El código ingresado no es correcto.');
            $this->redirect('registro/verificar');
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
        $password = bin2hex(random_bytes(8)); // Generate random password
        $role = $pending['type'] === 'prestador' ? 'prestador' : 'visitor';

        $userId = $this->users->insert([
            'name'     => $pending['name'],
            'email'    => $pending['email'],
            'password' => password_hash($password, PASSWORD_DEFAULT),
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

        $redirect = $role === 'prestador' ? 'admin' : 'mapa';
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