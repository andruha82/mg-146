<?php
session_start(); // Запускаем сессию в начале

// Если сессия не установлена – редирект на страницу входа
if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// Проверяем, не отключен ли администратор
$query = $pdo->prepare("SELECT status FROM admins WHERE email = ?");
$query->execute([$_SESSION['email']]);
$status = $query->fetchColumn();

if ($status === 'disabled') {
    session_destroy(); // Очищаем сессию
    header("Location: login.php");
    exit();
}
?>
