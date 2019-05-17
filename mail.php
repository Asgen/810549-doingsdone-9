<?php
require_once 'vendor/autoload.php';
require_once 'functions.php';

// Соединение с БД
$connection_resourse = connect_db();

// Сообщения электронной почты отправляются по протоколу SMTP. Поэтому нам понадобятся данные для доступа к SMTP-серверу. Указываем его адрес и логин с паролем.
$transport = new Swift_SmtpTransport("localhost", 25);
$transport->setUsername("");
$transport->setPassword("");

// Создадим главный объект библиотеки SwiftMailer, ответственный за отправку сообщений. Передадим туда созданный объект с SMTP-сервером.
$mailer = new Swift_Mailer($transport);

// Чтобы иметь максимально подробную информацию о процессе отправки сообщений мы попросим SwiftMailer журналировать все происходящее внутри массива.
$logger = new Swift_Plugins_Loggers_ArrayLogger();
$mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));

$sql = "SELECT * FROM tasks";
$res = mysqli_query($connection_resourse, $sql);
$tasks = parse_result($res, $connection_resourse, $sql);

// Теперь нам нужен список из всех пользователей сайта, которые будут получателями рассылки
$sql = "SELECT email, name FROM users";
$res = mysqli_query($connection_resourse, $sql);
$users = parse_result($res, $connection_resourse, $sql);

// Подготовим полученный список к формату вида "email -> имя"
$recipients = [];

foreach ($users as $user) {
    $recipients[$user['email']] = $user['name'];
}

// Установим параметры сообщения: тема, отправитель и список его получателей
$message = new Swift_Message();
$message->setSubject("Самые горячие гифки за этот месяц");
$message->setFrom(['keks@phpdemo.ru' => 'GifTube']);
$message->setBcc($recipients);

// Передадим список гифок в шаблон, используемый для сообщения
$msg_content = include_template('index.php', ['tasks' => $tasks]);
$message->setBody($msg_content, 'text/html');

$result = $mailer->send($message);

// Если результат не был успешным, то мы можем узнать подробности ошибки вызовом метода из объекта для журналирования
if ($result) {
    print("Рассылка успешно отправлена");
}
else {
    print("Не удалось отправить рассылку: " . $logger->dump());
}