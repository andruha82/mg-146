<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/auth.php'; // Проверка авторизации

// Получение списка пользователей
$users = $pdo->query("SELECT id, email, name FROM users ORDER BY registration_date DESC")->fetchAll();

// Обработка выбора пользователя
$selected_user = null;
if (isset($_GET['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['user_id']]);
    $selected_user = $stmt->fetch();
}

// Обновление данных
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id'], $_POST['email'], $_POST['status'])) {
        echo "<p style='color: red;'>Ошибка: Все поля обязательны!</p>";
        exit;
    }

    // Подготовка данных для обновления
    $fields = [
        "email = ?",
        "status = ?",
        "name = ?",
        "phone = ?",
        "telegram_id = ?",
        "tg_username = ?",
        "ai_user_id = ?",
        "referral_source = ?",
        "registration_lang = ?",
        "birth_date = ?",
        "gender = ?",
        "country = ?",
        "notes = ?",
        "failed_login_attempts = ?",
        "marketing_consent = ?",
        "network = ?",
        "partner_id = ?",
        "partner_subid = ?"
    ];

    // Преобразуем пустые значения в NULL
    $params = [
        $_POST['email'],
        $_POST['status'],
        $_POST['name'] ?: null,
        $_POST['phone'] ?: null,
        $_POST['telegram_id'] ?: null,
        $_POST['tg_username'] ?: null,
        $_POST['ai_user_id'] ?: null,
        $_POST['referral_source'] ?: null,
        $_POST['registration_lang'],
        $_POST['birth_date'] ?: null,
        $_POST['gender'] ?: null,
        $_POST['country'] ?: null,
        $_POST['notes'] ?: null,
        $_POST['failed_login_attempts'] ?: 0, // По умолчанию 0, если пусто
        $_POST['marketing_consent'] ? 1 : 0, // Преобразуем в 1 или 0
        $_POST['network'] ?: null,
        $_POST['partner_id'] ?: null,
        $_POST['partner_subid'] ?: null
    ];

    // Добавляем ID пользователя в параметры
    $params[] = $_POST['id'];

    // Формируем SQL-запрос
    $query = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        header("Location: users.php?user_id=" . $_POST['id'] . "&updated=true");
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
    <title>Админка</title>
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
        <h1>Редактирование пользователя</h1>

        <form method="get" action="users.php">
            <label for="user_id">Выберите пользователя:</label>
            <select name="user_id" id="user_id">
                <option value="">-- Выберите пользователя --</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>" <?= (isset($_GET['user_id']) && $_GET['user_id'] == $user['id']) ? 'selected' : '' ?>>
                        <?= $user['id'] ?>. <?= htmlspecialchars($user['email']) ?> (<?= $user['name']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="button">Выбрать</button>
        </form>

        <?php if ($selected_user): ?>
        <br><br>
        <form method="post" action="users.php">
            <input type="hidden" name="id" value="<?= $selected_user['id'] ?>">

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($selected_user['email']) ?>" required>

            <label>Статус пользователя:</label>
            <select name="status" required>
                <option value="pending" <?= ($selected_user['status'] == 'pending') ? 'selected' : '' ?>>Ожидает</option>
                <option value="active" <?= ($selected_user['status'] == 'active') ? 'selected' : '' ?>>Активен</option>
                <option value="banned" <?= ($selected_user['status'] == 'banned') ? 'selected' : '' ?>>Забанен</option>
                <option value="blocked" <?= ($selected_user['status'] == 'blocked') ? 'selected' : '' ?>>Заблокирован</option>
            </select>

            <label>Имя:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($selected_user['name']) ?>">

            <label>Телефон:</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($selected_user['phone']) ?>">

            <label>Telegram ID:</label>
            <input type="number" name="telegram_id" value="<?= htmlspecialchars($selected_user['telegram_id']) ?>" min="0">

            <label>Telegram Username:</label>
            <input type="text" name="tg_username" value="<?= htmlspecialchars($selected_user['tg_username']) ?>">

            <label>AI User ID:</label>
            <input type="text" name="ai_user_id" value="<?= htmlspecialchars($selected_user['ai_user_id']) ?>">

            <label>Язык регистрации:</label>
            <input type="text" name="registration_lang" value="<?= htmlspecialchars($selected_user['registration_lang']) ?>">

            <label>Дата рождения:</label>
            <input type="date" name="birth_date" value="<?= htmlspecialchars($selected_user['birth_date']) ?>">

            <label>Пол:</label>
            <select name="gender">
                <option value="">-- Не указано --</option>
                <option value="male" <?= ($selected_user['gender'] == 'male') ? 'selected' : '' ?>>Мужской</option>
                <option value="female" <?= ($selected_user['gender'] == 'female') ? 'selected' : '' ?>>Женский</option>
                <option value="other" <?= ($selected_user['gender'] == 'other') ? 'selected' : '' ?>>Другой</option>
            </select>

            <label>Страна:</label>
            <input type="text" name="country" value="<?= htmlspecialchars($selected_user['country']) ?>">

            <label>Заметки:</label>
            <textarea name="notes"><?= htmlspecialchars($selected_user['notes']) ?></textarea>

            <label>Неудачные попытки входа:</label>
            <input type="number" name="failed_login_attempts" value="<?= htmlspecialchars($selected_user['failed_login_attempts']) ?>" min="0">

            <label>Согласие на маркетинг:</label>
            <select name="marketing_consent">
                <option value="0" <?= ($selected_user['marketing_consent'] == 0) ? 'selected' : '' ?>>Нет</option>
                <option value="1" <?= ($selected_user['marketing_consent'] == 1) ? 'selected' : '' ?>>Да</option>
            </select>

            <label>Сеть либо партнерка:</label>
            <input type="text" name="network" value="<?= htmlspecialchars($selected_user['network']) ?>">

            <label>Источник регистрации:</label>
            <input type="text" name="referral_source" value="<?= htmlspecialchars($selected_user['referral_source']) ?>">

            <label>ID партнера или Click ID:</label>
            <input type="text" name="partner_id" value="<?= htmlspecialchars($selected_user['partner_id']) ?>" min="0">

            <label>Партнерский subid:</label>
            <input type="text" name="partner_subid" value="<?= htmlspecialchars($selected_user['partner_subid']) ?>">

            <input type="submit" name="update" value="Изменить" class="button">
        </form>
        <?php endif; ?>
    </div>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/bottom.php'; ?>

</body>
</html>