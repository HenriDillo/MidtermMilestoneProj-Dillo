<?php
// db.php - the ONE place where we connect to the database.
// Every page gets $pdo by including includes/init.php, which includes this file.

$host   = 'localhost';
$dbName = 'kusina_namin';
$dbUser = 'root';   // XAMPP default
$dbPass = '';       // XAMPP default (empty)

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // errors become exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // rows come back as arrays
            PDO::ATTR_EMULATE_PREPARES   => false,                  // real prepared statements
        ]
    );
} catch (PDOException $e) {
    die('Could not connect to the database. Please check includes/db.php.');
}
