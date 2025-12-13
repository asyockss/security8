<?php
session_start();
include("./settings/connect_datebase.php");

//если пользователь уже авторизован, перенаправляем на нужную страницу
if (isset($_SESSION['user'])) {

    include("./check_session.php");
    if(checkActiveSession($mysqli)) {
        $user_query = $mysqli->query("SELECT `roll` FROM `users` WHERE `id` = ".$_SESSION['user']);
        $user_read = $user_query->fetch_assoc();
        
        if($user_read['roll'] == 0) {
            header("Location: user.php");
        } else if($user_read['roll'] == 1) {
            header("Location: admin.php");
        }
        exit();
    } else {
        logoutUser($mysqli);
    }
}
?>
<html>
	<head> 
		<meta charset="utf-8">
		<title> Авторизация </title>
		
		<script src="https://code.jquery.com/jquery-1.8.3.js"></script>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<div class="top-menu">
			<a href=#><img src = "img/logo1.png"/></a>
			<div class="name">
				<a href="index.php">
					<div class="subname">БЗОПАСНОСТЬ  ВЕБ-ПРИЛОЖЕНИЙ</div>
					Пермский авиационный техникум им. А. Д. Швецова
				</a>
			</div>
		</div>
		<div class="space"> </div>
		<div class="main">
			<div class="content">
				<div class = "login">
					<div class="name">Авторизация</div>
				
					<div class = "sub-name">Логин:</div>
					<input name="_login" type="text" placeholder="" onkeypress="return PressToEnter(event)"/>
					<div class = "sub-name">Пароль:</div>
					<input name="_password" type="password" placeholder="" onkeypress="return PressToEnter(event)"/>
					
					<a href="regin.php">Регистрация</a>
					<br><a href="recovery.php">Забыли пароль?</a>
					<input type="button" class="button" value="Войти" onclick="LogIn()"/>
					<img src = "img/loading.gif" class="loading"/>
				</div>
				
				<div class="footer">
					© КГАПОУ "Авиатехникум", 2020
					<a href=#>Конфиденциальность</a>
					<a href=#>Условия</a>
				</div>
			</div>
		</div>
		
		<script>
			function LogIn() {
				var loading = document.getElementsByClassName("loading")[0];
				var button = document.getElementsByClassName("button")[0];
				
				var _login = document.getElementsByName("_login")[0].value;
				var _password = document.getElementsByName("_password")[0].value;
				
				if(_login == "" || _password == "") {
					alert("Заполните все поля");
					return;
				}
				
				loading.style.display = "block";
				button.className = "button_diactive";
				
				var data = new FormData();
				data.append("login", _login);
				data.append("password", _password);
				
				$.ajax({
					url: 'ajax/login_user.php',
					type: 'POST',
					data: data,
					cache: false,
					dataType: 'html',
					processData: false,
					contentType: false,
					success: function (_data) {
					console.log("Ответ сервера: " + _data);
					
					if(_data == "code_sent") {
						//перенаправляем на страницу ввода кода
						window.location.href = "verify_code.php";
					} else if(_data == "already_logged_in") {
						//пользователь уже авторизован в другом месте
						loading.style.display = "none";
						button.className = "button";
						
						var confirmLogout = confirm("Вы уже авторизованы в другом браузере/устройстве. " +
												"Хотите завершить предыдущую сессию и войти здесь?\n\n" +
												"Если вы выберете 'Отмена', вход будет невозможен.");
						
						if(confirmLogout) {
							// Запрос на принудительный выход из другой сессии
							forceLogoutAndLogin(_login, _password);
						}
					} else if(_data == "mail_error") {
						loading.style.display = "none";
						button.className = "button";
						alert("Ошибка отправки email. Попробуйте позже.");
					} else if(_data == "error") {
						loading.style.display = "none";
						button.className = "button";
						alert("Логин или пароль неверный.");
					} else {
						loading.style.display = "none";
						button.className = "button";
						alert("Неизвестная ошибка: " + _data);
					}
				},
					error: function() {
						console.log('Системная ошибка!');
						loading.style.display = "none";
						button.className = "button";
						alert("Системная ошибка!");
					}
				});
			}

			function forceLogoutAndLogin(login, password) {
				var loading = document.getElementsByClassName("loading")[0];
				var button = document.getElementsByClassName("button")[0];
				
				loading.style.display = "block";
				button.className = "button_diactive";
				
				var data = new FormData();
				data.append("login", login);
				data.append("password", password);
				data.append("force", "true"); //флаг принудительного входа
				
				$.ajax({
					url: 'ajax/force_login.php', //
					type: 'POST',
					data: data,
					cache: false,
					dataType: 'html',
					processData: false,
					contentType: false,
					success: function (response) {
					loading.style.display = 'none';
					
					if(response == "redirect_user") {
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
					} else if(response == "password_expired") {
						window.location.href = 'change_password.php';
					} else if(response == "location_check_required") {
						window.location.href = 'verify_location.php';
					} else {
						errorDiv.innerText = 'Ошибка: ' + response;
						errorDiv.style.display = 'block';
					}
				},
					error: function() {
						loading.style.display = "none";
						button.className = "button";
						alert("Системная ошибка!");
					}
				});
			}
			
			function PressToEnter(e) {
				if (e.keyCode == 13) {
					var _login = document.getElementsByName("_login")[0].value;
					var _password = document.getElementsByName("_password")[0].value;
					
					if(_password != "") {
						if(_login != "") {
							LogIn();
						}
					}
				}
			}
			
		</script>
	</body>
</html>