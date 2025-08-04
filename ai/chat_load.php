<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php';
require_once __DIR__ . '/Parsedown.php'; // ✅ Подключаем Markdown парсер
session_start();
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
$article_id = (int) ($_GET['article_id'] ?? 0);

if (!$user_id || !$article_id) {
    echo json_encode(['error' => 'Недостаточно данных']);
    exit;
}

// Получаем сообщения
$stmt = $pdo->prepare("
    SELECT role, message, created_at 
    FROM chat_messages 
    WHERE user_id = ? AND article_id = ?
    ORDER BY created_at ASC
");
$stmt->execute([$user_id, $article_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Преобразуем Markdown в HTML
$Parsedown = new Parsedown();
$Parsedown->setSafeMode(true); // Безопасный режим (XSS защита)

foreach ($messages as &$msg) {
    if ($msg['role'] === 'assistant') {
        $msg['message'] = $Parsedown->text($msg['message']);
    } else {
        // для user можно оставить как есть или применить htmlentities, если надо
        $msg['message'] = htmlspecialchars($msg['message']);
    }
}

echo json_encode(['messages' => $messages]);
?>
