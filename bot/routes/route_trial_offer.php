<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bot/functions.php';

// Отправка текста с предложением оформить пробную подписку и кнопкой
sendMessage($chat_id, $_['offer_trial'], [
    [[
        'text' => $_['start_trial'],
        'callback_data' => 'start_trial'
    ]]
], null); 