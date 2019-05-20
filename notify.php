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

// Запрос на получение количества задач на сегодня у каждого пользователя
$sql = "SELECT  user_id , COUNT(name) AS tasks
FROM tasks
WHERE status = 0 AND deadline = CURRENT_DATE()
GROUP BY user_id";

$res = mysqli_query($connection_resourse, $sql);

// Установим параметры сообщения: тема, отправитель и список его получателей
$message = new Swift_Message();
$message->setSubject("Уведомление от сервиса «Дела в порядке»");
$message->setFrom(['keks@phpdemo.ru' => 'Doingsdone']);


$tasks_count = parse_result($res, $connection_resourse, $sql);
$single_task_users = [];

// Отправка сообщений
foreach ($tasks_count as $key => $value) {
	// Отправка сообщений юзерам с несколькими задачами
	if ((int)$value['tasks'] > 1) {	

		$multiuser_id = $value['user_id'];
		$tasks_row = '';

		// Запрос на получение списка задач для пользователей, у которых сегодня больше одной задачи
		$sql = "SELECT u.name AS user, u.email, t.name AS task, t.deadline FROM tasks AS t 
				JOIN users AS u ON u.id = t.user_id
				WHERE t.status = 0 AND t.deadline = CURRENT_DATE() AND u.id = $multiuser_id";

		$res = mysqli_query($connection_resourse, $sql);
		$m_task = parse_result($res, $connection_resourse, $sql);

		foreach ($m_task as $value) {
			$tasks_row .= $value['task'] . ' на ' . $value['deadline'] . '<br>';
		}

		$message->setTo($value['email']);
		$msg_content = "Уважаемый, " . $value['user'] . "! У вас запланированы задачи: <br>" . $tasks_row;
		$message->setBody($msg_content, 'text/html');

		$result = $mailer->send($message);

		// Если результат не был успешным, то мы можем узнать подробности ошибки вызовом метода из объекта для журналирования
		if (!$result) {
		    print("Не удалось отправить рассылку: " . $logger->dump());
		}

	} else {
		array_push($single_task_users, $value['user_id']);
	}

}

if (count($single_task_users)) {
	$sql_u_ids = '(' . implode(',', $single_task_users) .')';
	$sql = "SELECT u.name AS user, u.email, t.name AS task, t.deadline FROM tasks AS t 
			JOIN users AS u ON u.id = t.user_id
			WHERE t.status = 0 AND t.deadline = CURRENT_DATE() AND u.id IN" . $sql_u_ids;

	$res = mysqli_query($connection_resourse, $sql);
	$tasks = parse_result($res, $connection_resourse, $sql);

	foreach ($tasks as $value) {
		$message->setTo($value['email']);
		$msg_content = "Уважаемый, " . $value['user'] . "! У вас запланирована задача " . $value['task'] . " на " . $value['deadline'];
		$message->setBody($msg_content, 'text/plain');

		$result = $mailer->send($message);

		// Если результат не был успешным, то мы можем узнать подробности ошибки вызовом метода из объекта для журналирования
		if (!$result) {
		    print("Не удалось отправить рассылку: " . $logger->dump());
		}
	}
}
print("Рассылка успешно отправлена");