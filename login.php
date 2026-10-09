<?php
// login.php - log in with username (or email) and password.
require_once __DIR__ . '/includes/init.php';
Auth::requireGuest();

$errors = [];
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $login    = post_text('login');
    $password = (isset($_POST['password']) && is_string($_POST['password'])) ? $_POST['password'] : '';

    $errors = Validator::login($login, $password);

    if (!$errors) {
        $user = (new User($pdo))->verifyLogin($login, $password);

        if ($user) {
            Auth::login($user);
            set_flash('success', 'Welcome back, ' . $user['username'] . '!');
            redirect('recipes.php');
        }

        // Same message for "no such user" and "wrong password", so nobody can guess usernames.
        $errors['password'] = 'Wrong username/email or password.';
    }
}

$pageTitle = 'Log in';
include __DIR__ . '/includes/header.php';
?>

<section class="form-box">
    <h1>Welcome back</h1>
    <p class="intro">Log in to see what your neighbors are cooking.</p>

    <form method="post" action="login.php" data-validate="login" novalidate>
        <?= csrf_field() ?>

        <div class="field">
            <label for="login">Username or email</label>
            <input type="text" id="login" name="login" maxlength="100"
                   value="<?= e($login) ?>" class="<?= isset($errors['login']) ? 'invalid' : '' ?>">
            <small class="error"><?= e($errors['login'] ?? '') ?></small>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" maxlength="72"
                   class="<?= isset($errors['password']) ? 'invalid' : '' ?>">
            <small class="error"><?= e($errors['password'] ?? '') ?></small>
        </div>

        <button type="submit" class="btn">Log in</button>
    </form>

    <p class="switch">New here? <a href="register.php">Create an account</a></p>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
