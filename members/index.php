<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/checklang.php'; // Подключаем проверку языка
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных
include_once $_SERVER['DOCUMENT_ROOT'] . '/members/check_auth.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/members/check_subscr.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $selected_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Zone | MindGuide</title>
    <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/member-index.css">
    <script src="../script.js"></script>
    <link href="images/icon.png" rel="icon" />
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/hreflang.php'; ?>
</head>
<body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/system/tmpl/navbar-member.php'; // Подключаем меню ?>

<div class="container">

<?php 
echo "Добро пожаловать в members!"; 
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


</body>
</html>