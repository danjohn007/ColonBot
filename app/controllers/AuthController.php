<?php
class AuthController extends Controller
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    public function loginForm(): void
    {
        if (isLoggedIn()) {
            $user = currentUser();
            $redirect = match ($user['role'] ?? '') {
                'visitor' => 'turista',
                'superadmin' => 'superadmin',
                'colaborador_admin' => 'colaborador',
                'prestador' => 'admin/crm',
                default => 'admin',
            };
            if (($user['role'] ?? '') === 'visitor') {
                $this->redirectForCurrentPrefix($redirect);
            }
            $this->redirect($redirect);
        }
        $a = random_int(1, 9);
        $b = random_int(1, 9);
        $_SESSION['captcha_sum'] = $a + $b;
        $routePrefix = $this->pathForCurrentPrefix('');
        $this->view('auth.login', ['csrf' => $this->csrf(), 'captchaA' => $a, 'captchaB' => $b, 'routePrefix' => $routePrefix]);
    }

    public function login(): void
    {
        $this->verifyCsrf();

        // Validate captcha
        $captchaInput    = isset($_POST['captcha']) ? (int)$_POST['captcha'] : null;
        $captchaExpected = isset($_SESSION['captcha_sum']) ? (int)$_SESSION['captcha_sum'] : null;
        unset($_SESSION['captcha_sum']);
        if ($captchaInput === null || $captchaExpected === null || $captchaInput !== $captchaExpected) {
            $this->flash('error', 'La verificación matemática es incorrecta.');
            $this->redirectForCurrentPrefix('login');
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $this->flash('error', 'Ingresa correo y contraseña.');
            $this->redirectForCurrentPrefix('login');
        }

        $user = $this->users->findByEmail($email);

        if (!$user || !$this->users->verifyPassword($password, $user['password'])) {
            $this->flash('error', 'Credenciales incorrectas.');
            $this->logAction('login_failed', 'users', 0, $email);
            $this->redirectForCurrentPrefix('login');
        }

        if (!$user['active']) {
            $this->flash('error', 'Tu cuenta está desactivada.');
            $this->redirectForCurrentPrefix('login');
        }

        $_SESSION['user'] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];

        $this->logAction('login', 'users', $user['id']);
        $redirect = match ($user['role']) {
            'superadmin'        => 'superadmin',
            'colaborador_admin' => 'colaborador',
            'visitor'           => 'turista',
            'prestador'         => 'admin/crm',
            default             => 'admin',
        };
        if ($user['role'] === 'visitor') {
            $this->redirectForCurrentPrefix($redirect);
        }
        $this->redirect($redirect);
    }

    public function logout(): void
    {
        $this->logAction('logout');
        session_destroy();
        $this->redirect('login');
    }

    public function forgotPasswordForm(): void
    {
        if (isLoggedIn()) {
            $user = currentUser();
            $this->redirect(($user['role'] ?? '') === 'visitor' ? 'turista' : (($user['role'] ?? '') === 'superadmin' ? 'superadmin' : 'admin'));
        }
        $this->view('auth.forgot_password', ['csrf' => $this->csrf()]);
    }

    public function forgotPassword(): void
    {
        $this->verifyCsrf();
        $email = trim($_POST['email'] ?? '');
        // Log regardless of whether email exists to prevent timing side-channel
        $this->logAction('forgot_password', 'users', 0, $email);
        // Always show success to prevent user enumeration
        $this->flash('success', 'Si el correo está registrado, contacta al administrador del sistema para recuperar tu acceso.');
        $this->redirect('olvide-contrasena');
    }
}
