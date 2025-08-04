<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bot/functions.php';

    $tg_id = $user['telegram_id'] ?? $chat_id;
    $lang = getUserLang($tg_id);
    $test_url = "https://www.mindguide.online/test.php?tg_id={$tg_id}&lang={$lang}";

if ($text === $_['phone_manual_button']) {
    sendMessage($chat_id, $_['ask_phone_manual']);
    return;
}

if ($contact) {
    $stmt = $pdo->prepare("UPDATE users SET phone = ? WHERE telegram_id = ?");
    $stmt->execute([$contact, $telegram_id]);

    sendMessage($chat_id, $_['phone_saved'], null, [
      [
        ['text' => $_['menu_open_site']],
        ['text' => $_['menu_channel']]
      ]
    ]);

    sendMessage($chat_id, $_['offer_test'], [
    [
        ['text' => $_['take_test'], 'web_app' => ['url' => $test_url]],
        ['text' => $_['skip_test'], 'callback_data' => 'skip_test']
    ]
]);
    return;
}

if (preg_match('/^\+\d{10,15}$/', $text)) {
    $stmt = $pdo->prepare("UPDATE users SET phone = ? WHERE telegram_id = ?");
    $stmt->execute([$text, $telegram_id]);

    sendMessage($chat_id, $_['phone_saved'], null, [
      [
        ['text' => $_['menu_open_site']],
        ['text' => $_['menu_channel']]
      ]
    ]);

    sendMessage($chat_id, $_['offer_test'], [
    [
        ['text' => $_['take_test'], 'web_app' => ['url' => $test_url]],
        ['text' => $_['skip_test'], 'callback_data' => 'skip_test']
    ]
]);
    return;
}

if ($text === $_['skip_button'] || $callback_data === 'skip_phone') {
    sendMessage($chat_id, $_['phone_skipped']);
    sendMessage($chat_id, $_['offer_test'], [
    [
        ['text' => $_['take_test'], 'web_app' => ['url' => $test_url]],
        ['text' => $_['skip_test'], 'callback_data' => 'skip_test']
    ]
]);

    if ($callback_data) {
        answerCallbackQuery($callback_query_id);
    }
    return;
}

// Если текст не соответствует формату телефона
if ($user && !empty($user['name']) && empty($user['phone']) &&
    $text && !preg_match('/^\+\d{10,15}$/', $text) &&
    $text !== $_['skip_button'] &&
    $text !== $_['phone_manual_button']) {

    sendMessage($chat_id, $_['phone_invalid']);
    return;
}
