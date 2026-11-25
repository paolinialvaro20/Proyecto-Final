-- =====================================================================
-- MEJORAS DE BASE DE DATOS  ·  Panadería Don Diego
-- Ejecutar UNA SOLA VEZ, después de importar panaderia_deudas.sql.
-- Pensado para MariaDB 10.4+ (XAMPP). Hacer una copia de seguridad antes.
-- =====================================================================
USE panaderia_deudas;

-- 1) Auditoría: el usuario puede ser NULL (intentos de login con un
--    usuario que no existe) y se guarda la IP de origen.
ALTER TABLE auditoria MODIFY id_usuario smallint(5) UNSIGNED NULL;
ALTER TABLE auditoria ADD COLUMN IF NOT EXISTS ip varchar(45) NULL AFTER valor_nuevo;
CREATE INDEX IF NOT EXISTS idx_auditoria_fecha ON auditoria (fecha_hora);

-- 2) Registro de intentos de login fallidos (bloqueo temporal anti fuerza bruta).
CREATE TABLE IF NOT EXISTS intento_login (
  id         int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario    varchar(32) NOT NULL,
  ip         varchar(45) NOT NULL DEFAULT '',
  fecha_hora datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY idx_intento_usuario (usuario, fecha_hora),
  KEY idx_intento_ip (ip, fecha_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3) factura.id_usuario_asignado no tenía clave foránea (y era "signed").
--    Primero se limpian asignaciones a usuarios que no existen
--    (en el dump original: la factura 111 apuntaba al usuario 16, inexistente).
UPDATE factura f
LEFT JOIN usuario u ON u.id = f.id_usuario_asignado
SET f.id_usuario_asignado = NULL
WHERE f.id_usuario_asignado IS NOT NULL AND u.id IS NULL;

ALTER TABLE factura MODIFY id_usuario_asignado smallint(5) UNSIGNED NULL;
ALTER TABLE factura
  ADD CONSTRAINT fk_factura_usuario_asignado
  FOREIGN KEY (id_usuario_asignado) REFERENCES usuario (id) ON UPDATE CASCADE;

-- 4) Una factura solo puede tener un pago (hasta ahora lo garantizaba solo el PHP).
ALTER TABLE pago ADD UNIQUE KEY uq_pago_factura (id_factura);

-- 5) Los montos deben ser positivos.
ALTER TABLE factura ADD CONSTRAINT chk_factura_monto CHECK (monto > 0);
ALTER TABLE pago    ADD CONSTRAINT chk_pago_monto    CHECK (monto > 0);

-- 6) Nombres de rol únicos y índices para los listados / filtros.
ALTER TABLE rol ADD UNIQUE KEY uq_rol_nombre (nombre);
CREATE INDEX IF NOT EXISTS idx_factura_estado ON factura (estado);
CREATE INDEX IF NOT EXISTS idx_factura_fecha  ON factura (fecha_registro);
CREATE INDEX IF NOT EXISTS idx_pago_fecha     ON pago (fecha_hora);

-- 7) Usuarios con la contraseña guardada en texto plano (no es un hash bcrypt):
--    en el dump original eran 'Paolini' y 'root'. Se desactivan; un
--    administrador debe reactivarlos y asignarles una contraseña nueva desde
--    "Editar usuario" (que sí la guarda con hash).
UPDATE usuario SET activo = 0 WHERE contrasenia NOT LIKE '$2y$%';

-- 8) REVISAR A MANO (no se modifica nada automáticamente):
--    pagos marcados como liquidados pero sin liquidación asociada
--    (en el dump original: pagos 86 y 88).
--    SELECT * FROM pago WHERE liquidado = 1 AND id_liquidacion IS NULL;
