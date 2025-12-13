<?php
session_start();

if(!isset($_SESSION['temp_user_id']) || !isset($_SESSION['auth_code'])) {
    echo "session_expired";
    exit();
}

$code = $_POST['code'];
$current_time = time();

//истекло ли время кода
if($current_time > $_SESSION['code_expire']) {
    echo "expired";
    exit();
}

//проверяем код
if($code == $_SESSION['auth_code']) {
    include("../settings/connect_datebase.php");
    $user_id = $_SESSION['temp_user_id'];
    
    $query = $mysqli->query("SELECT * FROM `users` WHERE `id` = $user_id LIMIT 1");
    if($query->num_rows == 1) {
        $user = $query->fetch_assoc();
        
        //токен сессии
        $session_token = bin2hex(random_bytes(32));
        
        //получаем инфу о текущем устройстве
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        
        //обновляем инфу о сессии в базе
        $current_time_db = date('Y-m-d H:i:s');
        $update_query = "UPDATE `users` SET 
                        `session_token` = '$session_token',
                        `last_activity` = '$current_time_db',
                        `user_agent` = '" . $mysqli->real_escape_string($user_agent) . "',
                        `ip_address` = '$ip_address'
                        WHERE `id` = $user_id";
        
        $mysqli->query($update_query);
        
        $_SESSION['user'] = $user['id'];
        $_SESSION['user_login'] = $user['login'];
        $_SESSION['user_role'] = $user['roll'];
        $_SESSION['session_token'] = $session_token;
        
        //клин временные данные
        unset($_SESSION['temp_user_id']);
        unset($_SESSION['auth_code']);
        unset($_SESSION['code_expire']);
        unset($_SESSION['login_email']);
        
        //роль возврат
        if($user['roll'] == 0) {
            echo "redirect_user";
        } else if($user['roll'] == 1) {
            echo "redirect_admin";
        } else {
            echo "redirect_index";
        }
    } else {
        echo "invalid";
    }
} else {
    echo "invalid";
}
?>