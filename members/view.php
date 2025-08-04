<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/checklang.php'; // Подключаем проверку языка
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных;
include_once $_SERVER['DOCUMENT_ROOT'] . '/members/check_auth.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/members/check_subscr.php';

// Проверка статуса, срока и лимитов просмотров в подписке для вывода контента

$today = date('Y-m-d');
$has_access = false;

$is_active = $subscription['status'] === 'active';
$not_expired = $subscription['end_date'] >= $today;
$within_limit = (
    $subscription['max_views'] == 9999 || // безлимит
    $subscription['views_used'] < $subscription['max_views']
);

if ($is_active && $not_expired && $within_limit) {
    $has_access = true;
}

// Получение ID статьи из GET-параметра

if (!isset($_GET['id'])) {
    die("Статья не найдена");
}
$articleId = (int)$_GET['id'];

// Динамическое формирование имен полей для заголовка, контента, видео и миниатюры
$title_field     = "title_" . $selected_lang;
$content_field   = "content_" . $selected_lang;
$video_field     = "video_path_" . $selected_lang;
$thumbnail_field = "thumbnail_" . $selected_lang;

// Формирование SQL-запроса
$query = "SELECT publication_date, reading_time, views, likes, status, 
                 {$title_field} AS title, {$content_field} AS content, 
                 {$video_field} AS video_path, {$thumbnail_field} AS thumbnail 
          FROM articles WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$articleId]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    die("Статья не найдена");
}

$publicationDate = date("d/m/Y", strtotime($article['publication_date']));
$readingTime     = $article['reading_time'];
$videoPath       = $article['video_path'];
$thumbnail       = $article['thumbnail'];
$title           = $article['title'];
$content         = $article['content'];

// ✅ Запись в таблицу user_views и увеличение views_used в таблице user_subscriptions
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Вставка или обновление в user_views
    $stmt = $pdo->prepare("INSERT INTO user_views (user_id, article_id) 
        VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE viewed_at = NOW()");
    $stmt->execute([$userId, $articleId]);

    // Увеличение views_used в user_subscriptions
    $stmt = $pdo->prepare("UPDATE user_subscriptions 
        SET views_used = views_used + 1 
        WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$userId]);
}

// Функция для обрезки текста
function getPreviewText($text, $wordLimit = 100) {
    $cleanText = strip_tags($text); // Убираем HTML
    $words = preg_split('/\s+/', $cleanText);

    if (count($words) <= $wordLimit) {
        return $text;
    }

    $shortText = implode(' ', array_slice($words, 0, $wordLimit)) . '...';
    return "<p>$shortText</p><br><center><div class='video-popup'><h2>⛔ Текст статьи скрыт.</h2><p>Обновите подписку чтобы снова получить доступ ко всем материалам.</p></div></center>";
}
?>

<!DOCTYPE html>
<html lang="<?php echo $selected_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $title; ?></title>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.3/dist/purify.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/view.css">
    <link rel="stylesheet" href="../styles/chat.css">
    <script src="../script.js"></script>
    <link href="../images/icon.png" rel="icon" />
</head>
<body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/navbar-member.php'; ?>
    <div class="section">
        <h2><?= $title; ?></h2>
        <div style="width: 100%; max-width: 1024px; margin: 10px auto; padding: 0 20px;">
            <div style="display: flex; align-items: center; text-align: center;">
                <hr style="flex: 1; border: none; border-top: 1px solid #ccc;">
                <span style="padding: 0 15px; font-size: 18px; background: #fff; color: #cece0f;">🌟🌟🌟🌟🌟</span>
                <hr style="flex: 1; border: none; border-top: 1px solid #ccc;">
            </div>
        </div>

        <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
        <script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>

<?php if ($has_access): ?>
        <div class="video-container" style="max-width: 1024px; margin: auto; position: relative; z-index: 1;">
            <video id="player" controls poster="<?php echo htmlspecialchars($thumbnail); ?>">
                <source src="<?php echo htmlspecialchars($videoPath); ?>" type="video/mp4">
                Ваш браузер не поддерживает видео.
            </video>
        </div>
        <script>
            const player = new Plyr('#player', {
                controls: ['play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen']
            });
        </script>
<?php else: ?>
        <div class="video-container" style="max-width: 1024px; margin: auto; position: relative; z-index: 1;">
            <video id="player" controls poster="<?php echo htmlspecialchars($thumbnail); ?>">
                <source src="null.mp4" type="video/mp4">
                Ваш браузер не поддерживает видео.
            </video>


    <!-- Оверлей -->

<style>
    .video-container {
        max-width: 1024px;
        margin: auto;
        position: relative;
        z-index: 1;
    }

    .video-container video {
        width: 100%;
        height: auto;
        display: block;
    }

.video-overlay {
    position: absolute;
    top: 0; left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 5;
    pointer-events: all;
    border-radius: 8px;
    padding: 20x;
    box-sizing: border-box;
}

.video-popup {
    background-color: #fff;
    color: #333;
    border-radius: 12px;
    padding: 15px 25px;
    max-width: 90%;
    width: 400px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
    text-align: center;
    animation: popup-fade-in 0.3s ease-in-out;
}

.video-popup h2 {
    font-size: 20px;
    margin-bottom: 10px;
}

.video-popup p {
    font-size: 16px;
    margin-bottom: 0px;
}

.video-popup a {
    background-color: #007bff;
    padding: 10px 20px;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    transition: background 0.3s;
    display: inline-block;
}

.video-popup a:hover {
    background-color: #0056b3;
}

@keyframes popup-fade-in {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

</style>

    <div class="video-overlay">
      <div class="video-popup">
        <h2>⛔ Доступ ограничен</h2>
        <p>Обновите подписку чтобы снова получить доступ ко всем материалам.</p>
        <a href="/upgrade">Подробнее</a>
      </div>
    </div>

</div>

        <script>
            const player = new Plyr('#player', {
                controls: ['play']
            });

            // Блокируем попытки воспроизведения
            player.on('play', event => {
            event.preventDefault();
            player.pause(); // сразу пауза
            });
        </script>
<?php endif; ?>

        <div class="info-row">
            <div class="date-block">
                <i class="fa-regular fa-calendar" title="Дата публикации"></i>
                <span><?php echo $publicationDate; ?></span>
            </div>
            <div class="article-actions">
                <i class="fa-regular fa-thumbs-up" id="likeBtn" title="Поставить лайк"></i>&nbsp;
                <i class="fa-regular fa-bookmark" id="favBtn" title="Добавить в избранное"></i>
            </div>
            <div class="reading-time-block">
                <span><?php echo $readingTime; ?> минут</span>
                <i class="fa-solid fa-book-open"></i>
            </div>
        </div>


        <div class="content">
            <?php echo $has_access ? $content : getPreviewText($content, 50); ?>
        </div>

    </div>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/ai/chat_widget.php'; ?>
</body>
</html>
