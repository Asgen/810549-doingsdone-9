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
    $project = $_POST;
    $required = ['name'];
    $errors = [];

    foreach ($project as $key => $value) {
        $project[$key] = trim($value);
    }

    if (empty($project['name'])) {
        $errors['name'] = 'Это поле надо заполнить';
    } elseif (strlen($project[$key]) > 200) {
        $errors[$key] = 'Допустимое количество символов превышено!';
    } else {

        // Выбран существующий ли проект
        $wrong_proj = false;
        foreach ($projects as $value) {
            if ($value['category'] === $project['name']) {
                $wrong_proj = true;
                break;
            }
        }
        if ($wrong_proj) {
            $errors['name'] = 'Проект с таким названием уже есть';
        }
    }

    // Если ошибок нет
    if (!count($errors)) {

        // Записываем в БД
        // Формируем запрос
        $user_id = $_SESSION['user']['id'];
        $sql = "INSERT INTO projects SET name = ?, user_id = $user_id";

        // Подготавливаем шаблон запроса
        $stmt = mysqli_prepare($connection_resourse, $sql);

        // Привязываем к маркеру значение переменных.
        $name = $project['name'];

        mysqli_stmt_bind_param($stmt, 's', $name);

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
    $page_content = include_template('add_project.php', [
        'project' => $project,
        'errors' => $errors
    ]);
} else {
    // Подключение шаблона
    $page_content = include_template('add_project.php', []);
}

// Поключение лэйаута с включением в него шаблона
$layout_content = include_template('layout.php', [
    'projects' => $projects,
    'content' => $page_content,
    'active_project' => $choosen_project ?? '',
    'page_title' => 'Добавление проекта ',
    'user' => $_SESSION['user']
]);

print($layout_content);
