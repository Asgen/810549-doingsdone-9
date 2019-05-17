<?php
require_once 'vendor/autoload.php';
require_once 'functions.php';

session_start();

if (isset($_SESSION['user'])) {
	$u_id = $_SESSION['user']['id'];
	$u_name = $_SESSION['user']['name'];
	$u_email = $_SESSION['user']['email'];
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

	$sql = "SELECT name, deadline FROM tasks WHERE status = 0 AND deadline = CURRENT_DATE() and user_id = $u_id";
	$res = mysqli_query($connection_resourse, $sql);

	if (mysqli_num_rows($res) > 1) {
		$tasks = parse_result($res, $connection_resourse, $sql);
		foreach ($tasks as $key => $value) {
			if ($key === (count($tasks) -1)) {
				$tasks_list .= $value['name'];
				$task_dates .= $value['deadline'] . " соответственно.";
				break;
			}
			$tasks_list .= $value['name'] . ", ";
			$task_dates .= $value['deadline'] . ", ";
		}
	} else {
		$tasks = parse_result($res, $connection_resourse, $sql, true);
		$tasks_list .= $tasks['name'];
		$task_dates .= $tasks['deadline'];
	}

	// Установим параметры сообщения: тема, отправитель и список его получателей
	$message = new Swift_Message();
	$message->setSubject("Уведомление от сервиса «Дела в порядке»");
	$message->setFrom(['keks@phpdemo.ru' => 'Doingsdone']);
	$message->setTo($u_email);

	// Передадим  сообщения
	$msg_content = "Уважаемый, $u_name. У вас запланирована задача " . $tasks_list . " на " . $task_dates;
	$message->setBody($msg_content, 'text/plain');

	$result = $mailer->send($message);

	// Если результат не был успешным, то мы можем узнать подробности ошибки вызовом метода из объекта для журналирования
	if ($result) {
	    print("Рассылка успешно отправлена");
	}
	else {
	    print("Не удалось отправить рассылку: " . $logger->dump());
	}
}