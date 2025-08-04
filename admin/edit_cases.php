<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/auth.php'; // Проверка авторизации

// Получение списка кейсов
$cases = $pdo->query("SELECT id, title_ru FROM cases ORDER BY created_at DESC")->fetchAll();

// Обработка выбора статьи
$selected_case = null;
if (isset($_GET['case_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM cases WHERE id = ?");
    $stmt->execute([$_GET['case_id']]);
    $selected_case = $stmt->fetch();
}

// Получение всех колонок таблицы
$columns = $pdo->query("SHOW COLUMNS FROM cases")->fetchAll(PDO::FETCH_COLUMN);

// Фильтруем языковые поля (title_* и content_*)
$language_fields = array_filter($columns, function ($column) {
    return preg_match('/^(title|content)_/', $column);
});

// Обновление данных
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id'], $_POST['image_url'], $_POST['status'])) {
        echo "<p style='color: red;'>Ошибка: Все поля обязательны!</p>";
        exit;
    }

    $fields = ["image_url = ?", "status = ?"];
    $params = [$_POST['image_url'], $_POST['status']];

    foreach ($language_fields as $field) {
        if (!isset($_POST[$field])) {
            echo "<p style='color: red;'>Ошибка: отсутствует поле $field</p>";
            exit;
        }
        $fields[] = "$field = ?";
        $params[] = $_POST[$field];
    }

    $fields[] = "updated_at = ?";
    $params[] = date("Y-m-d H:i:s");

    $query = "UPDATE cases SET " . implode(", ", $fields) . " WHERE id = ?";
    $params[] = $_POST['id'];

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        header("Location: edit_cases.php?case_id=" . $_POST['id'] . "&updated=true");
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
    <title>Редактирование кейса || Админка</title>
    <meta name="description" content="" />
    <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/admin.css">
    <script src="../script.js"></script>
    <link href="../images/icon.png" rel="icon" />
</head>
    <script>
        function openPreview(fieldId, lang) {
            var title = document.querySelector("input[name='title_" + lang + "']").value;
            var imageUrl = document.querySelector("input[name='image_url']").value;
            var content = document.querySelector("textarea[name='" + fieldId + "']").value;

            var previewWindow = window.open("", "_blank", "width=430,height=800");
            previewWindow.document.write(`
                <html>
                <head>
                    <title>Предварительный просмотр</title>
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">
                    <link rel="stylesheet" href="../styles/styles.css">
                    <link rel="stylesheet" href="../styles/navbar.css">
                    <link rel="stylesheet" href="../styles/cases.css">
                </head>
                <body>
                <div class="container">
                  <div class="section">
                    <h2>${title}</h2>
                    <div class="content">
                    <img src="${imageUrl}">
                    ${content}
                    </div>
                  </div>
                </div>
                </body>
                </html>
            `);
            previewWindow.document.close();
        }
    </script>
<body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/navbar-admin.php'; // Подключаем меню ?>
<div class="container">
    <div class="section">
        <h1>Редактирование кейса</h1>

        <form method="get" id="edit-form">
            <label for="case_id">Выберите кейс:</label>
            <select name="case_id" id="case_id">
                <option value="">-- Выберите кейс --</option>
                <?php foreach ($cases as $case): ?>
                    <option value="<?= $case['id'] ?>" <?= (isset($_GET['case_id']) && $_GET['case_id'] == $case['id']) ? 'selected' : '' ?>>
                        <?= $case['id'] ?>. <?= htmlspecialchars($case['title_ru']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="button">Выбрать</button>
        </form>

        <?php if ($selected_case): ?>
        <br><br>
        <form method="post" id="edit-form">
            <input type="hidden" name="id" value="<?= $selected_case['id'] ?>">

            <label>URL картинки:</label>
            <input type="text" name="image_url" value="<?= htmlspecialchars($selected_case['image_url']) ?>" required>

            <?php foreach ($language_fields as $field): ?>
                <?php $lang = explode('_', $field)[1]; ?>
                <label><?= strtoupper(str_replace('_', ' ', $field)) ?>:</label>
                <?php if (strpos($field, 'title_') === 0): ?>
                    <input type="text" name="<?= $field ?>" value="<?= htmlspecialchars($selected_case[$field]) ?>" required>
                <?php else: ?>
                    <textarea name="<?= $field ?>" required><?= htmlspecialchars($selected_case[$field]) ?></textarea>
                    <center><a href="javascript:void(0);" class="preview-link" onclick="openPreview('<?= $field ?>', '<?= $lang ?>')"><b>Предварительный просмотр</b></a></center><br>
                <?php endif; ?>
            <?php endforeach; ?>

            <label>Статус статьи:</label>
            <select name="status" required>
                <option value="disabled" <?= ($selected_case['status'] == 'disabled') ? 'selected' : '' ?>>Отключена</option>
                <option value="enabled" <?= ($selected_case['status'] == 'enabled') ? 'selected' : '' ?>>Включена</option>
            </select>

            <input type="submit" name="update" value="Изменить" class="button">
        </form>
        <?php endif; ?>
    </div>

</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/bottom.php'; ?>

</body>
</html>
