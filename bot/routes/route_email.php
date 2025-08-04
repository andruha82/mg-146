<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bot/functions.php';

$email_pattern = '/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/';

// Ввод email
if (preg_match($email_pattern, $text)) {
    $email = mb_strtolower(trim($text));

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user_check = $stmt->fetch();

    if ($user_check) {
        $verification_code = rand(100000, 999999);

        $stmt = $pdo->prepare("UPDATE users SET telegram_temp_id = ?, registration_token = ?, registration_token_expiration = NOW() + INTERVAL 15 MINUTE WHERE email = ?");
        $stmt->execute([$telegram_id, $verification_code, $email]);

        $fromName = 'MindGuide';
        $fromEmail = 'form@mindguide.online';
        $to = $email;
        $subject = $_['email_code_subject'];
        $messageBody = str_replace("{{code}}", $verification_code, $_['email_code_body']);

        $headers = "From: $fromName <$fromEmail>\r\n";
        $headers .= "Reply-To: $fromEmail\r\n";
        $headers .= "Return-Path: $fromEmail\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        ini_set("SMTP", "mindguide.online");
        ini_set("smtp_port", "587");
        ini_set("sendmail_from", $fromEmail);

        mail($to, $subject, $messageBody, $headers);

        sendMessage($chat_id, $_['email_code_sent']);
    } else {
        sendMessage($chat_id, $_['email_not_found'], [
            [['text' => $_['skip_button'], 'callback_data' => 'skip_email']]
        ]);
    }

    exit;
}

// Проверка 6-значного кода
if (preg_match('/^\d{6}$/', $text)) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE telegram_temp_id = ? AND registration_token = ?");
    $stmt->execute([$telegram_id, $text]);
    $user_check = $stmt->fetch();

    if ($user_check) {
        if (!empty($user_check['telegram_id']) && $user_check['telegram_id'] != $telegram_id) {
            sendMessage($chat_id, $_['email_already_attached']);
            exit;
        }

        $pdo->prepare("DELETE FROM users WHERE telegram_id = ? AND id != ?")->execute([$telegram_id, $user_check['id']]);

        $stmt = $pdo->prepare("UPDATE users SET telegram_id = ?, tg_username = ?, telegram_temp_id = NULL, registration_token = NULL, registration_token_expiration = NULL WHERE id = ?");
        $stmt->execute([$telegram_id, $username, $user_check['id']]);

        sendMessage($chat_id, $_['email_attached']);
        continueProfileFlow($chat_id, $user, $_);

    } else {
        sendMessage($chat_id, $_['email_code_invalid']);
    }

    exit;
}

