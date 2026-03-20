<?php
class SettingModel extends Model
{
    protected string $table = 'settings';

    public function allGrouped(): array
    {
        $rows   = $this->query('SELECT `key`, `value`, `group` FROM settings ORDER BY `group`, `key`');
        $groups = [];
        foreach ($rows as $row) {
            $groups[$row['group']][$row['key']] = $row['value'];
        }
        return $groups;
    }

    public function get(string $key, string $default = ''): string
    {
        $row = $this->queryOne('SELECT `value` FROM settings WHERE `key` = ? LIMIT 1', [$key]);
        return $row ? (string)$row['value'] : $default;
    }

    public function set(string $key, string $value, string $group = 'general'): void
    {
        $this->execute(
            'INSERT INTO settings (`key`, `value`, `group`) VALUES (?,?,?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
            [$key, $value, $group]
        );
    }

    public function saveMany(array $data, string $group = 'general'): void
    {
        foreach ($data as $key => $value) {
            $this->set($key, (string)$value, $group);
        }
    }
}
