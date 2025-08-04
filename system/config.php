<?php

// MySQL
$host = 'localhost';
$db   = 'mguide_main';
$user = 'mguide_usr';
$pass = 'Fh47rcs4cfg';
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
$botToken = '7763376179:AAHqT84I2CXfhQgu75uCZoFW0ZvosMhIJvg';
define('BOT_TOKEN', '7763376179:AAHqT84I2CXfhQgu75uCZoFW0ZvosMhIJvg');
define('API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');
define('DEFAULT_LANG', 'uk'); // язык по умолчанию
?>
