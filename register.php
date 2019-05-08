<?php

require_once('functions.php');
require_once('helpers.php');

// Если сценарий был вызван отправкой формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	// В массиве $_POST содержатся все данные из формы
	$form = $_POST;
	$required = ['email', 'password', 'name'];
	$errors = [];

	foreach ($required as $key) {
		if (empty($_POST[$key])) {
            $errors[$key] = 'Это поле надо заполнить';
		}
	}

	// Проверка пароля
	if (strlen($form['password']) < 6) {
		$errors['password'] = "Минимальная длина пароля 6 символов";
	}

	// Проверка email
	if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
    	$errors['email'] = "E-mail адрес указан неверно.\n";
	}

	// Проверка пользователя с введеным email
	if (!count($errors)) {
		// Соединение с БД
		$connection_resourse = connect_db();

        $email = mysqli_real_escape_string($connection_resourse, $form['email']);
        $sql = "SELECT id FROM users WHERE email = '$email'";
        $res = mysqli_query($connection_resourse, $sql);

         if (mysqli_num_rows($res) > 0) {
            $errors['email'] = 'Пользователь с этим email уже зарегистрирован';
        }
    }

	// Если ошибок нет
	if (!count($errors)) {

		// Записываем в БД

		// Хешируем пароль
		 $password = password_hash($form['password'], PASSWORD_DEFAULT);

		// Формируем запрос
		$sql = 'INSERT INTO users (datetime_add, email, name, password) VALUES (NOW(), ?, ?, ?)';
		
		// Подготавливаем шаблон запроса
		$stmt = db_get_prepare_stmt($connection_resourse, $sql, [$email, $form['name'], $password]);	  	

		// Выполняем подготовленный запрос.
		$result = mysqli_stmt_execute($stmt);

		// Редирект на страницу входа, если пользователь был успешно добавлен в БД.
		if ($result && empty($errors)) {
		    header("Location: /");
            exit();
		}
	}

}

// Подключение шаблона
$page_content = include_template('register.php', [
	'form' => $form,
	'errors' => $errors
]);

// Поключение лэйаута
$layout_content = include_template('layout.php', [
	'content' => $page_content,
	'user_name' => 'Мистер Твикс'
]);

print($layout_content);

?>