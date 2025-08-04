<?php
header('Content-Type: application/json');
require_once __DIR__ . '/ask_ai.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/checklang.php';
session_start();

function countWords($text) {
    $text = strip_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace([
        '/!\[.*?\]\(.*?\)/',
        '/\[.*?\]\(.*?\)/',
        '/[`*_#>~\-+=]+/',
        '/\n{2,}/',
    ], ' ', $text);
    $text = trim(preg_replace('/\s+/', ' ', $text));
    preg_match_all('/\p{L}+/u', $text, $matches);
    return count($matches[0]);
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['reply' => $_['chat_no_auth']]);
    exit;
}

$user_id = $_SESSION['user_id'];
$question = trim($_POST['question'] ?? '');
$article_id = (int)($_POST['article_id'] ?? 0);
$lang = $selected_lang ?? 'uk';

if (mb_strlen($question) < 2 || !$article_id) {
    echo json_encode(['reply' => $_['chat_invalid_request']]);
    exit;
}

// Получение подписки
$stmt = $pdo->prepare("SELECT us.*, p.max_questions FROM user_subscriptions us
    LEFT JOIN subscription_packages p ON us.package_id = p.id
    WHERE us.user_id = ? AND us.status = 'active' AND us.payment_status = 'paid'
    AND CURRENT_DATE BETWEEN us.start_date AND us.end_date
    ORDER BY us.id DESC LIMIT 1");
$stmt->execute([$user_id]);
$subscription = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$subscription) {
    echo json_encode(['reply' => $_['chat_no_subscription']]);
    exit;
}
if ($subscription['questions_used'] >= $subscription['max_questions']) {
    echo json_encode(['reply' => $_['chat_question_limit_reached']]);
    exit;
}

// Получаем имя, пол, возраст, ai_user_id
$stmt = $pdo->prepare("SELECT name, gender, birth_date, ai_user_id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$name = $user['name'] ?? 'друг';
$gender = $user['gender'] ?? 'unknown';
$birth_date = $user['birth_date'] ?? null;
$age = $birth_date ? floor((time() - strtotime($birth_date)) / (365.25 * 86400)) : 'неизвестен';
$ai_user_id = $user['ai_user_id'] ?? (string)$user_id;

// Краткое описание статьи
$lang_suffix = $lang;
$desc_field = "quick_description_" . $lang_suffix;
$stmt = $pdo->prepare("SELECT $desc_field FROM articles WHERE id = ?");
$stmt->execute([$article_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$short = $row[$desc_field] ?? '';

// Сохраняем вопрос (модель и токены позже)
$stmt = $pdo->prepare("INSERT INTO chat_messages (user_id, article_id, role, message, lang, source, created_at) VALUES (?, ?, 'user', ?, ?, 'web', NOW())");
$stmt->execute([$user_id, $article_id, $question, $lang]);

// История последних 11 сообщений
$stmt = $pdo->prepare("SELECT role, message FROM chat_messages
    WHERE user_id = ? AND article_id = ?
    ORDER BY created_at DESC LIMIT 11");
$stmt->execute([$user_id, $article_id]);
$history = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));

// Формируем сообщения
$messages = [];
$system_prompt_raw = $_['chat_prompt'];
$system_prompt = str_replace(['$name', '$gender', '$age', '$short'], [$name, $gender, $age, $short], $system_prompt_raw);
$messages[] = ['role' => 'system', 'content' => $system_prompt];
foreach ($history as $msg) {
    $messages[] = ['role' => $msg['role'], 'content' => $msg['message']];
}

// Подсчет слов, которые отправляются AI (промпт + история + сам вопрос) 
$all_text_for_prompt = '';
foreach ($messages as $msg) {
    $all_text_for_prompt .= ' ' . $msg['content'];
}
$prompt_word_count = countWords($all_text_for_prompt);

// Отправка в AI
$response = send_to_ai($messages, $ai_user_id);

// Сохраняем в файл отладочную информацию
file_put_contents(__DIR__ . '/debug_ai_out.json', json_encode([
    'messages_sent' => $messages,
    'user_id' => $ai_user_id,
    'reply' => $response,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Обработка
$reply = $response['choices'][0]['message']['content'] ?? null;
$model_used = $response['model'] ?? null;
$prompt_tokens = $response['usage']['prompt_tokens'] ?? null;
$completion_tokens = $response['usage']['completion_tokens'] ?? null;
$assistant_words = countWords($reply);

if ($reply) {
    // Обновляем предыдущее сообщение пользователя (последнее)
    $stmt = $pdo->prepare("UPDATE chat_messages SET model = ?, tokens = ?, words = ? WHERE user_id = ? AND article_id = ? AND role = 'user' ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$model_used, $prompt_tokens, $prompt_word_count, $user_id, $article_id]);

    // Сохраняем ответ ИИ
    $stmt = $pdo->prepare("INSERT INTO chat_messages (user_id, article_id, role, message, model, tokens, words, lang, source, created_at) VALUES (?, ?, 'assistant', ?, ?, ?, ?, ?, 'web', NOW())");
    $stmt->execute([$user_id, $article_id, $reply, $model_used, $completion_tokens, $assistant_words, $lang]);

    // Обновляем счётчик
    $stmt = $pdo->prepare("UPDATE user_subscriptions SET questions_used = questions_used + 1 WHERE id = ?");
    $stmt->execute([$subscription['id']]);

    echo json_encode(['reply' => $reply]);
} else {
    echo json_encode(['reply' => $_['chat_answer_not_received']]);
}
