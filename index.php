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
    'date' => '25.12.2018',
    'category' => 'Работа',
    'done' => 'Нет'
  ],
  [
    'task' => "Сделать задание первого раздела",
    'date' => '21.12.2018',
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

$page_content = include_template('index.php', [
  'tasks' => $tasks,
  'show_complete_tasks' => $show_complete_tasks
]);

$layout_content = include_template('layout.php', [
    'projects' => $projects,
    'tasks' => $tasks,
    'content' => $page_content,
    'page_title' => 'Hello',
    'user_name' => 'Zeppelin'
]);

print($layout_content);
