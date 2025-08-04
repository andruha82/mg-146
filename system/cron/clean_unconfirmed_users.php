<?php
// clean_unconfirmed_users.php

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/system/functions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/system/lang/checklang.php'; // подключает $_

// Подключение к базе данных
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($mysqli->connect_error) {
    die('Ошибка подключения к базе данных: ' . $mysqli->connect_error);
}

// === 1. Напоминание пользователям за 1 день до удаления ===
$reminder_query = "
    SELECT id, email
    FROM users
    WHERE status = 'pending'
      AND created_at < NOW() - INTERVAL 6 DAY
      AND created_at >= NOW() - INTERVAL 7 DAY
";

$result = $mysqli->query($reminder_query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $email = $row['email'];
        $subject = $_['email_reminder_subject'];
        $body = $_['email_reminder_body'];
        sendEmail($email, $subject, $body, false);
    }
}

// === 2. Удаление просроченных неподтверждённых аккаунтов ===
$delete_query = "
    DELETE FROM users
    WHERE status = 'pending'
      AND created_at < NOW() - INTERVAL 7 DAY
";
$mysqli->query($delete_query);

$mysqli->close();
