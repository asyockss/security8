<?php
//настройки системы
if (!defined('PASSWORD_EXPIRE_DAYS')) {
    define('PASSWORD_EXPIRE_DAYS', 1); //истекает через 1 день
}

if (!defined('LOCATION_CHECK_DISTANCE_KM')) {
    define('LOCATION_CHECK_DISTANCE_KM', 100);
}

if (!defined('GEO_API_KEY')) {
    define('GEO_API_KEY', ''); //API ключ для сервиса определения местоположения
}

if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 1800);
}
?>