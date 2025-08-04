<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/checklang.php'; // Подключаем проверку языка
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных
include_once $_SERVER['DOCUMENT_ROOT'] . '/members/check_auth.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/members/check_subscr.php';

// Формируем названия полей с учетом языка
$title_field     = "title_" . $selected_lang;
$thumbnail_field = "thumbnail_" . $selected_lang;

// Получаем последние 20 просмотренных статей
$stmt = $pdo->prepare("
    SELECT a.id, a.{$title_field} AS title, a.{$thumbnail_field} AS thumbnail, uv.viewed_at
    FROM user_views uv
    JOIN articles a ON a.id = uv.article_id
    WHERE uv.user_id = ?
    ORDER BY uv.viewed_at DESC
    LIMIT 20
");
$stmt->execute([$_SESSION['user_id']]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="<?php echo $selected_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>История просмотров</title>
    <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <style>
        .history-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
            justify-content: center;
        }
        .history-item {
            width: 260px;
            border: 1px solid #ddd;
            border-radius: 6px;
            overflow: hidden;
            text-align: left;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: 0.3s ease;
        }
        .history-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 10px rgba(0,0,0,0.1);
        }
        .history-item img {
            width: 100%;
            height: 160px;
            object-fit: cover;
        }
        .history-item a {
            text-decoration: none;
            color: inherit;
            display: block;
            padding: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/navbar-member.php'; ?>

<div class="history-container">
    <?php if (empty($history)): ?>
        <p>Вы еще не смотрели ни одной статьи.</p>
    <?php else: ?>
        <?php foreach ($history as $item): ?>
            <div class="history-item">
                <a href="/members/view.php?id=<?= $item['id'] ?>">
                    <img src="<?= htmlspecialchars($item['thumbnail']) ?>" alt="Превью">
                    <?= htmlspecialchars($item['title']) ?>
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
