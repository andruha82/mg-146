<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/checklang.php'; // Подключаем проверку языка
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных

if (!isset($_GET['token'])) {
    $message = $_['reset_password_notoken'];
}

$token = $_GET['token'];

// Проверяем, существует ли токен и не истек ли его срок
$stmt = $pdo->prepare("SELECT email, reset_token_expiration FROM users WHERE reset_token = ? LIMIT 1");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $message = $_['reset_password_errortoken'];
}

if (strtotime($user['reset_token_expiration']) < time()) {
    $message = $_['reset_password_expiredtoken'];
}

$email = $user['email'];

// Если отправлена форма с новым паролем
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["password"]) || empty($_POST["password_confirm"])) {
        $message = $_['reset_password_form_error1'];
    } elseif ($_POST["password"] !== $_POST["password_confirm"]) {
        $message = $_['reset_password_form_error2'];
    } else {
        $new_password = password_hash($_POST["password"], PASSWORD_BCRYPT);

        // Обновляем пароль пользователя и удаляем использованный токен
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expiration = NULL WHERE email = ?");
        $stmt->execute([$new_password, $email]);

        $message = $_['reset_password_success'];
    }
}

?>
<!DOCTYPE html>
<html lang="<?php echo $selected_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_['reset_password_title']; ?></title>
    <meta name="description" content="<?= $_['reset_password_description']; ?>" />
    <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/reset-password.css">
    <script src="../script.js"></script>
    <link href="images/icon.png" rel="icon" />
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/hreflang.php'; ?>
</head>
<body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/navbar-main.php'; // Подключаем меню ?>
<div class="container">

<div class="reset-section">

        <h2><?= $_['reset_password_h2']; ?></h2>

            <?php if (!empty($message)): ?>
            <div class="instruction">
            <?= $message ?>
            </div>
            <?php endif; ?>

            <?php if (empty($message)): ?>
<div class="instruction">
<?= $_['reset_password_text']; ?>
</div>
<div class="form">
<form method="post">
<div>
<input type="password" name="password" placeholder="Password" required>
</div>
<div>
<input type="password" name="password_confirm" placeholder="Password (confirm)" required>
</div>
<button type="submit"><?= $_['reset_password_button_text']; ?></button>
</form>
</div>
             <?php endif; ?>
</div>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/bottom.php'; ?>
  
</body>
</html>
