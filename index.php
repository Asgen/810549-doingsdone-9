<?php
// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);

// Проекты
$projects = [
  "Входящие",
  "Учеба",
  "Работа",
  "Домашние дела",
  "Авто"
];

// Задачи
$tasks = [
  [
    'task' => "Собеседование в IT компании",
    'date' => '01.12.2018',
    'category' => 'Работа',
    'done' => 'Нет'
  ],
  [
    'task' => "Выполнить тестовое задание",
    'date' => '21.04.2019',
    'category' => 'Работа',
    'done' => 'Нет'
  ],
  [
    'task' => "Сделать задание первого раздела",
    'date' => '18.04.2019',
    'category' => 'Учеба',
    'done' => 'Да'
  ],
  [
    'task' => "Встерча с другом",
    'date' => '22.12.2018',
    'category' => 'Входящие',
    'done' => 'Нет'
  ],
  [
    'task' => "Купить корм для кота",
    'date' => 'Нет',
    'category' => 'Домашние дела',
    'done' => 'Нет'
  ],
  [
    'task' => "Заказать пиццу",
    'date' => 'Нет',
    'category' => 'Домашние дела',
    'done' => 'Нет'
  ]
];

require_once('functions.php');

// Подключение к MySQL
require_once('config/db.php');

// Соединение с БД
$connection_resourse = mysqli_connect($db['host'], $db['user'], $db['password'], $db['database']);
mysqli_set_charset($connection_resourse, "utf8");

$tasks = [];
$projects = [];
$page_content = '';
$layout_content = 'sdvc';

// Если ошибка соединения - показываем ее
if (!$connection_resourse) {
    $error = mysqli_connect_error();
    $page_content = $error;
}

// При успешном соединении формируем запрос к БД
else {
    // Запрос на получение списк задач
    $sql = 'SELECT `name` AS `task`, `deadline` AS `date`, `status` AS `done`, `project_id` AS `category` FROM tasks';
    $result = mysqli_query($connection_resourse, $sql);

    // Если ответ получен, преобразуем его в двумерный массив и подключаем шаблон стр.
    if ($result) {
        $tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);

        // Подключение шаблона
        $page_content = include_template('index.php', [
          'tasks' => $tasks,
          'show_complete_tasks' => $show_complete_tasks
        ]);
    }

    // Если запрос неудачен, то выводим ошибку
    else {
        $error = mysqli_connect_error();
        $page_content = $error;
    }

    // Запрос на получение списка проектов для конкретного пользователя
    $sql = 'SELECT u.NAME AS `user`, p.NAME AS `category`, COUNT(t.id) `tasks_total` FROM `projects` AS `p` LEFT JOIN `tasks` AS `t` ON p.id = t.project_id JOIN `users` AS `u` ON p.user_id = u.id WHERE p.user_id = 2 GROUP BY p.name';
    $result = mysqli_query($connection_resourse, $sql);

    // Если ответ получен, преобразуем его в двумерный массив и подключаем шаблон стр.
    if ($result) {
        $projects = mysqli_fetch_all($result, MYSQLI_ASSOC);

        // Поключение лэйаута с включением в него шаблона
        $layout_content = include_template('layout.php', [
            'projects' => $projects,
            'tasks' => $tasks,
            'content' => $page_content,
            'page_title' => 'Hello '.$projects[0]['user'],
            'user_name' => $projects[0]['user']
        ]);
    }

    // Если запрос неудачен, то выводим ошибку
    else {
        $error = mysqli_connect_error();
        $layout_content = 'sfghbsfghsfrgh';
    }

}

print($layout_content);
