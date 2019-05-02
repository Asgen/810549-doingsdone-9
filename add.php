<?php

require_once('functions.php');
require_once('helpers.php');

// Подключение к MySQL
require_once('config/db.php');

// Соединение с БД
$connection_resourse = mysqli_connect($db['host'], $db['user'], $db['password'], $db['database']);

// Если ошибка соединения - показываем ее
if (!$connection_resourse) {
    print("Ошибка подключения к БД " . mysqli_connect_error());
    die();
}
mysqli_set_charset($connection_resourse, "utf8");

$projects = [];

// При успешном соединении формируем запрос к БД

// Запрос на получение списк задач
$sql = "SELECT * FROM projects";

// Подготавливаем шаблон запроса
$stmt = mysqli_prepare($connection_resourse, $sql);

// Выполняем подготовленный запрос.
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Если запрос неудачен, то выводим ошибку
if (!$result) {
    print("Ошибка в запросе к БД. Запрос $sql " . mysqli_error($connection_resourse));
    die();
}

// Если ответ получен, преобразуем его в двумерный массив и подключаем шаблон стр.
$projects = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Если сценарий был вызван отправкой формы
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// В массиве $_POST содержатся все данные из формы
	$task = $_POST;
//print_r($task['date']);
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
		move_uploaded_file($tmp_name, __DIR__ . '/index.php' . $path);
		$task['path'] = __DIR__ . '/index.php' . $path;

	}

	// Если ошибок нет
	if (!count($errors)) {

		// Записываем в БД

		// Формируем запрос
		$sql = "INSERT INTO tasks SET name = ?, project_id = ?, user_id = 1, file = ?";
		
		// Подготавливаем шаблон запроса
		$stmt = mysqli_prepare($connection_resourse, $sql);

		// Привязываем к маркеру значение переменных.
		$name = $task['name'];
		$project_id = $task['project'];
		$file = $task['path'];
		mysqli_stmt_bind_param($stmt, 'sss', $name, $project_id, $file);

		if ($task['date']) {
			$sql = $sql . ", deadline = ?";
			$stmt = mysqli_prepare($connection_resourse, $sql);
			$deadline = $task['date'];
			mysqli_stmt_bind_param($stmt, 'ssss', $name, $project_id, $deadline);
		}	  	

		// Выполняем подготовленный запрос.
		mysqli_stmt_execute($stmt);

		// Если запрос неудачен, то выводим ошибку
		if (!$result) {
		    print("Ошибка в запросе к БД. Запрос $sql " . mysqli_error($connection_resourse));
		    die();
		}

		header("Location: /");
	}

}

$layout_content = include_template('add.php', [
	'projects' => $projects,
	'task' => $task,
	'errors' => $errors
]);

print($layout_content);