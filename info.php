<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/checklang.php'; // Подключаем проверку языка
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных

// Получаем статус из GET-параметра
$status = $_GET['s'] ?? null;
if (!$status) {
    die('Unknown status.');
}

// Массив всех допустимых статусов
$allowedStatuses = [
    'pending',             // Ожидание подтверждения email
    'banned',              // Заблокирован админом
    'blocked',             // Временная блокировка за неверные пароли
    'no_subscription',     // Нет активной подписки
    'expired',             // Подписка истекла
    'cancelled',           // Подписка отменена
    'awaiting_payment'     // Ожидается оплата подписки
];

// Проверяем, что переданный статус входит в разрешенные
if (!in_array($status, $allowedStatuses, true)) {
    die('Invalid status.');
}

// Загружаем строки из языкового файла для текущего статуса
$title = $_["{$status}_title"] ?? 'Неизвестный статус';
$message = $_["{$status}_message"] ?? 'Описание для этого статуса отсутствует.';
$link = $_["{$status}_link"] ?? '';

// Шаблон вывода
?>
<!DOCTYPE html>
<html lang="<?php echo $selected_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $_['main_title']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/info.css">
    <script src="../script.js"></script>
    <link href="images/icon.png" rel="icon" />
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/hreflang.php'; ?>
</head>
<body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/navbar-main.php'; // Подключаем меню ?>
<div class="container">
<div class="message">
    <h1><?= htmlspecialchars($title) ?></h1>
    <p><?= nl2br(htmlspecialchars($message)) ?></p>
    <?= $link ? "$link" : '' ?>
</div>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/bottom.php'; ?>
</div>
</body>
</html>
