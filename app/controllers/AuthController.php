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
            $this->redirect(hasRole('superadmin') ? 'superadmin' : 'admin');
        }
        $a = random_int(1, 9);
        $b = random_int(1, 9);
        $_SESSION['captcha_sum'] = $a + $b;
        $this->view('auth.login', ['csrf' => $this->csrf(), 'captchaA' => $a, 'captchaB' => $b]);
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
            $this->redirect('login');
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $this->flash('error', 'Ingresa correo y contraseña.');
            $this->redirect('login');
        }

        $user = $this->users->findByEmail($email);

        if (!$user || !$this->users->verifyPassword($password, $user['password'])) {
            $this->flash('error', 'Credenciales incorrectas.');
            $this->logAction('login_failed', 'users', 0, $email);
            $this->redirect('login');
        }

        if (!$user['active']) {
            $this->flash('error', 'Tu cuenta está desactivada.');
            $this->redirect('login');
        }

        $_SESSION['user'] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];

        $this->logAction('login', 'users', $user['id']);
        $this->redirect($user['role'] === 'superadmin' ? 'superadmin' : 'admin');
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
            $this->redirect(hasRole('superadmin') ? 'superadmin' : 'admin');
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
