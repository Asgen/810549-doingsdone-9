<?php
require_once('functions.php');
require_once('helpers.php');

session_start();

if (!isset($_SESSION['user'])) {
    header("Location: /");
    die();
}

// Соединение с БД
$connection_resourse = connect_db();

// Запрос на получение списка проектов для конкретного пользователя
$projects = get_projects($connection_resourse, $_SESSION['user']['id']);

// Активный проект
if (isset($_COOKIE['choosen_project'])) {
    $choosen_project = (int)$_COOKIE['choosen_project'];
}

// Если сценарий был вызван отправкой формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // В массиве $_POST содержатся все данные из формы
    $task = $_POST;
    $required = ['name', 'project'];
    $errors = [];

    foreach ($task as $key => $value) {
        $task[$key] = trim($value);
    }

    // Проверка заполненности полей
    foreach ($required as $key) {
        if (empty($task[$key])) {
            $errors[$key] = 'Это поле надо заполнить';
        } elseif (strlen($task[$key]) > 200) {
            $errors[$key] = 'Допустимое количество символов превышено!';
        }
    }

    if (!empty($task['date'])) {
        if (!is_date_valid($task['date']) || $task['date'] < date('Y-m-d')) {
            $errors['date'] = 'Дата должна быть больше или равна текущей';
        }
    }

    // Валидация
    if (!count($errors)) {

        // Проверим, был ли загружен файл
        if (isset($_FILES['file']) && !$_FILES['file']['error']) {
            $tmp_name = $_FILES['file']['tmp_name'];
            $path = uniqid();
            move_uploaded_file($tmp_name, __DIR__  . '/uploads' . '/' . $path);
            $file = '/uploads' . '/' . $path;
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
