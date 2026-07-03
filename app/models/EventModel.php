<?php
class EventModel extends Model
{
    protected string $table = 'events';

    public function byBusiness(int $businessId): array
    {
        return $this->query(
            'SELECT e.*, u.name AS creator_name, a.name AS approver_name 
             FROM events e 
             LEFT JOIN users u ON u.id = e.user_id 
             LEFT JOIN users a ON a.id = e.approved_by 
             WHERE e.business_id = ? 
             ORDER BY e.created_at DESC',
            [$businessId]
        );
    }

    public function pendingForApproval(): array
    {
        return $this->query(
            'SELECT e.*, u.name AS creator_name, b.name AS business_name 
             FROM events e 
             LEFT JOIN users u ON u.id = e.user_id 
             LEFT JOIN businesses b ON b.id = e.business_id 
             WHERE e.status = "pending" 
             ORDER BY e.created_at ASC'
        );
    }

    public function approve(int $id, int $approvedBy): void
    {
        $this->update($id, [
            'status' => 'approved',
            'approved_by' => $approvedBy,
        ]);
    }

    public function active(): array
    {
        return $this->query(
            "SELECT e.*, b.name AS business_name, b.slug AS business_slug,
                    b.address, b.whatsapp, b.phone, b.lat, b.lng
             FROM events e
             LEFT JOIN businesses b ON b.id = e.business_id
             WHERE e.status = 'active' 
             AND (e.start_date IS NULL OR e.start_date <= NOW())
             AND (e.end_date IS NULL OR e.end_date >= NOW())"
        );
    }

    public function allWithBusiness(): array
    {
        return $this->query(
            'SELECT e.*, b.name AS business_name, b.slug AS business_slug,
                    b.address, b.whatsapp, b.phone, b.lat, b.lng
             FROM events e 
             LEFT JOIN businesses b ON b.id = e.business_id 
             ORDER BY e.created_at DESC'
        );
    }
}