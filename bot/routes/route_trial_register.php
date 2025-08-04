<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bot/functions.php';

// Формируем ссылку с telegram_id
$tg_id = $telegram_id;
$lang = getUserLang($tg_id);
$signup_url = "https://www.mindguide.online/signup.php?tg_id={$tg_id}&lang={$lang}";

// Отправляем сообщение с inline-кнопкой (ссылка откроется как Web App)
sendMessage($chat_id, $_['trial_register_intro'], [
    [[
        'text' => $_['trial_register_button'],
        'web_app' => ['url' => $signup_url]
    ]]
]);
