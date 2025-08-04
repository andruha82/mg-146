<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/auth.php'; // Проверка авторизации

// Получение списка категорий для выпадающего списка
$categories = $pdo->query("SELECT id, name_ru FROM categories")->fetchAll();

// Получение списка статей
$articles = $pdo->query("SELECT id, title_ru FROM articles ORDER BY publication_date DESC")->fetchAll();

// Обработка выбора статьи
$selected_article = null;
if (isset($_GET['article_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$_GET['article_id']]);
    $selected_article = $stmt->fetch();
}

// Получение всех колонок таблицы articles
$columns = $pdo->query("SHOW COLUMNS FROM articles")->fetchAll(PDO::FETCH_COLUMN);

// Фильтруем языковые поля (title_*, content_*, video_path_*, thumbnail_*)
$language_fields = array_filter($columns, function ($column) {
    return preg_match('/^(title|content|video_path|thumbnail|quick_description)_/', $column);
});

// Обновление данных
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id'], $_POST['category_id'], $_POST['publication_date'], $_POST['reading_time'], $_POST['status'])) {
        echo "<p style='color: red;'>Ошибка: Все поля обязательны!</p>";
        exit;
    }

    $fields = ["category_id = ?", "publication_date = ?", "reading_time = ?", "status = ?"];
    $params = [
        $_POST['category_id'],
        $_POST['publication_date'],
        $_POST['reading_time'],
        $_POST['status']
    ];

    // Добавляем языковые поля
    foreach ($language_fields as $field) {
        if (!isset($_POST[$field])) {
            echo "<p style='color: red;'>Ошибка: отсутствует поле $field</p>";
            exit;
        }
        $fields[] = "$field = ?";
        $params[] = $_POST[$field];
    }

    $query = "UPDATE articles SET " . implode(", ", $fields) . " WHERE id = ?";
    $params[] = $_POST['id'];

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        header("Location: edit_articles.php?article_id=" . $_POST['id'] . "&updated=true");
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
    <title>Редактирование статьи || Админка</title>
    <meta name="description" content="" />
    <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/admin.css">
    <script src="../script.js"></script>
    <link href="../images/icon.png" rel="icon" />
</head>
<?php
// Получение списка категорий для выпадающего списка
$categories = $pdo->query("SELECT id, name_ru FROM categories")->fetchAll();

// Получение списка статей
$articles = $pdo->query("SELECT id, category_id, title_ru FROM articles ORDER BY publication_date DESC")->fetchAll();
?>
<script>
// Сохраняем данные в JavaScript
const categories = <?= json_encode($categories) ?>;
const articles = <?= json_encode($articles) ?>;
</script>
<body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/navbar-admin.php'; // Подключаем меню ?>
<div class="container">
    <div class="section">
        <h1>Редактирование статьи</h1>

        <!-- Форма для выбора категории и статьи -->
        <form method="get" action="edit_articles.php" id="categoryForm">
            <label for="category_id">Выберите категорию:</label>
            <select name="category_id" id="category_id">
                <option value="">-- Все категории --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>"><?= $category['id'] ?>.<?= htmlspecialchars($category['name_ru']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="article_id">Выберите статью:</label>
            <select name="article_id" id="article_id">
                <option value="">-- Выберите статью --</option>
                <?php foreach ($articles as $article): ?>
                    <option value="<?= $article['id'] ?>" <?= (isset($_GET['article_id']) && $_GET['article_id'] == $article['id']) ? 'selected' : '' ?>>
                        <?= $article['id'] ?>. <?= htmlspecialchars($article['title_ru']) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="button">Выбрать</button>
        </form>

        <?php if ($selected_article): ?>
        <br><br>
        <form method="post" action="edit_articles.php" id="editForm">
            <input type="hidden" name="id" value="<?= $selected_article['id'] ?>">

            <label>Категория:</label>
            <select name="category_id" required>
                <option value="">-- Выберите категорию --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= ($selected_article['category_id'] == $category['id']) ? 'selected' : '' ?>>
                        <?= $category['id'] ?>.<?= htmlspecialchars($category['name_ru']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Дата публикации:</label>
            <input type="datetime-local" name="publication_date" value="<?= date('Y-m-d\TH:i', strtotime($selected_article['publication_date'])) ?>" required>

            <label>Время прочтения (в минутах):</label>
            <input type="number" name="reading_time" value="<?= htmlspecialchars($selected_article['reading_time']) ?>" required>


<?php foreach ($language_fields as $field): ?>
    <label><?= strtoupper(str_replace('_', ' ', $field)) ?>:</label>

    <?php if (strpos($field, 'content_') === 0 || strpos($field, 'quick_description_') === 0): ?>
        <!-- content_* и quick_description_* теперь отображаются как textarea -->
        <textarea name="<?= $field ?>" required><?= htmlspecialchars($selected_article[$field]) ?></textarea>

    <?php elseif (strpos($field, 'title_') === 0): ?>
        <input type="text" name="<?= $field ?>" value="<?= htmlspecialchars($selected_article[$field]) ?>" required>

    <?php elseif (strpos($field, 'video_path_') ===0): ?>
        <input type="text" name="<?= $field ?>" value="<?= htmlspecialchars($selected_article[$field]) ?>" required>
                    <?php
                    // Извлекаем язык из названия поля (например, video_path_ru -> ru)
                    $lang = explode('_', $field)[2];
                    ?>
                    <a href="javascript:void(0);" class="preview-link" onclick="openPreview('<?= $lang ?>')"><b>Предварительный просмотр</b></a><br><br>
                <?php else: ?>
                    <input type="text" name="<?= $field ?>" value="<?= htmlspecialchars($selected_article[$field]) ?>" required>
                <?php endif; ?>
            <?php endforeach; ?>




            <label>Статус статьи:</label>
            <select name="status" required>
                <option value="draft" <?= ($selected_article['status'] == 'draft') ? 'selected' : '' ?>>Черновик</option>
                <option value="published" <?= ($selected_article['status'] == 'published') ? 'selected' : '' ?>>Опубликовано</option>
                <option value="archived" <?= ($selected_article['status'] == 'archived') ? 'selected' : '' ?>>В архиве</option>
                <option value="pending" <?= ($selected_article['status'] == 'pending') ? 'selected' : '' ?>>На рассмотрении</option>
            </select>

            <input type="submit" name="update" value="Изменить" class="button">
        </form>
        <?php endif; ?>
    </div>

    <script>
function openPreview(lang) {
    console.log("Функция openPreview вызвана для языка:", lang); // Логируем вызов функции

    // Получаем значения из формы
    const form = document.getElementById('editForm');
    const title = form.querySelector(`input[name="title_${lang}"]`)?.value || "Нет данных";
    const content = form.querySelector(`textarea[name="content_${lang}"]`)?.value || "Нет данных";
    const thumbUrl = form.querySelector(`input[name="thumbnail_${lang}"]`)?.value || "Нет данных";
    const videoUrl = form.querySelector(`input[name="video_path_${lang}"]`)?.value || "Нет данных";
    const publicationDate = form.querySelector('input[name="publication_date"]')?.value || "Нет данных";
    const readingTime = form.querySelector('input[name="reading_time"]')?.value || "Нет данных";

    // Форматируем дату (оставляем только YYYY-MM-DD)
    const formattedDate = publicationDate.split('T')[0];

    console.log("Данные из формы:", { title, content, thumbUrl, videoUrl, publicationDate: formattedDate, readingTime }); // Логируем данные

    // Открываем всплывающее окно
    const previewWindow = window.open("", "_blank", "width=430,height=800");
    if (previewWindow) {
        previewWindow.document.write(`
            <html>
            <head>
                <title>Предварительный просмотр</title>
            </head>
            <body>
                <p><strong>Заголовок (${lang}):</strong> ${title}</p>
                <p><strong>Текст (${lang}):</strong> ${content}</p>
                <p><strong>Миниатюра (${lang}):</strong> ${thumbUrl}</p>
                <p><strong>Видео (${lang}):</strong> ${videoUrl}</p>
                <p><strong>Дата публикации:</strong> ${formattedDate}</p>
                <p><strong>Время прочтения:</strong> ${readingTime} минут</p>
            </body>
            </html>
        `);
        previewWindow.document.close();
    } else {
        console.error("Не удалось открыть всплывающее окно. Возможно, блокируется браузером.");
    }
}
    </script>

<script>
// Функция для обновления списка статей
function updateArticleList() {
    const categoryId = document.getElementById('category_id').value;
    const articleSelect = document.getElementById('article_id');

    // Очищаем список статей
    articleSelect.innerHTML = '<option value="">-- Выберите статью --</option>';

    // Фильтруем статьи по выбранной категории
    const filteredArticles = categoryId
        ? articles.filter(article => article.category_id == categoryId)
        : articles;

    // Добавляем отфильтрованные статьи в список
    filteredArticles.forEach(article => {
        const option = document.createElement('option');
        option.value = article.id;
        option.textContent = `${article.id}. ${article.title_ru}`;
        articleSelect.appendChild(option);
    });
}

// Назначаем обработчик события для выпадающего списка категорий
document.getElementById('category_id').addEventListener('change', updateArticleList);

// Инициализация списка статей при загрузке страницы
updateArticleList();
</script>

</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/bottom.php'; ?>

</body>
</html>
