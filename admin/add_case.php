<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/auth.php'; // Проверка авторизации

// Получение всех колонок таблицы
$columns = $pdo->query("SHOW COLUMNS FROM cases")->fetchAll(PDO::FETCH_COLUMN);

// Фильтруем языковые поля (title_* и content_*)
$language_fields = array_filter($columns, function ($column) {
    return preg_match('/^(title|content)_/', $column);
});

// Сообщение об успешном добавлении
$message = "";

// Добавление новой статьи
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['image_url'], $_POST['status'])) {
        echo "<p style='color: red;'>Ошибка: Все поля обязательны!</p>";
        exit;
    }

    $fields = ["image_url", "status"];
    $placeholders = ["?", "?"];
    $params = [$_POST['image_url'], $_POST['status']];

    foreach ($language_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            echo "<p style='color: red;'>Ошибка: Поле $field обязательно!</p>";
            exit;
        }
        $fields[] = $field;
        $placeholders[] = "?";
        $params[] = $_POST[$field];
    }

    $fields[] = "created_at";
    $fields[] = "updated_at";
    $placeholders[] = "CURRENT_TIMESTAMP";
    $placeholders[] = "CURRENT_TIMESTAMP";

    $query = "INSERT INTO cases (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $placeholders) . ")";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $message = "Данные сохранены";
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
    <title>Добавить новую статью || Админка</title>
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
        <h1>Добавить новую статью</h1>

        <?php if (!empty($message)): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="post" action="add_case.php"  id="add-form">
            <label>URL картинки:</label>
            <input type="text" name="image_url" required>

            <?php foreach ($language_fields as $field): ?>
                <label><?= strtoupper(str_replace('_', ' ', $field)) ?>:</label>
                <?php if (strpos($field, 'title_') === 0): ?>
                    <input type="text" name="<?= $field ?>" required>
                <?php else: ?>
                    <textarea name="<?= $field ?>" required></textarea>
                <?php endif; ?>
            <?php endforeach; ?>

            <label>Статус статьи:</label>
            <select name="status" required>
                <option value="disabled" selected>Отключена</option>
                <option value="enabled">Включена</option>
            </select>

            <input type="submit" value="Добавить статью" class="button">
        </form>
    </div>

</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/bottom.php'; ?>

</body>
</html>
