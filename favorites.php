<?php
// favorites.php - the recipes the logged-in member saved.
require_once __DIR__ . '/includes/init.php';
Auth::requireLogin();

// search(viewer, keyword, category, favoritesOnly): no keyword, all categories, favorites only.
$recipes = (new Recipe($pdo))->search(Auth::id(), '', 0, true);

$removeOnUnsave = true;   // when a member un-saves a card here, it disappears from the page

$pageTitle = 'My Favorites';
include __DIR__ . '/includes/header.php';
?>

<h1>My Favorites</h1>

<?php // This box is shown right away when empty, or by JavaScript after the last card is removed. ?>
<div class="empty-state" id="empty-favorites" <?= count($recipes) > 0 ? 'hidden' : '' ?>>
    <p>You have not saved any recipes yet.</p>
    <a href="recipes.php" class="btn">Browse recipes</a>
</div>

<div class="card-grid">
    <?php foreach ($recipes as $recipe): ?>
        <?php include __DIR__ . '/includes/recipe_card.php'; ?>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
