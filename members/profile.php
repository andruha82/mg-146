<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/checklang.php'; // Подключаем проверку языка
require_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных
require_once $_SERVER['DOCUMENT_ROOT'] . '/members/check_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/members/check_subscr.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$subscr_stmt = $pdo->prepare("SELECT us.*, sp.description, sp.max_views, sp.max_questions, sp.duration FROM user_subscriptions us LEFT JOIN subscription_packages sp ON us.package_id = sp.id WHERE us.user_id=? AND us.status='active' LIMIT 1");
$subscr_stmt->execute([$user_id]);
$subscription = $subscr_stmt->fetch(PDO::FETCH_ASSOC);

// Получаем последний купленный пакет с вопросами
$addon_stmt = $pdo->prepare("
    SELECT uqs.*, qp.name AS package_name, qp.questions, qp.price, qp.currency
    FROM user_question_subscriptions uqs
    JOIN question_packages qp ON uqs.package_id = qp.id
    WHERE uqs.user_id = ?
    ORDER BY uqs.purchase_date DESC
    LIMIT 1
");
$addon_stmt->execute([$user_id]);
$addon = $addon_stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="<?php echo $selected_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Zone | MindGuide</title>
    <link href="https://fonts.googleapis.com/css2?family=Pattaya&family=Mulish&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="css/profile.css">

    <script src="../script.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="../images/icon.png" rel="icon" />
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/hreflang.php'; ?>
</head>
<body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/navbar-member.php'; // Подключаем меню ?>

<div class="container">

<div class="section">

<!-- Блок О пользователе -->

  <div class="main-card" style="margin-top: 0;">
    <div>
      <h4 id="profile-name"><?= htmlspecialchars($user['name']) ?></h4>
      <p>email: <?= htmlspecialchars($user['email']) ?></p>
      <a href="/logout.php" class="btn danger">Выйти из аккаунта</a>
      <p><a href="./invite.php" style="font-size: 14px; transform: scaleY(1.1); display: inline-block;">Запрошуй друзів - отримай</a> 🎁🎁🎁!</p>
    </div>
  </div>

<!-- Блок Информация -->

  <div class="info-card">
    <h5 class="d-flex justify-content-between">Информация
      <div>
        <button class="btn d-none danger" id="cancel-edit-btn" title="Отменить редактирование">✕</button>
        <button class="btn secondary" id="toggle-edit-btn">Редактировать</button>
      </div>
    </h6>
    <div class="row" id="personal-info">
     <div class="info-row">
      <div class="info-item" data-field="name"><span class="label-text">Имя:</span> <span class="value-text"><?= htmlspecialchars($user['name']) ?></span></div>
      <div class="info-item" data-field="phone"><span class="label-text">Телефон:</span> <span class="value-text"><?= htmlspecialchars($user['phone']) ?></span></div>
     </div>
     <div class="info-row">
      <div class="info-item" data-field="birth_date"><span class="label-text">Дата рождения:</span> <span class="value-text"><?= htmlspecialchars($user['birth_date']) ?></span></div>
      <div class="info-item" data-field="gender"><span class="label-text"><?= $_['gender']; ?></span> <span class="value-text"><?php
        if ($user['gender'] === 'male') echo $_['male'];
        elseif ($user['gender'] === 'female') echo $_['female'];
        elseif ($user['gender'] === 'other') echo $_['other'];
      ?></span></div>
     </div>
     <div class="info-row">
      <div class="info-item" data-field="country"><span class="label-text">Страна:</span> <span class="value-text"><?= htmlspecialchars($user['country']) ?></span></div>
      <div class="info-item" data-field="tg_username"><span class="label-text">Telegram:</span> <span class="value-text"><?= htmlspecialchars($user['tg_username']) ?></span></div>
     </div>
     <div class="info-row">
      <div class="info-item" style="margin-bottom: 0;" data-field="marketing_consent"><span class="label-text">Получать новости:</span> <span class="value-text"><?= $user['marketing_consent'] ? 'Да' : 'Нет' ?></span></div>
     </div>

    </div>
  </div>

<!-- Блок О подписке -->

  <div class="subscr-card">
    <h5 class="d-flex justify-content-between">О подписке
      <?php if ($subscription): ?>
        <button class="btn danger">Отменить подписку</button>
      <?php else: ?>
        <a href="/subscribe.php" class="btn danger">Оформить подписку</a>
      <?php endif; ?>
    </h6>
    <?php if($subscription): ?>
<div class="row">

    <div class="package"><h6><?= htmlspecialchars($subscription['description']) ?></h6></div>

  <div class="info-row">
    <div class="info-item"><span class="label-text">Начало подписки:</span> <span class="value-text"><?= htmlspecialchars($subscription['start_date']) ?></span></div>
    <div class="info-item"><span class="label-text">Окончание подписки:</span> <span class="value-text"><?= htmlspecialchars($subscription['end_date']) ?></span></div>
  </div>
  <div class="info-row">
    <div class="info-item"><span class="label-text">Стоимость:</span> <span class="value-text"><?= htmlspecialchars($subscription['price'].' '.$subscription['currency']) ?></span></div>
    <div class="info-item"><span class="label-text">Автопродление:</span> <span class="value-text"><?= $subscription['autorenew'] ? 'Включено' : 'Отключено' ?></span></div>
  </div>
  <div class="info-row">
    <div class="info-item"><span class="label-text">Просмотры (исп):</span> <span class="value-text"><?= htmlspecialchars($subscription['views_used']) ?></span></div>
    <div class="info-item"><span class="label-text">Лимит просмотров:</span> <span class="value-text"><?= ($subscription['max_views'] == 9999) ? 'Не ограничено' : htmlspecialchars($subscription['max_views']) ?></span></div>
  </div>
  <div class="info-row">
    <div class="info-item"><span class="label-text">Вопросы (исп):</span> <span class="value-text"><?= htmlspecialchars($subscription['questions_used']) ?></span></div>
    <div class="info-item"><span class="label-text">Лимит вопросов:</span> <span class="value-text"><?= ($subscription['max_questions'] == 9999) ? 'Не ограничено' : htmlspecialchars($subscription['max_questions']) ?></span></div>
  </div>

<?php if (!empty($subscription['duration']) && $subscription['duration'] !== '1 year'): ?>
  <div class="info-row" style="margin-top: 20px;">
    <div class="info-item" style="width: 100%; justify-content: center; text-align: center;">
      <p>Хочешь сэкономить и забыть о продлениях? Оформляй:</p>
    </div>
    <div class="info-item" style="width: 100%; justify-content: center;">
      <a href="/upgrade.php" class="btn c-43b02a">Годовой план со скидкой 20%</a>
    </div>
  </div>
<?php endif; ?>

</div>

    <?php else: ?>
      <p>Активной подписки нет.</p>
    <?php endif; ?>
  </div>

<!-- Блок О пакетах вопросов -->

<div class="subscr-card">
  <h5 class="d-flex justify-content-between align-items-center">
    Пакеты с вопросами
  </h5>

  <?php if ($addon): ?>
    <div class="package">
      <h6><?= htmlspecialchars($addon['package_name']) ?></h6>
    </div>

    <div class="info-row">
      <div class="info-item">
        <span class="label-text">Дата покупки:</span>
        <span class="value-text"><?= date('d.m.Y', strtotime($addon['purchase_date'])) ?></span>
      </div>
      <div class="info-item">
        <span class="label-text">Количество вопросов:</span>
        <span class="value-text"><?= $addon['questions'] ?></span>
      </div>
      <div class="info-item">
        <span class="label-text">Стоимость:</span>
        <span class="value-text"><?= $addon['price'] ?> <?= $addon['currency'] ?></span>
      </div>
    </div>
  <div class="info-row" style="margin-top: 20px;">
    <div class="info-item" style="width: 100%; justify-content: center;">
      <a href="/upgrade.php" class="btn c-43b02a">Купить еще пакет вопросов</a>
    </div>
  </div>
  <?php else: ?>
    <p>Вы ещё не покупали дополнительные пакеты с вопросами.</p>
  <div class="info-row" style="margin-top: 20px;">
    <div class="info-item" style="width: 100%; justify-content: center;">
      <a href="/upgrade.php" class="btn c-43b02a">Купить пакет с вопросами</a>
    </div>
  </div>
  <?php endif; ?>
</div>

</div>

</div>

    <script src="js/profile.js"></script>
</body>
</html>