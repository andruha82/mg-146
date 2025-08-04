<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/checklang.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/functions.php';


// 1) Проверяем, что у нас есть в сессии user_id
if (empty($_SESSION['user_id'])) {
    header("Location: /signup.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// 2) Достаём пользователя со статусом pending
$stmt = $pdo->prepare("
    SELECT email
      FROM users
     WHERE id = ? AND status = 'pending'
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // Нечего пересылать — редирект обратно
    header("Location: /signup.php");
    exit;
}

// 3) Генерируем новый токен и время истечения
$token   = random_int(100000, 999999);
$expires = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');

// 4) Обновляем в БД
$stmt = $pdo->prepare("
    UPDATE users
       SET registration_token            = ?,
           registration_token_expiration = ?
     WHERE id = ?
");
$stmt->execute([$token, $expires, $user_id]);

// 5) Шлем письмо с новым токеном
sendConfirmationEmail($user['email'], $token, $_, $selected_lang);

// 6) Установим флеш-сообщение и редиректим обратно
$_SESSION['message'] = $_['confirm_resent'];  // нужно добавить в языковые файлы
header("Location: /confirm_email.php");
exit;
