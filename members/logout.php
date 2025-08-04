<?php
session_start();
setcookie("session_token", "", time() - 3600, "/", "", false, true); // Удаляем куки
session_destroy();
header("Location: ../login.php");
exit();
?>