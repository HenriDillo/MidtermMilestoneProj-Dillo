<?php
// index.php - the front door. Members go to the recipes, guests go to the login page.
require_once __DIR__ . '/includes/init.php';

if (Auth::check()) {
    redirect('recipes.php');
}
redirect('login.php');
