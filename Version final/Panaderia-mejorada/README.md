# Panadería Don Diego · Sistema de gestión de deudas y pagos

Aplicación web (PHP + MySQL/MariaDB + Bootstrap 5) para controlar facturas, pagos, observaciones y liquidaciones diarias de repartidores y cajeros, con auditoría de todas las acciones.

## Requisitos
XAMPP (o cualquier Apache + PHP 8.1+ + MariaDB 10.4+).

## Instalación
1. Copiá la carpeta del proyecto en `htdocs` (por ejemplo `htdocs/panaderia`).
2. Iniciá Apache y MySQL desde XAMPP.
3. En phpMyAdmin creá la base `panaderia_deudas` e importá, **en este orden**:
   1. `database/panaderia_deudas.sql`
   2. `database/mejoras.sql` (se ejecuta una sola vez)
4. Si tu MySQL tiene otra contraseña/usuario, definí las variables de entorno `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` o editá los valores por defecto de `conexion.php`.
5. Entrá a `http://localhost/panaderia/`.

### Primer administrador
Si no conocés la contraseña de ningún administrador, creá uno por consola (la contraseña se pide por teclado):
```
php database/crear_admin.php
```
> Importante: `mejoras.sql` desactiva los usuarios cuya contraseña estaba guardada en texto plano
> (`Paolini` y `root` en el dump original). Reactivalos desde **Usuarios → Editar usuario**
> asignándoles una contraseña nueva, o dejalos desactivados.

## Roles y permisos
Los permisos se definen en `permisos.php` y **se deniegan por defecto**: un rol nuevo no puede hacer nada hasta que se agregue a la lista correspondiente.

| Acción | Administrador | Cajero/a | Repartidor |
|---|:-:|:-:|:-:|
| Ver facturas / pagos | todas | todas | solo las suyas |
| Crear, editar y cambiar estado de facturas | ✔ | ✔ | ✘ |
| Registrar pagos, observaciones | ✔ | ✔ | ✔ |
| Confirmar y ver liquidaciones | ✔ | ✘ | ✘ |
| Usuarios, roles, auditoría | ✔ | ✘ | ✘ |

Para cambiar quién puede liquidar, editá `ROLES_LIQUIDAN` en `permisos.php`.

## Seguridad incorporada
- Token CSRF en todos los formularios que modifican datos (`seguridad.php`).
- Cookie de sesión `HttpOnly` + `SameSite`, cierre por 30 min de inactividad y cierre inmediato si el usuario es desactivado.
- Bloqueo temporal de 15 min tras 5 intentos fallidos de login (por usuario) o 20 (por IP).
- Mensajes de login que no revelan si el usuario existe; los errores técnicos van al log, no a la pantalla.
- Contraseñas con `password_hash`, mínimo 8 caracteres.
- No se puede desactivar ni eliminar al último administrador, ni desactivarse/eliminarse a uno mismo.

## Estructura
```
seguridad.php      sesión, CSRF, cabeceras, timeout (se incluye al inicio de cada página)
conexion.php       conexión PDO
permisos.php       roles y permisos
registrar_auditoria.php
menu.php, dd_ui.php, tema-don-diego.css   interfaz
database/          SQL de la base, migración y script del primer admin
```
