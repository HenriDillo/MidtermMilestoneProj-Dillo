<?php
// comment_edit.php - edit one of your own comments.
require_once __DIR__ . '/includes/init.php';
Auth::requireLogin();

$commentId    = (int) ($_GET['id'] ?? 0);
$commentModel = new Comment($pdo);
$comment      = $commentModel->find($commentId);

if (!$comment) {
    show_error_page(404, 'Comment not found', 'This comment does not exist.');
}
if ((int) $comment['user_id'] !== Auth::id()) {
    show_error_page(403, 'Not allowed', 'You can only edit your own comments.');
}

$errors = [];
$body   = $comment['body'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $body   = post_text('body');
    $errors = Validator::comment($body);

    if (!$errors) {
        // Only mark it "edited" if the text really changed.
        if ($body !== $comment['body']) {
            $commentModel->update($commentId, Auth::id(), $body);
            set_flash('success', 'Your comment was updated.');
        }
        redirect('recipe.php?id=' . (int) $comment['recipe_id'] . '#comments');
    }
}

$pageTitle = 'Edit comment';
include __DIR__ . '/includes/header.php';
?>

<section class="form-box">
    <h1>Edit your comment</h1>

    <form method="post" action="comment_edit.php?id=<?= (int) $commentId ?>" data-validate="comment" novalidate>
        <?= csrf_field() ?>

        <div class="field">
            <label for="body">Comment</label>
            <textarea id="body" name="body" rows="4" maxlength="500"
                      class="<?= isset($errors['body']) ? 'invalid' : '' ?>"><?= e($body) ?></textarea>
            <small class="error"><?= e($errors['body'] ?? '') ?></small>
        </div>

        <div class="actions">
            <button type="submit" class="btn">Save changes</button>
            <a class="btn btn-outline" href="recipe.php?id=<?= (int) $comment['recipe_id'] ?>#comments">Cancel</a>
        </div>
    </form>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
