<?php

require_once('functions.php');

session_start();

if (isset($_SESSION['user'])) {

  $connection_resourse = connect_db();
  $choosen_project = 0;

  // При успешном соединении формируем запрос к БД
  $u_id = $_SESSION['user']['id'];

  // Переключение состояния задачи
  if (isset($_GET['task_id'])) {

    $task_id = $_GET['task_id'];
    $sql = "SELECT id, name, status FROM tasks WHERE id = ? and user_id = $u_id";

    // Подготавливаем шаблон запроса
    $stmt = mysqli_prepare($connection_resourse, $sql);

    // Привязываем к маркеру значение переменной $task_id.
    mysqli_stmt_bind_param($stmt, 'i', $task_id);

    // Выполняем подготовленный запрос.
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $task = parse_result($result, $connection_resourse, $sql, true);

    // Меняем статус задачи на противоположный
    $status = $task['status'] ? 0 : 1;
    $t_id = $task['id'];

    $sql = "UPDATE tasks SET status = $status WHERE id = $t_id";
    $res = mysqli_query($connection_resourse, $sql);
    if (!$res) {
      print("Ошибка в запросе к БД. Запрос $sql " . mysqli_error($connection_resourse));
      die();
    }

    header("Location: /");
    die();
  }

  // Показать выполенные

  $show_completed_state = 'show_completed'; // Определимся как будет называться наша кука
  $show_complete_tasks = 0; // Значение по умолчанию
  $expire = strtotime("+30 days"); // Кука будет жить ровно 30 дней. Функция strtotime переводит дату в TIMESTAMP формат
  $path = "/"; // Путь на сайте, по которому будет доступна кука. Слеш означает весь сайт

  // Проверяем существование куки с этим именем. Если кука существует, то получаем её значение в переменную.
  if (isset($_COOKIE['show_completed'])) {
    $show_complete_tasks = $_COOKIE['show_completed'];
  }

  if (isset($_GET['show_completed'])) {
    $state = $show_complete_tasks;
    $show_complete_tasks = $state ? 0 : 1;
  }

  // Устанавливаем куку с помощью функции setcookie. Эта функция создаст новую куку, или обновит значение существующей.
  setcookie($show_completed_state, $show_complete_tasks, $expire, $path);


  // Фильтрация
  if (isset($_GET['filter'])) {

    $cur_date = date('Y-m-d');
    $sql = "SELECT id, `name` AS `task`, `deadline` AS `date`, `status` AS `done`, `project_id` AS `category`, file FROM tasks WHERE user_id = $u_id";

    switch ($_GET['filter']) {
      case 'today':
          $sql .= " and `deadline` = '$cur_date'";
          break;
      case 'tomorrow':
          $sql .= " and `deadline` = '$cur_date' + INTERVAL 1 DAY";
          break;
      case 'out_of_date':
          $sql .= " and `deadline` < NOW()";
          break;
      default:
          $sql = $sql;
    }

    $res = mysqli_query($connection_resourse, $sql);
    $tasks = parse_result($res, $connection_resourse, $sql);

  } else {

    // Запрос на получение списк задач
    $sql = "SELECT id, `name` AS `task`, `deadline` AS `date`, `status` AS `done`, `project_id` AS `category`, file FROM tasks WHERE `user_id` = $u_id";

    // Проверяем выбран ли проект
    if(isset($_GET['project_id'])) {
      $choosen_project = (int)$_GET['project_id'];
      $sql .= " and `project_id` = ?";
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
  }

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
} else {
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
