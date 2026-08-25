<?php
/**
 * Modelo de campañas de mensajes WhatsApp (CRM "Mensajes")
 * Tablas: mensajes_campanas y mensajes_envios (cola)
 */
class MensajeModel extends Model
{
    protected string $table = 'mensajes_campanas';

    /**
     * Crea una campaña y devuelve su id_campana.
     */
    public function createCampana(array $data): int
    {
        return $this->insert($data);
    }

    /**
     * Inserta una fila por destinatario en la cola de envíos.
     */
    public function addEnvios(array $rows): void
    {
        if (empty($rows)) {
            return;
        }
        $cols = [];
        foreach ($rows[0] as $key => $value) {
            $cols[] = "`{$key}`";
        }
        $placeholders = '(' . implode(',', array_fill(0, count($cols), '?')) . ')';
        $sql = 'INSERT INTO `mensajes_envios` (' . implode(',', $cols) . ') VALUES ' .
               implode(',', array_fill(0, count($rows), $placeholders));

        $params = [];
        foreach ($rows as $row) {
            foreach ($cols as $i => $col) {
                $params[] = $row[str_replace('`', '', $col)];
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    /**
     * Ventana de 24 horas: si el usuario escribió por chatbot recientemente.
     */
    public function sessionWindow(string $waId): array
    {
        if ($waId === '') {
            return ['ventana_24h_abierta' => 0, 'ultimo_mensaje_usuario_en' => null];
        }

        $row = $this->queryOne(
            'SELECT updated_at FROM chatbot_sessions WHERE wa_id = ? ORDER BY updated_at DESC LIMIT 1',
            [$waId]
        );

        if (!$row || empty($row['updated_at'])) {
            return ['ventana_24h_abierta' => 0, 'ultimo_mensaje_usuario_en' => null];
        }

        $last = $row['updated_at'];
        $open = (strtotime($last) !== false && strtotime($last) >= (time() - 24 * 3600)) ? 1 : 0;

        return [
            'ventana_24h_abierta'    => $open,
            'ultimo_mensaje_usuario_en' => $last,
        ];
    }

    /**
     * Historial de campañas de un negocio con conteo por estado de envío.
     * (mensajes_campanas no tiene business_id; se relaciona vía mensajes_envios -> contacts
     *  por id_contacto o por número de WhatsApp/phone.)
     */
    public function historialCampanas(int $businessId, int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));
        return $this->query(
            "SELECT mc.id_campana, mc.nombre, mc.mensaje, mc.segmento, mc.total_destinatarios,
                    mc.creado_por, mc.creado_en, u.name AS creador_nombre,
                    (SELECT COUNT(*) FROM mensajes_envios me WHERE me.id_campana = mc.id_campana) AS total_envios,
                    (SELECT COALESCE(SUM(me.estado = 'pendiente'), 0)     FROM mensajes_envios me WHERE me.id_campana = mc.id_campana) AS pendientes,
                    (SELECT COALESCE(SUM(me.estado = 'procesando'), 0)    FROM mensajes_envios me WHERE me.id_campana = mc.id_campana) AS procesando,
                    (SELECT COALESCE(SUM(me.estado = 'aceptado_meta'), 0) FROM mensajes_envios me WHERE me.id_campana = mc.id_campana) AS aceptados,
                    (SELECT COALESCE(SUM(me.estado = 'enviado'), 0)       FROM mensajes_envios me WHERE me.id_campana = mc.id_campana) AS enviados,
                    (SELECT COALESCE(SUM(me.estado = 'entregado'), 0)     FROM mensajes_envios me WHERE me.id_campana = mc.id_campana) AS entregados,
                    (SELECT COALESCE(SUM(me.estado = 'leido'), 0)         FROM mensajes_envios me WHERE me.id_campana = mc.id_campana) AS leidos,
                    (SELECT COALESCE(SUM(me.estado = 'error'), 0)         FROM mensajes_envios me WHERE me.id_campana = mc.id_campana) AS errores
             FROM mensajes_campanas mc
             LEFT JOIN users u ON u.id = mc.creado_por
             WHERE EXISTS (
                SELECT 1
                FROM mensajes_envios me
                INNER JOIN contacts c ON c.business_id = ?
                    AND (me.id_contacto = c.id OR me.whatsapp = c.phone OR me.whatsapp = c.wa_id)
                WHERE me.id_campana = mc.id_campana
             )
             ORDER BY mc.creado_en DESC
             LIMIT {$limit}",
            [$businessId]
        );
    }

    /**
     * Envíos (detalle) de una campaña.
     */
    public function enviosDeCampana(int $idCampana, int $limit = 300): array
    {
        $limit = max(1, min(1000, $limit));
        return $this->query(
            "SELECT me.*, c.name AS contacto_nombre
             FROM mensajes_envios me
             LEFT JOIN contacts c ON c.id = me.id_contacto
             WHERE me.id_campana = ?
             ORDER BY me.id_envio ASC
             LIMIT {$limit}",
            [$idCampana]
        );
    }
}