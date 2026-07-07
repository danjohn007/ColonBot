<?php
/**
 * Modelo para la tabla 'consultas'
 * Almacena acciones de usuarios del chatbot:
 * - solicitar_informacion: Cuando un usuario ve detalles de un negocio
 * - compra_reservacion: Cuando un usuario selecciona 'Reservar' en un negocio
 */
class ConsultaModel extends Model
{
    protected string $table = 'consultas';

    /**
     * Registrar una acción de solicitar información sobre un negocio
     */
    public function registrarSolicitudInfo(string $waId, int $businessId, string $detalle = ''): int
    {
        return $this->insert([
            'wa_id' => $waId,
            'tipo_accion' => 'solicitar_informacion',
            'business_id' => $businessId,
            'detalle' => $detalle ?: 'Solicitó información del negocio',
        ]);
    }

    /**
     * Registrar una acción de compra/reservación en un negocio
     */
    public function registrarCompraReservacion(string $waId, int $businessId, string $detalle = ''): int
    {
        return $this->insert([
            'wa_id' => $waId,
            'tipo_accion' => 'compra_reservacion',
            'business_id' => $businessId,
            'detalle' => $detalle ?: 'Realizó una reservación en el negocio',
        ]);
    }

    /**
     * Contar cuántas compras/reservaciones ha hecho un usuario en un negocio específico
     */
    public function countComprasByBusiness(string $waId, int $businessId): int
    {
        $row = $this->queryOne(
            'SELECT COUNT(*) AS total FROM consultas 
             WHERE wa_id = ? AND business_id = ? AND tipo_accion = "compra_reservacion"',
            [$waId, $businessId]
        );
        return (int)($row['total'] ?? 0);
    }

    /**
     * Verificar si un usuario ha realizado una acción específica
     */
    public function hasRealizadoAccion(string $waId, string $tipoAccion): bool
    {
        $row = $this->queryOne(
            'SELECT COUNT(*) AS total FROM consultas WHERE wa_id = ? AND tipo_accion = ?',
            [$waId, $tipoAccion]
        );
        return ((int)($row['total'] ?? 0)) > 0;
    }

    /**
     * Obtener todas las acciones de un usuario
     */
    public function accionesByWaId(string $waId): array
    {
        return $this->query(
            'SELECT c.*, b.name AS business_name
             FROM consultas c
             LEFT JOIN businesses b ON b.id = c.business_id
             WHERE c.wa_id = ?
             ORDER BY c.created_at DESC',
            [$waId]
        );
    }

    /**
     * Obtener todas las consultas con datos del negocio
     */
    public function allWithBusiness(): array
    {
        return $this->query(
            'SELECT c.*, b.name AS business_name
             FROM consultas c
             LEFT JOIN businesses b ON b.id = c.business_id
             ORDER BY c.created_at DESC'
        );
    }
}