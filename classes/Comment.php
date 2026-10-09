<?php
// Comment - database work for the comments table.
// Edit and delete only work on your own comments (see "AND user_id = :user_id").

class Comment
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // All comments of one recipe, newest first, with the author's username.
    public function forRecipe($recipeId)
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.user_id, c.body, c.created_at, c.updated_at, u.username
             FROM comments c
             JOIN users u ON u.id = c.user_id
             WHERE c.recipe_id = :recipe_id
             ORDER BY c.created_at DESC, c.id DESC'
        );
        $stmt->execute([':recipe_id' => $recipeId]);
        return $stmt->fetchAll();
    }

    // One comment, or null if it does not exist.
    public function find($commentId)
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, recipe_id, user_id, body FROM comments WHERE id = :id'
        );
        $stmt->execute([':id' => $commentId]);
        $comment = $stmt->fetch();
        return $comment ? $comment : null;
    }

    public function add($recipeId, $userId, $body)
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO comments (recipe_id, user_id, body) VALUES (:recipe_id, :user_id, :body)'
        );
        $stmt->execute([':recipe_id' => $recipeId, ':user_id' => $userId, ':body' => $body]);
    }

    // updated_at = NOW() is what shows "(edited)" next to the comment.
    public function update($commentId, $userId, $body)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE comments SET body = :body, updated_at = NOW()
             WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([':body' => $body, ':id' => $commentId, ':user_id' => $userId]);
    }

    public function delete($commentId, $userId)
    {
        $stmt = $this->pdo->prepare('DELETE FROM comments WHERE id = :id AND user_id = :user_id');
        $stmt->execute([':id' => $commentId, ':user_id' => $userId]);
    }
}
