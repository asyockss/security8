<?php
session_start();
include("./settings/connect_datebase.php");
include("./check_session.php");

//проверяем активную сессию
if(!checkActiveSession($mysqli)) {
    logoutUser($mysqli);
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE HTML>
<html>
    <head> 
        <meta charset="utf-8">
        <title>Информация о сессии</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="top-menu">
            <a href=#><img src = "img/logo1.png"/></a>
            <div class="name">
                <a href="index.php">
                    <div class="subname">БЗОПАСНОСТЬ ВЕБ-ПРИЛОЖЕНИЙ</div>
                    Пермский авиационный техникум им. А. Д. Швецова
                </a>
            </div>
        </div>
        <div class="space"> </div>
        <div class="main">
            <div class="content">
                <div class="name">Информация о вашей сессии</div>
                
                <?php
                $user_id = $_SESSION['user'];
                $query = $mysqli->query("SELECT * FROM `users` WHERE `id` = $user_id LIMIT 1");
                $user_data = $query->fetch_assoc();
                
                echo '<div class="session-info">';
                echo '<p><strong>Логин:</strong> ' . htmlspecialchars($user_data['login']) . '</p>';
                echo '<p><strong>Текущее устройство:</strong> ' . htmlspecialchars($user_data['user_agent'] ?? 'Неизвестно') . '</p>';
                echo '<p><strong>IP-адрес устройства:</strong> ' . htmlspecialchars($user_data['ip_address'] ?? 'Неизвестен') . '</p>';
                echo '<p><strong>Последняя активность:</strong> ' . htmlspecialchars($user_data['last_activity'] ?? 'Неизвестно') . '</p>';
                
                //время до автоматического выхода
                $last_activity_time = strtotime($user_data['last_activity']);
                $current_time = time();
                $time_left = 1800 - ($current_time - $last_activity_time); //30 минут - прошедшее время
                
                if($time_left > 0) {
                    $minutes = floor($time_left / 60);
                    $seconds = $time_left % 60;
                    echo '<p><strong>Сессия истечет через:</strong> ' . $minutes . ' мин ' . $seconds . ' сек</p>';
                } else {
                    echo '<p><strong>Статус сессии:</strong> <span style="color: red;">Истекла</span></p>';
                }
                
                echo '</div>';
                ?>
                
                <div style="margin-top: 20px;">
                    <a href="user.php" class="button">Вернуться в личный кабинет</a>
                    <a href="ajax/logout.php" class="button" style="background: #dc3545;" onclick="return confirm('Завершить сессию на всех устройствах?')">Завершить все сессии</a>
                </div>
                
                <div class="footer">
                    © КГАПОУ "Авиатехникум", 2020
                    <a href=#>Конфиденциальность</a>
                    <a href=#>Условия</a>
                </div>
            </div>
        </div>
    </body>
</html>