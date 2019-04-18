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
  $text = htmlspecialchars($str, ENT_QUOTES);

  return $text;
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
function is_important($tasksArr) {

    foreach ($tasksArr as $key => $value) {
      $tasksArr[$key]['important'] = false;
      if ($value['date'] !== 'Нет') {
        $current_date = date('d.m.Y');
        $task_date = $value['date'];

        $current_date =  strtotime($current_date);
        $task_date = strtotime($task_date);

        $hours_to_deadline = ($task_date - $current_date) / 3600;

        if ($hours_to_deadline < 24 && $hours_to_deadline >= 0 ) {
          $tasksArr[$key]['important'] = true;
        }
      }
    }

    return $tasksArr;
}
