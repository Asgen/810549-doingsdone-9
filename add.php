<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once('functions.php');
require_once('helpers.php');

session_start();

if (isset($_SESSION['user'])) {
	// Соединение с БД
	$connection_resourse = connect_db();

	// Запрос на получение списка проектов для конкретного пользователя
	$projects = get_projects($connection_resourse, $_SESSION['user']['id']);


	// Если сценарий был вызван отправкой формы
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		// В массиве $_POST содержатся все данные из формы
		$task = $_POST;
		$required = ['name', 'project'];
		$dict = ['name' => 'Название', 'project' => 'Проект', 'file' => 'Файл', 'date' => 'Дата'];
		$errors = [];

		foreach ($required as $key) {
			if (empty($_POST[$key])) {
	            $errors[$key] = 'Это поле надо заполнить';
			}
		}

		if (!empty($task['date'])) {
			if (!is_date_valid($task['date']) || $task['date'] < date('Y-m-d')) {
				$errors['date'] = 'Введите корректную дату';
			}
		}

		// Проверим, был ли загружен файл
		if (isset($_FILES['file'])) {
			$tmp_name = $_FILES['file']['tmp_name'];
			$path = $_FILES['file']['name'];
			move_uploaded_file($tmp_name, __DIR__ . '/' . $path);
			$file = __DIR__ . '/' . $path;
		}

		// Выбран существующий ли проект
		$wrong_proj = true;
		foreach ($projects as $value) {
			if ($value['project_id'] === $task['project']) {
				$wrong_proj = false;
				break;
			}
		}
		if ($wrong_proj) {
			$errors['project'] = 'Выберите существующий проект';
		}

		// Если ошибок нет
		if (!count($errors)) {
			// Записываем в БД
			// Формируем запрос
			$user_id = $_SESSION['user']['id'];
			$sql = "INSERT INTO tasks SET name = ?, project_id = ?, user_id = $user_id, file = ?";

			// Подготавливаем шаблон запроса
			$stmt = mysqli_prepare($connection_resourse, $sql);

			// Привязываем к маркеру значение переменных.
			$name = $task['name'];
			$project_id = $task['project'];

			if (!empty($task['date'])) {
				$sql .= ", deadline = ?";
				$stmt = mysqli_prepare($connection_resourse, $sql);
				$deadline = $task['date'];
				mysqli_stmt_bind_param($stmt, 'ssss', $name, $project_id, $file, $deadline);
			} else {
				mysqli_stmt_bind_param($stmt, 'sss', $name, $project_id, $file);
			}

			// Выполняем подготовленный запрос.
			$result = mysqli_stmt_execute($stmt);

			// Если запрос неудачен, то выводим ошибку
			if (!$result) {
			    print("Ошибка записи в БД. Запрос $sql " . mysqli_error($connection_resourse));
			    die();
			}

			header("Location: /");
			die();
		}

		// Подключение шаблона с ошибками
		$page_content = include_template('add.php', [
		  	'projects' => $projects,
			'task' => $task,
			'errors' => $errors
		]);

	} else {
		// Подключение шаблона
		$page_content = include_template('add.php', [
		  	'projects' => $projects
		]);
	}

	// Поключение лэйаута с включением в него шаблона
	$layout_content = include_template('layout.php', [
	    'projects' => $projects,
	    'content' => $page_content,
	    'active_project' => $choosen_project ?? '',
	    'page_title' => 'Добавление задачи ',
	    'user' => $_SESSION['user']
	]);

	print($layout_content);
} else {
	header("Location: /");
	die();
}
