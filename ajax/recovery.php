<?php
session_start();
include("../settings/connect_datebase.php");

$login = $_POST['login'];

//ищем пользователя
$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='".$mysqli->real_escape_string($login)."'");

$id = -1;
if($user_read = $query_user->fetch_row()) {
    //создаём новый пароль
    $id = $user_read[0];
}

function PasswordGeneration() {
    $chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
    $max = 10;
    $size = StrLen($chars)-1;
    $password = "";
    
    while($max--) {
        $password .= $chars[rand(0,$size)];
    }
    
    return $password;
}

if($id != 0) {
    //обновляем пароль
    $password = PasswordGeneration();
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    //обновляем пароль и дату изменения
    $current_time = date('Y-m-d H:i:s');
    $mysqli->query("UPDATE `users` SET `password`='".$mysqli->real_escape_string($hashed_password)."', `password_changed_at`='".$current_time."' WHERE `login` = '".$mysqli->real_escape_string($login)."'");
    
    //отправляем на почту
    $subject = 'Безопасность web-приложений КГАПОУ "Авиатехникум"';
    $message = "Ваш пароль был только что изменён. Новый пароль: ".$password;
    $headers = 'From: nastya28042020@yandex.ru' . "\r\n" .
               'Content-Type: text/plain; charset=utf-8';
    
    mail($login, $subject, $message, $headers);
}

echo $id;
?>