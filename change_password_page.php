<?php
session_start();
include("./settings/config.php");

if(!isset($_SESSION['password_expired']) || !isset($_SESSION['temp_user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE HTML>
<html>
    <head> 
        <meta charset="utf-8">
        <title>Смена пароля</title>
        <script src="https://code.jquery.com/jquery-1.8.3.js"></script>
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
                <div class = "login">
                    <div class="name">Смена пароля</div>
                    <div class="warning">
                        ⚠️ <strong>Ваш пароль истек!</strong><br>
                        По правилам безопасности пароль необходимо менять каждые <?php echo PASSWORD_EXPIRE_DAYS; ?> дней.
                    </div>
                    
                    <div class = "sub-name">Новый пароль:</div>
                    <input id="new_password" type="password" placeholder="Введите новый пароль"/>
                    <div style="font-size: 12px; margin: 5px 0;">
                        Требования: минимум 8 символов, одна заглавная буква, одна цифра, один специальный символ
                    </div>
                    
                    <div class = "sub-name">Подтвердите пароль:</div>
                    <input id="confirm_password" type="password" placeholder="Повторите новый пароль"/>
                    
                    <input type="button" class="button" value="Сменить пароль" onclick="changePassword()"/>
                    <img src = "img/loading.gif" class="loading" style="display: none;"/>
                    
                    <div id="errorMessage" style="color: red; margin-top: 10px; display: none;"></div>
                    <div id="successMessage" style="color: green; margin-top: 10px; display: none;"></div>
                </div>
            </div>
        </div>
        
        <script>
            function changePassword() {
                var newPass = document.getElementById('new_password').value;
                var confirmPass = document.getElementById('confirm_password').value;
                var errorDiv = document.getElementById('errorMessage');
                var successDiv = document.getElementById('successMessage');
                var loading = document.getElementsByClassName('loading')[0];
                
                errorDiv.style.display = 'none';
                successDiv.style.display = 'none';
                
                if(newPass === '' || confirmPass === '') {
                    errorDiv.innerText = 'Заполните все поля';
                    errorDiv.style.display = 'block';
                    return;
                }
                
                if(newPass !== confirmPass) {
                    errorDiv.innerText = 'Пароли не совпадают';
                    errorDiv.style.display = 'block';
                    return;
                }
                
                // Проверка сложности пароля
                var regex = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
                if(!regex.test(newPass)) {
                    errorDiv.innerText = 'Пароль не соответствует требованиям безопасности';
                    errorDiv.style.display = 'block';
                    return;
                }
                
                loading.style.display = 'block';
                
                var data = new FormData();
                data.append("new_password", newPass);
                data.append("confirm_password", confirmPass);
                
                $.ajax({
                    url: 'ajax/change_password.php',
                    type: 'POST',
                    data: data,
                    cache: false,
                    dataType: 'html',
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        loading.style.display = 'none';
                        
                        if(response == "success") {
                            successDiv.innerText = 'Пароль успешно изменен! Перенаправление...';
                            successDiv.style.display = 'block';
                            
                            setTimeout(function() {
                                window.location.href = 'user.php';
                            }, 2000);
                        } else {
                            errorDiv.innerText = 'Ошибка при смене пароля';
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
        </script>
    </body>
</html>