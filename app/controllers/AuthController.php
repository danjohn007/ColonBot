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
        $this->view('auth.login', ['csrf' => $this->csrf()]);
    }

    public function login(): void
    {
        $this->verifyCsrf();

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $this->flash('error', 'Ingresa correo y contraseña.');
            $this->redirect('login');
        }

        // Validate math captcha
        $captchaInput  = isset($_POST['captcha']) ? (int)$_POST['captcha'] : null;
        $captchaAnswer = isset($_SESSION['captcha_answer']) ? (int)$_SESSION['captcha_answer'] : null;
        unset($_SESSION['captcha_answer']);
        if ($captchaInput === null || $captchaAnswer === null || $captchaInput !== $captchaAnswer) {
            $this->flash('error', 'Verificación incorrecta. Intenta de nuevo.');
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
}
