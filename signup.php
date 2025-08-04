<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/checklang.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/functions.php';

// PDO в режим выброса исключений
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// === начальные данные ===

$telegram_id = (isset($_GET['tg_id']) && ctype_digit($_GET['tg_id']))
    ? (int)$_GET['tg_id']
    : null;

$user = null;
$message = '';

if ($telegram_id !== null) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE telegram_id = ?");
    $stmt->execute([$telegram_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && !empty($user['email'])) {
        $message = $_['error_already_account'];
    }
}

// === обработка POST ===

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $message === '') {
    $email            = trim($_POST['email']);
    $password         = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $name             = $_POST['name']    ?? null;
    $phone            = $_POST['phone']   ?? null;
    $gender           = $_POST['gender']  ?? null;
    $lang             = $selected_lang    ?? 'ru';
    $ip               = $_SERVER['REMOTE_ADDR'];

    // валидация
    $rules = [
        [strlen($password) < 6,                $_['error_password_length']],
        [$password !== $password_confirm,      $_['error_password_mismatch']],
        [!empty($phone) && strlen($phone) < 8, $_['error_phone_length']],
    ];
    foreach ($rules as list($fail, $errMsg)) {
        if ($fail) { $message = $errMsg; break; }
    }

    if ($message === '') {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing && $existing['id'] != ($user['id'] ?? 0)) {
            $message = $_['error_email_registered'];
        }
    }

    if ($message === '') {
        $password_hash      = password_hash($password, PASSWORD_DEFAULT);
        $device_type        = getDeviceType($_SERVER['HTTP_USER_AGENT'] ?? '');
        $country            = getCountryByIp($ip);
        $marketing_consent  = 1;
        $token              = random_int(100000, 999999);
        $expires            = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');

        // для новых пользователей без telegram_id генерируем ai_user_id
        $ai_user_id = null;
        if (!$user && $telegram_id === null) {
            $ai_user_id = generateAiUserId();
        }

        $t = $_COOKIE['token'] ?? null;
        if ($t) {
            $stmt = $pdo->prepare("
                SELECT partner_id, utm_source, utm_medium, utm_campaign
                  FROM traffic_tokens
                 WHERE token = ?
                 LIMIT 1
            ");
            $stmt->execute([$t]);
            $tok = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($tok) {
                // берём параметры из traffic_tokens
                $partner_id   = $tok['partner_id'];
                $utm_source   = $tok['utm_source'];
                $utm_medium   = $tok['utm_medium'];
                $utm_campaign = $tok['utm_campaign'];
            } else {
                // невалидный токен — сбрасываем
                $partner_id   = null;
                $click_id     = null;
                $utm_source   = null;
                $utm_medium   = null;
                $utm_campaign = null;
            }
        } else {
            // fallback на старые cookie
            $partner_id   = $_COOKIE['partner_id']   ?? null;
            $click_id     = $_COOKIE['click_id']     ?? null;
            $utm_source   = $_COOKIE['utm_source']   ?? null;
            $utm_medium   = $_COOKIE['utm_medium']   ?? null;
            $utm_campaign = $_COOKIE['utm_campaign'] ?? null;
        }


        if ($user) {
            // обновление в pending
            $sql = "
                UPDATE users SET
                  email                         = ?,
                  password_hash                 = ?,
                  name                          = COALESCE(?, name),
                  phone                         = COALESCE(?, phone),
                  gender                        = COALESCE(?, gender),
                  registration_lang             = ?,
                  registration_ip               = ?,
                  device_type                   = ?,
                  country                       = ?,
                  marketing_consent             = ?,
                  registration_token            = ?,
                  registration_token_expiration = ?,
                  status                        = 'pending'
                WHERE telegram_id = ?
            ";
            $params = [
                $email, $password_hash, $name, $phone, $gender,
                $lang, $ip, $device_type, $country, $marketing_consent,
                $token, $expires,
                $telegram_id,
            ];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $user_id = $user['id'];
        } else {
            // вставка нового в pending, включая ai_user_id

$sql = "
    INSERT INTO users (
      email, password_hash, name, phone, gender,
      telegram_id, ai_user_id, registration_lang, registration_ip,
      device_type, country, marketing_consent,
      registration_token, registration_token_expiration,
      partner_id, click_id, utm_source, utm_medium, utm_campaign,
      status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
";
$params = [
    $email, $password_hash, $name, $phone, $gender,
    $telegram_id, $ai_user_id, $lang, $ip,
    $device_type, $country, $marketing_consent,
    $token, $expires,
    $partner_id, $click_id, $utm_source, $utm_medium, $utm_campaign,
];

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $user_id = $pdo->lastInsertId();
        }

        $_SESSION['user_id'] = $user_id;
        sendConfirmationEmail($email, $token, $_, $selected_lang);
        header("Location: /confirm_email.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $selected_lang ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?= $_['signup_title'] ?></title>
  <meta name="description" content="<?= $_['signup_description'] ?>" />
  <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../styles/styles.css">
  <link rel="stylesheet" href="../styles/navbar.css">
  <link rel="stylesheet" href="../styles/signup.css">
  <script src="../script.js"></script>
  <link href="images/icon.png" rel="icon" />
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/hreflang.php'; ?>
</head>
<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/navbar-main.php'; ?>
  <div class="container">
    <div class="section">
      <h2><?= $_['signup_h2'] ?></h2>
      <?php if (!empty($message)): ?>
        <div class="instruction"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>
      <?php if (empty($message)): ?>
        <div class="form">
          <form method="post"<?php if ($telegram_id !== null) echo ' action="?tg_id=' . $telegram_id . '"'; ?>>
            <div><input type="email" name="email" placeholder="<?= $_['email_placeholder'] ?>" required></div>
            <div><input type="password" name="password" placeholder="<?= $_['password_placeholder'] ?>" required></div>
            <div><input type="password" name="password_confirm" placeholder="<?= $_['password_confirm_placeholder'] ?>" required></div>
            <?php if (!$user || empty($user['name'])): ?>
              <div><input type="text" name="name" placeholder="<?= $_['name_placeholder'] ?>"></div>
            <?php endif; ?>
            <?php if (!$user || empty($user['phone'])): ?>
              <div><input type="text" name="phone" placeholder="<?= $_['phone_placeholder'] ?>"></div>
            <?php endif; ?>
            <?php if (!$user || empty($user['gender'])): ?>
              <div>
                <select name="gender" id="gender">
                  <option value="" disabled selected>-- <?= $_['ch_gender'] ?> --</option>
                  <option value="male"><?= $_['male'] ?></option>
                  <option value="female"><?= $_['female'] ?></option>
                </select>
              </div>
            <?php endif; ?>
            <div class="checkbox-container">
              <input type="checkbox" id="agree" name="agree" required checked>
              <label for="agree">
                <?= $_['agree_text'] ?>
              </label>
            </div>
            <button type="submit"><?= $_['signup_button_text'] ?></button>
            <div class="bottomtext"><?= $_['signup_bottom_link'] ?></div>
          </form>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/bottom.php'; ?>
</body>
</html>
