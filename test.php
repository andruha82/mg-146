<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/system/db.php'; // Подключаем базу данных

if (isset($_GET['lang']) && in_array($_GET['lang'], ['ru', 'uk'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang; // сохраняем в сессию для следующих запросов
} elseif (isset($_SESSION['lang']) && in_array($_SESSION['lang'], ['ru', 'uk'])) {
    $lang = $_SESSION['lang'];
} else {
    $lang = 'uk'; // язык по умолчанию
}
require_once __DIR__ . "/system/lang/{$lang}_test.php";


// Коэффициенты категорий по вопросам (3 вопроса на категорию)
$coeffs = [1.1,1.1,1.1, 1.0,1.0,1.0, 1.0,1.0,1.0, 0.9,0.9,0.9];

// Тексты вопросов и ответов
$questions = [
    [$_['q1'], [$_['q1a1'], $_['q1a2'], $_['q1a3'], $_['q1a4']]],
    [$_['q2'], [$_['q2a1'], $_['q2a2'], $_['q2a3'], $_['q2a4']]],
    [$_['q3'], [$_['q3a1'], $_['q3a2'], $_['q3a3'], $_['q3a4']]],

    [$_['q4'], [$_['q4a1'], $_['q4a2'], $_['q4a3'], $_['q4a4']]],
    [$_['q5'], [$_['q5a1'], $_['q5a2'], $_['q5a3'], $_['q5a4']]],
    [$_['q6'], [$_['q6a1'], $_['q6a2'], $_['q6a3'], $_['q6a4']]],

    [$_['q7'], [$_['q7a1'], $_['q7a2'], $_['q7a3'], $_['q7a4']]],
    [$_['q8'], [$_['q8a1'], $_['q8a2'], $_['q8a3'], $_['q8a4']]],
    [$_['q9'], [$_['q9a1'], $_['q9a2'], $_['q9a3'], $_['q9a4']]],

    [$_['q10'], [$_['q10a1'], $_['q10a2'], $_['q10a3'], $_['q10a4']]],
    [$_['q11'], [$_['q11a1'], $_['q11a2'], $_['q11a3'], $_['q11a4']]],
    [$_['q12'], [$_['q12a1'], $_['q12a2'], $_['q12a3'], $_['q12a4']]],
];

$totalQuestions = count($questions);
$perPage = 3;
$totalSteps = ceil($totalQuestions / $perPage);
$telegram_id = isset($_GET['tg_id']) && is_numeric($_GET['tg_id']) ? $_GET['tg_id'] : null;

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$step = max(1, min($step, $totalSteps + 1));

$share_text = $_['share_text'];
$btn_signup = $_['btn_signup'];
$btn_share = $_['btn_share'];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['answers']) && count($_POST['answers']) >= ($perPage)) {
        foreach ($_POST['answers'] as $idx => $val) {
            $_SESSION['answers'][$idx] = (float)$val;
        }
        $direction = isset($_POST['back']) ? -1 : 1;
        header('Location: ?step=' . ($step + $direction) . ($telegram_id ? '&tg_id=' . $telegram_id : ''));
        exit;
    } else {
        $error = $_['answer_all'];
    }
}

function calculate_results() {
    global $coeffs;
    $answers = $_SESSION['answers'] ?? [];
    $sum = 0;
    foreach ($answers as $i => $val) {
        $sum += $val * $coeffs[$i];
    }
    $average = round($sum / count($coeffs), 2);
    $catAvgs = [];
    for ($c = 0; $c < 4; $c++) {
        $secSum = 0;
        for ($j = 0; $j < 3; $j++) {
            $idx = $c * 3 + $j;
            $secSum += ($_SESSION['answers'][$idx] ?? 0) * $coeffs[$idx];
        }
        $catAvgs[$c] = round($secSum / 3, 2);
    }
    return ['avg' => $average, 'cats' => $catAvgs];
}

