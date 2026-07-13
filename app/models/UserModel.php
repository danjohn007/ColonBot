<?php
class UserModel extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        return $this->queryOne('SELECT * FROM users WHERE email = ? LIMIT 1', [$email]);
    }

    public function admins(): array
    {
        return $this->query("SELECT * FROM users WHERE role IN ('colaborador_admin','superadmin') AND active = 1 ORDER BY name");
    }

    public function visitors(): array
    {
        return $this->query("SELECT * FROM users WHERE role = 'visitor' AND active = 1 ORDER BY name");
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}
