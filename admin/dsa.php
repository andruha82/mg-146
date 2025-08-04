<?php
// signup.php - регистрация админов

include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/auth.php'; // Проверка авторизации

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Проверяем, существует ли уже такой email
    $checkQuery = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE email = ?");
    $checkQuery->execute([$email]);
    if ($checkQuery->fetchColumn() > 0) {
        $message = "Ошибка: Администратор с таким email уже существует!";
    } else {
    // Хешируем пароль
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Добавляем нового администратора (по умолчанию role = admin, status = enabled)
    $query = $pdo->prepare("INSERT INTO admins (email, password, role, status) VALUES (?, ?, 'admin', 'enabled')");
    if ($query->execute([$email, $hashedPassword])) {
        $message = "Администратор успешно зарегистрирован!";
    } else {
        $message = "Ошибка при регистрации!";
    }
}

}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация админов || Админка</title>
    <meta name="description" content="" />
    <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/signup.css">
    <script src="../script.js"></script>
    <link href="../images/icon.png" rel="icon" />
</head>
<body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/navbar-admin.php'; // Подключаем меню ?>
<div class="container">

<div class="signup-section">

<h2>Регистрация админа</h2>

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
    <button type="submit">Зарегистрировать</button>
</form>
</div>
            <?php endif; ?>
</div>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/bottom.php'; ?>

</body>
</html>
