<?php
// Получаем текущие параметры из URL, кроме 'lang'
$query_params = $_GET;
unset($query_params['lang']); // Убираем lang

// Строим новый URL
$current_query = !empty($query_params) ? http_build_query($query_params) . '&' : '';
?>
<nav class="navbar">
        <div class="logo-container"><img src="images/logo.png" width=22 height=22><div class="logo">MindGuide</div></div>
        <div class="menu-icon" id="menu-icon">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>
        <ul class="nav-links" id="nav-links">
            <li><a href="./index.php"><?php echo $_['mainpage']; ?></a></li>
            <li class=cases><a class="disabled-link"><?php echo $_['cases']; ?></a>
                <ul class="dropdown">
<?php

$selected_lang = $_COOKIE['lang'] ?? 'uk';

// Формируем SQL-запрос с выбором нужного заголовка
$title_column = "title_" . $selected_lang;
$sql = "SELECT id, $title_column AS title FROM cases WHERE status = 'enabled' ORDER BY created_at DESC LIMIT 10";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка запроса: " . $e->getMessage());
}

// Выводим список кейсов
if (!empty($cases)) {
    foreach ($cases as $case) {
        echo "<li><a href='./cases.php?id=" . htmlspecialchars($case['id']) . "'>" . htmlspecialchars($case['title']) . "</a></li>\n";
    }
} else {
        echo "<li><a href='./cases.php'>No cases</a></li>";
}
?>
                </ul>
            </li>
            <li><a href="./prices.php"><?php echo $_['prices']; ?></a></li>
            <li><a href="./contacts.php"><?php echo $_['contacts']; ?></a></li>
            <li class="language"><a class="disabled-link"><?php echo $_['language']; ?></a>
                <ul class="dropdown">
                    <li><a href="?<?php echo $current_query; ?>lang=ru" data-lang="ru"><?php echo $_['russian']; ?></a></li>
                    <li><a href="?<?php echo $current_query; ?>lang=uk" data-lang="uk"><?php echo $_['ukrainian']; ?></a></li>
                </ul>
            </li>
    <div class="button-container">
            <li><a href="./login.php" class="button-style gray"><?php echo $_['signin']; ?></a></li>
            <li><a href="./signup.php" class="button-style green"><?php echo $_['signup']; ?></a></li>
    </div>
            <div class="mobile-stripes">
               <div class="stripe white"></div>
               <div class="stripe blue"></div>
            </div>
        </ul>
    </nav>
