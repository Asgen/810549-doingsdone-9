<?php
// Функция убирает опысные символы из строки
function esc($str) {
  return htmlspecialchars($str, ENT_QUOTES);
}

/**
 * Подключает шаблон, передает туда данные и возвращает итоговый HTML контент
 * @param string $name Путь к файлу шаблона относительно папки templates
 * @param array $data Ассоциативный массив с данными для шаблона
 * @return string Итоговый HTML
 */
function include_template($name, array $data = []) {
    $name = 'templates/' . $name;
    $result = '';

    if (!is_readable($name)) {
        return $result;
    }

    ob_start();
    extract($data);
    require $name;

    $result = ob_get_clean();

    return $result;
}

// Функция проверки времени для задания
function is_important($date) {

    if ($date === NULL) {
        return false;
    }

    $current_date =  time();
    $task_date = strtotime($date);

    $hours_to_deadline = floor(($task_date - $current_date) / 3600);

return $hours_to_deadline <= 24;
}

// Функция обработки ответа обращения к БД
function parse_result ($result, $connection_resourse, $sql) {

    // Если запрос неудачен, то выводим ошибку
    if (!$result) {
        print("Ошибка в запросе к БД. Запрос $sql " . mysqli_error($connection_resourse));
        die();
    }

    // Если ответ получен, преобразуем его в двумерный массив
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Установка соединения с БД
function connect_db () {
    require_once('config/db.php');

    $connection_resourse = mysqli_connect($db['host'], $db['user'], $db['password'], $db['database']);

    // Если ошибка соединения - показываем ее
    if (!$connection_resourse) {
        print("Ошибка подключения к БД " . mysqli_connect_error());
        die();
    }

    mysqli_set_charset($connection_resourse, "utf8");
    return $connection_resourse;
}

function get_projects ($connection_resourse, $user_id) {

    // Запрос на получение списка проектов для конкретного пользователя
    $sql = "SELECT p.NAME AS `category`, COUNT(t.id) `tasks_total`, p.id AS `project_id` FROM `projects` AS `p` LEFT JOIN `tasks` AS `t` ON p.id = t.project_id WHERE p.user_id = $user_id GROUP BY p.id";
    $result = mysqli_query($connection_resourse, $sql);

    return parse_result($result, $connection_resourse, $sql);
}