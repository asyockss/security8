<?php
session_start();
include("../settings/connect_datebase.php");

if(!isset($_SESSION['password_expired']) || !isset($_SESSION['temp_user_id'])) {
    echo "error";
    exit();
}

$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];
$user_id = $_SESSION['temp_user_id'];

//проверяем совпадение паролей
if($new_password !== $confirm_password) {
    echo "error";
    exit();
}

//проверяем сложность пароля
if(!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $new_password)) {
    echo "error";
    exit();
}

//хешируем пароль
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$current_time = date('Y-m-d H:i:s');

//обновляем пароль
$update_query = "UPDATE `users` SET 
                `password` = '" . $mysqli->real_escape_string($hashed_password) . "',
                `password_changed_at` = '$current_time'
                WHERE `id` = $user_id";
                
if($mysqli->query($update_query)) {
    //завершаем процесс авторизации
    $query = $mysqli->query("SELECT * FROM `users` WHERE `id` = $user_id LIMIT 1");
    $user = $query->fetch_assoc();
    
    //генерируем токен сессии
    $session_token = bin2hex(random_bytes(32));
    
    //обновляем сессию
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    $update_session = "UPDATE `users` SET 
                      `session_token` = '" . $mysqli->real_escape_string($session_token) . "',
                      `last_activity` = '$current_time',
                      `user_agent` = '" . $mysqli->real_escape_string($user_agent) . "',
                      `ip_address` = '$ip_address'
                      WHERE `id` = $user_id";
    
    $mysqli->query($update_session);
    
    //устанавливаем сессию
    $_SESSION['user'] = $user['id'];
    $_SESSION['user_login'] = $user['login'];
    $_SESSION['user_role'] = $user['roll'];
    $_SESSION['session_token'] = $session_token;
    
    //очищаем временные данные
    unset($_SESSION['password_expired']);
    unset($_SESSION['temp_user_id']);
    unset($_SESSION['auth_code']);
    unset($_SESSION['code_expire']);
    unset($_SESSION['login_email']);
    
    echo "success";
} else {
    echo "error";
}
?>