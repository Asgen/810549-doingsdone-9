<?php
require_once('functions.php');
require_once('helpers.php');

// Обнуляем куки
set_cookie('choosen_project', 0, -30);
set_cookie('show_completed', 0, -30);
set_cookie('filter', 0, -30);

session_start();

if (isset($_SESSION['user'])) {
    header("Location: /index.php");
    die();
}

// Если сценарий был вызван отправкой формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // В массиве $_POST содержатся все данные из формы
    $form = $_POST;
    $required = ['email', 'password'];
    $errors = [];

    foreach ($form as $key => $value) {
        $form[$key] = trim($value);
    }

    // Проверяем заполненность полей
    foreach ($required as $key) {
        if (empty($form[$key])) {
            $errors[$key] = 'Это поле надо заполнить';
        } elseif (strlen($form[$key]) > 200) {
            $errors[$key] = 'Допустимое количество символов превышено!';
        }
    }

    // Отправляем запрос в БД если все поля заполнены
    if (!count($errors)) {
        // Соединение с БД
        $connection_resourse = connect_db();

        $email = mysqli_real_escape_string($connection_resourse, $form['email']);
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $res = mysqli_query($connection_resourse, $sql);

        // Если запрос неудачен, то выводим ошибку
        if (!$res) {
            print("Ошибка в запросе к БД. Запрос $sql " . mysqli_error($connection_resourse));
            die();
        }

        $user = $res ? mysqli_fetch_array($res, MYSQLI_ASSOC) : null;
    }

    // Если форма заполнена и пользователь найден - валидируем пароли
    if (isset($user)) {
        // Сравниваем хеши паролей и если совпадают то записываем в сессию
        if (password_verify($form['password'], $user['password'])) {
            $_SESSION['user'] = $user;
        } else {
            $errors['fired'] = '1';
        }
    } elseif (!isset($user) && !count($errors)) {
        $errors['fired'] = '1';
    }

    if (count($errors)) {
        $page_content = include_template('auth.php', [
            'form' => $form,
            'errors' => $errors
        ]);
    } else {
        header("Location: /index.php");
        die();
    }
} else {
    $page_content = include_template('auth.php', []);
}

$layout = include_template('layout.php', [
    'content' => $page_content,
    'page_title' => 'Авторизация'
]);

print($layout);
