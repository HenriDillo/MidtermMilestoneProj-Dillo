<?php
// logout.php - log out. Only accepts POST (the "Log out" button in the menu).
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    Auth::logout();
    set_flash('success', 'You have been logged out. Kain na tayo next time!');
}

redirect('login.php');
