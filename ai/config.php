<?php
// 🧠 OpenAI (провайдер по умолчанию)
define('AI_PROVIDER', 'openai');                     // Провайдер по умолчанию
define('API_KEY', 'sk-proj');    // API-ключ от OpenAI
define('AI_MODEL', 'gpt-4.1-nano');                        // Модель по умолчанию

// 💸 Groq (эконом-вариант, дешёвые ответы)
define('GROQ_API_KEY', 'sk-здесь_ваш_groq_ключ');    // API-ключ от Groq
define('GROQ_MODEL', 'mixtral-8x7b');                // Быстрая модель от Groq

// 🌟 DeepSeek (премиум-вариант)
define('DEEPSEEK_API_KEY', 'sk-здесь_ваш_deepseek_ключ');  // API-ключ от DeepSeek
define('DEEPSEEK_MODEL', 'deepseek-chat');                 // Модель от DeepSeek

// 🔮 Qwen (если захочешь подключить в будущем)
/*
define('QWEN_API_KEY', 'sk-здесь_ваш_qwen_ключ');
define('QWEN_MODEL', 'qwen-plus');
*/

?>
