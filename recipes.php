<?php
// recipes.php - browse recipes (newest first), search by keyword, filter by category.
require_once __DIR__ . '/includes/init.php';
Auth::requireLogin();

// Read the search box and the category dropdown from the URL (?q=...&category=...)
$keyword    = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$categoryId = (int) ($_GET['category'] ?? 0);

$categories = (new Category($pdo))->all();
$recipes    = (new Recipe($pdo))->search(Auth::id(), $keyword, $categoryId);

$isFiltering = ($keyword !== '' || $categoryId > 0);

$pageTitle = 'Recipes';
include __DIR__ . '/includes/header.php';
?>

<h1><?= $isFiltering ? 'Search results' : 'Latest recipes' ?></h1>

<form method="get" action="recipes.php" class="filter-bar">
    <input type="search" name="q" placeholder="Search title, description or ingredient"
           value="<?= e($keyword) ?>" maxlength="100" aria-label="Search recipes">

    <select name="category" aria-label="Filter by category">
        <option value="0">All categories</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?= (int) $category['id'] ?>"
                <?= $categoryId === (int) $category['id'] ? 'selected' : '' ?>>
                <?= e($category['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="btn">Search</button>
    <?php if ($isFiltering): ?>
        <a href="recipes.php" class="btn btn-outline">Clear</a>
    <?php endif; ?>
</form>

<?php if (count($recipes) === 0): ?>
    <div class="empty-state">
        <?php if ($isFiltering): ?>
            <p>No recipes match your search. Try another keyword or category.</p>
        <?php else: ?>
            <p>No recipes yet. Be the first to share one!</p>
            <a href="recipe_form.php" class="btn">Share a recipe</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <p class="meta"><?= count($recipes) ?> recipe<?= count($recipes) === 1 ? '' : 's' ?> found</p>

    <div class="card-grid">
        <?php foreach ($recipes as $recipe): ?>
            <?php include __DIR__ . '/includes/recipe_card.php'; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
