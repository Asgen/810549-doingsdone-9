INSERT INTO users
SET email = 'rick@gmail.com', password = 'myspew', name = 'Rick';
INSERT INTO users
SET email = 'morty@gmail.com', password = 'ammieforever', name = 'Morty';

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
SET name = 'Купить корм для кота', user_id = 1, project_id = 4;

INSERT INTO tasks
SET name = 'Заказать пиццу', user_id = 1, project_id = 4;

/* получить список из всех проектов для одного пользователя. Объедините проекты с задачами, чтобы посчитать количество задач в каждом проекте и в дальнейшем выводить эту цифру рядом с именем проекта*/
SELECT p.NAME AS category, COUNT(t.id) AS tasks_total FROM projects AS p
JOIN tasks AS t ON p.id = t.project_id WHERE p.user_id = 1
GROUP BY p.name;


/* получить список из всех задач для одного проекта */
SELECT t.* FROM tasks AS t
INNER JOIN projects p ON t.project_id = p.id
WHERE p.id = 3;

/* пометить задачу как выполненную */
UPDATE tasks SET status = 1 WHERE id = 1;

/* обновить название задачи по её идентификатору */
UPDATE tasks SET name = 'Новая задача' WHERE id = 1;
