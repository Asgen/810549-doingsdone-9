<?php
require_once('functions.php');
require_once('helpers.php');

// Обнуляем куки
set_cookie('choosen_project', 0, -30);
set_cookie('show_completed', 0, -30);
set_cookie('filter', 0, -30);

// Если сценарий был вызван отправкой формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    // В массиве $_POST содержатся все данные из формы
    $form = $_POST;
    $required = ['email', 'password', 'name'];
    $errors = [];

    foreach ($form as $key => $value) {
        $form[$key] = trim($value);
    }

    foreach ($required as $key) {
        if (empty($form[$key])) {
            $errors[$key] = 'Это поле надо заполнить';
        } elseif (strlen($form[$key]) > 200) {
            $errors[$key] = 'Допустимое количество символов превышено!';
        }
    }

    // Валидация заполненных полей
    if (!count($errors)) {
        // Проверка пароля
        if (strlen($form['password']) < 6) {
            $errors['password'] = "Минимальная длина пароля 6 символов";
        }

        // Проверка email
        if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "E-mail адрес указан неверно.\n";
        }
    }

    // Проверка пользователя с введеным email
    if (!count($errors)) {
        // Соединение с БД
        $connection_resourse = connect_db();

        $email = mysqli_real_escape_string($connection_resourse, $form['email']);
        $sql = "SELECT id FROM users WHERE email = '$email'";
        $res = mysqli_query($connection_resourse, $sql);

        parse_result($res, $connection_resourse, $sql);

        if (mysqli_num_rows($res) > 0) {
            $errors['email'] = 'Пользователь с этим email уже зарегистрирован';
        }
    }

    // Если ошибок нет
    if (!count($errors)) {

        // Записываем в БД

        // Хешируем пароль
        $password = password_hash($form['password'], PASSWORD_DEFAULT);

        // Формируем запрос
        $sql = 'INSERT INTO users (email, name, password) VALUES (?, ?, ?)';

        // Подготавливаем шаблон запроса
        $stmt = db_get_prepare_stmt($connection_resourse, $sql, [$email, $form['name'], $password]);

        // Выполняем подготовленный запрос.
        $result = mysqli_stmt_execute($stmt);

        parse_result($result, $connection_resourse, $sql);

        // Редирект на страницу входа, если пользователь был успешно добавлен в БД.
        if ($result) {
            session_start();
            $user = [
                'id' => mysqli_insert_id($connection_resourse),
                'email' => $email,
                'name' => $form['name'],
                'password' => $password
            ];
            $_SESSION['user'] = $user;

            header("Location: /");
            die();
        }
    }

    // Подключение шаблона
    $page_content = include_template('register.php', [
        'form' => $form,
        'errors' => $errors
    ]);
} else {
    // Подключение шаблона
    $page_content = include_template('register.php', []);
}

// Поключение лэйаута
$layout_content = include_template('layout.php', [
    'content' => $page_content,
    'page_title' => 'Регистрация '
]);

print($layout_content);
