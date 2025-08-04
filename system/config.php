<?php

// MySQL
$host = 'localhost';
$db   = 'database';
$user = 'user';
$pass = 'password';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     $pdo->exec("SET NAMES utf8mb4");
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Telegram
$botToken = 'BOT-TOKEN';
define('BOT_TOKEN', 'BOT-TOKEN');
define('API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');
define('DEFAULT_LANG', 'uk'); // язык по умолчанию
?>

