<?php
// recipe_form.php - ONE page for both sharing a new recipe and editing your own.
//   recipe_form.php          -> new recipe
//   recipe_form.php?id=5     -> edit recipe 5 (only if it is yours)
require_once __DIR__ . '/includes/init.php';
Auth::requireLogin();

$recipeModel = new Recipe($pdo);
$categories  = (new Category($pdo))->all();
$validIds    = array_column($categories, 'id');

$recipeId = (int) ($_GET['id'] ?? 0);
$editing  = ($recipeId > 0);

$errors = [];

// Starting values for a brand-new recipe
$data = [
    'title' => '', 'description' => '', 'category_id' => 0,
    'cook_time_minutes' => '', 'servings' => '', 'steps' => '',
];
$ingredients = [''];   // start with one empty ingredient box

// Editing: load the recipe and make sure it belongs to the logged-in member.
if ($editing) {
    $existing = $recipeModel->find($recipeId, Auth::id());

    if (!$existing) {
        show_error_page(404, 'Recipe not found', 'This recipe does not exist.');
    }
    if ((int) $existing['user_id'] !== Auth::id()) {
        show_error_page(403, 'Not allowed', 'You can only edit your own recipes.');
    }

    $data = [
        'title'             => $existing['title'],
        'description'       => $existing['description'],
        'category_id'       => (int) $existing['category_id'],
        'cook_time_minutes' => (string) $existing['cook_time_minutes'],
        'servings'          => (string) $existing['servings'],
        'steps'             => $existing['steps'],
    ];
    $ingredients = $recipeModel->ingredients($recipeId);
}

// The form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $data = [
        'title'             => post_text('title'),
        'description'       => post_text('description'),
        'category_id'       => (int) post_text('category_id'),
        'cook_time_minutes' => post_text('cook_time_minutes'),
        'servings'          => post_text('servings'),
        'steps'             => post_text('steps'),
    ];

    // The ingredient boxes arrive as a list: ingredients[]. Keep only the filled ones.
    $ingredients = [];
    if (isset($_POST['ingredients']) && is_array($_POST['ingredients'])) {
        foreach ($_POST['ingredients'] as $item) {
            if (is_string($item) && trim($item) !== '') {
                $ingredients[] = trim($item);
            }
        }
    }

    $errors = Validator::recipe($data, $ingredients, $validIds);

    if (!$errors) {
        if ($editing) {
            $recipeModel->update($recipeId, Auth::id(), $data, $ingredients);
            set_flash('success', 'Your recipe was updated.');
            redirect('recipe.php?id=' . $recipeId);
        }

        $newId = $recipeModel->create(Auth::id(), $data, $ingredients);
        set_flash('success', 'Your recipe was shared. Salamat!');
        redirect('recipe.php?id=' . $newId);
    }

    if (count($ingredients) === 0) {
        $ingredients = [''];   // always show at least one box
    }
}

$pageTitle = $editing ? 'Edit recipe' : 'Share a recipe';
$formAction = $editing ? 'recipe_form.php?id=' . $recipeId : 'recipe_form.php';
include __DIR__ . '/includes/header.php';
?>

<section class="form-box form-wide">
    <h1><?= $editing ? 'Edit your recipe' : 'Share a recipe' ?></h1>

    <form method="post" action="<?= e($formAction) ?>" data-validate="recipe" novalidate>
        <?= csrf_field() ?>

        <div class="field">
            <label for="title">Recipe title</label>
            <input type="text" id="title" name="title" maxlength="100"
                   value="<?= e($data['title']) ?>" class="<?= isset($errors['title']) ? 'invalid' : '' ?>">
            <small class="error"><?= e($errors['title'] ?? '') ?></small>
        </div>

        <div class="field">
            <label for="description">Short description</label>
            <textarea id="description" name="description" rows="3" maxlength="300"
                      class="<?= isset($errors['description']) ? 'invalid' : '' ?>"><?= e($data['description']) ?></textarea>
            <small class="error"><?= e($errors['description'] ?? '') ?></small>
        </div>

        <div class="field">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" class="<?= isset($errors['category_id']) ? 'invalid' : '' ?>">
                <option value="">Choose a category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>"
                        <?= (int) $data['category_id'] === (int) $category['id'] ? 'selected' : '' ?>>
                        <?= e($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="error"><?= e($errors['category_id'] ?? '') ?></small>
        </div>

        <div class="field-row">
            <div class="field">
                <label for="cook_time_minutes">Cooking time (minutes)</label>
                <input type="number" id="cook_time_minutes" name="cook_time_minutes" min="1" max="1440"
                       value="<?= e($data['cook_time_minutes']) ?>"
                       class="<?= isset($errors['cook_time_minutes']) ? 'invalid' : '' ?>">
                <small class="error"><?= e($errors['cook_time_minutes'] ?? '') ?></small>
            </div>

            <div class="field">
                <label for="servings">Servings</label>
                <input type="number" id="servings" name="servings" min="1" max="100"
                       value="<?= e($data['servings']) ?>"
                       class="<?= isset($errors['servings']) ? 'invalid' : '' ?>">
                <small class="error"><?= e($errors['servings'] ?? '') ?></small>
            </div>
        </div>

        <div class="field">
            <label>Ingredients</label>
            <div id="ingredient-list">
                <?php foreach ($ingredients as $ingredient): ?>
                    <div class="ingredient-row">
                        <input type="text" name="ingredients[]" maxlength="100"
                               placeholder="e.g. 2 cloves garlic" value="<?= e($ingredient) ?>"
                               class="<?= isset($errors['ingredients']) ? 'invalid' : '' ?>">
                        <button type="button" class="btn-remove" title="Remove" aria-label="Remove ingredient">&#10005;</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <small class="error"><?= e($errors['ingredients'] ?? '') ?></small>
            <button type="button" id="add-ingredient" class="btn btn-outline btn-small">+ Add ingredient</button>
        </div>

        <div class="field">
            <label for="steps">Cooking steps</label>
            <textarea id="steps" name="steps" rows="8" maxlength="5000"
                      placeholder="Write one step per line."
                      class="<?= isset($errors['steps']) ? 'invalid' : '' ?>"><?= e($data['steps']) ?></textarea>
            <small class="hint">Write one step per line. Each line becomes a numbered step.</small>
            <small class="error"><?= e($errors['steps'] ?? '') ?></small>
        </div>

        <div class="actions">
            <button type="submit" class="btn"><?= $editing ? 'Save changes' : 'Share recipe' ?></button>
            <a class="btn btn-outline" href="<?= $editing ? 'recipe.php?id=' . $recipeId : 'recipes.php' ?>">Cancel</a>
        </div>
    </form>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
