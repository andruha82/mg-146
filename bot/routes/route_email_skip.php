<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bot/functions.php';

$ai_user_id = generateAiUserId();

// Проверим, не создан ли уже ai_user_id
$stmt = $pdo->prepare("SELECT ai_user_id FROM users WHERE telegram_id = ?");
$stmt->execute([$telegram_id]);
$existing = $stmt->fetchColumn();

if (!$existing) {
    $stmt = $pdo->prepare("UPDATE users SET ai_user_id = ? WHERE telegram_id = ?");
    $stmt->execute([$ai_user_id, $telegram_id]);
}

sendMessage($chat_id, $_['skip_email']);
continueProfileFlow($chat_id, $user, $_);

answerCallbackQuery($callback_query_id);
