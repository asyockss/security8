<?php
session_start();
include("../settings/connect_datebase.php");
include("../settings/config.php");

$login = $_POST['login'];
$password = $_POST['password'];

$login = $mysqli->real_escape_string($login);

//ищем пользователя
$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='$login' LIMIT 1");

if($query_user->num_rows == 1) {
    $user_read = $query_user->fetch_assoc();
    
    if(password_verify($password, $user_read['password'])){
        
        //проверка истечения пароля
        $changed_date = new DateTime($user_read['password_changed_at']);
        $current_date = new DateTime();
        $interval = $changed_date->diff($current_date);
        
        if($interval->days >= PASSWORD_EXPIRE_DAYS) {
            $_SESSION['password_expired'] = true;
            $_SESSION['temp_user_id'] = $user_read['id'];
            echo "password_expired";
            exit();
        }
        
        //очищаем предыдущую сессию
        $mysqli->query("UPDATE `users` SET `session_token` = NULL, `last_activity` = NULL WHERE `id` = {$user_read['id']}");
        
        //генерация кода
        $code = sprintf("%06d", random_int(0, 999999));
        
        //сохраняем код и ID в сессии
        $_SESSION['temp_user_id'] = $user_read['id'];
        $_SESSION['auth_code'] = $code;
        $_SESSION['code_expire'] = time() + 600;
        $_SESSION['login_email'] = $login;
        
        //отправка email с кодом
        $subject = 'Код подтверждения авторизации';
        $message = 'Ваш код для авторизации: ' . $code . "\r\n";
        $message .= 'Код действителен в течение 10 минут.';
        $headers = 'From: nastya28042020@yandex.ru' . "\r\n" .
                   'Reply-To: nastya28042020@yandex.ru' . "\r\n" .
                   'Content-Type: text/plain; charset=utf-8' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();
        
        if(mail($login, $subject, $message, $headers)) {
            echo "code_sent";
        } else {
            echo "mail_error";
        }
    } else {
        echo "error";
    }
} else {
    echo "error";
}
?>