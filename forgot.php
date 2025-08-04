<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/checklang.php'; // Подключаем проверку языка
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных

$message = ''; // Переменная для отображения сообщений

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    $email = trim($_POST['email']);

    // Проверяем, существует ли пользователь
    $stmt = $pdo->prepare("SELECT id, email, subscription_status, reset_token, reset_token_expiration 
                           FROM users 
                           WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        if ($user['status'] === 'banned' || $user['status'] === 'blocked') {
            $message = $_['forgot_message_blocked_banned'];
        } else {
            $now = new DateTime();
            $resetToken = $user['reset_token'];
            $resetTokenExpiration = $user['reset_token_expiration'];

            // Проверяем, есть ли действующий токен
            if (!empty($resetToken) && !empty($resetTokenExpiration)) {
                $expiration = new DateTime($resetTokenExpiration);

                if ($now < $expiration) {
                    // Токен ещё действителен — просто выводим сообщение о блоке повторного запроса
                    $interval = $now->diff($expiration);
                    $minutesLeft = ($interval->h * 60) + $interval->i;

                    $message = str_replace('$minutesLeft', $minutesLeft, $_['forgot_message_asked']);
                } else {
                    // Токен истёк — очищаем его
                    $stmt = $pdo->prepare("UPDATE users SET reset_token = NULL, reset_token_expiration = NULL WHERE email = ?");
                    $stmt->execute([$email]);

                    // Создаем новый токен ниже
                    $createNewToken = true;
                }
            } else {
                // Если токена не было — создаем новый
                $createNewToken = true;
            }

            if (isset($createNewToken) && $createNewToken) {
                // Генерация нового токена
                $token = bin2hex(random_bytes(32));
                $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $updateStmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiration = ? WHERE email = ?");
                $updateStmt->execute([$token, $expiration, $email]);

                // Ссылка для восстановления
                // Определяем текущий домен (учитывает и HTTPS, и HTTP)
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
                $domain = $protocol . $_SERVER['HTTP_HOST'];
                $resetLink = "$domain/reset_password.php?token=$token";

                // Отправка письма
                $fromName = 'MindGuide Support';
                $fromEmail = 'support@mindguide.online';
                $headers = "From: $fromName <$fromEmail>\r\n";
                $headers .= "Reply-To: support@mindguide.online\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                $subject = $_['forgot_email_subject'];
                $messageBody = str_replace(['\n', '$resetLink'], ["\n", $resetLink], $_['forgot_email_messageBody']);

                ini_set("SMTP", "mindguide.online");
                ini_set("smtp_port", "587"); 
                ini_set("sendmail_from", $fromEmail);

                mail($email, $subject, $messageBody, $headers);

                $message = $_['forgot_message_success'];
            }
        }
    } else {
        $message = $_['forgot_message_notfound'];
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $selected_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_['forgot_title']; ?></title>
    <meta name="description" content="<?= $_['forgot_description']; ?>" />
    <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/forgot.css">
    <script src="../script.js"></script>
    <link href="images/icon.png" rel="icon" />
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/hreflang.php'; ?>
</head>
<body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/navbar-main.php'; ?>

<div class="container">

<div class="forgot-section">

        <h2><?= $_['forgot_h2']; ?></h2>

            <?php if (!empty($message)): ?>
            <div class="instruction">
            <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <?php if (empty($message)): ?>
                <!-- Форма показывается только если нет блокировки на повторный запрос -->
            <div class="instruction">
            <?= $_['forgot_instruction']; ?>
            </div>
    <div class="form">
        <form method="post">
                <div>
                    <input type="email" name="email" placeholder="Email" required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$">
                </div>
                <button type="submit"><?= $_['forgot_button_text']; ?></button>

                <div class="bottomtext">
                    <?= $_['forgot_bottom_link']; ?>
                </div>
        </form>
            <?php endif; ?>
    </div>

</div>
</div>

    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/bottom.php'; ?>

</body>
</html>
