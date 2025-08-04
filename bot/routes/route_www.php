<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bot/functions.php';

sendMessage($chat_id, $_['link_www_text'], [
    [[
        'text' => $_['link_www_button'],
        'url' => 'https://www.mindguide.online/'
    ]]
]);
