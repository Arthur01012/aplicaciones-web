-- Esquema en español para una instalación nueva dentro de la base asignada.
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

INSERT IGNORE INTO tareas (codigo, idcurso, titulo, fecha_entrega)
SELECT 'PHP-INICIO', idcurso, 'Introducción a PHP', NULL
FROM cursos WHERE codigo = 'PHP-WEB';

INSERT IGNORE INTO avisos (titulo, mensaje)
VALUES ('Bienvenida', 'El panel académico está listo para usarse.');
