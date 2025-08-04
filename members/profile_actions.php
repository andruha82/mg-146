<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Не авторизован']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_POST['action'] !== 'update_user_info') {
    echo json_encode(['status' => 'error', 'message' => 'Неверное действие']);
    exit;
}

// Разрешённые поля
$allowed_fields = [
    'name',
    'phone',
    'birth_date',
    'gender',
    'country',
    'tg_username',
    'marketing_consent'
];

$update_fields = [];
$update_values = [];

// Валидация и подготовка
foreach ($allowed_fields as $field) {
    if (isset($_POST[$field])) {
        $value = trim($_POST[$field]);

        if ($field === 'gender') {
            if (!in_array($value, ['male', 'female', 'other'])) {
                echo json_encode(['status' => 'error', 'message' => 'Неверное значение поля "Пол"']);
                exit;
            }
        }

        if ($field === 'marketing_consent') {
            $value = ($value == '1') ? 1 : 0;
        }

        $update_fields[] = "$field = ?";
        $update_values[] = $value;
    }
}

if (empty($update_fields)) {
    echo json_encode(['status' => 'error', 'message' => 'Нет данных для обновления']);
    exit;
}

$update_values[] = $user_id;
$sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
$stmt = $pdo->prepare($sql);

if ($stmt->execute($update_values)) {
    // Получаем обновлённые данные, чтобы вернуть на фронт
    $stmt2 = $pdo->prepare("SELECT name, phone, birth_date, gender, country, tg_username, marketing_consent FROM users WHERE id = ?");
    $stmt2->execute([$user_id]);
    $updated_user = $stmt2->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $updated_user
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка при сохранении']);
}
