<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bot/functions.php';

// Выбор пола
if ($callback_data === 'gender_female' || $callback_data === 'gender_male') {
    $gender = $callback_data === 'gender_female' ? 'female' : 'male';

    $stmt = $pdo->prepare("UPDATE users SET gender = ? WHERE telegram_id = ?");
    $stmt->execute([$gender, $telegram_id]);

    answerCallbackQuery($callback_query_id);
    sendMessage($chat_id, $_['gender_saved']);
    sendMessage($chat_id, $_['ask_birthdate']);
    return;
}

// Дата рождения (валидный формат)
if (preg_match('~^\d{4}-\d{2}-\d{2}$~', $text)) {
    $birthdate = $text;
    $date = DateTime::createFromFormat('Y-m-d', $birthdate);

    if (!$date || $date->format('Y-m-d') !== $birthdate) {
        sendMessage($chat_id, $_['birthdate_invalid']);
        return;
    }

    $today = new DateTime();
    $age = $today->diff($date)->y;

    $stmt = $pdo->prepare("UPDATE users SET birth_date = ? WHERE telegram_id = ?");
    $stmt->execute([$birthdate, $telegram_id]);

    sendMessage($chat_id, $age < 16 ? $_['birthdate_too_young'] : $_['birthdate_saved']);
    sendMessage($chat_id, $_['ask_phone'], null, [
        [['text' => $_['phone_send_button'], 'request_contact' => true]],
        [['text' => $_['phone_manual_button']], ['text' => $_['skip_button']]]
    ]);
    return;
}

// Дата рождения (неверная форма)
if (preg_match('~\d{4}[-/]\d{2}[-/]\d{2}~', $text)) {
    sendMessage($chat_id, $_['birthdate_invalid']);
    return;
}

// Имя
if (
    $user &&
    empty($user['name']) &&
    (
        !empty($user['telegram_temp_id']) || !empty($user['ai_user_id'])
    ) &&
    !empty($text) &&
    mb_strlen($text) <= 40 &&
    preg_match('/^[\p{L}\s\-]+$/u', $text) &&
    !filter_var($text, FILTER_VALIDATE_EMAIL) &&
    !preg_match('/^\d{6}$/', $text) &&
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $text) &&
    !preg_match('/^\+\d{10,15}$/', $text)
) {
    $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE telegram_id = ?");
    $stmt->execute([$text, $telegram_id]);

    sendMessage($chat_id, $_['ask_gender'], [
        [
            ['text' => $_['gender_female'], 'callback_data' => 'gender_female'],
            ['text' => $_['gender_male'], 'callback_data' => 'gender_male']
        ]
    ]);
    return;
}

// Иначе — сообщение не распознано
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/bot/unrecognized.log', "[" . date('Y-m-d H:i:s') . "] [$telegram_id] $text\n", FILE_APPEND);
sendMessage($chat_id, $_['unrecognized_input']);
