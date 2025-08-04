<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bot/functions.php';

// Загрузить текущий язык
$_ = loadLang(getUserLang($telegram_id));

// Логируем непонятные сообщения
file_put_contents(
    $_SERVER['DOCUMENT_ROOT'] . '/bot/unrecognized.log',
    "[" . date('Y-m-d H:i:s') . "] [$telegram_id] $text\n",
    FILE_APPEND
);

// Отправляем сообщение с пояснением
sendMessage($chat_id, $_['unrecognized_input']);