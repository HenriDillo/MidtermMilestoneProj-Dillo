<?php
// ajax/toggle_favorite.php - called by JavaScript (fetch) when the heart button is clicked.
// It saves or removes a favorite and answers with a small JSON message.
// The page itself is never reloaded.
require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

// Send a JSON answer and stop.
function json_reply($data, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_reply(['success' => false, 'message' => 'Invalid request.'], 405);
}
if (!Auth::check()) {
    json_reply(['success' => false, 'message' => 'Please log in again.'], 401);
}
if (!csrf_valid()) {
    json_reply(['success' => false, 'message' => 'Invalid request. Please refresh the page.'], 403);
}

$recipeId = (int) ($_POST['recipe_id'] ?? 0);

if (!(new Recipe($pdo))->exists($recipeId)) {
    json_reply(['success' => false, 'message' => 'That recipe no longer exists.'], 404);
}

$isNowFavorite = (new Favorite($pdo))->toggle(Auth::id(), $recipeId);

json_reply(['success' => true, 'favorited' => $isNowFavorite]);
