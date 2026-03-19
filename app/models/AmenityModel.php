<?php
class AmenityModel extends Model
{
    protected string $table = 'amenities';

    public function active(): array
    {
        return $this->query('SELECT * FROM amenities WHERE active = 1 ORDER BY name');
    }
}
