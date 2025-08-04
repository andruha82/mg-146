<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bot/functions.php';

sendMessage($chat_id, $_['link_telegram_text'], [
    [[
        'text' => $_['link_telegram_button'],
        'url' => 'https://t.me/MindGuideOnline'
    ]]
]);
