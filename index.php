<?php
// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);

require_once('functions.php');

session_start();

if (isset($_SESSION['user'])) {
  $connection_resourse = connect_db();

  $tasks = [];
  $projects = [];
  $choosen_project = 0;

  // При успешном соединении формируем запрос к БД
  $u_id = $_SESSION['user']['id'];
  // Запрос на получение списк задач
  $sql = "SELECT `name` AS `task`, `deadline` AS `date`, `status` AS `done`, `project_id` AS `category`, file FROM tasks WHERE `user_id` = $u_id";

  // Проверяем выбран ли проект
  if(isset($_GET['project_id'])) {
    $choosen_project = (int)$_GET['project_id'];
    $sql .= " WHERE `project_id` = ?";
  }

  $sql .= " ORDER BY datetime_add DESC";

  // Подготавливаем шаблон запроса
  $stmt = mysqli_prepare($connection_resourse, $sql);

  // Привязываем к маркеру значение переменной $choosen_project.
  if ($choosen_project) {
    mysqli_stmt_bind_param($stmt, 'i', $choosen_project);
  }

  // Выполняем подготовленный запрос.
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  $tasks = parse_result($result, $connection_resourse, $sql);

  // Подключение шаблона
  $page_content = include_template('index.php', [
    'tasks' => $tasks,
    'show_complete_tasks' => $show_complete_tasks
  ]);

  // Запрос на получение списка проектов для конкретного пользователя
  $projects = get_projects($connection_resourse, $u_id);

  // Поключение лэйаута с включением в него шаблона
  $layout_content = include_template('layout.php', [
      'projects' => $projects,
      'tasks' => $tasks,
      'content' => $page_content,
      'active_project' => $choosen_project,
      'page_title' => 'Hello ',
      'user' => $_SESSION['user']
  ]);
}

else {
  // Подключение шаблона
  $page_content = include_template('guest.php', [
  ]);

  // Поключение лэйаута с включением в него шаблона
  $layout_content = include_template('layout.php', [
      'content' => $page_content,
      'page_title' => 'Hello '
  ]);
}

print($layout_content);
