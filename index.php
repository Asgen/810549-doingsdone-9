<?php
require_once('functions.php');
require_once('helpers.php');

session_start();

if (isset($_SESSION['user'])) {
    $connection_resourse = connect_db();
    $choosen_project = 0;
    $show_complete_tasks = 0;
    $choosen_filter = 'show_all';
    $cur_date = date('Y-m-d');

    // При успешном соединении формируем запрос к БД
    $u_id = $_SESSION['user']['id'];

    /* Переключение состояния задачи ------ */
    if (isset($_GET['task_id'])) {
        $task_id = (int)$_GET['task_id'];
        $task_status = (int)$_GET['check'];

        $sql = "UPDATE tasks SET status = $task_status WHERE id = $task_id AND user_id = $u_id";
        $res = mysqli_query($connection_resourse, $sql);

        if (!$res) {
            print("Ошибка в запросе к БД. Запрос $sql " . mysqli_error($connection_resourse));
            die();
        }

        header("Location: /");
        die();
    }

    /* Показать выполенные ------ */

    // Проверяем существование куки с этим именем. Если кука существует, то получаем её значение в переменную.
    if (isset($_COOKIE['show_completed'])) {
        $show_complete_tasks = $_COOKIE['show_completed'];
    }

    if (isset($_GET['show_completed'])) {
        $show_complete_tasks = $_GET['show_completed'];
    }

    // Устанавливаем куку с помощью функции setcookie. Эта функция создаст новую куку, или обновит значение существующей.
    set_cookie('show_completed', $show_complete_tasks, 30);

    /* Фильтрация ------ */
    if (isset($_GET['search']) && !empty($_GET['search'])) {

        /* Полнотекстовый поиск ------ */
        $search = trim($_GET['search']);
        $sql = "SELECT id, `name` AS `task`, `deadline` AS `date`, `status` AS `done`, `project_id` AS `category`, file FROM tasks WHERE MATCH (name) AGAINST (?) AND user_id = $u_id";

        $stmt = db_get_prepare_stmt($connection_resourse, $sql, $data = [$search]);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $tasks = parse_result($result, $connection_resourse, $sql, false);
    
        if (mysqli_num_rows($result) < 1) {
            $tasks = null;
        }
        mysqli_free_result($result);
    } else {

        // Запрос на получение списк задач
        $sql = "SELECT id, `name` AS `task`, `deadline` AS `date`, `status` AS `done`, `project_id` AS `category`, file FROM tasks WHERE `user_id` = $u_id";

        // Проверяем выбран ли проект
        if (isset($_GET['project_id'])) {
            $choosen_project = (int)$_GET['project_id'];
            set_cookie('choosen_project', $choosen_project, 30);
        } elseif (isset($_COOKIE['choosen_project'])) {
            $choosen_project = (int)$_COOKIE['choosen_project'];
        } else {
            set_cookie('choosen_project', $choosen_project, -30);
        }

        // Проверяем фильтр
        if (isset($_GET['filter'])) {
            switch ($_GET['filter']) {
                case 'show_all':
                  $sql .= "";
                  set_cookie('filter', 'show_all', 30);
                  break;
                case 'today':
                  $sql .= " and `deadline` = '$cur_date' ";
                  set_cookie('filter', 'today', 30);
                  break;
                case 'tomorrow':
                  $sql .= " and `deadline` = '$cur_date' + INTERVAL 1 DAY ";
                  set_cookie('filter', 'tomorrow', 30);
                  break;
                case 'out_of_date':
                  $sql .= " and `deadline` < NOW() - 1 ";
                  set_cookie('filter', 'out_of_date', 30);
                  break;
            }
        } elseif (isset($_COOKIE['filter'])) {
            switch ($_COOKIE['filter']) {
                case 'show_all':
                  $sql .= "";
                  break;
                case 'today':
                  $sql .= " and `deadline` = '$cur_date' ";
                  break;
                case 'tomorrow':
                  $sql .= " and `deadline` = '$cur_date' + INTERVAL 1 DAY ";
                  break;
                case 'out_of_date':
                  $sql .= " and `deadline` < NOW() - 1 ";
                  break;
            }
        }

        if (isset($_GET['filter'])) {
            $choosen_filter = $_GET['filter'];
        } elseif (isset($_COOKIE['filter'])) {
            $choosen_filter = $_COOKIE['filter'];
        }


        if (!$show_complete_tasks) {
            $sql .= " AND `status` = 0";
        }

        if (isset($_GET['all_projects'])) {
            $choosen_project = 0;
            set_cookie('choosen_project', 0, -30);
        }

        $sql .= $choosen_project ? " and `project_id` = ?" : '';

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

    // Запрос на получение списка проектов для конкретного пользователя
    $projects = get_projects($connection_resourse, $u_id);

    // Подключение шаблона
    $page_content = include_template('index.php', [
    'tasks' => $tasks,
    'show_complete_tasks' => $show_complete_tasks,
    'active_filter' => $choosen_filter
    ]);

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
      'page_title' => 'Дела в порядке? '
    ]);
}

print($layout_content);
