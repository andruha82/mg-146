<?php
require_once 'config.php';

function ask_ai($provider, $api_key, $model, $messages, $user_id = null) {
    switch ($provider) {
        case 'openai':
            $url = 'https://api.openai.com/v1/chat/completions';
            $headers = [
                'Authorization: Bearer ' . $api_key,
                'Content-Type: application/json'
            ];
            $payload = [
                'model' => $model,
                'messages' => $messages,
                'user' => (string)$user_id,
                'temperature' => 0.7
            ];
            break;

        case 'deepseek':
            $url = 'https://api.deepseek.com/v1/chat/completions';
            $headers = [
                'Authorization: Bearer ' . $api_key,
                'Content-Type: application/json'
            ];
            $payload = [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.7
            ];
            break;

        case 'qwen':
            $url = 'https://dashscope.aliyuncs.com/api/v1/services/aigc/text-generation/generation';
            $headers = [
                'Authorization: Bearer ' . $api_key,
                'Content-Type: application/json'
            ];
            $payload = [
                'model' => $model,
                'input' => [ 'messages' => $messages ],
                'parameters' => [ 'temperature' => 0.7 ]
            ];
            break;

        case 'groq':
            $url = 'https://api.groq.com/openai/v1/chat/completions';
            $headers = [
                'Authorization: Bearer ' . $api_key,
                'Content-Type: application/json'
            ];
            $payload = [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.7
            ];
            break;

        default:
            return ['error' => 'Неизвестный AI-провайдер: ' . $provider];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => $headers
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['error' => $error];
    }

    return json_decode($response, true);
}

function send_to_ai(array $messages, string $user_id, string $task_type = 'chat') {
    // Гибкий выбор провайдера и модели по типу задачи
    switch ($task_type) {
        case 'economy':
            $provider = 'groq';
            $api_key = GROQ_API_KEY;
            $model = GROQ_MODEL;
            break;

        case 'premium':
            $provider = 'deepseek';
            $api_key = DEEPSEEK_API_KEY;
            $model = DEEPSEEK_MODEL;
            break;

        case 'qwen':
            $provider = 'qwen';
            $api_key = QWEN_API_KEY;
            $model = QWEN_MODEL;
            break;

        default:
            $provider = AI_PROVIDER;
            $api_key = API_KEY;
            $model = AI_MODEL;
    }

    return ask_ai($provider, $api_key, $model, $messages, $user_id);
}

?>
