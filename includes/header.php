<?php
// header.php - the top of every page (logo, menu, flash message).
// The page sets $pageTitle before including this file.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle ?? 'Home') ?> | Kusina Namin</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php">Kusina Namin</a>

        <nav class="main-nav">
            <?php if (Auth::check()): ?>
                <a href="recipes.php">Recipes</a>
                <a href="recipe_form.php">Share a Recipe</a>
                <a href="favorites.php">My Favorites</a>
                <span class="nav-user">Hi, <?= e(Auth::username()) ?></span>
                <form action="logout.php" method="post" class="logout-form">
                    <?= csrf_field() ?>
                    <button type="submit" class="nav-button">Log out</button>
                </form>
            <?php else: ?>
                <a href="login.php">Log in</a>
                <a href="register.php">Sign up</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container">
<?php $flash = get_flash(); ?>
<?php if ($flash): ?>
    <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