function html_to_markdown($html) {
    // Замена <strong> на *
    $html = preg_replace('/<strong>(.*?)<\/strong>/i', '*$1*', $html);
    
    // Замена <em> или <i> на _
    $html = preg_replace('/<(em|i)>(.*?)<\/(em|i)>/i', '_$2_', $html);
    
    // Замена <br> на \n
    $html = preg_replace('/<br\\s*\\/?>/i', "\n", $html);
    
    // Замена <p> на \n\n
    $html = preg_replace('/<p\\s*[^>]*>/i', '', $html);
    $html = str_replace('</p>', "\n\n", $html);
    
    // Убираем оставшиеся HTML-теги
    $html = strip_tags($html);

    // Экранирование Markdown-символов внутри текста
    $html = htmlspecialchars_decode($html); // на всякий случай
    return trim($html);
}

function get_user_id_by_telegram($telegram_id, $pdo) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE telegram_id = ?");
    $stmt->execute([$telegram_id]);
    return $stmt->fetchColumn() ?: 0;
}

function send_result_to_telegram_with_button($chat_id, $text, $pdo) {

$bot_token = "7763376179:AAHqT84I2CXfhQgu75uCZoFW0ZvosMhIJvg";
$signup_url = "https://www.mindguide.online/signup.php?tg_id=$chat_id";
global $btn_signup, $btn_share, $share_text;

    global $_;
    $user_id = get_user_id_by_telegram($chat_id, $pdo);
    $share_text = str_replace('{USER_ID}', "$user_id", $share_text);

$keyboard = [
    "inline_keyboard" => [
        [
            ["text" => $btn_signup, "url" => $signup_url]
        ],
        [
            ["text" => $btn_share, "switch_inline_query" => $share_text]
        ]
    ]
];


    $params = [
        "chat_id" => $chat_id,
        "text" => $text,
        "parse_mode" => "Markdown",
        "reply_markup" => json_encode($keyboard)
    ];
    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    $options = [
        "http" => [
            "header"  => "Content-type: application/x-www-form-urlencoded\r\n",
            "method"  => "POST",
            "content" => http_build_query($params),
        ]
    ];
    $context = stream_context_create($options);
    file_get_contents($url, false, $context);
}

?>

