<?php
session_start();
include("../settings/connect_datebase.php");

$login = $_POST['login'];
$password = $_POST['password'];
$force = isset($_POST['force']) && $_POST['force'] == 'true';

$login = $mysqli->real_escape_string($login);

//ищем пользователя
$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='$login' LIMIT 1");

if($query_user->num_rows == 1) {
    $user_read = $query_user->fetch_assoc();
    
    if(password_verify($password, $user_read['password'])){
        
        if($force) {
            //очищаем предыдущую сессию при принудительном входе
            $mysqli->query("UPDATE `users` SET `session_token` = NULL, `last_activity` = NULL WHERE `id` = {$user_read['id']}");
        } else {
            //проверяем активную сессию
            $current_time = time();
            $last_activity_time = 0;
            
            if(!empty($user_read['last_activity'])) {
                $last_activity_time = strtotime($user_read['last_activity']);
            }
            
            //если была активность в последние 30 минут, считаем сессию активной
            if(!empty($user_read['session_token']) && ($current_time - $last_activity_time) < 1800) {
                //есть активная сессия
                echo "already_logged_in";
                exit();
            }
        }
        
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