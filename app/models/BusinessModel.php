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
             WHERE b.status = "published" AND b.is_open = 1
             ORDER BY b.featured DESC, b.rating DESC'
        );
    }

    public function publishedForChatbot(): array
    {
        return $this->query(
            'SELECT b.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon
             FROM businesses b
             JOIN categories c ON c.id = b.category_id
             WHERE b.status = "published" AND b.is_open = 1
             ORDER BY b.featured DESC, b.rating DESC'
        );
    }

    public function topVisited(int $limit = 10): array
    {
        return $this->query(
            'SELECT b.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon
             FROM businesses b
             JOIN categories c ON c.id = b.category_id
             WHERE b.status = "published" AND b.is_open = 1
             ORDER BY b.visits DESC, b.rating DESC
             LIMIT ' . max(1, min(50, $limit))
        );
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->queryOne(
            'SELECT b.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon, c.slug AS category_slug
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
        $sql    = 'SELECT b.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon, c.slug AS category_slug
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
        if (!empty($filters['trip_type'])) {
            $sql .= ' AND b.id IN (SELECT business_id FROM business_trip_types WHERE trip_type = ?)';
            $params[] = $filters['trip_type'];
        }
        $sql .= ' ORDER BY b.featured DESC, b.rating DESC';
        return $this->query($sql, $params);
    }

    public function setTrusted(int $businessId, bool $trusted, int $userId, string $note = ''): bool
    {
        return $this->update($businessId, [
            'is_trusted'   => $trusted ? 1 : 0,
            'trusted_by'   => $trusted ? $userId : null,
            'trusted_at'   => $trusted ? date('Y-m-d H:i:s') : null,
            'trusted_note' => $trusted ? ($note !== '' ? $note : null) : null,
        ]);
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

    public function activeAmenities(): array
    {
        return $this->query('SELECT * FROM amenities WHERE active = 1 ORDER BY name');
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
            'SELECT b.*, c.name AS category_name, u.name AS owner_name,
                    COALESCE(rs.reviews_count, 0) AS reviews_count,
                    COALESCE(rs.reviews_avg, 0) AS reviews_avg,
                    COALESCE(rs.low_reviews_count, 0) AS low_reviews_count,
                    (
                        IF(b.lat IS NOT NULL AND b.lng IS NOT NULL, 1, 0) +
                        IF(NULLIF(TRIM(COALESCE(b.description, "")), "") IS NOT NULL, 1, 0) +
                        IF(NULLIF(TRIM(COALESCE(b.cover_image, "")), "") IS NOT NULL, 1, 0) +
                        IF(NULLIF(TRIM(COALESCE(b.whatsapp, "")), "") IS NOT NULL OR NULLIF(TRIM(COALESCE(b.phone, "")), "") IS NOT NULL, 1, 0)
                    ) AS verification_profile_score,
                    CASE
                      WHEN b.status = "published"
                       AND COALESCE(b.is_trusted, 0) = 0
                       AND COALESCE(rs.reviews_count, 0) >= 3
                       AND COALESCE(rs.reviews_avg, 0) >= 4.3
                       AND COALESCE(rs.low_reviews_count, 0) = 0
                       AND (
                            IF(b.lat IS NOT NULL AND b.lng IS NOT NULL, 1, 0) +
                            IF(NULLIF(TRIM(COALESCE(b.description, "")), "") IS NOT NULL, 1, 0) +
                            IF(NULLIF(TRIM(COALESCE(b.cover_image, "")), "") IS NOT NULL, 1, 0) +
                            IF(NULLIF(TRIM(COALESCE(b.whatsapp, "")), "") IS NOT NULL OR NULLIF(TRIM(COALESCE(b.phone, "")), "") IS NOT NULL, 1, 0)
                       ) >= 3
                      THEN 1 ELSE 0
                    END AS verification_suggested
             FROM businesses b
             JOIN categories c ON c.id = b.category_id
             JOIN users u ON u.id = b.user_id
             LEFT JOIN (
                SELECT business_id,
                       COUNT(*) AS reviews_count,
                       ROUND(AVG(rating), 1) AS reviews_avg,
                       SUM(CASE WHEN rating <= 2 THEN 1 ELSE 0 END) AS low_reviews_count
                FROM reviews
                GROUP BY business_id
             ) rs ON rs.business_id = b.id
             ORDER BY b.created_at DESC'
        );
    }

    public function verificationCandidates(): array
    {
        return $this->query(
            'SELECT b.*, c.name AS category_name, u.name AS owner_name,
                    COALESCE(rs.reviews_count, 0) AS reviews_count,
                    COALESCE(rs.reviews_avg, 0) AS reviews_avg,
                    COALESCE(rs.low_reviews_count, 0) AS low_reviews_count,
                    (
                        IF(b.lat IS NOT NULL AND b.lng IS NOT NULL, 1, 0) +
                        IF(NULLIF(TRIM(COALESCE(b.description, "")), "") IS NOT NULL, 1, 0) +
                        IF(NULLIF(TRIM(COALESCE(b.cover_image, "")), "") IS NOT NULL, 1, 0) +
                        IF(NULLIF(TRIM(COALESCE(b.whatsapp, "")), "") IS NOT NULL OR NULLIF(TRIM(COALESCE(b.phone, "")), "") IS NOT NULL, 1, 0)
                    ) AS verification_profile_score,
                    1 AS verification_suggested
             FROM businesses b
             JOIN categories c ON c.id = b.category_id
             JOIN users u ON u.id = b.user_id
             LEFT JOIN (
                SELECT business_id,
                       COUNT(*) AS reviews_count,
                       ROUND(AVG(rating), 1) AS reviews_avg,
                       SUM(CASE WHEN rating <= 2 THEN 1 ELSE 0 END) AS low_reviews_count
                FROM reviews
                GROUP BY business_id
             ) rs ON rs.business_id = b.id
             WHERE b.status = "published"
               AND COALESCE(b.is_trusted, 0) = 0
               AND COALESCE(rs.reviews_count, 0) >= 3
               AND COALESCE(rs.reviews_avg, 0) >= 4.3
               AND COALESCE(rs.low_reviews_count, 0) = 0
               AND (
                    IF(b.lat IS NOT NULL AND b.lng IS NOT NULL, 1, 0) +
                    IF(NULLIF(TRIM(COALESCE(b.description, "")), "") IS NOT NULL, 1, 0) +
                    IF(NULLIF(TRIM(COALESCE(b.cover_image, "")), "") IS NOT NULL, 1, 0) +
                    IF(NULLIF(TRIM(COALESCE(b.whatsapp, "")), "") IS NOT NULL OR NULLIF(TRIM(COALESCE(b.phone, "")), "") IS NOT NULL, 1, 0)
               ) >= 3
             ORDER BY rs.reviews_avg DESC, rs.reviews_count DESC, b.visits DESC'
        );
    }

    public function incrementVisit(int $id): void
    {
        $this->execute('UPDATE businesses SET visits = visits + 1 WHERE id = ?', [$id]);
    }

    public function recordVisitorVisit(int $userId, int $businessId): void
    {
        try {
            $this->execute(
                'INSERT INTO visitor_place_visits (user_id, business_id, visited_at) VALUES (?,?,NOW())',
                [$userId, $businessId]
            );
        } catch (PDOException $e) {
            error_log('Visitor visit history skipped: ' . $e->getMessage());
        }
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
        return $this->query(
            "SELECT p.*, p.title AS name, p.start_date AS date
             FROM promotions p
             WHERE p.business_id = ? AND p.type = 'evento'
             ORDER BY p.start_date, p.id",
            [$businessId]
        );
    }

    public function publicEvents(int $businessId): array
    {
        try {
            return $this->query(
                "SELECT e.id, e.title AS name, e.title, e.description, e.price,
                        e.start_date AS date, e.start_date, e.end_date, e.image,
                        e.public_url, e.status, 'events' AS source
                 FROM events e
                 WHERE e.business_id = ?
                   AND e.status IN ('active', 'approved')
                   AND (e.end_date IS NULL OR e.end_date >= NOW())
                 UNION ALL
                 SELECT p.id, p.title AS name, p.title, p.description, p.price,
                        p.start_date AS date, p.start_date, p.end_date, p.image,
                        p.public_url, p.status, 'promotions' AS source
                 FROM promotions p
                 WHERE p.business_id = ?
                   AND p.type = 'evento'
                   AND p.status IN ('active', 'approved')
                   AND (p.end_date IS NULL OR p.end_date >= NOW())
                 ORDER BY date, id",
                [$businessId, $businessId]
            );
        } catch (PDOException $e) {
            return array_merge(
                $this->legacyPublicEvents($businessId),
                $this->promotionPublicEvents($businessId)
            );
        }
    }

    private function legacyPublicEvents(int $businessId): array
    {
        try {
            return $this->query(
                "SELECT e.id, e.name AS name, e.name AS title, e.description, e.price,
                        e.date AS date, e.date AS start_date, NULL AS end_date,
                        NULL AS image, NULL AS public_url, 'active' AS status,
                        'events' AS source
                 FROM events e
                 WHERE e.business_id = ?
                   AND (e.date IS NULL OR e.date >= NOW())
                 ORDER BY e.date, e.id",
                [$businessId]
            );
        } catch (PDOException $e) {
            error_log('Legacy public events skipped: ' . $e->getMessage());
            return [];
        }
    }

    private function promotionPublicEvents(int $businessId): array
    {
        try {
            return $this->query(
                "SELECT p.id, p.title AS name, p.title, p.description, p.price,
                        p.start_date AS date, p.start_date, p.end_date, p.image,
                        p.public_url, p.status, 'promotions' AS source
                 FROM promotions p
                 WHERE p.business_id = ?
                   AND p.type = 'evento'
                   AND p.status IN ('active', 'approved')
                   AND (p.end_date IS NULL OR p.end_date >= NOW())
                 ORDER BY p.start_date, p.id",
                [$businessId]
            );
        } catch (PDOException $e) {
            error_log('Promotion public events skipped: ' . $e->getMessage());
            return [];
        }
    }

    public function reviews(int $businessId): array
    {
        try {
            return $this->query(
                'SELECT r.*, COALESCE(u.name, r.user_name) AS display_name
                 FROM reviews r
                 LEFT JOIN users u ON u.id = r.user_id
                 WHERE r.business_id = ?
                 ORDER BY r.created_at DESC',
                [$businessId]
            );
        } catch (PDOException $e) {
            error_log('Reviews user relation skipped: ' . $e->getMessage());
            return $this->query(
                'SELECT r.*, r.user_name AS display_name
                 FROM reviews r
                 WHERE r.business_id = ?
                 ORDER BY r.created_at DESC',
                [$businessId]
            );
        }
    }

    public function addReview(int $businessId, string $userName, string $comment, int $rating, ?int $userId = null): bool
    {
        try {
            return $this->execute(
                'INSERT INTO reviews (business_id, user_id, user_name, comment, rating) VALUES (?,?,?,?,?)',
                [$businessId, $userId, $userName, $comment, $rating]
            );
        } catch (PDOException $e) {
            error_log('Review user relation skipped: ' . $e->getMessage());
            return $this->execute(
                'INSERT INTO reviews (business_id, user_name, comment, rating) VALUES (?,?,?,?)',
                [$businessId, $userName, $comment, $rating]
            );
        }
    }

    public function reviewsByUser(int $userId): array
    {
        return $this->query(
            'SELECT r.*, b.name AS business_name, b.slug AS business_slug, b.cover_image,
                    c.name AS category_name, c.color AS category_color
             FROM reviews r
             JOIN businesses b ON b.id = r.business_id
             JOIN categories c ON c.id = b.category_id
             WHERE r.user_id = ?
             ORDER BY r.created_at DESC',
            [$userId]
        );
    }

    public function visitedByUser(int $userId): array
    {
        return $this->query(
            'SELECT b.*, c.name AS category_name, c.color AS category_color, c.icon AS category_icon,
                    MAX(v.visited_at) AS last_visited_at, COUNT(v.id) AS visit_count
             FROM visitor_place_visits v
             JOIN businesses b ON b.id = v.business_id
             JOIN categories c ON c.id = b.category_id
             WHERE v.user_id = ?
             GROUP BY b.id
             ORDER BY last_visited_at DESC',
            [$userId]
        );
    }

    public function updateRating(int $businessId): void
    {
        $row = $this->queryOne(
            'SELECT ROUND(AVG(rating), 2) AS avg_rating FROM reviews WHERE business_id = ?',
            [$businessId]
        );
        $avg = $row['avg_rating'] ?? 0;
        $this->execute('UPDATE businesses SET rating = ? WHERE id = ?', [$avg, $businessId]);
    }

    public function tripTypes(int $businessId): array
    {
        return $this->query('SELECT trip_type FROM business_trip_types WHERE business_id = ? ORDER BY trip_type', [$businessId]);
    }

    public function syncTripTypes(int $businessId, array $tripTypes): void
    {
        $this->execute('DELETE FROM business_trip_types WHERE business_id = ?', [$businessId]);
        foreach ($tripTypes as $type) {
            $this->execute(
                'INSERT IGNORE INTO business_trip_types (business_id, trip_type) VALUES (?,?)',
                [$businessId, trim($type)]
            );
        }
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
