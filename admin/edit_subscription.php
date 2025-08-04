<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/auth.php'; // Проверка авторизации

// Получение списка подписок с данными пользователя и названием пакета
$subscriptions = $pdo->query("
    SELECT us.id, us.user_id, us.package_id, u.name, u.email, sp.name AS package_name
    FROM user_subscriptions us
    JOIN users u ON us.user_id = u.id
    JOIN subscription_packages sp ON us.package_id = sp.id
    ORDER BY us.created_at DESC
")->fetchAll();

// Обработка выбора подписки
$selected_subscription = null;
if (isset($_GET['subscription_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM user_subscriptions WHERE id = ?");
    $stmt->execute([$_GET['subscription_id']]);
    $selected_subscription = $stmt->fetch();
}

// Обновление данных
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id'], $_POST['user_id'], $_POST['package_id'], $_POST['price'], $_POST['status'])) {
        echo "<p style='color: red;'>Ошибка: Все поля обязательны!</p>";
        exit;
    }

    // Подготовка данных для обновления
    $fields = [
        "user_id = ?",
        "package_id = ?",
        "partner_id = ?",
        "price = ?",
        "currency = ?",
        "promo_code = ?",
        "notes = ?",
        "payment_status = ?",
        "payment_method = ?",
        "status = ?",
        "autorenew = ?",
        "transaction_id = ?",
        "start_date = ?",
        "end_date = ?",
        "views_used = ?",
        "questions_used = ?"
    ];

    // Преобразуем пустые значения в NULL
    $params = [
        $_POST['user_id'],
        $_POST['package_id'],
        $_POST['partner_id'] ?: null,
        $_POST['price'],
        $_POST['currency'],
        $_POST['promo_code'] ?: null,
        $_POST['notes'] ?: null,
        $_POST['payment_status'],
        $_POST['payment_method'] ?: null,
        $_POST['status'],
        $_POST['autorenew'] ? 1 : 0,
        $_POST['transaction_id'] ?: null,
        $_POST['start_date'] ?: null,
        $_POST['end_date'] ?: null,
        $_POST['views_used'] ?: 0,
        $_POST['questions_used'] ?: 0
    ];

    // Добавляем ID подписки в параметры
    $params[] = $_POST['id'];

    // Формируем SQL-запрос
    $query = "UPDATE user_subscriptions SET " . implode(", ", $fields) . " WHERE id = ?";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        header("Location: edit_subscription.php?subscription_id=" . $_POST['id'] . "&updated=true");
        exit;
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Ошибка: " . $e->getMessage() . "</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование подписки || Админка</title>
    <meta name="description" content="" />
    <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/admin.css">
    <script src="../script.js"></script>
    <link href="../images/icon.png" rel="icon" />
</head>
<body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/navbar-admin.php'; // Подключаем меню ?>
<div class="container">
    <div class="section">
        <h1>Редактирование подписки</h1>

        <form method="get" action="edit_subscription.php">
            <label for="subscription_id">Выберите подписку:</label>
            <select name="subscription_id" id="subscription_id">
                <option value="">-- Выберите подписку --</option>
                <?php foreach ($subscriptions as $subscription): ?>
                    <option value="<?= $subscription['id'] ?>" <?= (isset($_GET['subscription_id']) && $_GET['subscription_id'] == $subscription['id']) ? 'selected' : '' ?>>
                        #<?= $subscription['id'] ?>: <?= htmlspecialchars($subscription['email']) ?> (<?= $subscription['name'] ?> [<?= htmlspecialchars($subscription['user_id']) ?>], пакет: <?= htmlspecialchars($subscription['package_name']) ?> [<?= $subscription['package_id'] ?>])
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="button">Выбрать</button>
        </form>

        <?php if ($selected_subscription): ?>
        <br><br>
        <form method="post" action="edit_subscription.php">
            <input type="hidden" name="id" value="<?= $selected_subscription['id'] ?>">

            <label>ID пользователя:</label>
            <input type="number" name="user_id" value="<?= htmlspecialchars($selected_subscription['user_id']) ?>" required>

            <label>ID пакета:</label>
            <input type="number" name="package_id" value="<?= htmlspecialchars($selected_subscription['package_id']) ?>" required>

            <label>ID партнера:</label>
            <input type="number" name="partner_id" value="<?= htmlspecialchars($selected_subscription['partner_id']) ?>">

            <label>Цена:</label>
            <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($selected_subscription['price']) ?>" required>

            <label>Валюта:</label>
            <input type="text" name="currency" value="<?= htmlspecialchars($selected_subscription['currency']) ?>" required>

            <label>Промокод:</label>
            <input type="text" name="promo_code" value="<?= htmlspecialchars($selected_subscription['promo_code']) ?>">

            <label>Заметки:</label>
            <textarea name="notes"><?= htmlspecialchars($selected_subscription['notes']) ?></textarea>

            <label>Статус оплаты:</label>
            <select name="payment_status" required>
                <option value="paid" <?= ($selected_subscription['payment_status'] == 'paid') ? 'selected' : '' ?>>Оплачено</option>
                <option value="unpaid" <?= ($selected_subscription['payment_status'] == 'unpaid') ? 'selected' : '' ?>>Не оплачено</option>
                <option value="refunded" <?= ($selected_subscription['payment_status'] == 'refunded') ? 'selected' : '' ?>>Возвращено</option>
            </select>

            <label>Способ оплаты:</label>
            <input type="text" name="payment_method" value="<?= htmlspecialchars($selected_subscription['payment_method']) ?>">

            <label>Статус подписки:</label>
            <select name="status" required>
                <option value="awaiting_payment" <?= ($selected_subscription['status'] == 'awaiting_payment') ? 'selected' : '' ?>>Ожидает оплаты</option>
                <option value="active" <?= ($selected_subscription['status'] == 'active') ? 'selected' : '' ?>>Активна</option>
                <option value="expired" <?= ($selected_subscription['status'] == 'expired') ? 'selected' : '' ?>>Истекла</option>
                <option value="cancelled" <?= ($selected_subscription['status'] == 'cancelled') ? 'selected' : '' ?>>Отменена</option>
            </select>

            <label>Автопродление:</label>
            <select name="autorenew">
                <option value="1" <?= ($selected_subscription['autorenew'] == 1) ? 'selected' : '' ?>>Да</option>
                <option value="0" <?= ($selected_subscription['autorenew'] == 0) ? 'selected' : '' ?>>Нет</option>
            </select>

            <label>ID транзакции:</label>
            <input type="text" name="transaction_id" value="<?= htmlspecialchars($selected_subscription['transaction_id']) ?>">

            <label>Дата начала:</label>
            <input type="date" name="start_date" value="<?= htmlspecialchars($selected_subscription['start_date']) ?>">

            <label>Дата окончания:</label>
            <input type="date" name="end_date" value="<?= htmlspecialchars($selected_subscription['end_date']) ?>">

            <label>Использовано просмотров:</label>
            <input type="number" name="views_used" value="<?= htmlspecialchars($selected_subscription['views_used']) ?>">

            <label>Использовано вопросов:</label>
            <input type="number" name="questions_used" value="<?= htmlspecialchars($selected_subscription['questions_used']) ?>">

            <input type="submit" name="update" value="Изменить" class="button">
        </form>
        <?php endif; ?>
    </div>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/bottom.php'; ?>

</body>
</html>