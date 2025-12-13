<?php
session_start();
if(!isset($_SESSION['location_check']) || !isset($_SESSION['temp_user_id'])) {
    header("Location: login.php");
    exit();
}

$location = $_SESSION['current_location'] ?? [];
$distance = $_SESSION['distance_km'] ?? 0;
?>
<!DOCTYPE HTML>
<html>
    <head> 
        <meta charset="utf-8">
        <title>Проверка нового местоположения</title>
        <script src="https://code.jquery.com/jquery-1.8.3.js"></script>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="top-menu">
            <a href=#><img src = "./img/logo1.png"/></a>
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
                    <div class="name">Проверка нового местоположения</div>
                    
                    <div class="warning">
                        ⚠️ <strong>Обнаружен вход с нового местоположения!</strong><br><br>
                        <strong>Местоположение:</strong> <?php echo htmlspecialchars($location['city'] . ', ' . $location['country']); ?><br>
                        <strong>Расстояние от предыдущего входа:</strong> <?php echo htmlspecialchars($distance); ?> км<br>
                        <strong>IP-адрес:</strong> <?php echo htmlspecialchars($location['ip'] ?? 'Неизвестен'); ?>
                    </div>
                    
                    <div class="sub-name">Введите код, отправленный на вашу почту:</div>
                    <input id="locationCode" type="text" maxlength="6" placeholder="000000" onkeypress="return handleCodeInput(event)"/>
                    
                    <div class="timer" id="timer">Код действителен: 05:00</div>
                    
                    <div style="margin-top: 20px;">
                        <input type="button" class="button" value="Подтвердить" onclick="verifyLocationCode()"/>
                        <input type="button" class="button" value="Отмена" onclick="cancelLocationCheck()" style="background: #ccc; margin-left: 10px;"/>
                    </div>
                    
                    <img src = "img/loading.gif" class="loading" style="display: none;"/>
                    
                    <div id="errorMessage" style="color: red; margin-top: 10px; display: none;"></div>
                    <div id="successMessage" style="color: green; margin-top: 10px; display: none;"></div>
                </div>
            </div>
        </div>
        
        <script>
            let countdown = 300; // 5 минут
            
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
            
            setInterval(updateTimer, 1000);
            
            function handleCodeInput(e) {
                if(e.keyCode < 48 || e.keyCode > 57) {
                    if(e.keyCode != 8) {
                        e.preventDefault();
                        return false;
                    }
                }
                return true;
            }
            
            function verifyLocationCode() {
                var code = document.getElementById('locationCode').value;
                var loading = document.getElementsByClassName('loading')[0];
                var errorDiv = document.getElementById('errorMessage');
                var successDiv = document.getElementById('successMessage');
                
                if(code.length != 6) {
                    errorDiv.innerText = 'Введите 6-значный код';
                    errorDiv.style.display = 'block';
                    successDiv.style.display = 'none';
                    return;
                }
                
                loading.style.display = 'block';
                errorDiv.style.display = 'none';
                successDiv.style.display = 'none';
                
                var data = new FormData();
                data.append("code", code);
                
                $.ajax({
                    url: 'ajax/verify_location.php',
                    type: 'POST',
                    data: data,
                    cache: false,
                    dataType: 'html',
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        loading.style.display = 'none';
                        
                        if(response == "success") {
                            successDiv.innerText = 'Местоположение подтверждено! Перенаправление...';
                            successDiv.style.display = 'block';
                            
                            setTimeout(function() {
                                window.location.href = 'user.php';
                            }, 2000);
                        } else if(response == "invalid") {
                            errorDiv.innerText = 'Неверный код';
                            errorDiv.style.display = 'block';
                        } else if(response == "expired") {
                            errorDiv.innerText = 'Время действия кода истекло. Пожалуйста, войдите снова.';
                            errorDiv.style.display = 'block';
                            setTimeout(function() {
                                window.location.href = 'login.php';
                            }, 3000);
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
            
            function cancelLocationCheck() {
                if(confirm('Вы уверены? Это приведет к завершению сессии.')) {
                    window.location.href = 'ajax/logout.php';
                }
            }
        </script>
    </body>
</html>