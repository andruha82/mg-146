<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bot/functions.php';

// Обновляем язык пользователя
$lang = substr($callback_data, 5); // получаем uk или ru
updateUserLang($telegram_id, $lang);
$_ = loadLang($lang);

// Отправляем приветственное сообщение
sendMessage($chat_id, $_['welcome_intro'], [
    [['text' => $_['continue_button'], 'callback_data' => 'continue_intro']]
]);


answerCallbackQuery($callback_query_id);

// Удаляем сообщение с кнопками выбора языка
$language_message_id = $data['callback_query']['message']['message_id'];
deleteMessage($chat_id, $language_message_id);
