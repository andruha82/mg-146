<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bot/functions.php';

// Показываем следующий шаг
sendMessage($chat_id, $_['welcome_email'], [
    [['text' => $_['skip_button'], 'callback_data' => 'skip_email']]
]);
