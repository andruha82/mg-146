<?php
require_once './system/db.php';
session_start();

// Удаляем все данные сессии
$_SESSION = [];

// Если есть кука с долгосрочной сессией, тоже удаляем её
if (isset($_COOKIE['session_token'])) {
    setcookie('session_token', '', time() - 3600, '/'); // Удаляем куку

    // Можно сразу удалить запись о сессии из базы (по желанию)
    $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE session_token = ?");
    $stmt->execute([$_COOKIE['session_token']]);
}

// Уничтожаем сессию полностью
session_destroy();

// Перенаправляем на страницу входа или главную страницу
header('Location: login.php');
exit;
