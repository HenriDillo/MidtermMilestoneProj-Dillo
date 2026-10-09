<?php
// Favorite - database work for the favorites table.
// Each row means "this member saved this recipe".

class Favorite
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function isFavorite($userId, $recipeId)
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM favorites WHERE user_id = :user_id AND recipe_id = :recipe_id'
        );
        $stmt->execute([':user_id' => $userId, ':recipe_id' => $recipeId]);
        return $stmt->fetchColumn() > 0;
    }

    public function add($userId, $recipeId)
    {
        // INSERT IGNORE: if it is already saved, do nothing (no error).
        $stmt = $this->pdo->prepare(
            'INSERT IGNORE INTO favorites (user_id, recipe_id) VALUES (:user_id, :recipe_id)'
        );
        $stmt->execute([':user_id' => $userId, ':recipe_id' => $recipeId]);
    }

    public function remove($userId, $recipeId)
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM favorites WHERE user_id = :user_id AND recipe_id = :recipe_id'
        );
        $stmt->execute([':user_id' => $userId, ':recipe_id' => $recipeId]);
    }

    // Save it if it is not saved yet, remove it if it is.
    // Returns true when the recipe is now a favorite, false when it was removed.
    public function toggle($userId, $recipeId)
    {
        if ($this->isFavorite($userId, $recipeId)) {
            $this->remove($userId, $recipeId);
            return false;
        }
        $this->add($userId, $recipeId);
        return true;
    }
}
