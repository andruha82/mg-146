<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bot/functions.php';

$data = json_decode(file_get_contents('php://input'), true);
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/bot/input.log', json_encode($data, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

$chat_id = $data['message']['chat']['id'] ?? ($data['callback_query']['message']['chat']['id'] ?? null);
$telegram_id = $data['message']['from']['id'] ?? ($data['callback_query']['from']['id'] ?? null);
$username = $data['message']['from']['username'] ?? ($data['callback_query']['from']['username'] ?? null);
$text = $data['message']['text'] ?? '';
$callback_data = $data['callback_query']['data'] ?? null;
$callback_query_id = $data['callback_query']['id'] ?? null;
$contact = $data['message']['contact']['phone_number'] ?? null;

$_ = loadLang(getUserLang($telegram_id));

$stmt = $pdo->prepare("SELECT * FROM users WHERE telegram_id = ?");
$stmt->execute([$telegram_id]);
$user = $stmt->fetch();

$is_email = filter_var($text, FILTER_VALIDATE_EMAIL);

// Роутинг по событиям (меню)

if ($text === '/www' || $text === $_['menu_open_site']) {
    require __DIR__ . '/routes/route_www.php';
    exit;
}

if ($text === '/telegram' || $text === $_['menu_telegram']) {
    require __DIR__ . '/routes/route_telegram.php';
    exit;
}

if ($text === '/instagram') {
    require __DIR__ . '/routes/route_instagram.php';
    exit;
}

if ($text === '/support' || $text === $_['menu_support']) {
    sendMessage($chat_id, $_['support_unavailable']);
    exit;
}


// Роутинг по событиям (остальное)

if (strpos($text, '/start') === 0) {
    require __DIR__ . '/routes/route_start.php';
    exit;
}

if ($callback_data && strpos($callback_data, 'lang_') === 0) {
    require __DIR__ . '/routes/route_language.php';
    exit;
}

if ($callback_data === 'continue_intro') {
    require __DIR__ . '/routes/route_continue_intro.php';
    exit;
}

if (!empty($text) && trim($text) === '/menu') {
    require __DIR__ . '/routes/route_menu.php';
    exit;
}

if ($callback_data === 'skip_email') {
    require __DIR__ . '/routes/route_email_skip.php';
    exit;
}

if ($is_email) {
    require __DIR__ . '/routes/route_email.php';
    exit;
}

if (preg_match('/^\d{6}$/', $text)) {
    require __DIR__ . '/routes/route_email.php';
    exit;
}

if (
    $callback_data === 'gender_female' ||
    $callback_data === 'gender_male' ||
    preg_match('~^\d{4}-\d{2}-\d{2}$~', $text) ||
    preg_match('~\d{4}[-/]\d{2}[-/]\d{2}~', $text) ||
    (
        $user &&
        (
            empty($user['name']) ||
            empty($user['gender']) ||
            empty($user['birth_date'])
        )
    )
) {
    require __DIR__ . '/routes/route_profile.php';
    exit;
}

if ($text === $_['phone_manual_button'] ||
    $contact ||
    preg_match('/^\+\d{10,15}$/', $text) ||
    $text === $_['skip_button'] ||
    $callback_data === 'skip_phone') {
    require __DIR__ . '/routes/route_phone.php';
    exit;
}

if ($callback_data === 'skip_test') {
    require __DIR__ . '/routes/route_trial_offer.php';
    exit;
}

if ($callback_data === 'start_trial') {
    require __DIR__ . '/routes/route_trial_register.php';
    exit;
}


// Финальный обработчик: всё, что не подходит ни под одно условие
if (!empty($text)) {
    require __DIR__ . '/routes/route_fallback.php';
    exit;
}

file_put_contents(__DIR__ . '/debug_text.log', "[" . date('Y-m-d H:i:s') . "] TEXT: " . $text . "\n", FILE_APPEND);

