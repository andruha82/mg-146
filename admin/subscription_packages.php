<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/auth.php'; // Проверка авторизации

// Получение списка пакетов
$packages = $pdo->query("SELECT * FROM subscription_packages ORDER BY id DESC")->fetchAll();

// Обработка выбора пакета
$selected_package = null;
if (isset($_GET['package_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM subscription_packages WHERE id = ?");
    $stmt->execute([$_GET['package_id']]);
    $selected_package = $stmt->fetch();
}

// Обновление данных
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id'], $_POST['name'], $_POST['price'], $_POST['currency'], $_POST['duration_days'])) {
        echo "<p style='color: red;'>Ошибка: Все поля обязательны!</p>";
        exit;
    }

    // Подготовка данных для обновления
    $fields = [
        "name = ?",
        "price = ?",
        "currency = ?",
        "max_views = ?",
        "max_questions = ?",
        "duration_days = ?"
    ];

    // Преобразуем пустые значения в NULL
    $params = [
        $_POST['name'],
        $_POST['price'],
        $_POST['currency'],
        $_POST['max_views'] ?: 0,
        $_POST['max_questions'] ?: 0,
        $_POST['duration_days']
    ];

    // Добавляем ID пакета в параметры
    $params[] = $_POST['id'];

    // Формируем SQL-запрос
    $query = "UPDATE subscription_packages SET " . implode(", ", $fields) . " WHERE id = ?";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        header("Location: subscription_packages.php?package_id=" . $_POST['id'] . "&updated=true");
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
    <title>Редактирование пакетов подписки || Админка</title>
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
        <h1>Редактирование пакетов подписки</h1>

        <form method="get" action="subscription_packages.php">
            <label for="package_id">Выберите пакет:</label>
            <select name="package_id" id="package_id">
                <option value="">-- Выберите пакет --</option>
                <?php foreach ($packages as $package): ?>
                    <option value="<?= $package['id'] ?>" <?= (isset($_GET['package_id']) && $_GET['package_id'] == $package['id']) ? 'selected' : '' ?>>
                        #<?= $package['id'] ?>: <?= htmlspecialchars($package['name']) ?> (Цена: <?= $package['price'] ?> <?= $package['currency'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="button">Выбрать</button>
        </form>

        <?php if ($selected_package): ?>
        <br><br>
        <form method="post" action="subscription_packages.php">
            <input type="hidden" name="id" value="<?= $selected_package['id'] ?>">

            <label>Название пакета:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($selected_package['name']) ?>" required>

            <label>Цена:</label>
            <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($selected_package['price']) ?>" required>

            <label>Валюта:</label>
            <input type="text" name="currency" value="<?= htmlspecialchars($selected_package['currency']) ?>" required>

            <label>Максимум просмотров:</label>
            <input type="number" name="max_views" value="<?= htmlspecialchars($selected_package['max_views']) ?>">

            <label>Максимум вопросов:</label>
            <input type="number" name="max_questions" value="<?= htmlspecialchars($selected_package['max_questions']) ?>">

            <label>Длительность (дни):</label>
            <input type="number" name="duration_days" value="<?= htmlspecialchars($selected_package['duration_days']) ?>" required>

            <input type="submit" name="update" value="Изменить" class="button">
        </form>
        <?php endif; ?>
    </div>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/bottom.php'; ?>

</body>
</html>