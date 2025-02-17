//TODO Добавить команды на корректное заполнение всех таблиц

--Faculty
INSERT INTO Faculty (name_faculty, avggrade_for_budget, avggrade_for_paid, examgrade_for_budget, examgrade_for_paid)
VALUES
    ('Факультет информационных технологий', 4.50, 3.80, 250, 200),
    ('Факультет экономики и управления', 4.20, 3.60, 240, 190),
    ('Факультет медицины', 4.80, 4.00, 270, 220),
    ('Факультет юриспруденции', 4.30, 3.70, 245, 205),
    ('Факультет психологии', 4.10, 3.50, 235, 195),
    ('Факультет филологии и журналистики', 4.00, 3.40, 230, 190),
    ('Факультет международных отношений', 4.40, 3.75, 248, 210),
    ('Факультет прикладной математики', 4.60, 3.90, 255, 215),
    ('Факультет дизайна и искусства', 4.05, 3.45, 232, 192),
    ('Факультет экологии и природопользования', 4.25, 3.65, 242, 202);

--Employee
INSERT INTO Employee (fullname, email, login_employee, password_employee) VALUES
('Иван Иванов', 'ivan.ivanov@example.com', 'ivan_ivanov', 'password'),
('Петр Петров', 'petr.petrov@example.com', 'petr_petrov', 'password'),
('Сидор Сидоров', 'sidor.sidorov@example.com', 'sidor_sidorov', 'password'),
('Алексей Алексеев', 'alexey.alexeev@example.com', 'alexey_alexeev', 'password'),
('Михаил Михайлов', 'mikhail.mikhailov@example.com', 'mikhail_mikhailov', 'password'),
('Андрей Андреев', 'andrey.andreev@example.com', 'andrey_andreev', 'password'),
('Дмитрий Дмитриев', 'dmitry.dmitriev@example.com', 'dmitry_dmitriev', 'password'),
('Владимир Владимиров', 'vladimir.vladimirov@example.com', 'vladimir_vladimirov', 'password'),
('Николай Николаев', 'nikolay.nikolaev@example.com', 'nikolay_nikolaev', 'password'),
('Александр Александров', 'alexander.alexandrov@example.com', 'alexander_alexandrov', 'password');

--Faculty_Employee
INSERT INTO Faculty_Employee (faculty_id, employee_id) VALUES
(1, 1),
(2, 1),
(3, 2),
(4, 3),
(5, 3),
(6, 4),
(7, 5),
(8, 6),
(9, 7),
(10, 8),
(1, 9),
(2, 10);