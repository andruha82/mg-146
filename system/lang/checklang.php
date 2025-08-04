<?php
// Разрешённые языки
$allowed_langs = ['uk', 'ru'];

// Обработка выбора языка
if (isset($_GET['lang']) && in_array($_GET['lang'], $allowed_langs)) {
    $selected_lang = $_GET['lang'];
    setcookie('lang', $selected_lang, time() + 365 * 24 * 3600, '/', '', true, true); // Secure + HttpOnly
    
    // Убираем параметр lang из URL
    $query_params = $_GET;
    unset($query_params['lang']); // Удаляем lang
    
    // Собираем новый URL
    $new_url = $_SERVER['PHP_SELF'] . (!empty($query_params) ? '?' . http_build_query($query_params) : '');
    
    header("Location: $new_url");
    exit;
}

// Определяем выбранный язык
$selected_lang = $_COOKIE['lang'] ?? 'uk';

// Если язык некорректен, сбрасываем на 'uk'
if (!in_array($selected_lang, $allowed_langs)) {
    $selected_lang = 'uk';
}

// Подключаем языковой файл
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/' . $selected_lang . '.php';
?>

