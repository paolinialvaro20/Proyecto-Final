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
   3. `database/permisos_por_rol.sql` (se ejecuta una sola vez)
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
Cada rol tiene las **funciones** que se le habilitan. Se guardan en la base con una relación N-N:

```
rol  ──<  rol_permiso  >──  permiso
(id, nombre)   (id_rol, id_permiso)   (id, clave, nombre, descripcion, grupo)
```

- Al **crear un rol** (Usuarios → Nuevo rol) se eligen con casillas las funciones que habilita.
- Al **editar un rol** (Usuarios → Editar rol) se puede cambiar el nombre y las funciones.
- **Listar roles** muestra las funciones de cada rol.
- Cada pantalla valida el permiso y el menú muestra solo lo que el rol puede usar.
- El rol **Administrador** siempre puede todo y no se puede modificar.
- Un rol sin funciones solo puede ver el inicio, cambiar su contraseña y cerrar sesión.
- Quien no es administrador no puede tocar cuentas de administradores, no puede dar permisos que él mismo no tiene y no puede modificar su propio rol.

Las 23 funciones (tabla `permiso`): listar / ver todas / ingresar / editar / cambiar estado de facturas; listar y agregar observaciones; listar / ver todos / registrar pagos; listar liquidaciones y liquidar repartidores o cajeros; listar / ingresar / editar / eliminar / cambiar estado de usuarios; listar / ingresar / editar / eliminar roles; ver auditoría.

Valores iniciales (según el diagrama de casos de uso), que carga `permisos_por_rol.sql`:

| | Cajero/a | Repartidor |
|---|:-:|:-:|
| Facturas: listar (todas) | ✔ | ✔ (solo las asignadas) |
| Facturas: ingresar, editar | ✔ | ✘ |
| Facturas: cambiar estado | ✔ | ✔ (solo las asignadas) |
| Observaciones: listar y agregar | ✔ | ✔ |
| Pagos: listar | ✔ (todos) | ✔ (solo los suyos) |
| Pagos: registrar | ✔ | ✔ |
| Liquidaciones, usuarios, roles, auditoría | ✘ | ✘ |

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
permisos.php       funciones para consultar y asignar permisos (rol ⇄ permiso)
permisos_ui.php    casillas de permisos que usan crear y editar rol
registrar_auditoria.php
menu.php, dd_ui.php, tema-don-diego.css   interfaz
database/          SQL de la base, migración y script del primer admin
```
