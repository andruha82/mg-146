<?php

function sendMessage($chat_id, $text, $inline_keyboard = null, $reply_keyboard = null) {
    $params = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];

    if ($inline_keyboard) {
        $params['reply_markup'] = json_encode([
            'inline_keyboard' => $inline_keyboard
        ]);
    } elseif ($reply_keyboard) {
        $params['reply_markup'] = json_encode([
            'keyboard' => $reply_keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
    }

    $ch = curl_init(API_URL . "sendMessage");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    $response = curl_exec($ch);
    curl_close($ch);

    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/bot/send.log', "SEND:\n" . $response . "\n", FILE_APPEND);
}

function answerCallbackQuery($callback_query_id, $text = '', $show_alert = false) {
    $params = [
        'callback_query_id' => $callback_query_id,
        'text' => $text,
        'show_alert' => $show_alert,
    ];

    $ch = curl_init(API_URL . "answerCallbackQuery");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_exec($ch);
    curl_close($ch);
}

// СТАРЫЙ ВАРИАНТ !!!
function saveTelegramUser_OLD($telegram_id, $tg_username, $ref = null) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT id FROM users WHERE telegram_id = ?");
    $stmt->execute([$telegram_id]);

    if ($stmt->rowCount() === 0) {
        $stmt = $pdo->prepare("INSERT INTO users (telegram_id, tg_username, partner_id, registration_lang) VALUES (?, ?, ?, ?)");
        $stmt->execute([$telegram_id, $tg_username, $ref, DEFAULT_LANG]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET tg_username = ?, partner_id = ? WHERE telegram_id = ?");
        $stmt->execute([$tg_username, $ref, $telegram_id]);
    }
}

// НОВЫЙ ВАРИАНТ !!!
function saveTelegramUser($telegram_id, $tg_username, $ref = null) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT id, partner_id FROM users WHERE telegram_id = ?");
    $stmt->execute([$telegram_id]);
    $user = $stmt->fetch();

    // Обработка partner_id, только если он ещё не установлен
    $partner_id = $user['partner_id'] ?? null;

    if (empty($partner_id) && $ref && strlen($ref) >= 2) {
        $prefix = strtolower($ref[0]);
        $partner_id = $ref;

        switch ($prefix) {
            case 'u':
                $network = 'tg_invite';
                break;
            case 't':
                $network = 'tg_ads';
                break;
            case 'i':
                $network = 'influencer';
                break;
            case 'p':
                $network = 'paid_ads';
                break;
            case 'w':
                $network = 'website';
                break;
            case 'o':
                $network = 'offline_campaign';
                break;
            default:
                $network = 'unknown';
                break;
        }
    }

    if ($user) {
        $stmt = $pdo->prepare("UPDATE users SET tg_username = ?, network = ?, partner_id = ? WHERE telegram_id = ?");
        $stmt->execute([$tg_username, $network, $partner_id, $telegram_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (telegram_id, tg_username, network, partner_id, registration_lang) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$telegram_id, $tg_username, $network, $partner_id, DEFAULT_LANG]);
    }
}


function updateUserLang($telegram_id, $lang) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET registration_lang = ? WHERE telegram_id = ?");
    $stmt->execute([$lang, $telegram_id]);
}

function getUserLang($telegram_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT registration_lang FROM users WHERE telegram_id = ?");
    $stmt->execute([$telegram_id]);
    return $stmt->fetchColumn() ?: DEFAULT_LANG;
}

function loadLang($lang) {
    $_ = [];
    $file = $_SERVER['DOCUMENT_ROOT'] . "/system/lang/{$lang}_bot.php";
    if (file_exists($file)) {
        require $file;
    }
    return $_;
}

function generateAiUserId() {
    $uuid = bin2hex(random_bytes(16));
    return 'u_' .
        substr($uuid, 0, 8) . '-' .
        substr($uuid, 8, 4) . '-' .
        substr($uuid, 12, 4) . '-' .
        substr($uuid, 16, 4) . '-' .
        substr($uuid, 20, 12);
}


function continueProfileFlow($chat_id, $user, $_) {
if (!empty($user['name']) && !empty($user['gender']) && !empty($user['birth_date']) && !empty($user['phone'])) {

    $tg_id = $user['telegram_id'] ?? $chat_id;
    $lang = getUserLang($tg_id);
    $test_url = "https://www.mindguide.online/test.php?tg_id={$tg_id}&lang={$lang}";

    sendMessage($chat_id, $_['profile_complete']);
    sendMessage($chat_id, $_['offer_test'], [
    [
        ['text' => $_['take_test'], 'web_app' => ['url' => $test_url]],
        ['text' => $_['skip_test'], 'callback_data' => 'skip_test']
    ]
]);

} elseif (!empty($user['name']) || !empty($user['gender']) || !empty($user['birth_date']) || !empty($user['phone'])) {
        sendMessage($chat_id, $_['profile_partial']);
        if (empty($user['name'])) {
            sendMessage($chat_id, $_['ask_name']);
        } elseif (empty($user['gender'])) {
            sendMessage($chat_id, $_['ask_gender'], [
                [
                    ['text' => $_['gender_female'], 'callback_data' => 'gender_female'],
                    ['text' => $_['gender_male'], 'callback_data' => 'gender_male']
                ]
            ]);
        } elseif (empty($user['birth_date'])) {
            sendMessage($chat_id, $_['ask_birthdate']);
        } elseif (empty($user['phone'])) {
            sendMessage($chat_id, $_['ask_phone'], null, [
                [['text' => $_['phone_send_button'], 'request_contact' => true]],
                [['text' => $_['phone_manual_button']], ['text' => $_['skip_button']]]
            ]);
        }
    } else {
        sendMessage($chat_id, $_['ask_name']);
    }
}



function getUser($telegram_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE telegram_id = ?");
    $stmt->execute([$telegram_id]);
    return $stmt->fetch();
}

function deleteMessage($chat_id, $message_id) {
    $params = [
        'chat_id' => $chat_id,
        'message_id' => $message_id
    ];

    $ch = curl_init(API_URL . "deleteMessage");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_exec($ch);
    curl_close($ch);
}
