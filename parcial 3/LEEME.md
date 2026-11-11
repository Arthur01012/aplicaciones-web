# Parcial 3: prácticas DevAcademy

Las prácticas comparten la conexión MySQLi y la autenticación definidas en `config/`.
Configura estas variables en el servidor PHP antes de abrir cualquier práctica:

- `DB_HOST`
- `DB_USER`
- `DB_PASSWORD`
- `DB_NAME`

En Windows PowerShell, durante una sesión local:

```powershell
$env:DB_HOST = "servidor"
$env:DB_USER = "usuario"
$env:DB_PASSWORD = "contraseña"
$env:DB_NAME = "base_de_datos"
php -S 127.0.0.1:8000 -t "parcial 3"
```

Para una instalación vacía, revisa `archivos/devAcademic_es.sql`. Para actualizar la
base hospedada sin reemplazar sus tablas, revisa y ejecuta `archivos/actualizar_devAcademic_es.sql`
después de crear un respaldo. No ejecutes el volcado original `devAcademic1.sql` en
la base compartida: contiene instrucciones que reemplazan tablas.

El script de migración copia estudiantes, cursos, tareas, entregas, avisos y cuentas
de `users` a las tablas españolas. Conserva las tablas originales. Las cuentas
migradas mantienen su usuario y hash de contraseña; las cuentas de administrador
se crean localmente con `2_login/crear_admin.php` usando variables de entorno.
En la práctica 6, los administradores inician con correo y los docentes con su
nombre de usuario. `6_conjuntio/database.sql` se conserva como aviso: no se importa,
porque el esquema oficial está en los dos scripts anteriores.

Orden de prácticas: conexión, login, logout/dashboard, alta, CRUD e integración.
