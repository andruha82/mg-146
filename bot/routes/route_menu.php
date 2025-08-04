<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bot/functions.php';

file_put_contents(__DIR__ . '/../debug_menu.log', print_r($_, true));

sendMessage($chat_id, $_['menu_greeting'], null, [
    [['text' => $_['menu_open_site']]],
    [['text' => $_['menu_take_test']]],
    [['text' => $_['menu_ai_consultant']]],
    [['text' => $_['menu_support']]]
]);
