    <nav class="navbar">
        <div class="logo-container"><img src="../images/logo.png" width=22 height=22><div class="logo">MindGuide</div></div>
        <div class="menu-icon" id="menu-icon">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>
        <ul class="nav-links" id="nav-links">
            <li><a href="./index.php">Главная</a></li>
    <?php if (isset($_SESSION['role'])): ?>
            <li class=cases><a class="disabled-link">Управление контентом</a>
                <ul class="dropdown">
                    <li><a href="./add_category.php">⏩ Добавить категорию</a></li>
                    <li><a href="./edit_categories.php">✍️ Редактировать категории</a></li>
                    <li><a href="./add_article.php">⏩ Добавить статью</a></li>
                    <li><a href="./edit_articles.php">✍️ Редактировать статьи</a></li>
                    <li><a href="./add_case.php">⏩ Добавить кейс</a></li>
                    <li><a href="./edit_cases.php">✍️ Редактировать кейсы</a></li>
                </ul>
            </li>
            <li><a href="./users.php">Пользователи</a></li>
            <li class=cases><a class="disabled-link">Подписки</a>
                <ul class="dropdown">
                    <li><a href="./subscription_packages.php">Тарифные планы</a></li>
                    <li><a href="./edit_subscription.php">Активные подписки</a></li>
                    <li><a href="">Платежи</a></li>
                </ul>
            </li>
    <?php if ($_SESSION['role'] === 'superadmin'): ?>
                     <li><a href="./dsa.php">Регистрация админов</a></li>
    <?php endif; ?>
            <li><a href="./logout.php">Выход</a></li>
    <?php endif; ?>
        </ul>
    </nav>
