<?php
class BusinessModel extends Model
{
    protected string $table = 'businesses';

    public function published(): array
    {
        return $this->query(
            'SELECT b.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon
             FROM businesses b
             JOIN categories c ON c.id = b.category_id
             WHERE b.status = "published"
             ORDER BY b.featured DESC, b.rating DESC'
        );
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->queryOne(
            'SELECT b.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon
             FROM businesses b
             JOIN categories c ON c.id = b.category_id
             WHERE b.slug = ? LIMIT 1',
            [$slug]
        );
    }

    public function byUser(int $userId): array
    {
        return $this->query(
            'SELECT b.*, c.name AS category_name FROM businesses b
             JOIN categories c ON c.id = b.category_id
             WHERE b.user_id = ? ORDER BY b.created_at DESC',
            [$userId]
        );
    }

    public function withFilters(array $filters): array
    {
        $sql    = 'SELECT b.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon
                   FROM businesses b JOIN categories c ON c.id = b.category_id
                   WHERE b.status = "published"';
        $params = [];

        if (!empty($filters['category'])) {
            $sql .= ' AND c.slug = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['search'])) {
            $sql .= ' AND (b.name LIKE ? OR b.description LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params[] = $like;
            $params[] = $like;
        }
        $sql .= ' ORDER BY b.featured DESC, b.rating DESC';
        return $this->query($sql, $params);
    }

    public function images(int $businessId): array
    {
        return $this->query('SELECT * FROM business_images WHERE business_id = ? ORDER BY sort_order', [$businessId]);
    }

    public function services(int $businessId): array
    {
        return $this->query('SELECT * FROM services WHERE business_id = ? AND active = 1', [$businessId]);
    }

    public function allServices(int $businessId): array
    {
        return $this->query('SELECT * FROM services WHERE business_id = ? ORDER BY id', [$businessId]);
    }

    public function products(int $businessId): array
    {
        return $this->query('SELECT * FROM products WHERE business_id = ? AND available = 1 ORDER BY sort_order', [$businessId]);
    }

    public function allProducts(int $businessId): array
    {
        return $this->query('SELECT * FROM products WHERE business_id = ? ORDER BY sort_order, id', [$businessId]);
    }

    public function amenities(int $businessId): array
    {
        return $this->query(
            'SELECT a.* FROM amenities a
             JOIN business_amenities ba ON ba.amenity_id = a.id
             WHERE ba.business_id = ?',
            [$businessId]
        );
    }

    public function businessCategories(int $businessId): array
    {
        return $this->query(
            'SELECT c.* FROM categories c
             JOIN business_categories bc ON bc.category_id = c.id
             WHERE bc.business_id = ?
             ORDER BY c.sort_order, c.name',
            [$businessId]
        );
    }

    public function syncCategories(int $businessId, array $categoryIds): void
    {
        $this->execute('DELETE FROM business_categories WHERE business_id = ?', [$businessId]);
        foreach ($categoryIds as $catId) {
            $this->execute(
                'INSERT IGNORE INTO business_categories (business_id, category_id) VALUES (?,?)',
                [$businessId, (int)$catId]
            );
        }
    }

    public function allWithCategory(): array
    {
        return $this->query(
            'SELECT b.*, c.name AS category_name, u.name AS owner_name
             FROM businesses b
             JOIN categories c ON c.id = b.category_id
             JOIN users u ON u.id = b.user_id
             ORDER BY b.created_at DESC'
        );
    }

    public function incrementVisit(int $id): void
    {
        $this->execute('UPDATE businesses SET visits = visits + 1 WHERE id = ?', [$id]);
    }

    public function addImage(int $businessId, string $path, string $caption = ''): void
    {
        $this->execute(
            'INSERT INTO business_images (business_id, path, caption) VALUES (?,?,?)',
            [$businessId, $path, $caption]
        );
    }

    public function findImage(int $id): ?array
    {
        return $this->queryOne('SELECT * FROM business_images WHERE id = ? LIMIT 1', [$id]);
    }

    public function deleteImage(int $id): void
    {
        $this->execute('DELETE FROM business_images WHERE id = ?', [$id]);
    }

    public function syncAmenities(int $businessId, array $amenityIds): void
    {
        $this->execute('DELETE FROM business_amenities WHERE business_id = ?', [$businessId]);
        foreach ($amenityIds as $aId) {
            $this->execute(
                'INSERT IGNORE INTO business_amenities (business_id, amenity_id) VALUES (?,?)',
                [$businessId, (int)$aId]
            );
        }
    }

    public function upsertService(int $businessId, array $data, int $serviceId = 0): void
    {
        if ($serviceId > 0) {
            $this->execute(
                'UPDATE services SET name=?, description=?, price=? WHERE id=? AND business_id=?',
                [$data['name'], $data['description'] ?? null, $data['price'] ?? null, $serviceId, $businessId]
            );
        } else {
            $this->execute(
                'INSERT INTO services (business_id, name, description, price) VALUES (?,?,?,?)',
                [$businessId, $data['name'], $data['description'] ?? null, $data['price'] ?? null]
            );
        }
    }

    public function upsertProduct(int $businessId, array $data, int $productId = 0): void
    {
        if ($productId > 0) {
            $this->execute(
                'UPDATE products SET name=?, description=?, price=?, available=? WHERE id=? AND business_id=?',
                [$data['name'], $data['description'] ?? null, $data['price'] ?? null, $data['available'] ?? 1, $productId, $businessId]
            );
        } else {
            $this->execute(
                'INSERT INTO products (business_id, name, description, price, available) VALUES (?,?,?,?,?)',
                [$businessId, $data['name'], $data['description'] ?? null, $data['price'] ?? null, $data['available'] ?? 1]
            );
        }
    }

    public function deleteService(int $serviceId, int $businessId): void
    {
        $this->execute('DELETE FROM services WHERE id = ? AND business_id = ?', [$serviceId, $businessId]);
    }

    public function deleteProduct(int $productId, int $businessId): void
    {
        $this->execute('DELETE FROM products WHERE id = ? AND business_id = ?', [$productId, $businessId]);
    }

    public function allEvents(int $businessId): array
    {
        return $this->query('SELECT * FROM events WHERE business_id = ? ORDER BY date, id', [$businessId]);
    }

    public function upsertEvent(int $businessId, array $data, int $eventId = 0): void
    {
        if ($eventId > 0) {
            $this->execute(
                'UPDATE events SET name=?, description=?, price=?, date=? WHERE id=? AND business_id=?',
                [$data['name'], $data['description'] ?? null, $data['price'] ?? null, $data['date'] ?? null, $eventId, $businessId]
            );
        } else {
            $this->execute(
                'INSERT INTO events (business_id, name, description, price, date) VALUES (?,?,?,?,?)',
                [$businessId, $data['name'], $data['description'] ?? null, $data['price'] ?? null, $data['date'] ?? null]
            );
        }
    }

    public function deleteEvent(int $eventId, int $businessId): void
    {
        $this->execute('DELETE FROM events WHERE id = ? AND business_id = ?', [$eventId, $businessId]);
    }
}
