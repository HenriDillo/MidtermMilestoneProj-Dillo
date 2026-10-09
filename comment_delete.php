<?php
// comment_delete.php - delete one of your own comments. Only accepts POST.
require_once __DIR__ . '/includes/init.php';
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('recipes.php');
}
verify_csrf();

$commentId    = (int) ($_POST['id'] ?? 0);
$commentModel = new Comment($pdo);
$comment      = $commentModel->find($commentId);

if (!$comment) {
    show_error_page(404, 'Comment not found', 'This comment does not exist.');
}
if ((int) $comment['user_id'] !== Auth::id()) {
    show_error_page(403, 'Not allowed', 'You can only delete your own comments.');
}

$commentModel->delete($commentId, Auth::id());
set_flash('success', 'Your comment was deleted.');

redirect('recipe.php?id=' . (int) $comment['recipe_id'] . '#comments');
