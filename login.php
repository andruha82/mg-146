<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/checklang.php'; // Подключаем проверку языка
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных

if (isset($_SESSION['user_id'])) {
    // Если сессия активна, сразу переходим в личный кабинет
    header('Location: ./members/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $message = $_['login_notfound'];
    } elseif ($user['status'] === 'banned' || $user['status'] === 'blocked') {
        $message = $_['login_banned'];
    } elseif (!password_verify($password, $user['password_hash'])) {
        $stmt = $pdo->prepare("UPDATE users SET failed_login_attempts = failed_login_attempts + 1, last_failed_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        $message = $_['login_wrongpassword'];
    } else {
        $_SESSION['user_id'] = $user['id'];

        $stmt = $pdo->prepare("UPDATE users SET failed_login_attempts = 0, last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);

        // Обработка "Запомнить меня"
        if ($remember_me) {
            $session_token = bin2hex(random_bytes(32));
            $expires = time() + 30 * 24 * 60 * 60; // 30 дней

            $stmt = $pdo->prepare("INSERT INTO user_sessions (user_id, session_token, ip_address, device_type, expires_at) VALUES (?, ?, ?, ?, FROM_UNIXTIME(?))");
            $stmt->execute([
                $user['id'],
                $session_token,
                $_SERVER['REMOTE_ADDR'],
                'desktop', // Можно будет сделать определение устройства
                $expires
            ]);

            setcookie('session_token', $session_token, $expires, '/', '', false, true);
        } else {
            ini_set('session.gc_maxlifetime', 86400); // Сессия 24 часа
            session_set_cookie_params(86400);
        }

        header('Location: ./members/index.php');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="<?php echo $selected_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_['login_title']; ?></title>
    <meta name="description" content="<?= $_['login_description']; ?>" />
    <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/login.css">
    <script src="../script.js"></script>
    <link href="images/icon.png" rel="icon" />
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/hreflang.php'; ?>
</head>
<body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/navbar-main.php'; // Подключаем меню ?>
<div class="container">

<div class="login-section">

<h2><?= $_['login_h2']; ?></h2>

            <?php if (!empty($message)): ?>
            <div class="instruction">
            <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <?php if (empty($message)): ?>
<div class="form">
<form method="post">
<div>
<input type="email" name="email" placeholder="Email" required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$">
</div>
<div>
<input type="password" name="password" placeholder="Password" required>
</div>
<div class="additional-options">
<div class="remember-me">
<input type="checkbox" id="remember" name="remember_me">
<label for="remember"><?= $_['login_remember_me']; ?></label>
</div>
<div class="forgot-password">
<a href="../forgot.php"><?= $_['login_forgot']; ?></a>
</div>
</div>
<button type="submit"><?= $_['login_button_text']; ?></button>
 
<div class="bottomtext">
<?= $_['login_bottom_link']; ?>
</div>
</form>
</div>
            <?php endif; ?>
</div>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/bottom.php'; ?>

</body>
</html>
