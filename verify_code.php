<?php
session_start();
if(!isset($_SESSION['temp_user_id'])) {
    header("Location: login.php");
    exit();
}

//получаем информацию о текущем устройстве
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$browser_info = '';

//определяем браузер
if(strpos($user_agent, 'Chrome') !== false) {
    $browser_info = 'Google Chrome';
} elseif(strpos($user_agent, 'Firefox') !== false) {
    $browser_info = 'Mozilla Firefox';
} elseif(strpos($user_agent, 'Safari') !== false) {
    $browser_info = 'Apple Safari';
} elseif(strpos($user_agent, 'Edge') !== false) {
    $browser_info = 'Microsoft Edge';
} elseif(strpos($user_agent, 'Opera') !== false) {
    $browser_info = 'Opera';
} else {
    $browser_info = 'Неизвестный браузер';
}

//определяем ОС
$os_info = '';
if(strpos($user_agent, 'Windows') !== false) {
    $os_info = 'Windows';
} elseif(strpos($user_agent, 'Mac') !== false) {
    $os_info = 'macOS';
} elseif(strpos($user_agent, 'Linux') !== false) {
    $os_info = 'Linux';
} elseif(strpos($user_agent, 'Android') !== false) {
    $os_info = 'Android';
} elseif(strpos($user_agent, 'iOS') !== false) {
    $os_info = 'iOS';
} else {
    $os_info = 'Неизвестная ОС';
}

