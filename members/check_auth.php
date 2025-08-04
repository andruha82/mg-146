<?php

// Проверка активной сессии или восстановление из cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['session_token'])) {
    $stmt = $pdo->prepare("SELECT * FROM user_sessions WHERE session_token = ? AND expires_at > NOW()");
    $stmt->execute([$_COOKIE['session_token']]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($session) {
        $_SESSION['user_id'] = $session['user_id'];
        setcookie('session_token', $session['session_token'], time() + 30 * 24 * 60 * 60, '/', '', false, true);
    } else {
        setcookie('session_token', '', time() - 3600, '/', '', false, true);
    }
}

// Если все еще нет сессии — редирект на login
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: /login.php');
    exit;
}

// Проверка статуса учетной записи
switch ($user['status']) {
    case 'pending':
        header('Location: /account_status.php?s=pending');
        exit;
    case 'banned':
        header('Location: /account_status.php?s=banned');
        exit;
    case 'blocked':
        if ($user['lock_until'] && strtotime($user['lock_until']) > time()) {
            header('Location: /account_status.php?s=blocked');
            exit;
        } else {
            // Разблокируем, если время прошло
            $stmt = $pdo->prepare("UPDATE users SET status = 'active', failed_login_attempts = 0, lock_until = NULL WHERE id = ?");
            $stmt->execute([$user['id']]);
        }
        break;
}
?>