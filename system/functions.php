<?php
// system/functions.php

/**
 * Определяет тип устройства по User-Agent
 */
function getDeviceType(string $ua): string
{
    if (preg_match('/mobile/i', $ua))   return 'mobile';
    if (preg_match('/tablet|ipad/i', $ua)) return 'tablet';
    if (preg_match('/bot|crawl|spider|slurp/i', $ua)) return 'bot';
    return 'desktop';
}

/**
 * Получает страну по IP через публичный API
 */
function getCountryByIp(string $ip): ?string
{
    $json = @file_get_contents("http://ip-api.com/json/{$ip}?fields=country");
    if (!$json) return null;
    $data = json_decode($json, true);
    return $data['country'] ?? null;
}

/**
 * Генерирует уникальный ai_user_id
 */
function generateAiUserId(): string
{
    $uuid = bin2hex(random_bytes(16));
    return 'u_' .
        substr($uuid, 0, 8) . '-' .
        substr($uuid, 8, 4) . '-' .
        substr($uuid, 12, 4) . '-' .
        substr($uuid, 16, 4) . '-' .
        substr($uuid, 20, 12);
}

/**
 * Универсальная функция отправки письма
 *
 * @param string $to      – кому
 * @param string $subject – тема
 * @param string $body    – тело (HTML или plain)
 * @param bool   $html    – true, если HTML; false – text/plain
 * @return bool           – результат mail()
 */
function sendEmail(string $to, string $subject, string $body, bool $html = true): bool
{
    $fromName  = 'MindGuide Support';
    $fromEmail = 'support@mindguide.online';

    // SMTP / sendmail настройки
    ini_set("SMTP",         "mindguide.online");
    ini_set("smtp_port",    "587");
    ini_set("sendmail_from",$fromEmail);

    // Заголовки
    $headers  = "MIME-Version: 1.0\r\n";
    if ($html) {
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    } else {
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    }
    $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
    $headers .= "Reply-To: {$fromEmail}\r\n";

    return mail($to, $subject, $body, $headers);
}

/**
 * Письмо с кодом подтверждения e-mail
 */
function sendConfirmationEmail(string $to, int $token, array $_, string $selected_lang): bool
{
    // Тема письма из языковых переменных
    $subject = $_['email_signup_subject'];

    // HTML-тело письма (обновлённый шаблон)
    $html = <<<HTML
<!DOCTYPE html>
<html lang="{$selected_lang}">
<head>
  <meta charset="UTF-8">
  <title>{$subject}</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Verdana,sans-serif;">
  <table align="center" width="100%" cellpadding="0" cellspacing="0"
         style="max-width:600px;margin:20px auto;background:#fff;border-radius:8px;overflow:hidden;
                box-shadow:0 2px 8px rgba(0,0,0,0.1);">
    <tr style="background:#1579c2;color:#fff;">
      <td style="padding:20px;text-align:center;font-size:24px;font-family:Verdana,sans-serif;">
        <b>MindGuide</b>
      </td>
    </tr>
    <tr>
      <td style="padding:30px;color:#333;">
        <h2 style="margin-top:0;font-family:Verdana,sans-serif;">{$_['email_signup_greeting']}</h2>
        <p style="margin:0 0 20px;">{$_['email_signup_thanks']}</p>
        <p style="font-size:18px;margin:0 0 5px;">{$_['email_signup_code_label']}</p>
        <p style="font-size:32px;font-weight:bold;margin:0 0 20px;color:#1579c2;">
          {$token}
        </p>
        <p style="margin:0 0 30px;">{$_['email_signup_expiry']}</p>
        <hr style="border:none;border-top:1px solid #eee;margin:30px 0;">
        <p style="font-size:12px;color:#888;margin:0;">{$_['email_signup_ignore_text']}</p>
      </td>
    </tr>
    <tr>
      <td style="background:#f8f8f8;text-align:center;padding:10px;font-size:12px;color:#aaa;">
        {$_['email_signup_footer']}
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

    return sendEmail($to, $subject, $html, true);
}

/**
 * Письмо со ссылкой для сброса пароля
 */
function sendPasswordResetEmail(string $to, string $resetLink, array $_): bool
{
    $subject = $_['forgot_email_subject'];
    $body = str_replace(
        '$resetLink',
        $resetLink,
        $_['forgot_email_messageBody']
    );
    return sendEmail($to, $subject, $body, false);
}

/**
 * Письмо об успешной регистрации
 */
function sendRegistrationSuccessEmail(string $to, array $_): bool
{
    $subject = $_['registration_success_subject'];
    $body    = $_['registration_success_messageBody'];
    return sendEmail($to, $subject, $body, false);
}

/**
 * Письмо о покупке подписки
 */

function sendSubscriptionPurchaseEmail(string $to, array $details, array $_): bool
{
    $subject = $_['subscription_email_subject'];
    $body = str_replace(
        ['{period}', '{amount}'], 
        [$details['period'], $details['amount']], 
        $_['subscription_email_messageBody']
    );
    return sendEmail($to, $subject, $body, false);
}

