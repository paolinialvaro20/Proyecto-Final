# Cambios respecto a la versión original (Panaderia-v3)

## Errores corregidos
- **Login con usuario inexistente fallaba con error 500**: la auditoría exigía un usuario y recibía `NULL`. Ahora la columna acepta `NULL` y un fallo de auditoría ya no rompe el login.
- **Agregar factura mostraba los errores en verde** como si fueran éxito, y aceptaba montos 0, seriales repetidos (error de BD) y usuarios inválidos.
- **Editar factura** permitía cambiar monto o estado de una factura ya pagada (quedaba inconsistente con el pago), y aceptaba monto 0.
- **Cambiar estado de factura** podía hacerlo cualquier usuario (incluso un repartidor), y permitía pasar a *Pendiente* o *Anulada* una factura con pago.
- **`editar_usuario.php` y `eliminar_usuario.php`** imprimían el menú *antes* del `<!DOCTYPE>` (modo quirks); además eliminar tenía el título "Crear usuario".
- **Eliminar usuario no dejaba rastro en la auditoría.**
- Una sesión abierta seguía funcionando después de desactivar al usuario.
- `crear_usuario.php` tenía variables sin inicializar y no validaba el rol.
- El nombre de archivo `cambiar_contraseña.php` (con ñ) pasó a `cambiar_contrasenia.php`.
- Paginación del inicio de 4 en 4 → 15.
- Pantalla de bienvenida del login: 2,8 s en cada ingreso → 1,5 s y solo la primera vez por pestaña.

## Seguridad
- Protección CSRF en los 16 formularios POST.
- Permisos "denegar por defecto" (antes cualquier rol nuevo era administrador) y permisos faltantes en liquidaciones de cajero, listado de usuarios/roles y cambio de estado.
- Los repartidores solo ven sus facturas y sus pagos.
- Bloqueo por intentos fallidos, sesión endurecida, timeout, errores técnicos fuera de pantalla.
- Protección del último administrador.
- `.htaccess` para bloquear `.sql`, listado de carpetas y la carpeta `database/`.

## Base de datos (`database/mejoras.sql`)
- FK en `factura.id_usuario_asignado` (no existía; había una asignación a un usuario 16 inexistente).
- `UNIQUE` en `pago.id_factura`, `CHECK monto > 0`, `UNIQUE` en `rol.nombre`, índices para los listados.
- Tabla `intento_login`, columna `auditoria.ip`.
- Desactiva usuarios con contraseña en texto plano.

## No se tocó (conviene hacerlo más adelante)
- Las páginas `liquidacion_*` y `*_cajero` están duplicadas casi línea por línea: unificarlas en una sola parametrizada por rol.
- Pagos 86 y 88 del dump: marcados como liquidados pero sin liquidación (ver consulta al final de `mejoras.sql`).
- El cierre de sesión es por GET; lo ideal es que sea POST con token.
- Quedan carpetas con versiones viejas en el repo original (`Panaderia`, `Panaderia-v2`, `COSTA`): conviene dejar una sola y usar ramas/tags de Git.

## Permisos por rol (segunda ronda)
- **Base de datos:** nueva tabla `permiso` (catálogo de 23 funciones) y tabla intermedia `rol_permiso` → relación N-N con `rol` (`database/permisos_por_rol.sql`, ejecutar una vez).
- **Ingresar rol:** ahora se eligen con casillas las funciones que habilita el rol.
- **Editar rol:** permite cambiar nombre y funciones. Se corrigió además una comilla sin cerrar en el HTML y el largo máximo del nombre (la columna admite 20, el campo aceptaba 100).
- **Listar roles:** nueva columna con las funciones de cada rol.
- Cada página y cada enlace del menú validan la función correspondiente.
- Alcance de datos por permiso: sin "Ver facturas de todos" / "Ver pagos de todos" solo se ven los propios (también en Cambiar estado de factura).
- Administrador intocable; quien no es administrador no puede editar administradores, asignar ese rol, dar permisos que no tiene ni modificar su propio rol; el nombre "Administrador" queda reservado.
- Valores iniciales de Cajero/a y Repartidor tomados del diagrama de casos de uso.
