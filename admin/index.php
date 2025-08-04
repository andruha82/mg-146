<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/auth.php'; // Проверка авторизации
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админка</title>
    <meta name="description" content="" />
    <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/admin.css">
    <script src="../script.js"></script>
    <link href="../images/icon.png" rel="icon" />
</head>
<body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/navbar-admin.php'; // Подключаем меню ?>
<div class="container">

<?php 
echo "Добро пожаловать в админку, " . ($_SESSION['role'] === 'superadmin' ? "Главный админ" : "Обычный админ") . " (" . $_SESSION['email'] . ")!"; 
echo "<h2>Данные в \$_SESSION:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Данные в \$_COOKIE:</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

echo "gc_maxlifetime в другом файле: " . ini_get('session.gc_maxlifetime');
?>

</div>



<?php 
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/bottom.php'; 
?>

</body>
</html>
