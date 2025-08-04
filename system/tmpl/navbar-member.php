<?php
// Получаем текущие параметры из URL, кроме 'lang'
$query_params = $_GET;
unset($query_params['lang']); // Убираем lang

// Строим новый URL
$current_query = !empty($query_params) ? http_build_query($query_params) . '&' : '';
?>
    <nav class="navbar">
        <div class="logo-container"><img src="../images/logo.png" width=22 height=22><div class="logo">MindGuide</div></div>
        <div class="menu-icon" id="menu-icon">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>
        <ul class="nav-links" id="nav-links">
            <li><a href="./index.php"><?php echo $_['mainpage']; ?></a></li>
            <li class=cases><a class="disabled-link"><?php echo $_['categories']; ?></a>
                <ul class="dropdown">
                    <li><a href="#">Самооценка и уверенность в себе</a></li>
                    <li><a href="#">Эмоциональный интеллект и управление эмоциями</a></li>
                    <li><a href="#">Отношения и коммуникация</a></li>
                    <li><a href="#">Продуктивность и управление временем</a></li>
                    <li><a href="#">Самореализация и поиск смысла жизни</a></li>
                    <li><a href="#">Конфликты и их решение</a></li>
                    <li><a href="#">Финансовая грамотность и психология денег</a></li>
                    <li><a href="#">Психология успеха и мышление роста</a></li>
                    <li><a href="#">Осознанность и медитация</a></li>
                    <li><a href="#">Выход из кризисных ситуаций</a></li>
                    <li><a href="#">Воспитание детей и родительская психология</a></li>
                    <li><a href="#">Лайф-коучинг и личная эффективность</a></li>
                    <li><a href="#">Психосоматика и здоровье</a></li>
                    <li><a href="#">Гармония в жизни и счастье</a></li>
                    <li><a href="#">Психология творчества и креативность</a></li>
                </ul>
            </li>
            <li class="language"><a class="disabled-link"><?php echo $_['language']; ?></a>
                <ul class="dropdown">
                    <li><a href="?<?php echo $current_query; ?>lang=ru" data-lang="ru"><?php echo $_['russian']; ?></a></li>
                    <li><a href="?<?php echo $current_query; ?>lang=uk" data-lang="uk"><?php echo $_['ukrainian']; ?></a></li>
                </ul>
            </li>
            <li><a href="./favorites.php"><?php echo $_['favorites']; ?></a></li>
            <li><a href="./history.php"><?php echo $_['history']; ?></a></li>
            <li><a href="./profile.php"><?php echo $_['profile']; ?></a></li>
            <div class="mobile-stripes">
               <div class="stripe white"></div>
               <div class="stripe blue"></div>
            </div>


        </ul>
    </nav>
