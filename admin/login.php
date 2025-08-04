<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных

$session_lifetime = 86400 * 30;
session_set_cookie_params($session_lifetime);
ini_set('session.gc_maxlifetime', $session_lifetime);

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mb_strtolower(trim($_POST['email'])); // Приводим email к нижнему регистру
    $password = $_POST['password'];

    // Проверяем, пустые ли поля
    if (empty($email) || empty($password)) {
        echo "❌ Ошибка: Все поля обязательны!";
        exit();
    }

    // Проверяем, существует ли админ с таким email
    $query = $pdo->prepare("SELECT email, password, role, status FROM admins WHERE email = ?");
    $query->execute([$email]);
    $admin = $query->fetch(PDO::FETCH_ASSOC);

    // Если админ не найден
    if (!$admin) {
        echo "❌ Ошибка: Неверный email или пароль!";
        exit();
    }

    // Проверяем статус (если `disabled`, не даем войти)
    if ($admin['status'] === 'disabled') {
        echo "❌ Ошибка: Аккаунт отключен администратором!";
        exit();
    }

    // Проверяем пароль
    if (!password_verify($password, $admin['password'])) {
        echo "❌ Ошибка: Неверный email или пароль!";
        exit();
    }

    // Авторизация успешна, создаем сессию
    $_SESSION['email'] = $admin['email'];
    $_SESSION['role'] = $admin['role'];

    // Обновляем дату последнего входа
    $updateQuery = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE email = ?");
    $updateQuery->execute([$admin['email']]);

    // Логируем вход (IP, User-Agent)
    $logQuery = $pdo->prepare("INSERT INTO login_logs (email, ip_address, user_agent) VALUES (?, ?, ?)");
    $logQuery->execute([$admin['email'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);

    // Перенаправляем в админку
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация админа || Админка</title>
    <meta name="description" content="" />
    <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/login.css">
    <script src="../script.js"></script>
    <link href="../images/icon.png" rel="icon" />
</head>
<body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/navbar-admin.php'; // Подключаем меню ?>
<div class="container">

<div class="login-section">

<h2>Авторизация админа</h2>

            <?php if (!empty($message)): ?>
            <div class="instruction">
            <?= $message ?>
            </div>
            <?php endif; ?>

            <?php if (empty($message)): ?>
<div class="form">
<form method="POST">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Пароль" required>
    <button type="submit">Войти</button>
</form>
</div>
            <?php endif; ?>
</div>
</div>


<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/bottom.php'; ?>

</body>
</html>
