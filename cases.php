<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/checklang.php'; // Подключаем проверку языка
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных

$message = ''; // Переменная для отображения сообщений

// Читаем параметр id из GET-запроса
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Формирование запроса с учетом языка и ID
$sql = "SELECT image_url, title_$selected_lang AS title, content_$selected_lang AS content FROM cases WHERE id = :id AND status = 'enabled'";

// Подготовленный запрос с использованием PDO
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    $image_url = $result['image_url'];
    $title = $result['title'];
    $content = $result['content'];
} else {
    $message = 'Некорректный ID';
}

?>

<!DOCTYPE html>
<html lang="<?php echo $selected_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_['forgot_title']; ?></title>
    <meta name="description" content="<?= $_['forgot_description']; ?>" />
    <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/cases.css">
    <script src="../script.js"></script>
    <link href="images/icon.png" rel="icon" />
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/hreflang.php'; ?>
</head>
<body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/navbar-main.php'; ?>

<div class="container">
    <?php if (!empty($message)): ?>
        <div class="instruction"><?= $message; ?></div>
    <?php else: ?>
        <div class="section">
            <h2><?= $title; ?></h2>
            <div class="content">
                <img src="<?= $image_url; ?>">
                <?= $content; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/bottom.php'; ?>

</body>
</html>
