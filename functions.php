<?php
/**
 * Преобразует специальные символы в HTML-сущности
 * @param string $str Строка для обработки
 * @return string Преобразованная строка
 */
function esc($str)
{
    return htmlspecialchars($str, ENT_QUOTES);
}

/**
 * Подключает шаблон, передает туда данные и возвращает итоговый HTML контент
 * @param string $name Путь к файлу шаблона относительно папки templates
 * @param array $data Ассоциативный массив с данными для шаблона
 * @return string Итоговый HTML
 */
function include_template($name, array $data = [])
{
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

/**
 * Проверяет оставшееся время до выполнения задачи
 * @param string $date Дата в виде строки
 * @return bool True в случае если до выполнения осталось менее 24 часов
 */
function is_important($date)
{
    if ($date === null) {
        return false;
    }

    $current_date =  time();
    $task_date = strtotime($date);

    $hours_to_deadline = floor(($task_date - $current_date) / 3600);

    return $hours_to_deadline <= 24;
}

/**
 * Обрабатывает результат обращения к БД
 * @param object or bool  $result Объект результата соединения или результат соединения
 * @param object  $connection_resourse Ресурс соединения
 * @param string  $sql Запрос
 * @param bool  $fetch Параметр возвращаемого массива
 * @return array Возвращает массив содержащий ассоциативные или обычные массивы с данными результирующей таблицы.
 */
function parse_result($result, $connection_resourse, $sql, $fetch = false)
{

    // Если запрос неудачен, то выводим ошибку
    if (!$result) {
        print("Ошибка в запросе к БД. Запрос $sql " . mysqli_error($connection_resourse));
        die();
    }

    if (is_bool($result)) {
        return;
    }

    if ($fetch) {
        return mysqli_fetch_assoc($result);
    }

    // Если ответ получен, преобразуем его в двумерный массив
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Устанавливает соединение с БД
 * @return object При успешном соединении возвращает ресурс соединения.
 */
function connect_db()
{
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

/**
 * Получает список всез проектов пользователя
 * @param object  $connection_resourse Ресурс соединения
 * @param string  $user_id Идентификатор пользователя
 * @return array Возвращает ассоциативный массив с проектами.
 */
function get_projects($connection_resourse, $user_id)
{

    // Запрос на получение списка проектов для конкретного пользователя
    $sql = "SELECT p.NAME AS `category`, COUNT(t.id) `tasks_total`, p.id AS `project_id` FROM `projects` AS `p` LEFT JOIN `tasks` AS `t` ON p.id = t.project_id WHERE p.user_id = $user_id GROUP BY p.id";
    $result = mysqli_query($connection_resourse, $sql);

    return parse_result($result, $connection_resourse, $sql);
}

/**
 * Устанавливает куку
 * @param string  $name Название куки
 * @param string or int $value Значение по умолчанию
 * @param string $days Количество дней жизни куки
 * @param string $path Путь на сайте, по которому будет доступна кука. Слеш означает весь сайт
 * @return Ничего не возвращает
 */
function set_cookie($name, $value, $days, $path = "/")
{
    $expire = strtotime("+$days days");
    setcookie($name, $value, $expire, $path);
    return;
}
