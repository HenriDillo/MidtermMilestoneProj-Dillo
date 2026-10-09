<?php
// init.php - the first line of every page includes this file.
// It starts the session, connects to the database, and loads our helpers and classes.

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
date_default_timezone_set('Asia/Manila');

require_once __DIR__ . '/db.php';          // gives us $pdo
require_once __DIR__ . '/functions.php';   // small helper functions

// Autoloader: when we write "new Recipe(...)", PHP loads classes/Recipe.php for us.
spl_autoload_register(function ($className) {
    require_once __DIR__ . '/../classes/' . $className . '.php';
});
