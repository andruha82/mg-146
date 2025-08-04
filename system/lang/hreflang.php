<?php
// Доступные языки (добавляй нужные)
$languages = ['ru', 'uk'];
$default_language = 'uk';

// Определяем текущий язык (по умолчанию — украинский)
$selected_lang = $_GET['lang'] ?? $default_language;

// Определяем текущий домен (учитывает и HTTPS, и HTTP)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$domain = $protocol . $_SERVER['HTTP_HOST'];

// Получаем текущий URL с параметрами
$query_params = $_GET; // Копируем текущие GET параметры
unset($query_params['lang']); // Убираем старый параметр lang

// Базовый URL без ?lang=
$base_url = strtok($_SERVER["REQUEST_URI"], '?');
$base_query = http_build_query($query_params);

// Генерация hreflang-тегов
foreach ($languages as $lang) {
    $query_params['lang'] = $lang; // Добавляем нужный язык в параметры
    $full_query = http_build_query($query_params);
    echo '<link rel="alternate" hreflang="'.$lang.'" href="'.$domain.$base_url.($full_query ? '?'.$full_query : '').'" />' . PHP_EOL;
}

// Добавляем x-default с параметром lang
$query_params['lang'] = $default_language;
$full_query_xdefault = http_build_query($query_params);
echo '<link rel="alternate" hreflang="x-default" href="'.$domain.$base_url.($full_query_xdefault ? '?'.$full_query_xdefault : '').'" />' . PHP_EOL;
?>
