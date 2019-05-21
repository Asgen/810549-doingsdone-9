<?php
require_once 'vendor/autoload.php';
require_once 'functions.php';

// Соединение с БД
$connection_resourse = connect_db();

// Сообщения электронной почты отправляются по протоколу SMTP. Поэтому нам понадобятся данные для доступа к SMTP-серверу. Указываем его адрес и логин с паролем.
$transport = new Swift_SmtpTransport("phpdemo.ru", 25);
$transport->setUsername("keks@phpdemo.ru");
$transport->setPassword("htmlacademy");

// Создадим главный объект библиотеки SwiftMailer, ответственный за отправку сообщений. Передадим туда созданный объект с SMTP-сервером.
$mailer = new Swift_Mailer($transport);

// Чтобы иметь максимально подробную информацию о процессе отправки сообщений мы попросим SwiftMailer журналировать все происходящее внутри массива.
$logger = new Swift_Plugins_Loggers_ArrayLogger();
$mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));

// Запрос на получение всех задач на сегодня
$sql = "SELECT u.name AS user, u.email, t.user_id, t.name AS task, t.deadline
FROM tasks AS t
JOIN users AS u ON u.id = t.user_id
WHERE t.status = 0 AND t.deadline = CURRENT_DATE()";

$res = mysqli_query($connection_resourse, $sql);

if (mysqli_num_rows($res) < 1) {
    print("На сегодня срочных задач нет");
    die();
}

$tasks = parse_result($res, $connection_resourse, $sql);
$task_users = [];

// Установим параметры сообщения: тема, отправитель и список его получателей
$message = new Swift_Message();
$message->setSubject("Уведомление от сервиса «Дела в порядке»");
$message->setFrom(['keks@phpdemo.ru' => 'Doingsdone']);

// Формируем массив для рассылки
foreach ($tasks as $key => $value) {
    $task_users[$value['email']] [] = ['task_name' => $value['task'], 'deadline' => $value['deadline'], 'user' => $value['user']];
}

// Отправка сообщений
foreach ($task_users as $user => $tasks) {
    $email = '';
    $name = '';
    $tasks_list = '';

    $email = $user;
 
    foreach ($tasks as $key => $task) {
        $name = $task['user'];
        $tasks_list .= '"' . $task['task_name'] . '"' . ' на ' . $task['deadline'] . '<br>';
    }

    $message->setTo($email);
    $msg_content = "Уважаемый, " . $name . "! У вас запланирована задача: <br>" . $tasks_list;
    $message->setBody($msg_content, 'text/html');

    $result = $mailer->send($message);

    // Если результат не был успешным, то мы можем узнать подробности ошибки вызовом метода из объекта для журналирования
    if (!$result) {
        print("Не удалось отправить рассылку: " . $logger->dump());
    }
}

print("Рассылка успешно отправлена");
