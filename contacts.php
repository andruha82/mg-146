<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/checklang.php'; // Подключаем проверку языка
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    // Валидация данных
    if (empty($name) || empty($email) || empty($message)) {
        $message = $_['contacts_allfields'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = $_['contacts_emailnotcorrect'];
    }


                // Отправка письма
                $fromName = 'MindGuide Form';
                $fromEmail = 'form@mindguide.online';
                $to = 'mindguide7@gmail.com';
                $headers = "From: $fromName <$fromEmail>\r\n";
                $headers .= "Reply-To: $fromEmail\r\n";
                $headers .= "Return-Path: $fromEmail\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                $subject = 'MindGuide Form';
                $messageBody = "Name: $name\nEmail: $email\n\nMessage:\n$message\n";

                ini_set("SMTP", "mindguide.online");
                ini_set("smtp_port", "587"); 
                ini_set("sendmail_from", $fromEmail);


    // Отправка письма
    if (mail($to, $subject, $messageBody, $headers)) {
        $message = $_['contacts_success'];
    } else {
        $message = $_['contacts_error'];
    }
}

?>
<!DOCTYPE html>
<html lang="<?php echo $selected_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_['contacts_title']; ?></title>
    <meta name="description" content="<?= $_['contacts_description']; ?>" />
    <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./styles/styles.css">
    <link rel="stylesheet" href="./styles/navbar.css">
    <link rel="stylesheet" href="./styles/contacts.css">
    <script src="../script.js"></script>
    <link href="images/icon.png" rel="icon" />
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/hreflang.php'; ?>
</head>
<body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/navbar-main.php'; // Подключаем меню ?>
<div class="container">

<div class="contact-section">
  <h2><?= $_['contacts_h2'] ?></h2>

            <?php if (!empty($message)): ?>
            <div class="instruction">
            <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <?php if (empty($message)): ?>
  <p><?= $_['contacts_text'] ?></p>
  
  <div class="contact-container">
    <!-- Поддержка в Telegram -->
    <div class="contact-item">
      <a href="https://t.me/MindGuideOnlineBot?start=support">
        <img src="images/telegram.svg" alt="Telegram" width="60" height="60">
      </a>
      <a href="https://t.me/MindGuideOnlineBot?start=support"><?= $_['contacts_support'] ?> в<br><strong>Telegram</strong></a>
    </div>
    
    <!-- Поддержка по e-mail -->
    <div class="contact-item">
      <a href="mailto:support@mindguide.online">
        <img src="images/email.svg" width="60" height="60" alt="E-mail">
      </a>
      <a href="mailto:support@mindguide.online"><?= $_['contacts_support'] ?> по <br><strong>e-mail</strong></a>
    </div>
  </div>

  <!-- Форма обратной связи -->
  <div class="contact-form">
    <h3><?= $_['contacts_form'] ?></h3>
    <form method="post">
      <label for="name"><?= $_['contacts_name'] ?></label>
      <input type="text" id="name" name="name" required>

      <label for="email"><?= $_['contacts_email'] ?></label>
      <input type="email" id="email" name="email" required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$">

      <label for="message"><?= $_['contacts_message'] ?></label>
      <textarea id="message" name="message" rows="4" required></textarea>

      <button type="submit"><?= $_['contacts_button_text'] ?></button>
    </form>
  </div>
            <?php endif; ?>
</div>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/bottom.php'; ?>

</body>
</html>
