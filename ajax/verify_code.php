<?php
session_start();
include("./settings/connect_datebase.php");
include("./settings/config.php");

if(!isset($_SESSION['temp_user_id']) || !isset($_SESSION['auth_code'])) {
    echo "session_expired";
    exit();
}

$code = $_POST['code'];
$current_time = time();

//проверяем, не истекло ли время кода
if($current_time > $_SESSION['code_expire']) {
    echo "expired";
    exit();
}

//проверяем код
if($code == $_SESSION['auth_code']) {
    //получаем информацию о пользователе из базы
    $user_id = $_SESSION['temp_user_id'];
    
    $query = $mysqli->query("SELECT * FROM `users` WHERE `id` = $user_id LIMIT 1");
    if($query->num_rows == 1) {
        $user = $query->fetch_assoc();
        
        //проверяем истечение пароля
        include("./includes/geolocation.php");
        $password_expired = checkPasswordExpiry($user['password_changed_at']);
        if($password_expired) {
            $_SESSION['password_expired'] = true;
            $_SESSION['temp_user_id'] = $user_id;
            echo "password_expired";
            exit();
        }
        
        //получаем текущее местоположение
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $current_location = getLocationByIP($ip_address);
        
        //проверяем смену местоположения
        $location_check_required = false;
        if(!empty($user['last_location_lat']) && !empty($user['last_location_lon']) && $current_location) {
            $distance = calculateDistance(
                $user['last_location_lat'], 
                $user['last_location_lon'],
                $current_location['lat'], 
                $current_location['lon']
            );
            
            if($distance > LOCATION_CHECK_DISTANCE_KM) {
                $location_check_required = true;
                //генерируем код для проверки местоположения
                $location_code = sprintf("%06d", random_int(0, 999999));
                
                $_SESSION['location_check'] = true;
                $_SESSION['location_code'] = $location_code;
                $_SESSION['location_code_expire'] = time() + 300; // 5 минут
                $_SESSION['current_location'] = $current_location;
                $_SESSION['distance_km'] = round($distance, 2);
                
                //отправляем email с кодом проверки местоположения
                $subject = 'Проверка нового местоположения';
                $message = 'Обнаружен вход с нового местоположения.' . "\r\n";
                $message .= 'Расстояние от предыдущего входа: ' . round($distance, 2) . ' км' . "\r\n";
                $message .= 'Город: ' . $current_location['city'] . ', ' . $current_location['country'] . "\r\n";
                $message .= 'Код для подтверждения: ' . $location_code . "\r\n";
                $message .= 'Код действителен 5 минут.';
                $headers = 'From: nastya28042020@yandex.ru' . "\r\n" .
                           'Content-Type: text/plain; charset=utf-8';
                
                mail($user['login'], $subject, $message, $headers);
                
                echo "location_check_required";
                exit();
            }
        }
        
        //генерируем уникальный токен сессии
        $session_token = bin2hex(random_bytes(32));
        
        //обновляем информацию о сессии и местоположении
        $current_time_db = date('Y-m-d H:i:s');
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $update_query = "UPDATE `users` SET 
                        `session_token` = '" . $mysqli->real_escape_string($session_token) . "',
                        `last_activity` = '$current_time_db',
                        `user_agent` = '" . $mysqli->real_escape_string($user_agent) . "',
                        `ip_address` = '$ip_address'";
        
        //сохраняем местоположение
        if($current_location) {
            $update_query .= ", `last_location_city` = '" . $mysqli->real_escape_string($current_location['city']) . "'";
            $update_query .= ", `last_location_country` = '" . $mysqli->real_escape_string($current_location['country']) . "'";
            $update_query .= ", `last_location_lat` = " . floatval($current_location['lat']);
            $update_query .= ", `last_location_lon` = " . floatval($current_location['lon']);
            $update_query .= ", `last_location_time` = '$current_time_db'";
        }
        
        $update_query .= " WHERE `id` = $user_id";
        
        $mysqli->query($update_query);
        
        //устанавливаем сессию пользователя
        $_SESSION['user'] = $user['id'];
        $_SESSION['user_login'] = $user['login'];
        $_SESSION['user_role'] = $user['roll'];
        $_SESSION['session_token'] = $session_token;
        
        //очищаем временные данные
        unset($_SESSION['temp_user_id']);
        unset($_SESSION['auth_code']);
        unset($_SESSION['code_expire']);
        unset($_SESSION['login_email']);
        
        //возвращаем роль пользователя
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