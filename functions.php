<?php
// Функция подсчета задач
function count_tasks($tasks_arr, $project_name) {
  $count = 0;
  foreach ($tasks_arr as $value) {
    if ($value['category'] == $project_name) {
      $count++;
    }
  }

  return $count;
}

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
