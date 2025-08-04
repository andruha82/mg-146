<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/config.php';

function setTelegramCommands($commands, $lang = null) {
    $params = [
        'commands' => json_encode($commands)
    ];

    if ($lang) {
        $params['language_code'] = $lang;
    }

    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/setMyCommands?" . http_build_query($params);
    $response = file_get_contents($url);

    echo "<pre>Команды для " . ($lang ?: 'по умолчанию') . ":\n" . $response . "</pre>";
}

// 🇷🇺 Команды на русском
$commands_ru = [
    ['command' => 'www',    'description' => '🌐 Наш сайт'],
    ['command' => 'telegram', 'description' => '📌 Наш канал'],
    ['command' => 'instagram', 'description' => '📌 Наш Instagram'],
    ['command' => 'support', 'description' => '🛠 Техподдержка'],
];

// 🇺🇦 Команды на украинском
$commands_uk = [
    ['command' => 'www',    'description' => '🌐 Наш сайт'],
    ['command' => 'telegram', 'description' => '📌 Наш канал'],
    ['command' => 'instagram', 'description' => '📌 Наш Instagram'],
    ['command' => 'support', 'description' => '🛠 Техпідтримка'],
];

// 🔤 Общие (по умолчанию, если язык не определён)
$commands_default = $commands_uk;

setTelegramCommands($commands_default);       // без языка — по умолчанию
setTelegramCommands($commands_ru, 'ru');      // русский интерфейс
setTelegramCommands($commands_uk, 'uk');      // украинский интерфейс
