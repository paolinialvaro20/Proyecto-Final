-- =====================================================================
-- PERMISOS POR ROL  ·  Panadería Don Diego
-- Relación N-N:   rol  ──<  rol_permiso  >──  permiso
-- Ejecutar UNA SOLA VEZ (después de mejoras.sql).
--
-- Si ya habías ejecutado una versión anterior de este archivo, este script
-- reemplaza la tabla rol_permiso por la definitiva (los permisos de los roles
-- se vuelven a cargar con los valores iniciales de abajo).
-- El rol Administrador no necesita filas: siempre puede todo.
-- =====================================================================
USE panaderia_deudas;

DROP TABLE IF EXISTS rol_permiso;
DROP TABLE IF EXISTS permiso;

-- Catálogo de funciones del sistema
CREATE TABLE permiso (
  id          smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  clave       varchar(50)  NOT NULL,   -- identificador que usa el código (no cambiar)
  nombre      varchar(60)  NOT NULL,   -- lo que ve el usuario
  descripcion varchar(160) NOT NULL,
  grupo       varchar(30)  NOT NULL,   -- para agrupar en pantalla
  PRIMARY KEY (id),
  UNIQUE KEY uq_permiso_clave (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla intermedia de la relación N-N
CREATE TABLE rol_permiso (
  id_rol     tinyint(3)   UNSIGNED NOT NULL,
  id_permiso smallint(5)  UNSIGNED NOT NULL,
  PRIMARY KEY (id_rol, id_permiso),
  KEY idx_rol_permiso_permiso (id_permiso),
  CONSTRAINT fk_rol_permiso_rol FOREIGN KEY (id_rol) REFERENCES rol (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_rol_permiso_permiso FOREIGN KEY (id_permiso) REFERENCES permiso (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO permiso (clave, nombre, descripcion, grupo) VALUES
('facturas.listar',          'Listar facturas',             'Ver el listado de facturas',                                           'Facturas'),
('facturas.ver_todas',       'Ver facturas de todos',       'Si no se marca, solo ve las facturas que tiene asignadas',             'Facturas'),
('facturas.crear',           'Ingresar factura',            'Registrar facturas nuevas',                                            'Facturas'),
('facturas.editar',          'Editar factura',              'Modificar los datos de una factura',                                   'Facturas'),
('facturas.cambiar_estado',  'Cambiar estado de factura',   'Pasar una factura a Pendiente, Cobrado o Anulada',                     'Facturas'),
('observaciones.listar',     'Listar observaciones',        'Ver las observaciones de las facturas',                                'Observaciones'),
('observaciones.agregar',    'Agregar observación',         'Escribir observaciones en una factura',                                'Observaciones'),
('pagos.listar',             'Listar pagos',                'Ver el listado de pagos',                                              'Pagos'),
('pagos.ver_todos',          'Ver pagos de todos',          'Si no se marca, solo ve los pagos que cobró él mismo',                 'Pagos'),
('pagos.registrar',          'Registrar pago',              'Registrar el cobro de una factura',                                    'Pagos'),
('liquidaciones.listar',     'Listar liquidaciones',        'Ver el listado de liquidaciones realizadas',                           'Liquidaciones'),
('liquidaciones.repartidor', 'Liquidar repartidores',       'Confirmar la liquidación diaria de un repartidor',                     'Liquidaciones'),
('liquidaciones.cajero',     'Liquidar cajeros',            'Confirmar la liquidación diaria de un cajero',                         'Liquidaciones'),
('usuarios.listar',          'Listar usuarios',             'Ver el listado de usuarios',                                           'Usuarios'),
('usuarios.crear',           'Ingresar usuario',            'Crear usuarios nuevos (nunca administradores)',                        'Usuarios'),
('usuarios.editar',          'Editar usuario',              'Modificar usuarios (nunca administradores)',                           'Usuarios'),
('usuarios.eliminar',        'Eliminar usuario',            'Eliminar (desactivar) usuarios',                                       'Usuarios'),
('usuarios.cambiar_estado',  'Cambiar estado de usuario',   'Activar o desactivar usuarios',                                        'Usuarios'),
('roles.listar',             'Listar roles',                'Ver el listado de roles y sus permisos',                               'Roles'),
('roles.crear',              'Ingresar rol',                'Crear roles nuevos y elegir sus permisos',                             'Roles'),
('roles.editar',             'Editar rol',                  'Cambiar el nombre y los permisos de un rol (solo los que uno mismo tiene)', 'Roles'),
('roles.eliminar',           'Eliminar rol',                'Eliminar roles que no tengan usuarios',                                'Roles'),
('auditoria.ver',            'Ver auditoría',               'Consultar el historial de acciones del sistema',                       'Auditoría');

-- Valores iniciales según el diagrama de casos de uso -------------------

-- Cajero/a: facturas completas, observaciones y pagos.
INSERT IGNORE INTO rol_permiso (id_rol, id_permiso)
SELECT r.id, p.id FROM rol r JOIN permiso p
WHERE LOWER(TRIM(r.nombre)) IN ('cajero', 'cajera')
  AND p.clave IN ('facturas.listar', 'facturas.ver_todas', 'facturas.crear', 'facturas.editar',
                  'facturas.cambiar_estado', 'observaciones.listar', 'observaciones.agregar',
                  'pagos.listar', 'pagos.ver_todos', 'pagos.registrar');

-- Repartidor: ve solo lo suyo, cambia el estado de sus facturas,
-- agrega/lista observaciones y registra sus cobros.
INSERT IGNORE INTO rol_permiso (id_rol, id_permiso)
SELECT r.id, p.id FROM rol r JOIN permiso p
WHERE LOWER(TRIM(r.nombre)) = 'repartidor'
  AND p.clave IN ('facturas.listar', 'facturas.cambiar_estado', 'observaciones.listar',
                  'observaciones.agregar', 'pagos.listar', 'pagos.registrar');