$device_info = "$browser_info на $os_info";
?>
<!DOCTYPE HTML>
<html>
    <head> 
        <meta charset="utf-8">
        <title>Подтверждение авторизации</title>
        <script src="https://code.jquery.com/jquery-1.8.3.js"></script>
        <link rel="stylesheet" href="style.css">
        <style>
            .device-info {
                background-color: #f0f0f0;
                padding: 10px;
                border-radius: 5px;
                margin: 10px 0;
                font-size: 14px;
            }
            .warning {
                background-color: #fff3cd;
                border: 1px solid #ffeaa7;
                color: #856404;
                padding: 10px;
                border-radius: 5px;
                margin: 10px 0;
                font-size: 14px;
            }
        </style>
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
                <div class = "login">
                    <div class="name">Подтверждение авторизации</div>
                    
                    <div class="device-info">
                        <strong>Информация об устройстве:</strong><br>
                        Браузер и ОС: <?php echo htmlspecialchars($device_info); ?><br>
                        IP-адрес: <?php echo htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'Неизвестен'); ?>
                    </div>
                    
                    <div class="warning">
                        ⚠️ Внимание: система позволяет быть авторизованным только на одном устройстве одновременно.
                        Если вы войдете здесь, все другие ваши сессии будут завершены.
                    </div>
                    
                    <div class="sub-name">На email <strong><?php echo $_SESSION['login_email']; ?></strong> отправлен код подтверждения</div>
                    
                    <div class = "sub-name">Введите 6-значный код:</div>
                    <input id="code" type="text" class="code-input" maxlength="6" placeholder="000000" onkeypress="return handleCodeInput(event)"/>
                    
                    <div class="timer" id="timer">Код действителен: 10:00</div>
                    
                    <div style="margin-top: 20px;">
                        <a href="javascript:void(0)" class="resend-link" id="resendLink" onclick="resendCode()">Отправить код повторно</a>
                        <span id="resendTimer" style="display: none;">(доступно через <span id="resendTime">60</span> сек.)</span>
                    </div>
                    
                    <input type="button" class="button" value="Подтвердить" onclick="verifyCode()"/>
                    <input type="button" class="button" value="Назад" onclick="window.location='login.php'" style="background: #ccc; margin-left: 10px;"/>
                    
                    <img src = "img/loading.gif" class="loading" style="display: none;"/>
                    
                    <div id="errorMessage" style="color: red; margin-top: 10px; display: none;"></div>
                </div>
                
                <div class="footer">
                    © КГАПОУ "Авиатехникум", 2020
                    <a href=#>Конфиденциальность</a>
                    <a href=#>Условия</a>
                </div>
            </div>
        </div>
        
        <script>
            let countdown = 600;
            let canResend = false;
            let resendCountdown = 60;
            
            //таймер жизни кода
            function updateTimer() {
                if(countdown > 0) {
                    countdown--;
                    let minutes = Math.floor(countdown / 60);
                    let seconds = countdown % 60;
                    document.getElementById('timer').innerText = `Код действителен: ${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                    
                    if(countdown <= 0) {
                        document.getElementById('timer').innerText = 'Время действия кода истекло';
                        document.getElementById('timer').style.color = 'red';
                    }
                }
            }
            
            //таймер повторной отправки
            function updateResendTimer() {
                if(resendCountdown > 0) {
                    resendCountdown--;
                    document.getElementById('resendTime').innerText = resendCountdown;
                    
                    if(resendCountdown <= 0) {
                        canResend = true;
                        document.getElementById('resendLink').classList.remove('disabled');
                        document.getElementById('resendTimer').style.display = 'none';
                    }
                }
            }
            
            setInterval(updateTimer, 1000);
            setInterval(updateResendTimer, 1000);
            
            function handleCodeInput(e) {
                if(e.keyCode < 48 || e.keyCode > 57) {
                    if(e.keyCode != 8) { //разрешаем backspace
                        e.preventDefault();
                        return false;
                    }
                }
                
                return true;
            }
            
            function verifyCode() {
                var code = document.getElementById('code').value;
                var loading = document.getElementsByClassName('loading')[0];
                var errorDiv = document.getElementById('errorMessage');
                
                if(code.length != 6) {
                    errorDiv.innerText = 'Введите 6-значный код';
                    errorDiv.style.display = 'block';
                    return;
                }
                
                loading.style.display = 'block';
                errorDiv.style.display = 'none';
                
                var data = new FormData();
                data.append("code", code);
                
                $.ajax({
                    url: 'ajax/verify_code.php',
                    type: 'POST',
                    data: data,
                    cache: false,
                    dataType: 'html',
                    processData: false,
                    contentType: false,
                    success: function (response) {
                    loading.style.display = 'none';
                    
                    if(response == "redirect_user") {
                        //авторизация успешна - сразу перенаправляем
                        window.location.href = 'user.php';
                    } else if(response == "redirect_admin") {
                        window.location.href = 'admin.php';
                    } else if(response == "redirect_index") {
                        window.location.href = 'index.php';
                    } else if(response == "invalid") {
                        errorDiv.innerText = 'Неверный код';
                        errorDiv.style.display = 'block';
                        document.getElementById('code').value = '';
                        document.getElementById('code').focus();
                    } else if(response == "expired") {
                        errorDiv.innerText = 'Время действия кода истекло. Запросите новый код.';
                        errorDiv.style.display = 'block';
                    } else if(response == "session_expired") {
                        errorDiv.innerText = 'Сессия истекла. Пожалуйста, войдите снова.';
                        errorDiv.style.display = 'block';
                        setTimeout(function() {
                            window.location.href = 'login.php';
                        }, 2000);
                    } else {
                        errorDiv.innerText = 'Ошибка: ' + response;
                        errorDiv.style.display = 'block';
                    }
                },
                    error: function() {
                        loading.style.display = 'none';
                        errorDiv.innerText = 'Системная ошибка';
                        errorDiv.style.display = 'block';
                    }
                });
            }
            
            function resendCode() {
                if(!canResend) return;
                
                var loading = document.getElementsByClassName('loading')[0];
                var errorDiv = document.getElementById('errorMessage');
                
                loading.style.display = 'block';
                errorDiv.style.display = 'none';
                
                var data = new FormData();
                data.append("login", '<?php echo $_SESSION['login_email']; ?>');
                
                $.ajax({
                    url: 'ajax/resend_code.php',
                    type: 'POST',
                    data: data,
                    cache: false,
                    dataType: 'html',
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        loading.style.display = 'none';
                        
                        if(response == "code_resent") {
                            errorDiv.innerText = 'Новый код отправлен на вашу почту';
                            errorDiv.style.color = 'green';
                            errorDiv.style.display = 'block';
                            
                            //сброс таймеров
                            canResend = false;
                            resendCountdown = 60;
                            countdown = 600;
                            document.getElementById('resendLink').classList.add('disabled');
                            document.getElementById('resendTimer').style.display = 'inline';
                            document.getElementById('resendTime').innerText = '60';
                            document.getElementById('timer').style.color = '';
                            
                            //очистка поля ввода
                            document.getElementById('code').value = '';
                            document.getElementById('code').focus();
                        } else if(response == "session_expired") {
                            errorDiv.innerText = 'Сессия истекла. Пожалуйста, войдите снова.';
                            errorDiv.style.display = 'block';
                            setTimeout(function() {
                                window.location.href = 'login.php';
                            }, 2000);
                        }
                    },
                    error: function() {
                        loading.style.display = 'none';
                        errorDiv.innerText = 'Ошибка при отправке кода';
                        errorDiv.style.display = 'block';
                    }
                });
            }
            
            //блокировка повторной отправки сразу
            document.getElementById('resendLink').classList.add('disabled');
            document.getElementById('resendTimer').style.display = 'inline';
        </script>
    </body>
</html>