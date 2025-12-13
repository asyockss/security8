<?php
session_start();
include("../settings/connect_datebase.php");

if(isset($_SESSION['user'])) {
    $user_id = $_SESSION['user'];
    //очищаем инфу о сессии в бд
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

echo "success";
?>