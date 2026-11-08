CREATE DATABASE IF NOT EXISTS educontrol
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE educontrol;

CREATE TABLE IF NOT EXISTS users (
    user_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(80) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(120) NOT NULL,
    role VARCHAR(30) NOT NULL DEFAULT 'docente',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (user_id),
    UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS students (
    student_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_code VARCHAR(12) NOT NULL,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (student_id),
    UNIQUE KEY uq_students_code (student_code),
    UNIQUE KEY uq_students_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS courses (
    course_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    course_code VARCHAR(12) NOT NULL,
    name VARCHAR(120) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (course_id),
    UNIQUE KEY uq_courses_code (course_code)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS assignments (
    assignment_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    assignment_code VARCHAR(20) NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    title VARCHAR(160) NOT NULL,
    due_date DATE DEFAULT NULL,
    PRIMARY KEY (assignment_id),
    UNIQUE KEY uq_assignments_code (assignment_code),
    CONSTRAINT fk_assignments_course
        FOREIGN KEY (course_id) REFERENCES courses (course_id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS submissions (
    submission_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id INT UNSIGNED NOT NULL,
    assignment_id INT UNSIGNED NOT NULL,
    status ENUM('entregado', 'pendiente') NOT NULL DEFAULT 'pendiente',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (submission_id),
    UNIQUE KEY uq_submissions_student_assignment (student_id, assignment_id),
    KEY idx_submissions_status (status),
    KEY idx_submissions_updated (updated_at),
    CONSTRAINT fk_submissions_student
        FOREIGN KEY (student_id) REFERENCES students (student_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_submissions_assignment
        FOREIGN KEY (assignment_id) REFERENCES assignments (assignment_id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notices (
    notice_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(120) NOT NULL,
    message VARCHAR(500) NOT NULL,
    published_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (notice_id),
    UNIQUE KEY uq_notices_title_date (title, published_at),
    KEY idx_notices_active_date (is_active, published_at)
) ENGINE=InnoDB;

INSERT INTO users (username, password_hash, full_name, role, is_active)
VALUES (
    'carlos.mendoza',
    '$2y$12$2wx1K0GO1bp7S8ORKwbohOktn8VGqZxEj2uZMZF3gLb7uEyIx51iu',
    'Prof. Carlos Mendoza',
    'docente',
    1
)
ON DUPLICATE KEY UPDATE
    password_hash = VALUES(password_hash),
    full_name = VALUES(full_name),
    role = VALUES(role),
    is_active = VALUES(is_active);

INSERT INTO students (student_code, full_name, email, is_active)
VALUES
    ('A001', 'Ana Gómez', 'ana.gomez@educontrol.local', 1),
    ('A002', 'Luis Hernández', 'luis.hernandez@educontrol.local', 1),
    ('A003', 'María López', 'maria.lopez@educontrol.local', 1),
    ('A004', 'Jorge Ruiz', 'jorge.ruiz@educontrol.local', 1)
ON DUPLICATE KEY UPDATE
    full_name = VALUES(full_name),
    email = VALUES(email),
    is_active = VALUES(is_active);

INSERT INTO students (student_code, full_name, email, is_active)
SELECT
    CONCAT('A', LPAD(n.student_number, 3, '0')),
    CONCAT('Alumno ', LPAD(n.student_number, 3, '0')),
    CONCAT('alumno', LPAD(n.student_number, 3, '0'), '@educontrol.local'),
    1
FROM (
    SELECT d1.digit + d2.digit * 10 + d3.digit * 100 + 1 AS student_number
    FROM
        (SELECT 0 AS digit UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
         UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS d1
    CROSS JOIN
        (SELECT 0 AS digit UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
         UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS d2
    CROSS JOIN
        (SELECT 0 AS digit UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
         UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS d3
) AS n
WHERE n.student_number BETWEEN 5 AND 142
ON DUPLICATE KEY UPDATE
    full_name = VALUES(full_name),
    email = VALUES(email),
    is_active = VALUES(is_active);

INSERT INTO courses (course_code, name, is_active)
VALUES
    ('HTML', 'HTML5 & CSS3', 1),
    ('JS', 'JavaScript', 1),
    ('DB', 'Bases de Datos', 1),
    ('WEB', 'Desarrollo Web', 1)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    is_active = VALUES(is_active);

INSERT INTO assignments (assignment_code, course_id, title, due_date)
SELECT 'HTML-GRID', course_id, 'Práctica 2: Grid', '2026-11-15'
FROM courses WHERE course_code = 'HTML'
ON DUPLICATE KEY UPDATE title = VALUES(title), due_date = VALUES(due_date);

INSERT INTO assignments (assignment_code, course_id, title, due_date)
SELECT 'JS-FINAL', course_id, 'Proyecto Final', '2026-11-20'
FROM courses WHERE course_code = 'JS'
ON DUPLICATE KEY UPDATE title = VALUES(title), due_date = VALUES(due_date);

INSERT INTO assignments (assignment_code, course_id, title, due_date)
SELECT 'DB-EXAM', course_id, 'Examen Parcial', '2026-11-18'
FROM courses WHERE course_code = 'DB'
ON DUPLICATE KEY UPDATE title = VALUES(title), due_date = VALUES(due_date);

INSERT INTO assignments (assignment_code, course_id, title, due_date)
SELECT 'WEB-PROTOTYPE', course_id, 'Prototipo de plataforma', '2026-11-22'
FROM courses WHERE course_code = 'WEB'
ON DUPLICATE KEY UPDATE title = VALUES(title), due_date = VALUES(due_date);

INSERT INTO submissions (student_id, assignment_id, status, updated_at)
SELECT s.student_id, a.assignment_id, 'entregado', '2026-11-06 12:45:00'
FROM students AS s
JOIN assignments AS a ON a.assignment_code = 'HTML-GRID'
WHERE s.student_code = 'A001'
ON DUPLICATE KEY UPDATE status = VALUES(status), updated_at = VALUES(updated_at);

INSERT INTO submissions (student_id, assignment_id, status, updated_at)
SELECT s.student_id, a.assignment_id, 'pendiente', '2026-11-05 10:30:00'
FROM students AS s
JOIN assignments AS a ON a.assignment_code = 'JS-FINAL'
WHERE s.student_code = 'A002'
ON DUPLICATE KEY UPDATE status = VALUES(status), updated_at = VALUES(updated_at);

INSERT INTO submissions (student_id, assignment_id, status, updated_at)
SELECT s.student_id, a.assignment_id, 'entregado', '2026-11-04 15:10:00'
FROM students AS s
JOIN assignments AS a ON a.assignment_code = 'HTML-GRID'
WHERE s.student_code = 'A003'
ON DUPLICATE KEY UPDATE status = VALUES(status), updated_at = VALUES(updated_at);

INSERT INTO submissions (student_id, assignment_id, status, updated_at)
SELECT s.student_id, a.assignment_id, 'entregado', '2026-11-03 09:20:00'
FROM students AS s
JOIN assignments AS a ON a.assignment_code = 'DB-EXAM'
WHERE s.student_code = 'A004'
ON DUPLICATE KEY UPDATE status = VALUES(status), updated_at = VALUES(updated_at);

INSERT INTO submissions (student_id, assignment_id, status, updated_at)
SELECT s.student_id, a.assignment_id, 'pendiente',
    DATE_SUB('2026-10-28 09:00:00', INTERVAL n.day_offset DAY)
FROM (
    SELECT 1 AS day_offset UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
    UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8
    UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12
    UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15 UNION ALL SELECT 16
    UNION ALL SELECT 17
) AS n
JOIN students AS s
    ON s.student_code = CONCAT('A', LPAD(n.day_offset + 4, 3, '0'))
JOIN assignments AS a
    ON a.assignment_code = CASE MOD(n.day_offset - 1, 4)
        WHEN 0 THEN 'HTML-GRID'
        WHEN 1 THEN 'JS-FINAL'
        WHEN 2 THEN 'DB-EXAM'
        ELSE 'WEB-PROTOTYPE'
    END
ON DUPLICATE KEY UPDATE status = VALUES(status), updated_at = VALUES(updated_at);

INSERT INTO notices (title, message, published_at, is_active)
VALUES
    ('Reunión docente', 'Viernes 15:00 hrs.', '2026-10-20 09:00:00', 1),
    ('Cierre de actas', '28 de octubre', '2026-10-21 09:00:00', 1)
ON DUPLICATE KEY UPDATE
    message = VALUES(message),
    is_active = VALUES(is_active);
