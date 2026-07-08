<?php
class PromotionModel extends Model
{
    protected string $table = 'promotions';

    public function byBusiness(int $businessId): array
    {
        return $this->query(
            'SELECT p.*, u.name AS creator_name, a.name AS approver_name,
                    COALESCE(pv.view_count, 0) AS view_count,
                    COALESCE(pi.inquiry_count, 0) AS inquiry_count
             FROM promotions p 
             LEFT JOIN users u ON u.id = p.user_id 
             LEFT JOIN users a ON a.id = p.approved_by 
             LEFT JOIN (
                SELECT promotion_id, COUNT(*) AS view_count
                FROM promotion_views
                GROUP BY promotion_id
             ) pv ON pv.promotion_id = p.id
             LEFT JOIN (
                SELECT promotion_id, COUNT(*) AS inquiry_count
                FROM promotion_inquiries
                GROUP BY promotion_id
             ) pi ON pi.promotion_id = p.id
             WHERE p.business_id = ? 
             ORDER BY p.created_at DESC',
            [$businessId]
        );
    }

    public function globalPromotions(): array
    {
        return $this->query(
            'SELECT p.*, u.name AS creator_name, a.name AS approver_name 
             FROM promotions p 
             LEFT JOIN users u ON u.id = p.user_id 
             LEFT JOIN users a ON a.id = p.approved_by 
             WHERE p.business_id IS NULL AND p.status = "approved" 
             ORDER BY p.created_at DESC'
        );
    }

    public function pendingForApproval(): array
    {
        return $this->query(
            'SELECT p.*, u.name AS creator_name, b.name AS business_name 
             FROM promotions p 
             LEFT JOIN users u ON u.id = p.user_id 
             LEFT JOIN businesses b ON b.id = p.business_id 
             WHERE p.status = "pending" 
             ORDER BY p.created_at ASC'
        );
    }

    public function approve(int $id, int $approvedBy): void
    {
        $this->update($id, [
            'status' => 'active',
            'approved_by' => $approvedBy,
        ]);
    }

    public function active(): array
    {
        return $this->query(
            "SELECT p.*, b.name AS business_name, b.slug AS business_slug
             FROM promotions p
             LEFT JOIN businesses b ON b.id = p.business_id
             WHERE p.type = 'promocion'
             AND p.status IN ('active', 'approved')
             AND (p.end_date IS NULL OR p.end_date >= NOW())
             ORDER BY COALESCE(p.start_date, p.created_at) ASC, p.id DESC"
        );
    }

    public function activeEvents(): array
    {
        return $this->query(
            "SELECT p.*, b.name AS business_name, b.slug AS business_slug,
                    b.address, b.whatsapp, b.phone, b.lat, b.lng,
                    b.google_maps_link, b.name AS business_name_full
             FROM promotions p
             LEFT JOIN businesses b ON b.id = p.business_id
             WHERE p.type = 'evento'
             AND p.status IN ('active', 'approved')
             AND (p.end_date IS NULL OR p.end_date >= NOW())
             ORDER BY p.start_date ASC"
        );
    }

    public function publicEvents(): array
    {
        return $this->query(
            "SELECT * FROM promotions
             WHERE type = 'evento'
             AND status IN ('active', 'approved')
             AND (start_date IS NULL OR start_date >= NOW())
             ORDER BY start_date ASC"
        );
    }

    public function getTargetContacts(int $promotionId): array
    {
        $promo = $this->find($promotionId);
        if (!$promo) return [];

        $segments = explode(',', $promo['target_segment']);
        $businessId = $promo['business_id'];

        if (in_array('todos', $segments)) {
            return $this->query(
                "SELECT * FROM contacts WHERE business_id = ? ORDER BY created_at DESC",
                [$businessId]
            );
        }

        $conditions = [];
        $params = [$businessId];

        if (in_array('clientes_frecuentes', $segments)) {
            $conditions[] = "c.category = 'lovemark'";
        }
        if (in_array('clientes', $segments)) {
            $conditions[] = "c.category = 'cliente'";
        }
        if (in_array('prospectos_recurrentes', $segments)) {
            $conditions[] = "(c.category = 'prospecto' AND c.last_contact_at IS NOT NULL)";
        }
        if (in_array('prospectos_sin_historial', $segments)) {
            $conditions[] = "(c.category = 'prospecto' AND c.last_contact_at IS NULL)";
        }

        if (empty($conditions)) return [];

        $sql = "SELECT * FROM contacts c WHERE c.business_id = ? AND (" . implode(' OR ', $conditions) . ") ORDER BY c.created_at DESC";
        return $this->query($sql, $params);
    }

    public function logSend(int $promotionId, ?int $contactId, string $via = 'whatsapp'): void
    {
        $this->execute(
            'INSERT INTO promotion_sends (promotion_id, contact_id, sent_via) VALUES (?,?,?)',
            [$promotionId, $contactId, $via]
        );
    }

    public function getSendHistory(int $promotionId): array
    {
        return $this->query(
            'SELECT ps.*, c.name AS contact_name, c.phone AS contact_phone 
             FROM promotion_sends ps 
             LEFT JOIN contacts c ON c.id = ps.contact_id 
             WHERE ps.promotion_id = ? 
             ORDER BY ps.sent_at DESC',
            [$promotionId]
        );
    }
}
