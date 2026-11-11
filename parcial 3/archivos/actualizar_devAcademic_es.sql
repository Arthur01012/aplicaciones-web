-- Actualización aditiva para la base hospedada existente.
-- Conserva las tablas originales y agrega el esquema español de la aplicación.
USE innegone_bd_devacademic;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre_usuario VARCHAR(80) NULL,
    nombre VARCHAR(120) NOT NULL,
    email VARCHAR(190) NULL,
    psw VARCHAR(255) NOT NULL,
    rol VARCHAR(20) NOT NULL DEFAULT 'admin',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_nombre_usuario (nombre_usuario),
    UNIQUE KEY uq_usuarios_email (email),
    KEY idx_usuarios_rol_activo_es (rol, activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS nombre_usuario VARCHAR(80) NULL,
    MODIFY COLUMN email VARCHAR(190) NULL,
    ADD COLUMN IF NOT EXISTS activo TINYINT(1) NOT NULL DEFAULT 1;

ALTER TABLE usuarios
    ADD UNIQUE INDEX IF NOT EXISTS uq_usuarios_nombre_usuario (nombre_usuario),
    ADD INDEX IF NOT EXISTS idx_usuarios_rol_activo_es (rol, activo);

SET @usuarios_tiene_is_active = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'usuarios'
      AND COLUMN_NAME = 'is_active'
);
SET @actualizar_estado_usuarios = IF(
    @usuarios_tiene_is_active > 0,
    'UPDATE usuarios SET activo = is_active',
    'SELECT 1'
);
PREPARE consulta_estado_usuarios FROM @actualizar_estado_usuarios;
EXECUTE consulta_estado_usuarios;
DEALLOCATE PREPARE consulta_estado_usuarios;

CREATE TABLE IF NOT EXISTS estudiantes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    matricula VARCHAR(12) NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_estudiantes_matricula (matricula),
    UNIQUE KEY uq_estudiantes_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cursos (
    idcurso INT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(20) NULL,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10, 2) UNSIGNED NOT NULL DEFAULT 0.00,
    destacado TINYINT(1) NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (idcurso),
    UNIQUE KEY uq_cursos_codigo (codigo),
    KEY idx_cursos_destacado (destacado),
    KEY idx_cursos_activo (activo),
    KEY idx_cursos_titulo (titulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE cursos
    ADD COLUMN IF NOT EXISTS codigo VARCHAR(20) NULL,
    ADD COLUMN IF NOT EXISTS activo TINYINT(1) NOT NULL DEFAULT 1;

ALTER TABLE cursos
    ADD UNIQUE INDEX IF NOT EXISTS uq_cursos_codigo (codigo),
    ADD INDEX IF NOT EXISTS idx_cursos_activo (activo);

CREATE TABLE IF NOT EXISTS tareas (
    idtarea INT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(20) NOT NULL,
    idcurso INT UNSIGNED NOT NULL,
    titulo VARCHAR(160) NOT NULL,
    fecha_entrega DATE NULL,
    PRIMARY KEY (idtarea),
    UNIQUE KEY uq_tareas_codigo (codigo),
    KEY idx_tareas_curso (idcurso),
    CONSTRAINT fk_tareas_curso
        FOREIGN KEY (idcurso) REFERENCES cursos (idcurso)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS entregas (
    identrega INT UNSIGNED NOT NULL AUTO_INCREMENT,
    idestudiante INT UNSIGNED NOT NULL,
    idtarea INT UNSIGNED NOT NULL,
    estado ENUM('entregada', 'pendiente') NOT NULL DEFAULT 'pendiente',
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (identrega),
    UNIQUE KEY uq_entregas_estudiante_tarea (idestudiante, idtarea),
    KEY idx_entregas_estado (estado),
    KEY idx_entregas_actualizado (actualizado_en),
    CONSTRAINT fk_entregas_estudiante
        FOREIGN KEY (idestudiante) REFERENCES estudiantes (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_entregas_tarea
        FOREIGN KEY (idtarea) REFERENCES tareas (idtarea)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS avisos (
    idaviso INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titulo VARCHAR(120) NOT NULL,
    mensaje VARCHAR(500) NOT NULL,
    publicado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (idaviso),
    UNIQUE KEY uq_avisos_titulo_fecha (titulo, publicado_en),
    KEY idx_avisos_activo_fecha (activo, publicado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO usuarios (nombre_usuario, nombre, email, psw, rol, activo)
SELECT username, full_name, NULL, password_hash,
       IF(role = 'admin', 'admin', 'docente'), is_active
FROM users;

INSERT IGNORE INTO estudiantes (matricula, nombre, email, activo)
SELECT student_code, full_name, email, is_active
FROM students;

INSERT IGNORE INTO cursos (codigo, titulo, descripcion, precio, destacado, activo)
SELECT course_code, name, '', 0.00, 0, is_active
FROM courses;

INSERT IGNORE INTO tareas (codigo, idcurso, titulo, fecha_entrega)
SELECT tarea.assignment_code, curso.idcurso, tarea.title, tarea.due_date
FROM assignments AS tarea
INNER JOIN courses AS curso_origen ON curso_origen.course_id = tarea.course_id
INNER JOIN cursos AS curso ON curso.codigo = curso_origen.course_code;

INSERT IGNORE INTO entregas (idestudiante, idtarea, estado, actualizado_en)
SELECT estudiante.id, tarea.idtarea,
       IF(entrega.status = 'entregado', 'entregada', 'pendiente'),
       entrega.updated_at
FROM submissions AS entrega
INNER JOIN students AS estudiante_origen
    ON estudiante_origen.student_id = entrega.student_id
INNER JOIN estudiantes AS estudiante
    ON estudiante.matricula = estudiante_origen.student_code
INNER JOIN assignments AS tarea_origen
    ON tarea_origen.assignment_id = entrega.assignment_id
INNER JOIN tareas AS tarea ON tarea.codigo = tarea_origen.assignment_code;

INSERT IGNORE INTO avisos (titulo, mensaje, publicado_en, activo)
SELECT title, message, published_at, is_active
FROM notices;

INSERT IGNORE INTO cursos (codigo, titulo, descripcion, precio, destacado)
VALUES
    ('PHP-WEB', 'Desarrollo Web con PHP', 'Aprende PHP desde cero y crea aplicaciones web.', 199.99, 1),
    ('BOOTSTRAP5', 'Bootstrap 5 Avanzado', 'Domina los layouts y componentes de Bootstrap.', 49.99, 0);

INSERT IGNORE INTO estudiantes (matricula, nombre, email)
VALUES
    ('A001', 'Ana Gómez', 'ana.gomez@educontrol.local'),
    ('A002', 'Luis Hernández', 'luis.hernandez@educontrol.local'),
    ('A003', 'María López', 'maria.lopez@educontrol.local'),
    ('A004', 'Jorge Ruiz', 'jorge.ruiz@educontrol.local');
