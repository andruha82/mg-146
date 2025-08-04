<?php
session_start();
setcookie("remember_token", "", time() - 3600, "/", "", false, true); // Удаляем куки
session_destroy();
header("Location: login.php");
exit();
?>