<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bot/functions.php';

sendMessage($chat_id, $_['link_instagram_text'], [
    [[
        'text' => $_['link_instagram_button'],
        'url' => 'https://www.instagram.com/mindguide.online/'
    ]]
]);
