<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/auth.php'; // Проверка авторизации

// Получение списка категорий
$categories = $pdo->query("SELECT id, name_ru FROM categories ORDER BY id DESC")->fetchAll();

// Обработка выбора категории
$selected_category = null;
if (isset($_GET['category_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$_GET['category_id']]);
    $selected_category = $stmt->fetch();
}

// Получение всех колонок таблицы
$columns = $pdo->query("SHOW COLUMNS FROM categories")->fetchAll(PDO::FETCH_COLUMN);

// Фильтруем языковые поля (name_* и description_*)
$language_fields = array_filter($columns, function ($column) {
    return preg_match('/^(name|description)_/', $column);
});

// Обновление данных
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id'], $_POST['status'])) {
        echo "<p style='color: red;'>Ошибка: Все поля обязательны!</p>";
        exit;
    }

    $fields = ["status = ?"];
    $params = [$_POST['status']];

    foreach ($language_fields as $field) {
        if (!isset($_POST[$field])) {
            echo "<p style='color: red;'>Ошибка: отсутствует поле $field</p>";
            exit;
        }
        $fields[] = "$field = ?";
        $params[] = $_POST[$field];
    }

    if (isset($_POST['info'])) {
        $fields[] = "info = ?";
        $params[] = $_POST['info'];
    }

    $query = "UPDATE categories SET " . implode(", ", $fields) . " WHERE id = ?";
    $params[] = $_POST['id'];

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        header("Location: edit_categories.php?category_id=" . $_POST['id'] . "&updated=true");
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
    <title>Редактирование категории || Админка</title>
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
        <h1>Редактирование категории</h1>

        <form method="get" action="edit_categories.php">
            <label for="category_id">Выберите категорию:</label>
            <select name="category_id" id="category_id">
                <option value="">-- Выберите категорию --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= (isset($_GET['category_id']) && $_GET['category_id'] == $category['id']) ? 'selected' : '' ?>>
                        <?= $category['id'] ?>. <?= htmlspecialchars($category['name_ru']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="button">Выбрать</button>
        </form>

        <?php if ($selected_category): ?>
        <br><br>
        <form method="post" action="edit_categories.php">
            <input type="hidden" name="id" value="<?= $selected_category['id'] ?>">

            <?php foreach ($language_fields as $field): ?>
                <label><?= strtoupper(str_replace('_', ' ', $field)) ?>:</label>
                <?php if (strpos($field, 'name_') === 0): ?>
                    <input type="text" name="<?= $field ?>" value="<?= htmlspecialchars($selected_category[$field]) ?>" required>
                <?php else: ?>
                    <textarea name="<?= $field ?>" required><?= htmlspecialchars($selected_category[$field]) ?></textarea>
                <?php endif; ?>
            <?php endforeach; ?>

            <label>Дополнительная информация:</label>
            <textarea name="info"><?= htmlspecialchars($selected_category['info']) ?></textarea>

            <label>Статус категории:</label>
            <select name="status" required>
                <option value="disabled" <?= ($selected_category['status'] == 'disabled') ? 'selected' : '' ?>>Отключена</option>
                <option value="enabled" <?= ($selected_category['status'] == 'enabled') ? 'selected' : '' ?>>Включена</option>
            </select>

            <input type="submit" name="update" value="Изменить" class="button">
        </form>
        <?php endif; ?>
    </div>

</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/bottom.php'; ?>

</body>
</html>
