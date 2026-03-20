<?php
class CategoryModel extends Model
{
    protected string $table = 'categories';

    public function active(): array
    {
        return $this->query('SELECT * FROM categories WHERE active = 1 ORDER BY sort_order, name');
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->queryOne('SELECT * FROM categories WHERE slug = ? LIMIT 1', [$slug]);
    }
}
