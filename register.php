<?php
// register.php - create a new account.
require_once __DIR__ . '/includes/init.php';
Auth::requireGuest();

$errors = [];
$old = ['username' => '', 'email' => ''];   // so the form keeps what was typed

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = post_text('username');
    $email    = post_text('email');
    $password = (isset($_POST['password']) && is_string($_POST['password'])) ? $_POST['password'] : '';
    $confirm  = (isset($_POST['confirm_password']) && is_string($_POST['confirm_password'])) ? $_POST['confirm_password'] : '';

    $old = ['username' => $username, 'email' => $email];

    // 1) Check the format rules
    $errors = Validator::registration($username, $email, $password, $confirm);

    // 2) Check that the username and email are not taken yet
    $users = new User($pdo);
    if (!isset($errors['username']) && $users->usernameExists($username)) {
        $errors['username'] = 'That username is already taken.';
    }
    if (!isset($errors['email']) && $users->emailExists($email)) {
        $errors['email'] = 'That email is already registered.';
    }

    // 3) No errors? Save the member and log them in.
    if (!$errors) {
        try {
            $newId = $users->create($username, $email, $password);
            Auth::login(['id' => $newId, 'username' => $username]);
            set_flash('success', 'Welcome to Kusina Namin, ' . $username . '!');
            redirect('recipes.php');
        } catch (PDOException $e) {
            // Rare case: someone took the same username a split second before us.
            $errors['username'] = 'That username or email was just taken. Please try another.';
        }
    }
}

$pageTitle = 'Sign up';
include __DIR__ . '/includes/header.php';
?>

<section class="form-box">
    <h1>Join Us</h1>
    <p class="intro">Share your family recipes with the barangay.</p>

    <form method="post" action="register.php" data-validate="register" novalidate>
        <?= csrf_field() ?>

        <div class="field">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" maxlength="20"
                   value="<?= e($old['username']) ?>" class="<?= isset($errors['username']) ? 'invalid' : '' ?>">
            <small class="error"><?= e($errors['username'] ?? '') ?></small>
        </div>

        <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" maxlength="100"
                   value="<?= e($old['email']) ?>" class="<?= isset($errors['email']) ? 'invalid' : '' ?>">
            <small class="error"><?= e($errors['email'] ?? '') ?></small>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" maxlength="72"
                   class="<?= isset($errors['password']) ? 'invalid' : '' ?>">
            <small class="hint">At least 8 characters, with a letter and a number.</small>
            <small class="error"><?= e($errors['password'] ?? '') ?></small>
        </div>

        <div class="field">
            <label for="confirm_password">Confirm password</label>
            <input type="password" id="confirm_password" name="confirm_password" maxlength="72"
                   class="<?= isset($errors['confirm_password']) ? 'invalid' : '' ?>">
            <small class="error"><?= e($errors['confirm_password'] ?? '') ?></small>
        </div>

        <button type="submit" class="btn">Create account</button>
    </form>

    <p class="switch">Already a member? <a href="login.php">Log in</a></p>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