<!DOCTYPE html>
<html lang="<?= $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./styles/styles.css">
    <title>Психологический тест</title>
    <style>
         * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Mulish', sans-serif; }
        .container { background-color: #fff; width: 100%; padding: 0; }
        .section { padding: 5px 20px; background: #f3ffef; max-width: 500px; margin: 0 auto; border: 1px solid #d9ffcd; border-radius: 12px; text-align: left}
         h2 { margin-top: 0;}
         body { font-family: Mulish, sans-serif; max-width: 600px; margin: 20px auto; padding: 0 10px; line-height: 1.6; }
        .progress-bar { background: #eee; border-radius: 5px; overflow: hidden; margin-bottom: 20px; margin-top: 10px; }
        .progress { height: 10px; width: <?php echo round((($step-1)/$totalSteps)*100); ?>%; background: #4CAF50; }
        .question { margin-bottom: 20px; font-size: 13px;}
        .question p { margin-bottom: 5px;}
        .error { color: red; }
        .btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-weight: 700}
        .btn-next { background: #e74582; color: #fff; }
        .btn-back { background: #e5598e; color: #fff; }
        .result { font-size: 14px; color: #555; }
        .result p { margin: 10px 0;}
        .result strong { color: #222; }
        .note { color: #555; text-align: center; font-size: 13px;}
        .buttons, p.button { text-align: center; margin: 30px 0;}
        
    </style>
</head>
<body>
<div class="container">
 <div class="section">
<?php if ($step <= $totalSteps): ?>

    <div class="progress-bar"><div class="progress"></div></div>
    <form method="post">
        <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <?php
        $start = ($step - 1) * $perPage;
        $end = min($start + $perPage, $totalQuestions);
        $valueMap = [1.0, 0.7, 0.5, 0.0];
        for ($i = $start; $i < $end; $i++): $q = $questions[$i]; ?>
            <div class="question">
                <p><strong><?php echo ($i + 1) . ". " . htmlspecialchars($q[0]); ?></strong></p>
                <?php foreach ($q[1] as $k => $text): ?>
                    <?php $val = $valueMap[$k]; $checked = (isset($_SESSION['answers'][$i]) && $_SESSION['answers'][$i] === $val) ? 'checked' : ''; ?>
                    <label><input type="radio" name="answers[<?php echo $i; ?>]" value="<?php echo $val; ?>" <?php echo $checked; ?>> <?php echo htmlspecialchars($text); ?></label><br>
                <?php endforeach; ?>
            </div>
        <?php endfor; ?>

    <div class="buttons"><?php if ($step > 1): ?><button type="submit" name="back" class="btn btn-back"><?= $_['back']; ?></button><?php endif; ?>    
    <button type="submit" class="btn btn-next"><?php echo $step == $totalSteps ? $_['result'] : $_['continue']; ?></button></div>

    </form>

<?php else:
    $res = calculate_results();
    $avg = $res['avg'];
    $cats = $res['cats'];
    $catNames = [$_['cat_stress'], $_['cat_confidence'], $_['cat_social'], $_['cat_emotions']];
    // Выбираем категорию для внимания
    $maxDiff = 0; $highlightCat = ''; $highlightScore = 0;
    foreach ($cats as $i => $score) {
        $diff = $score - $avg;
        if ($diff > $maxDiff) { $maxDiff = $diff; $highlightCat = $catNames[$i]; $highlightScore = $score; }
    }
    // Интерпретация общего уровня
    if ($avg >= 0.85) {
      $level = $_['level_critical'];
      $description = $_['desc_critical'];
    } elseif ($avg >= 0.65) {
      $level = $_['level_high'];
      $description = $_['desc_high'];
    } elseif ($avg >= 0.40) {
      $level = $_['level_moderate'];
      $description = $_['desc_moderate'];
    } elseif ($avg >= 0.20) {
      $level = $_['level_light'];
      $description = $_['desc_light'];
    } else {
      $level = $_['level_ok'];
      $description = $_['desc_ok'];
    }


if ($telegram_id !== null) {


    // Построим текст результата
$resultText  = $_['res_intro'] . "\n\n";
$resultText .= $_['res_level'] . "<strong>" . round($avg * 100) . "%</strong>\n";
$resultText .= $_['res_summary'] . "<strong>$level</strong>\n";

if ($highlightCat) {
    $resultText .= $_['res_focus'] . "<strong>$highlightCat</strong>\n";
}

$resultText .= "\n" . $_['res_meaning'] . "\n\n";
$resultText .= strip_tags($description) . "\n";

$resultText .= "\n" . $_['res_action'] . "\n\n";
$resultText .= $_['res_tip_1'] . "<strong>$highlightCat</strong>.\n";
$resultText .= $_['res_tip_2'] . "\n\n";
$resultText .= $_['res_trial'] . "\n";

    // Отправка в Telegram
    $resultText = html_to_markdown($resultText);
    send_result_to_telegram_with_button($telegram_id, $resultText, $pdo);
}

?>
    <div class="result">
<p><strong><?php echo $_['res_level']; ?><span style="color: red"><?php echo ($avg * 100); ?>%</span></strong>. 
    <?php echo $_['res_scale_explained']; ?></p>

<?php if ($highlightCat): ?>
<p><strong><?php echo $_['res_focus']; ?></strong> <?php echo sprintf($_['res_focus_block'], $highlightCat); ?></p>
<?php endif; ?>

<p><?php echo $_['res_meaning']; ?> <?php echo $description; ?></p>

<p><?php echo $_['res_action']; ?></p>
<p><?php echo sprintf($_['res_tip_1_block'], $highlightCat); ?></p>
<p><?php echo $_['res_tip_2']; ?></p>

<p class=button>
    <a href="https://www.mindguide.online/signup.php?tg_id=<?= $telegram_id; ?>" class="btn btn-next">
        <?php echo $_['btn_signup']; ?>
    </a>
</p>
<p class="note"><?php echo $_['res_trial']; ?></p>
    </div>
<?php endif; ?>
 </div>
</div>
</body>
</html>
