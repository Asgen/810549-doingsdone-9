<?php
// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);

require_once('functions.php');

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

$tasks = [];
$projects = [];
$page_content = '';
$layout_content = '';

// При успешном соединении формируем запрос к БД

// Запрос на получение списк задач
$sql = 'SELECT `name` AS `task`, `deadline` AS `date`, `status` AS `done`, `project_id` AS `category` FROM tasks';
$result = mysqli_query($connection_resourse, $sql);

// Если запрос неудачен, то выводим ошибку
if (!$result) {
    print("Ошибка в запросе к БД " . mysqli_error($connection_resourse));
    die();
}

// Если ответ получен, преобразуем его в двумерный массив и подключаем шаблон стр.
$tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Подключение шаблона
$page_content = include_template('index.php', [
  'tasks' => $tasks,
  'show_complete_tasks' => $show_complete_tasks
]);



// Запрос на получение списка проектов для конкретного пользователя
$sql = 'SELECT p.NAME AS `category`, COUNT(t.id) `tasks_total` FROM `projects` AS `p` LEFT JOIN `tasks` AS `t` ON p.id = t.project_id WHERE p.user_id = 1 GROUP BY p.name';
$result = mysqli_query($connection_resourse, $sql);

// Если запрос неудачен, то выводим ошибку
if (!$result) {
    print("Ошибка в запросе к БД " . mysqli_error($connection_resourse));
    die();
}

// Если ответ получен, преобразуем его в двумерный массив и подключаем шаблон стр.
$projects = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Поключение лэйаута с включением в него шаблона
$layout_content = include_template('layout.php', [
    'projects' => $projects,
    'tasks' => $tasks,
    'content' => $page_content,
    'page_title' => 'Hello ',
    'user_name' => 'Nick Cave'
]);



print($layout_content);
