<?php
session_start();
include("../settings/connect_datebase.php");

if(!isset($_SESSION['temp_user_id'])) {
    echo "session_expired";
    exit();
}

$login = $_POST['login'];

//генерация кода
$code = sprintf("%06d", random_int(0, 999999));

//новый код в сессии
$_SESSION['auth_code'] = $code;
$_SESSION['code_expire'] = time() + 600; // 10 минут

//отправка нового кода
$subject = 'Новый код подтверждения авторизации';
$message = 'Ваш новый код для авторизации: ' . $code . "\r\n";
$message .= 'Код действителен в течение 10 минут.';
$headers = 'From: nastya28042020@yandex.ru' . "\r\n" .
           'Reply-To: nastya28042020@yandex.ru' . "\r\n" .
           'Content-Type: text/plain; charset=utf-8' . "\r\n" .
           'X-Mailer: PHP/' . phpversion();

if(mail($login, $subject, $message, $headers)) {
    echo "code_resent";
} else {
    echo "mail_error";
}
?>