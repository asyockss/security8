<?php
function checkActiveSession($mysqli) {
    if(!isset($_SESSION['user']) || !isset($_SESSION['session_token'])) {
        return false;
    }
    
    $user_id = $_SESSION['user'];
    $session_token = $_SESSION['session_token'];
    $current_user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    //получаем инфу о сессии из базы
    $query = $mysqli->query("SELECT `session_token`, `last_activity`, `user_agent` FROM `users` WHERE `id` = $user_id LIMIT 1");
    
    if($query->num_rows == 1) {
        $user_data = $query->fetch_assoc();

        if(empty($user_data['session_token']) || $user_data['session_token'] !== $session_token) {
            //токен не совпадает или пустой - сессия была перезаписана другим устройством
            return false;
        }
        
        //проверяем время активности (30 минут)
        $last_activity_time = strtotime($user_data['last_activity']);
        $current_time = time();
        
        if(empty($user_data['last_activity']) || ($current_time - $last_activity_time) > 1800) { // 30 минут
            return false;
        }
        
        //обновляем время последней активности
        $current_time_db = date('Y-m-d H:i:s');
        $mysqli->query("UPDATE `users` SET `last_activity` = '$current_time_db' WHERE `id` = $user_id");
        
        return true;
    }
    
    return false;
}

function logoutUser($mysqli) {
    if(isset($_SESSION['user'])) {
        $user_id = $_SESSION['user'];
        //очищаем инфу о сессии в базе
        $mysqli->query("UPDATE `users` SET `session_token` = NULL, `last_activity` = NULL WHERE `id` = $user_id");
    }
    
    //очищаем сессию
    $_SESSION = array();
    
    if(ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}
?>