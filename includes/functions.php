<?php
// functions.php - small helpers used all over the site.

// Make text safe to print inside HTML. This stops XSS.
// Use e() on EVERYTHING a user typed before echoing it.
function e($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

// Send the browser to another page and stop running this one.
function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

// Read a text field from $_POST (trimmed). Gives '' if it is missing.
function post_text($key)
{
    if (isset($_POST[$key]) && is_string($_POST[$key])) {
        return trim($_POST[$key]);
    }
    return '';
}

function format_date($date)
{
    return date('M j, Y', strtotime($date));
}

/* ---------- Flash messages (show once, then disappear) ---------- */

function set_flash($type, $message)   // $type is 'success' or 'error'
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/* ---------- CSRF protection ----------
   Every form that changes data carries a secret token. A fake form on another
   website cannot know it, so the server can tell real requests from fake ones. */

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field()   // put this inside every POST form
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_valid()
{
    $sent = $_POST['csrf_token'] ?? '';
    return is_string($sent) && hash_equals(csrf_token(), $sent);
}

function verify_csrf()
{
    if (!csrf_valid()) {
        http_response_code(403);
        die('Invalid request. Please go back, refresh the page and try again.');
    }
}

/* ---------- Pieces of HTML we reuse ---------- */

// The Save / Saved heart button. The JavaScript in app.js makes it work.
function favorite_button($recipeId, $isFavorite, $removeCard = false)
{
    $class = 'btn btn-fav' . ($isFavorite ? ' is-saved' : '');
    $label = $isFavorite ? '♥ Saved' : '♡ Save';
    $extra = $removeCard ? ' data-remove-card="1"' : '';
    return '<button type="button" class="' . $class . '" data-recipe-id="' . (int) $recipeId . '"' . $extra . '>'
         . $label . '</button>';
}

// Show a simple error page (404 not found, 403 not allowed) and stop.
function show_error_page($code, $title, $message)
{
    http_response_code($code);
    $pageTitle = $title;
    include __DIR__ . '/header.php';
    echo '<div class="notice-page"><h1>' . e($title) . '</h1><p>' . e($message) . '</p>';
    echo '<a class="btn" href="recipes.php">Back to recipes</a></div>';
    include __DIR__ . '/footer.php';
    exit;
}
