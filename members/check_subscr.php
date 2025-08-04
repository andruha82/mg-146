<?php
// Получаем последнюю подписку пользователя
$stmt = $pdo->prepare("
    SELECT us.*, sp.max_views, sp.max_questions 
    FROM user_subscriptions us
    JOIN subscription_packages sp ON sp.id = us.package_id
    WHERE us.user_id = ?
    ORDER BY us.end_date DESC
    LIMIT 1
");
$stmt->execute([$_SESSION['user_id']]);
$subscription = $stmt->fetch(PDO::FETCH_ASSOC);

// Если подписок нет вообще
if (!$subscription) {
    header('Location: /subscribe.php?s=no_subscription');
    exit;
}

// Если последняя подписка — не пробная, истекла или не активна
if (
    $subscription['package_id'] != 1 &&
    ($subscription['end_date'] < date('Y-m-d') || $subscription['status'] != 'active')
) {
    header('Location: /subscribe.php?s=expired_subscription');
    exit;
}

// Теперь переменная $subscription доступна в любом месте, где подключён check_subscr.php

// Всё хорошо — продолжаем выполнение
?>

