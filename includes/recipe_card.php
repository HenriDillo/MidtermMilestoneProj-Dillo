<?php
// recipe_card.php - one recipe in the list. Used by recipes.php and favorites.php.
// Expects $recipe. Set $removeOnUnsave = true to make the card disappear when unsaved.
?>
<article class="card">
    <span class="badge"><?= e($recipe['category_name']) ?></span>

    <h2 class="card-title">
        <a href="recipe.php?id=<?= (int) $recipe['id'] ?>"><?= e($recipe['title']) ?></a>
    </h2>

    <p class="meta">
        by <?= e($recipe['username']) ?> &middot; <?= e(format_date($recipe['created_at'])) ?>
        <?php if ($recipe['updated_at']): ?>
            &middot; <em>Edited</em>
        <?php endif; ?>
    </p>

    <p><?= e($recipe['description']) ?></p>

    <p class="meta">
        <?= (int) $recipe['cook_time_minutes'] ?> min &middot; serves <?= (int) $recipe['servings'] ?>
    </p>

    <?= favorite_button($recipe['id'], $recipe['is_favorite'] > 0, !empty($removeOnUnsave)) ?>
</article>
