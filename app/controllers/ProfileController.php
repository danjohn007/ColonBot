<?php
class ProfileController extends Controller
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    public function index(): void
    {
        $this->requireAuth('prestador');
        $sessionUser = currentUser();
        $user = $this->users->find((int)$sessionUser['id']) ?: $sessionUser;
        $routePrefix = $this->pathForCurrentPrefix('');

        $this->view('profile.index', compact('user', 'routePrefix') + ['csrf' => $this->csrf()]);
    }

    public function update(): void
    {
        $this->requireAuth('prestador');
        $this->verifyCsrf();

        $user = currentUser();
        $data = [
            'name' => trim($_POST['name'] ?? $user['name']),
            'email' => trim($_POST['email'] ?? $user['email']),
            'phone' => trim($_POST['phone'] ?? ''),
        ];

        if (!empty($_POST['password'])) {
            $data['password'] = $this->users->hashPassword($_POST['password']);
        }

        $this->users->update((int)$user['id'], $data);

        // Update session
        $_SESSION['user']['name'] = $data['name'];
        if (isset($data['email'])) $_SESSION['user']['email'] = $data['email'];
        $_SESSION['user']['phone'] = $data['phone'];

        $this->flash('success', 'Perfil actualizado correctamente.');
        $this->redirectForCurrentPrefix('mi-perfil');
    }
}
