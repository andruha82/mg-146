<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/system/config.php';
require_once __DIR__ . '/../functions.php';

// /start — регистрация
if (strpos($text, '/start') === 0) {
    $parts = explode(' ', $text);
    $ref = $parts[1] ?? null;

    saveTelegramUser($telegram_id, $username, $ref);

    sendMessage($chat_id, "👋 Вітаємо в MindGuide.online!\nОберіть мову спілкування:", [
        [
            ['text' => '🇺🇦 Українська', 'callback_data' => 'lang_uk'],
            ['text' => '🇷🇺 Русский',    'callback_data' => 'lang_ru']
        ]
    ]);
    exit;
}

// Выбор языка
if (strpos($callback_data, 'lang_') === 0) {
    $lang = substr($callback_data, 5);
    updateUserLang($telegram_id, $lang);
    $_ = loadLang($lang);

    sendMessage($chat_id, $_['greeting'], [
        [['text' => $_['skip_button'], 'callback_data' => 'skip_email']]
    ]);
    answerCallbackQuery($callback_query_id);
    exit;
}
