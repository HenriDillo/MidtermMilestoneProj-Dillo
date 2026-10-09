<?php
// recipe.php - one recipe in full, with its comments.
require_once __DIR__ . '/includes/init.php';
Auth::requireLogin();

$recipeId = (int) ($_GET['id'] ?? 0);

$recipeModel = new Recipe($pdo);
$recipe = $recipeModel->find($recipeId, Auth::id());

if (!$recipe) {
    show_error_page(404, 'Recipe not found', 'This recipe does not exist. It may have been removed.');
}

$ingredients = $recipeModel->ingredients($recipeId);
$comments    = (new Comment($pdo))->forRecipe($recipeId);
$isOwner     = ((int) $recipe['user_id'] === Auth::id());

// Each line of the steps text becomes one numbered step.
$steps = [];
foreach (preg_split('/\R/', $recipe['steps']) as $line) {
    $line = trim($line);
    if ($line !== '') {
        $steps[] = $line;
    }
}

$pageTitle = $recipe['title'];
include __DIR__ . '/includes/header.php';
?>

<article class="recipe-detail">
    <span class="badge"><?= e($recipe['category_name']) ?></span>
    <h1><?= e($recipe['title']) ?></h1>

    <p class="meta">
        by <?= e($recipe['username']) ?> &middot; posted <?= e(format_date($recipe['created_at'])) ?>
        <?php if ($recipe['updated_at']): ?>
            &middot; <em>Edited <?= e(format_date($recipe['updated_at'])) ?></em>
        <?php endif; ?>
    </p>

    <p class="lead"><?= e($recipe['description']) ?></p>

    <ul class="facts">
        <li>Cooking time: <strong><?= (int) $recipe['cook_time_minutes'] ?> min</strong></li>
        <li>Serves: <strong><?= (int) $recipe['servings'] ?></strong></li>
    </ul>

    <div class="actions">
        <?= favorite_button($recipe['id'], $recipe['is_favorite'] > 0) ?>

        <?php if ($isOwner): ?>
            <a class="btn btn-outline" href="recipe_form.php?id=<?= (int) $recipe['id'] ?>">Edit</a>

            <form method="post" action="recipe_delete.php"
                  data-confirm="Delete this recipe? Its comments will be deleted too.">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $recipe['id'] ?>">
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        <?php endif; ?>
    </div>

    <h2>Ingredients</h2>
    <ul class="ingredient-list">
        <?php foreach ($ingredients as $ingredient): ?>
            <li><?= e($ingredient) ?></li>
        <?php endforeach; ?>
    </ul>

    <h2>Steps</h2>
    <ol class="step-list">
        <?php foreach ($steps as $step): ?>
            <li><?= e($step) ?></li>
        <?php endforeach; ?>
    </ol>
</article>

<section class="comments" id="comments">
    <h2>Comments (<?= count($comments) ?>)</h2>

    <form method="post" action="comment_add.php" class="comment-form" data-validate="comment" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="recipe_id" value="<?= (int) $recipe['id'] ?>">

        <div class="field">
            <label for="body">Leave a comment</label>
            <textarea id="body" name="body" rows="3" maxlength="500"
                      placeholder="Did you try this? How did it taste?"></textarea>
            <small class="error"></small>
        </div>
        <button type="submit" class="btn">Post comment</button>
    </form>

    <?php if (count($comments) === 0): ?>
        <p class="meta">No comments yet. Be the first!</p>
    <?php endif; ?>

    <?php foreach ($comments as $comment): ?>
        <div class="comment">
            <p class="meta">
                <strong><?= e($comment['username']) ?></strong>
                &middot; <?= e(format_date($comment['created_at'])) ?>
                <?php if ($comment['updated_at']): ?>
                    &middot; <em>edited <?= e(format_date($comment['updated_at'])) ?></em>
                <?php endif; ?>
            </p>
            <p><?= nl2br(e($comment['body'])) ?></p>

            <?php if ((int) $comment['user_id'] === Auth::id()): ?>
                <div class="actions">
                    <a class="btn btn-small btn-outline" href="comment_edit.php?id=<?= (int) $comment['id'] ?>">Edit</a>
                    <form method="post" action="comment_delete.php" data-confirm="Delete this comment?">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $comment['id'] ?>">
                        <button type="submit" class="btn btn-small btn-danger">Delete</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
