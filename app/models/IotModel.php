<?php
class IotModel extends Model
{
    protected string $table = 'iot_hikvision';

    public function allHikvision(): array
    {
        return $this->query('SELECT * FROM iot_hikvision ORDER BY name');
    }

    public function findHikvision(int $id): ?array
    {
        return $this->queryOne('SELECT * FROM iot_hikvision WHERE id = ? LIMIT 1', [$id]);
    }

    public function createHikvision(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO iot_hikvision (name, ip, port, username, password, stream_url, type, location, active)
             VALUES (?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $data['name'], $data['ip'], $data['port'] ?? 80,
            $data['username'] ?? 'admin', $data['password'],
            $data['stream_url'] ?? null, $data['type'] ?? 'camera',
            $data['location'] ?? null, 1,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function deleteHikvision(int $id): void
    {
        $this->execute('DELETE FROM iot_hikvision WHERE id = ?', [$id]);
    }

    public function allShelly(): array
    {
        return $this->query('SELECT * FROM iot_shelly ORDER BY name');
    }

    public function findShelly(int $id): ?array
    {
        return $this->queryOne('SELECT * FROM iot_shelly WHERE id = ? LIMIT 1', [$id]);
    }

    public function createShelly(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO iot_shelly (name, device_id, auth_key, server_uri, type, location, active)
             VALUES (?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $data['name'], $data['device_id'], $data['auth_key'],
            $data['server_uri'] ?? 'https://shelly-41-eu.shelly.cloud',
            $data['type'] ?? 'relay', $data['location'] ?? null, 1,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function deleteShelly(int $id): void
    {
        $this->execute('DELETE FROM iot_shelly WHERE id = ?', [$id]);
    }

    public function allGps(): array
    {
        return $this->query('SELECT * FROM gps_trackers ORDER BY name');
    }

    public function findGps(int $id): ?array
    {
        return $this->queryOne('SELECT * FROM gps_trackers WHERE id = ? LIMIT 1', [$id]);
    }

    public function createGps(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO gps_trackers (name, imei, api_key, provider, active) VALUES (?,?,?,?,?)'
        );
        $stmt->execute([
            $data['name'], $data['imei'], $data['api_key'] ?? null,
            $data['provider'] ?? null, 1,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function deleteGps(int $id): void
    {
        $this->execute('DELETE FROM gps_trackers WHERE id = ?', [$id]);
    }

    public function updateGpsPosition(string $imei, float $lat, float $lng): void
    {
        $this->execute(
            'UPDATE gps_trackers SET last_lat=?, last_lng=?, last_seen=NOW() WHERE imei=?',
            [$lat, $lng, $imei]
        );
    }
}
