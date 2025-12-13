<?php
session_start();
include("../settings/connect_datebase.php");

if(!isset($_SESSION['location_check']) || !isset($_SESSION['location_code']) || !isset($_SESSION['temp_user_id'])) {
    echo "expired";
    exit();
}

$code = $_POST['code'];
$current_time = time();

//проверяем время кода
if($current_time > $_SESSION['location_code_expire']) {
    echo "expired";
    exit();
}

//проверяем код
if($code == $_SESSION['location_code']) {
    $user_id = $_SESSION['temp_user_id'];
    $current_location = $_SESSION['current_location'];
    
    //обновляем местоположение в базе
    $current_time_db = date('Y-m-d H:i:s');
    $update_query = "UPDATE `users` SET 
                    `last_location_city` = '" . $mysqli->real_escape_string($current_location['city']) . "',
                    `last_location_country` = '" . $mysqli->real_escape_string($current_location['country']) . "',
                    `last_location_lat` = " . floatval($current_location['lat']) . ",
                    `last_location_lon` = " . floatval($current_location['lon']) . ",
                    `last_location_time` = '$current_time_db'
                    WHERE `id` = $user_id";
    
    $mysqli->query($update_query);
    
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
                      `last_activity` = '$current_time_db',
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
    unset($_SESSION['location_check']);
    unset($_SESSION['location_code']);
    unset($_SESSION['location_code_expire']);
    unset($_SESSION['current_location']);
    unset($_SESSION['distance_km']);
    unset($_SESSION['temp_user_id']);
    unset($_SESSION['auth_code']);
    unset($_SESSION['code_expire']);
    unset($_SESSION['login_email']);
    
    echo "success";
} else {
    echo "invalid";
}
?>