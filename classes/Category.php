<?php
// Category - database work for the categories table.

class Category
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // All categories, A to Z (used by the filter and the recipe form).
    public function all()
    {
        return $this->pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
    }
}
