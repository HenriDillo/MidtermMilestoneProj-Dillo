<?php
// Recipe - database work for the recipes table (and its ingredients).
//
// Rule for edit/delete: the SQL itself says "AND user_id = :user_id",
// so a member can never change someone else's recipe, even if they fake a request.

class Recipe
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* ---------- Create / update / delete ---------- */

    // $data = title, description, category_id, cook_time_minutes, servings, steps
    // $ingredients = list of ingredient texts
    public function create($userId, array $data, array $ingredients)
    {
        // A transaction means: either EVERYTHING is saved, or NOTHING is.
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO recipes (user_id, category_id, title, description, steps, cook_time_minutes, servings)
                 VALUES (:user_id, :category_id, :title, :description, :steps, :cook_time, :servings)'
            );
            $stmt->execute([
                ':user_id'     => $userId,
                ':category_id' => $data['category_id'],
                ':title'       => $data['title'],
                ':description' => $data['description'],
                ':steps'       => $data['steps'],
                ':cook_time'   => (int) $data['cook_time_minutes'],
                ':servings'    => (int) $data['servings'],
            ]);
            $recipeId = (int) $this->pdo->lastInsertId();

            $this->saveIngredients($recipeId, $ingredients);

            $this->pdo->commit();
            return $recipeId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Returns false if the recipe does not belong to $userId.
    public function update($recipeId, $userId, array $data, array $ingredients)
    {
        if (!$this->isOwner($recipeId, $userId)) {
            return false;
        }

        $this->pdo->beginTransaction();
        try {
            // updated_at = NOW() is what lets other members see "Edited".
            $stmt = $this->pdo->prepare(
                'UPDATE recipes
                 SET category_id = :category_id, title = :title, description = :description,
                     steps = :steps, cook_time_minutes = :cook_time, servings = :servings,
                     updated_at = NOW()
                 WHERE id = :id AND user_id = :user_id'
            );
            $stmt->execute([
                ':category_id' => $data['category_id'],
                ':title'       => $data['title'],
                ':description' => $data['description'],
                ':steps'       => $data['steps'],
                ':cook_time'   => (int) $data['cook_time_minutes'],
                ':servings'    => (int) $data['servings'],
                ':id'          => $recipeId,
                ':user_id'     => $userId,
            ]);

            // Easiest way to handle changed ingredients: delete the old ones, save the new ones.
            $stmt = $this->pdo->prepare('DELETE FROM ingredients WHERE recipe_id = :recipe_id');
            $stmt->execute([':recipe_id' => $recipeId]);
            $this->saveIngredients($recipeId, $ingredients);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Deleting a recipe also deletes its ingredients, comments and favorites
    // automatically (ON DELETE CASCADE in database.sql).
    public function delete($recipeId, $userId)
    {
        $stmt = $this->pdo->prepare('DELETE FROM recipes WHERE id = :id AND user_id = :user_id');
        $stmt->execute([':id' => $recipeId, ':user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    private function saveIngredients($recipeId, array $ingredients)
    {
        $stmt = $this->pdo->prepare('INSERT INTO ingredients (recipe_id, name) VALUES (:recipe_id, :name)');
        foreach ($ingredients as $name) {
            $stmt->execute([':recipe_id' => $recipeId, ':name' => $name]);
        }
    }

    /* ---------- Reading ---------- */

    // One recipe with its author, category, and whether $viewerId saved it. Null if not found.
    public function find($recipeId, $viewerId)
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*, u.username, c.name AS category_name,
                    (SELECT COUNT(*) FROM favorites f
                     WHERE f.recipe_id = r.id AND f.user_id = :viewer_id) AS is_favorite
             FROM recipes r
             JOIN users u ON u.id = r.user_id
             JOIN categories c ON c.id = r.category_id
             WHERE r.id = :id'
        );
        $stmt->execute([':viewer_id' => $viewerId, ':id' => $recipeId]);
        $recipe = $stmt->fetch();
        return $recipe ? $recipe : null;
    }

    // The ingredient texts of one recipe, in the order they were entered.
    public function ingredients($recipeId)
    {
        $stmt = $this->pdo->prepare('SELECT name FROM ingredients WHERE recipe_id = :id ORDER BY id');
        $stmt->execute([':id' => $recipeId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // The recipe list. Newest first.
    //   $keyword       - looks in title, description and ingredients ('' = no search)
    //   $categoryId    - only this category (0 = all)
    //   $favoritesOnly - only recipes $viewerId has saved
    public function search($viewerId, $keyword = '', $categoryId = 0, $favoritesOnly = false)
    {
        $sql = 'SELECT r.id, r.title, r.description, r.cook_time_minutes, r.servings,
                       r.created_at, r.updated_at, u.username, c.name AS category_name,
                       (SELECT COUNT(*) FROM favorites f
                        WHERE f.recipe_id = r.id AND f.user_id = :viewer_id) AS is_favorite
                FROM recipes r
                JOIN users u ON u.id = r.user_id
                JOIN categories c ON c.id = r.category_id';

        $where  = [];
        $params = [':viewer_id' => $viewerId];

        if ($keyword !== '') {
            // Escape % and _ so the visitor cannot use them as wildcards.
            $like = '%' . addcslashes($keyword, '%_\\') . '%';
            $where[] = '(r.title LIKE :kw_title
                         OR r.description LIKE :kw_desc
                         OR EXISTS (SELECT 1 FROM ingredients i
                                    WHERE i.recipe_id = r.id AND i.name LIKE :kw_ing))';
            $params[':kw_title'] = $like;
            $params[':kw_desc']  = $like;
            $params[':kw_ing']   = $like;
        }

        if ($categoryId > 0) {
            $where[] = 'r.category_id = :category_id';
            $params[':category_id'] = $categoryId;
        }

        if ($favoritesOnly) {
            $where[] = 'r.id IN (SELECT recipe_id FROM favorites WHERE user_id = :fav_user_id)';
            $params[':fav_user_id'] = $viewerId;
        }

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY r.created_at DESC, r.id DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /* ---------- Small checks ---------- */

    public function exists($recipeId)
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM recipes WHERE id = :id');
        $stmt->execute([':id' => $recipeId]);
        return $stmt->fetchColumn() > 0;
    }

    public function isOwner($recipeId, $userId)
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM recipes WHERE id = :id AND user_id = :user_id');
        $stmt->execute([':id' => $recipeId, ':user_id' => $userId]);
        return $stmt->fetchColumn() > 0;
    }
}
