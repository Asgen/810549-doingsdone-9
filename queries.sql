INSERT INTO users
SET email = 'rick@gmail.com', password = 'myspew', name = 'Rick', datetime_add = NOW();
INSERT INTO users
SET email = 'morty@gmail.com', password = 'ammieforever', name = 'Morty', datetime_add = NOW();

INSERT INTO projects (name, user_id)
VALUES ('Входящие', 1), ('Учеба', 2), ('Работа', 1), ('Домашние дела', 1), ('Авто', 2);

INSERT INTO tasks
SET name = 'Собеседование в IT компании', datetime_add = '2018-01-11', deadline = '2018-01-12', user_id = 2, project_id = 3;

INSERT INTO tasks
SET name = 'Выполнить тестовое задание', datetime_add = '2019-04-11', deadline = '2019-04-21', user_id = 1, project_id = 3;

INSERT INTO tasks
SET name = 'Сделать задание первого раздела', datetime_add = '2019-04-20', status = 1, deadline = '2019-04-23', user_id = 2, project_id = 2;

INSERT INTO tasks
SET name = 'Встерча с другом', datetime_add = '2018-12-12', deadline = '2018-12-22', user_id = 2, project_id = 1;

INSERT INTO tasks
SET name = 'Купить корм для кота', datetime_add = NOW(), user_id = 1, project_id = 4;

INSERT INTO tasks
SET name = 'Заказать пиццу', datetime_add = NOW(), user_id = 1, project_id = 4;