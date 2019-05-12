<?php 

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once('functions.php');
require_once('helpers.php');

session_start();

// Если сценарий был вызван отправкой формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	// В массиве $_POST содержатся все данные из формы
	$form = $_POST;
	$required = ['email', 'password'];
	$errors = [];

	foreach ($required as $key) {
		if (empty($_POST[$key])) {
            $errors[$key] = 'Это поле надо заполнить';
		}
	}

	// Соединение с БД
	$connection_resourse = connect_db();

	$email = mysqli_real_escape_string($connection_resourse, $form['email']);
	$sql = "SELECT * FROM users WHERE email = '$email'";
	$res = mysqli_query($connection_resourse, $sql);

	$user = $res ? mysqli_fetch_array($res, MYSQLI_ASSOC) : null;

	if (!count($errors) and $user) {
		// Сравниваем хеши паролей и если совпадают то записываем в сессию
		if (password_verify($form['password'], $user['password'])) {
			$_SESSION['user'] = $user;
		}
		else {
			$errors['password'] = 'Неверный пароль';
		}
	}
	else {
		$errors['email'] = 'Такой пользователь не найден';
	}

	if (count($errors)) {
		$page_content = include_template('auth.php', [
			'form' => $form,
			'errors' => $errors
		]);

		$layout = include_template('layout.php', [
			'content' => $page_content,
			'page_title' => 'Авторизация'
		]);
	}

	else {
		header("Location: /index.php");
		exit();
	}
}

else {

	if (isset($_SESSION['user'])) {
        header("Location: /index.php");
		exit();
    }
    else {
        $page_content = include_template('auth.php', []);
    }

	$layout = include_template('layout.php', [
		'content' => $page_content,
		'page_title' => 'Авторизация'
	]);
}

print($layout);