<?php
/**
 * Clase base de Modelos
 */
abstract class Model
{
    protected PDO $db;
    protected string $table = '';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function all(string $orderBy = 'id', string $dir = 'ASC'): array
    {
        $dir  = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        $stmt = $this->db->query("SELECT * FROM `{$this->table}` ORDER BY `{$orderBy}` {$dir}");
        return $stmt->fetchAll();
    }

    public function insert(array $data): int
    {
        $cols = implode(',', array_map(fn($k) => "`{$k}`", array_keys($data)));
        $vals = implode(',', array_fill(0, count($data), '?'));
        $stmt = $this->db->prepare("INSERT INTO `{$this->table}` ({$cols}) VALUES ({$vals})");
        $stmt->execute(array_values($data));
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sets = implode(',', array_map(fn($k) => "`{$k}` = ?", array_keys($data)));
        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET {$sets} WHERE id = ?");
        return $stmt->execute([...array_values($data), $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function count(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM `{$this->table}`")->fetchColumn();
    }

    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    protected function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    protected function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
