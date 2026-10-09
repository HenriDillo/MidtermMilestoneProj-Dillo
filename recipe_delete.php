<?php
// recipe_delete.php - delete one of your own recipes. Only accepts POST.
require_once __DIR__ . '/includes/init.php';
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('recipes.php');
}
verify_csrf();

$recipeId = (int) ($_POST['id'] ?? 0);
$recipeModel = new Recipe($pdo);

// delete() only works if the recipe belongs to the logged-in member.
if ($recipeModel->delete($recipeId, Auth::id())) {
    set_flash('success', 'Your recipe was deleted.');
} else {
    set_flash('error', 'That recipe could not be deleted. You can only delete your own recipes.');
}

redirect('recipes.php');
