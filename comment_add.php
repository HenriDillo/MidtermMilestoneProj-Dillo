<?php
// comment_add.php - save a new comment. Only accepts POST (from the form on recipe.php).
require_once __DIR__ . '/includes/init.php';
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('recipes.php');
}
verify_csrf();

$recipeId = (int) ($_POST['recipe_id'] ?? 0);
$body     = post_text('body');

if (!(new Recipe($pdo))->exists($recipeId)) {
    show_error_page(404, 'Recipe not found', 'You cannot comment on a recipe that does not exist.');
}

$errors = Validator::comment($body);

if ($errors) {
    set_flash('error', $errors['body']);
} else {
    (new Comment($pdo))->add($recipeId, Auth::id(), $body);
    set_flash('success', 'Your comment was posted.');
}

redirect('recipe.php?id=' . $recipeId . '#comments');
