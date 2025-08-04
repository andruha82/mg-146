<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/checklang.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/functions.php';


$message = '';

// Показываем флеш-сообщение от resend_email_token.php
if (!empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// 1) Проверяем, что в сессии есть ID пользователя, которого нужно подтвердить
if (empty($_SESSION['user_id'])) {
    header("Location: /signup.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 2) Загружаем из БД токен и время истечения для этого пользователя, статус = pending
$stmt = $pdo->prepare("
    SELECT registration_token, registration_token_expiration
      FROM users
     WHERE id = ? AND status = 'pending'
");
$stmt->execute([$user_id]);
$pending = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pending) {
    $message = $_['confirm_error'];
}

// 3) Обработка POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pending) {
    $entered = trim($_POST['token'] ?? '');

    $now     = new DateTime();
    $expires = new DateTime($pending['registration_token_expiration']);

    if ($entered !== $pending['registration_token']) {
        $message = $_['confirm_invalid'];
    }
    elseif ($now > $expires) {
        $message = $_['confirm_expired'];
    }
    else {
        // Всё ок — активируем
        $stmt = $pdo->prepare("
            UPDATE users
               SET status                        = 'active',
                   registration_token            = NULL,
                   registration_token_expiration = NULL,
                   registration_date             = NOW()
             WHERE id = ?
        ");
        $stmt->execute([$user_id]);
        header("Location: /members/index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $selected_lang ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?= $_['confirm_title'] ?></title>
  <link rel="stylesheet" href="../styles/styles.css">
  <link rel="stylesheet" href="../styles/navbar.css">
  <link rel="stylesheet" href="../styles/confirm-email.css">
  <script src="../script.js"></script>
  <link href="images/icon.png" rel="icon" />
</head>
<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/navbar-main.php'; ?>
  <div class="container">
    <div class="section">
    <h2><?= $_['confirm_h2'] ?></h2>

    <?php if (!empty($message)): ?>
      <div class="instruction"><?= $message ?></div>
    <?php endif; ?>

    <?php if ($pending): ?>
      <form method="post" class="form">
        <div>
          <input type="text" id="token" name="token" pattern="\d{6}" placeholder="<?= $_['confirm_label'] ?>" required>
        </div>
        <button type="submit"><?= $_['confirm_button'] ?></button>
      </form>
    <?php endif; ?>
    </div>
  </div>
</body>
</html>
