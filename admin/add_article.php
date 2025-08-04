<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/auth.php'; // Проверка авторизации

// Получение списка категорий для выпадающего списка
$categories = $pdo->query("SELECT id, name_ru FROM categories")->fetchAll();

// Получение всех колонок таблицы articles
$columns = $pdo->query("SHOW COLUMNS FROM articles")->fetchAll(PDO::FETCH_COLUMN);

// Фильтруем языковые поля (title_*, content_*, video_path_*, thumbnail_*)
$language_fields = array_filter($columns, function ($column) {
    return preg_match('/^(title|content|video_path|thumbnail)_/', $column);
});

// Сообщение об успешном добавлении
$message = "";

// Добавление новой статьи
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка обязательных полей
    if (!isset($_POST['category_id'], $_POST['publication_date'], $_POST['reading_time'], $_POST['status'])) {
        echo "<p style='color: red;'>Ошибка: Все поля обязательны!</p>";
        exit;
    }

    $fields = ["category_id", "publication_date", "reading_time", "status"];
    $placeholders = ["?", "?", "?", "?"];
    $params = [
        $_POST['category_id'],
        $_POST['publication_date'],
        $_POST['reading_time'],
        $_POST['status']
    ];

    // Добавляем языковые поля
    foreach ($language_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            echo "<p style='color: red;'>Ошибка: Поле $field обязательно!</p>";
            exit;
        }
        $fields[] = $field;
        $placeholders[] = "?";
        $params[] = $_POST[$field];
    }

    // Формируем SQL-запрос
    $query = "INSERT INTO articles (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $placeholders) . ")";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $message = "Статья успешно добавлена";
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
    <title>Добавить статью || Админка</title>
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

        <form method="post" action="add_article.php">
            <label>Категория:</label>
            <select name="category_id" required>
                <option value="">-- Выберите категорию --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>"><?= $category['id'] ?>.<?= htmlspecialchars($category['name_ru']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Дата публикации:</label>
            <input type="datetime-local" name="publication_date" required>

            <label>Время прочтения (в минутах):</label>
            <input type="number" name="reading_time" required>

            <?php foreach ($language_fields as $field): ?>
                <label><?= strtoupper(str_replace('_', ' ', $field)) ?>:</label>
                <?php if (strpos($field, 'title_') === 0): ?>
                    <input type="text" name="<?= $field ?>" required>
                <?php elseif (strpos($field, 'content_') === 0): ?>
                    <textarea name="<?= $field ?>" required></textarea>
                <?php else: ?>
                    <input type="text" name="<?= $field ?>" required>
                <?php endif; ?>
            <?php endforeach; ?>

            <label>Статус статьи:</label>
            <select name="status" required>
                <option value="draft" selected>Черновик</option>
                <option value="published">Опубликовано</option>
                <option value="archived">В архиве</option>
                <option value="pending">На рассмотрении</option>
            </select>

            <input type="submit" value="Добавить статью" class="button">
        </form>
    </div>

</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/bottom.php'; ?>

</body>
</html>
