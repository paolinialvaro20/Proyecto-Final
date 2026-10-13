-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-10-2026 a las 00:50:45
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `panaderia_deudas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_usuario` smallint(5) UNSIGNED NOT NULL,
  `accion` varchar(30) NOT NULL,
  `entidad` varchar(30) NOT NULL,
  `id_entidad` int(10) UNSIGNED DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `valor_anterior` text DEFAULT NULL,
  `valor_nuevo` text DEFAULT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `auditoria`
--

INSERT INTO `auditoria` (`id`, `id_usuario`, `accion`, `entidad`, `id_entidad`, `descripcion`, `valor_anterior`, `valor_nuevo`, `fecha_hora`) VALUES
(1, 4, 'cobro', 'Factura', 1, 'se paso a cobrar', 'Pendiente', 'cobrado', '2026-10-01 19:52:25'),
(3, 11, 'CREAR', 'Usuario', 21, 'Se creó el usuario poroto', NULL, 'Usuario: poroto | Rol: Repartidor | Estado: Activo', '2026-10-01 20:10:30'),
(4, 11, 'CREAR', 'Factura', 22, 'Se creó la factura sss', NULL, 'Factura: sss | Monto: $100000 | Estado: Cobrado | Asignado a: poroto', '2026-10-01 20:22:11'),
(5, 11, 'EDITAR', 'Usuario', 4, 'Se modificó el usuario Leon', 'Usuario: Leon | Rol: Repartidor | Estado: Activo', 'Usuario: Leon | Rol: Cajera | Estado: Activo | Contraseña modificada', '2026-10-01 20:32:31'),
(6, 11, 'EDITAR', 'Usuario', 4, 'Se modificó el usuario Leon', 'Usuario: Leon | Rol: Cajera | Estado: Activo', 'Usuario: Leon | Rol: Repartidor | Estado: Inactivo | Contraseña modificada', '2026-10-01 20:34:02'),
(7, 11, 'DESACTIVAR', 'Usuario', 13, 'Se desactivó el usuario roberto', 'Usuario: roberto | Rol: Cajera | Estado: Activo', 'Usuario: roberto | Rol: Cajera | Estado: Inactivo', '2026-10-01 20:41:46'),
(8, 11, 'ACTIVAR', 'Usuario', 4, 'Se activó el usuario Leon', 'Usuario: Leon | Rol: Repartidor | Estado: Inactivo', 'Usuario: Leon | Rol: Repartidor | Estado: Activo', '2026-10-01 21:11:02'),
(9, 11, 'CREAR', 'Rol', 10, 'Se creó el rol ayudate', NULL, 'Rol: ayudate', '2026-10-01 21:17:46'),
(10, 11, 'EDITAR', 'Rol', 10, 'Se modificó el rol ayudate por comedor', 'Rol: ayudate', 'Rol: comedor', '2026-10-01 21:24:10'),
(11, 11, 'ELIMINAR', 'Rol', 10, 'Se eliminó el rol comedor', 'Rol: comedor', 'Rol eliminado', '2026-10-01 21:27:53'),
(12, 11, 'CREAR', 'Factura', 23, 'Se creó la factura sssddd', NULL, 'Factura: sssddd | Monto: $12346 | Estado: Pendiente | Asignado a: poroto', '2026-10-01 21:33:12'),
(13, 11, 'COBRAR', 'Factura', 23, 'Se marcó como cobrada la factura sssddd', 'Factura: sssddd | Monto: $12346.00 | Estado: Pendiente | Asignado a: poroto', 'Factura: sssddd | Monto: $5000000 | Estado: Cobrado | Asignado a: poroto | Fecha cobro: 2026-10-02 02:37:34', '2026-10-01 21:37:34'),
(14, 11, 'EDITAR', 'Factura', 22, 'Se modificó la factura sss', 'Factura: sss | Monto: $100000.00 | Estado: Cobrado | Asignado a: poroto | Fecha cobro: 2026-10-02 02:31:58', 'Factura: sss | Monto: $100000.00 | Estado: Pendiente | Asignado a: poroto', '2026-10-01 21:38:44'),
(15, 11, 'COBRAR', 'Factura', 22, 'Se marcó como cobrada la factura sss', 'Factura: sss | Monto: $100000.00 | Estado: Pendiente | Asignado a: poroto', 'Factura: sss | Monto: $100000.00 | Estado: Cobrado | Asignado a: poroto | Fecha cobro: 2026-10-02 02:39:57', '2026-10-01 21:39:57'),
(16, 11, 'ANULAR', 'Factura', 22, 'Se anuló la factura sss', 'Factura: sss | Monto: $100000.00 | Estado: Cobrado | Asignado a: poroto | Fecha cobro: 2026-10-02 02:39:57', 'Factura: sss | Monto: $100000.00 | Estado: Anulada | Asignado a: poroto', '2026-10-01 21:41:23'),
(17, 11, 'CREAR', 'Factura', 24, 'Se creó la factura s', NULL, 'Factura: s | Monto: $12000 | Estado: Pendiente | Asignado a: poroto', '2026-10-01 21:43:50'),
(18, 11, 'COBRAR', 'Factura', 24, 'Se marcó como cobrada la factura s', 'Factura: s | Monto: $12000.00 | Estado: Pendiente | Asignado a: poroto', 'Factura: s | Monto: $12000.00 | Estado: Cobrado | Asignado a: poroto | Fecha cobro: 2026-10-01 21:48:07', '2026-10-01 21:48:07'),
(19, 11, 'CONSULTAR', 'Observacion', 15, 'Se consultaron las observaciones de la factura F58', NULL, 'Factura: F58 | Cantidad de observaciones: 2', '2026-10-01 21:55:03'),
(20, 11, 'CONSULTAR', 'Observacion', 24, 'Se consultaron las observaciones de la factura s', NULL, 'Factura: s | Cantidad de observaciones: 0', '2026-10-01 21:55:10'),
(21, 11, 'CONSULTAR', 'Observacion', 15, 'Se consultaron las observaciones de la factura F58', NULL, 'Factura: F58 | Cantidad de observaciones: 2', '2026-10-01 21:55:12'),
(22, 11, 'CREAR', 'Observacion', 7, 'Se agregó una observación a la factura sss', NULL, 'Factura: sss | Observación: el repartido se durmió', '2026-10-01 21:59:28'),
(23, 11, 'CONSULTAR', 'Observacion', 22, 'Se consultaron las observaciones de la factura sss', NULL, 'Factura: sss | Cantidad de observaciones: 1', '2026-10-01 21:59:57'),
(24, 11, 'CREAR', 'Observacion', 8, 'Se agregó una observación a la factura sss', NULL, 'Factura: sss | Observación: hey tu que pasa con esta factura??', '2026-10-01 22:03:54'),
(25, 11, 'CERRAR SESION', 'Sesion', NULL, 'El usuario cerró sesión en el sistema', 'Sesión activa', 'Sesión cerrada', '2026-10-01 22:07:32'),
(26, 11, 'CERRAR SESION', 'Sesion', NULL, 'El usuario cerró sesión en el sistema', 'Sesión activa', 'Sesión cerrada', '2026-10-01 22:08:08'),
(27, 11, 'EDITAR', 'Usuario', 19, 'Se modificó el usuario Costa', 'Usuario: Costa | Rol: Cajera | Estado: Activo', 'Usuario: Costa | Rol: Cajera | Estado: Activo | Contraseña modificada', '2026-10-01 22:08:32'),
(28, 11, 'CERRAR SESION', 'Sesion', NULL, 'El usuario cerró sesión en el sistema', 'Sesión activa', 'Sesión cerrada', '2026-10-01 22:08:38'),
(29, 19, 'CERRAR SESION', 'Sesion', NULL, 'El usuario cerró sesión en el sistema', 'Sesión activa', 'Sesión cerrada', '2026-10-01 22:08:55'),
(30, 11, 'CERRAR SESION', 'Sesion', NULL, 'El usuario cerró sesión en el sistema', 'Sesión activa', 'Sesión cerrada', '2026-10-01 22:13:05'),
(31, 11, 'INICIAR SESION', 'Sesion', NULL, 'El usuario inició sesión en el sistema', NULL, 'Sesión iniciada', '2026-10-01 22:13:12'),
(32, 11, 'COBRAR', 'Factura', 14, 'Se marcó como cobrada la factura S575757', 'Factura: S575757 | Monto: $25000.00 | Estado: Pendiente | Asignado a: Sin usuario asignado', 'Factura: S575757 | Monto: $25000.00 | Estado: Cobrado | Asignado a: Paolini | Fecha cobro: 2026-10-03 02:09:34', '2026-10-02 21:09:34'),
(33, 11, 'EDITAR', 'Factura', 14, 'Se modificó la factura S575757', 'Factura: S575757 | Monto: $25000.00 | Estado: Cobrado | Asignado a: Paolini | Fecha cobro: 2026-10-03 02:09:34', 'Factura: S575757 | Monto: $25000.00 | Estado: Cobrado | Asignado a: Paolini | Fecha cobro: 2026-10-03 02:09:42', '2026-10-02 21:09:42'),
(34, 11, 'EDITAR', 'Factura', 5, 'Se modificó la factura Z6575', 'Factura: Z6575 | Monto: $890.00 | Estado: Pendiente | Asignado a: Sin usuario asignado', 'Factura: Z6575 | Monto: $890.00 | Estado: Pendiente | Asignado a: Pablo', '2026-10-02 21:15:46'),
(35, 11, 'REGISTRAR PAGO', 'Pago', 1, 'Se registró el pago de la factura Z6575', 'Factura pendiente de cobro', 'Pago ID: 1 | Factura: Z6575 | Repartidor: Pablo | Monto: $890.00 | Forma de pago: Efectivo | Fecha: 2026-10-02 21:17:34 | Liquidado: No', '2026-10-02 21:17:34'),
(36, 11, 'EDITAR', 'Factura', 5, 'Se modificó la factura Z6575', 'Factura: Z6575 | Monto: $890.00 | Estado: Cobrado | Asignado a: Pablo | Fecha cobro: 2026-10-02 21:17:34', 'Factura: Z6575 | Monto: $890.00 | Estado: Pendiente | Asignado a: Pablo', '2026-10-02 21:26:57'),
(37, 11, 'COBRAR', 'Factura', 18, 'Se marcó como cobrada la factura hg6545', 'Factura: hg6545 | Monto: $45678.00 | Estado: Pendiente | Asignado a: Sin usuario asignado', 'Factura: hg6545 | Monto: $45678.00 | Estado: Cobrado | Asignado a: Sin usuario asignado | Fecha cobro: 2026-10-04 15:54:55', '2026-10-04 15:54:55'),
(38, 11, 'COBRAR', 'Factura', 6, 'Se marcó como cobrada la factura 44324', 'Factura: 44324 | Monto: $500000.00 | Estado: Pendiente | Asignado a: Sin usuario asignado', 'Factura: 44324 | Monto: $500000.00 | Estado: Cobrado | Asignado a: Sin usuario asignado | Fecha cobro: 2026-10-04 15:55:08', '2026-10-04 15:55:08'),
(39, 11, 'CREAR', 'Factura', 25, 'Se creó la factura ghug', NULL, 'Factura: ghug | Monto: $454657 | Estado: Pendiente | Asignado a: Paolini', '2026-10-04 15:56:00'),
(40, 11, 'COBRAR', 'Factura', 25, 'Se marcó como cobrada la factura ghug', 'Factura: ghug | Monto: $454657.00 | Estado: Pendiente | Asignado a: Paolini', 'Factura: ghug | Monto: $454657.00 | Estado: Cobrado | Asignado a: Paolini | Fecha cobro: 2026-10-04 20:56:36', '2026-10-04 15:56:36'),
(41, 11, 'CREAR', 'Usuario', 22, 'Se creó el usuario Luciano', NULL, 'Usuario: Luciano | Rol: Repartidor | Estado: Activo', '2026-10-04 16:06:19'),
(42, 11, 'EDITAR', 'Usuario', 14, 'Se modificó el usuario Pablo', 'Usuario: Pablo | Rol: Cajera | Estado: Activo', 'Usuario: Pablo | Rol: Repartidor | Estado: Activo | Contraseña modificada', '2026-10-04 16:07:56'),
(43, 11, 'CREAR', 'Usuario', 23, 'Se creó el usuario Juan Pablo', NULL, 'Usuario: Juan Pablo | Rol: Repartidor | Estado: Activo', '2026-10-04 16:08:41'),
(44, 11, 'CREAR', 'Usuario', 24, 'Se creó el usuario Flor', NULL, 'Usuario: Flor | Rol: Cajera | Estado: Activo', '2026-10-04 16:09:17'),
(45, 11, 'CREAR', 'Usuario', 25, 'Se creó el usuario Dana', NULL, 'Usuario: Dana | Rol: Administrador | Estado: Activo', '2026-10-04 16:09:37'),
(46, 11, 'CREAR', 'Usuario', 26, 'Se creó el usuario Patricia P', NULL, 'Usuario: Patricia P | Rol: Administrador | Estado: Activo', '2026-10-04 16:09:58'),
(47, 11, 'CREAR', 'Usuario', 27, 'Se creó el usuario Hector O', NULL, 'Usuario: Hector O | Rol: Administrador | Estado: Activo', '2026-10-04 16:10:18'),
(48, 11, 'CREAR', 'Usuario', 28, 'Se creó el usuario Gabriel', NULL, 'Usuario: Gabriel | Rol: Cajera | Estado: Activo', '2026-10-04 16:17:34'),
(49, 11, 'CREAR', 'Usuario', 29, 'Se creó el usuario Yeilan', NULL, 'Usuario: Yeilan | Rol: Cajera | Estado: Activo', '2026-10-04 16:17:56'),
(50, 11, 'CREAR', 'Usuario', 30, 'Se creó el usuario Juan O', NULL, 'Usuario: Juan O | Rol: Repartidor | Estado: Activo', '2026-10-04 16:34:26'),
(51, 11, 'EDITAR', 'Factura', 4, 'Se modificó la factura T75659', 'Factura: T75659 | Monto: $89765.00 | Estado: Pendiente | Asignado a: Sin usuario asignado', 'Factura: T75659 | Monto: $89765.00 | Estado: Pendiente | Asignado a: Luciano', '2026-10-04 16:37:56'),
(52, 11, 'EDITAR', 'Factura', 1, 'Se modificó la factura A2563', 'Factura: A2563 | Monto: $1599.00 | Estado: Pendiente | Asignado a: Sin usuario asignado', 'Factura: A2563 | Monto: $1599.00 | Estado: Pendiente | Asignado a: Juan Pablo', '2026-10-04 16:38:27'),
(53, 11, 'REGISTRAR PAGO', 'Pago', 2, 'Se registró el pago de la factura T75659 cobrado por Flor', 'Factura pendiente de cobro', 'Factura: T75659 | Asignada a: Luciano | Cobrado por: Flor | Monto: $89765.00 | Forma de pago: Efectivo | Liquidado: No', '2026-10-04 16:39:04'),
(54, 11, 'REGISTRAR PAGO', 'Pago', 3, 'Se registró el pago de la factura A2563 cobrado por Juan Pablo', 'Factura pendiente de cobro', 'Factura: A2563 | Asignada a: Juan Pablo | Cobrado por: Juan Pablo | Monto: $1599.00 | Forma de pago: Efectivo | Liquidado: No', '2026-10-04 23:11:40'),
(55, 11, 'CREAR', 'Factura', 26, 'Se creó la factura ghjgjhg', NULL, 'Factura: ghjgjhg | Monto: $45678 | Estado: Pendiente | Asignado a: Luciano', '2026-10-04 23:47:49'),
(56, 11, 'REGISTRAR PAGO', 'Pago', 4, 'Se registró el pago de la factura ghjgjhg cobrado por Luciano', 'Factura pendiente de cobro', 'Factura: ghjgjhg | Asignada a: Luciano | Cobrado por: Luciano | Monto: $45678.00 | Forma de pago: Efectivo | Liquidado: No', '2026-10-04 23:48:30'),
(57, 11, 'CREAR', 'Factura', 27, 'Se creó la factura jhh', NULL, 'Factura: jhh | Monto: $56464 | Estado: Pendiente | Asignado a: Paolini', '2026-10-06 20:09:06'),
(58, 11, 'REGISTRAR PAGO', 'Pago', 5, 'Se registró el pago de la factura jhh cobrado por Paolini', 'Factura pendiente de cobro', 'Factura: jhh | Asignada a: Paolini | Cobrado por: Paolini | Monto: $56464.00 | Forma de pago: Debito | Liquidado: No', '2026-10-06 20:09:40'),
(59, 11, 'LIQUIDAR', 'Pago', 5, 'Se liquidó el pago de la factura jhh correspondiente al repartidor Paolini', 'Liquidado: No', 'Liquidado: Sí | Fecha de liquidación: 2026-10-06 | Monto: $56464.00', '2026-10-06 20:11:58'),
(60, 11, 'CREAR', 'Factura', 28, 'Se creó la factura hgjj', NULL, 'Factura: hgjj | Monto: $5677778 | Estado: Pendiente | Asignado a: Juan Pablo', '2026-10-06 20:22:49'),
(61, 11, 'REGISTRAR PAGO', 'Pago', 6, 'Se registró el pago de la factura hgjj cobrado por Juan Pablo', 'Factura pendiente de cobro', 'Factura: hgjj | Asignada a: Juan Pablo | Cobrado por: Juan Pablo | Monto: $999999.99 | Forma de pago: Debito | Liquidado: No', '2026-10-06 20:23:10'),
(62, 11, 'LIQUIDAR', 'Pago', 6, 'Se liquidó el pago de la factura hgjj correspondiente al repartidor Juan Pablo', 'Liquidado: No', 'Liquidado: Sí | Fecha de liquidación: 2026-10-06 | Monto: $999999.99', '2026-10-06 20:23:47'),
(63, 11, 'CREAR', 'Factura', 29, 'Se creó la factura q2343', NULL, 'Factura: q2343 | Monto: $10 | Estado: Pendiente | Asignado a: Luciano', '2026-10-06 20:36:52'),
(64, 11, 'REGISTRAR PAGO', 'Pago', 7, 'Se registró el pago de la factura q2343 cobrado por Dana', 'Factura pendiente de cobro', 'Factura: q2343 | Asignada a: Luciano | Cobrado por: Dana | Monto: $10.00 | Forma de pago: Efectivo | Liquidado: No', '2026-10-06 20:37:57'),
(65, 11, 'CREAR', 'Factura', 30, 'Se creó la factura s3456', NULL, 'Factura: s3456 | Monto: $940.01 | Estado: Pendiente | Asignado a: pepe2026', '2026-10-06 20:39:14'),
(66, 11, 'REGISTRAR PAGO', 'Pago', 8, 'Se registró el pago de la factura s3456 cobrado por pepe2026', 'Factura pendiente de cobro', 'Factura: s3456 | Asignada a: pepe2026 | Cobrado por: pepe2026 | Monto: $940.01 | Forma de pago: Debito | Liquidado: No', '2026-10-06 20:39:37'),
(67, 11, 'LIQUIDAR', 'Pago', 8, 'Se liquidó el pago de la factura s3456 correspondiente al repartidor pepe2026', 'Liquidado: No', 'Liquidado: Sí | Fecha de liquidación: 2026-10-06 | Monto: $940.01', '2026-10-06 20:40:06'),
(68, 11, 'CREAR', 'Factura', 31, 'Se creó la factura jbi', NULL, 'Factura: jbi | Monto: $56476 | Estado: Pendiente | Asignado a: Juan O', '2026-10-06 20:55:30'),
(69, 11, 'REGISTRAR PAGO', 'Pago', 9, 'Se registró el pago de la factura jbi cobrado por Juan O', 'Factura pendiente de cobro', 'Factura: jbi | Asignada a: Juan O | Cobrado por: Juan O | Monto: $56476.00 | Forma de pago: Efectivo | Liquidado: No', '2026-10-06 20:55:47'),
(70, 11, 'LIQUIDAR', 'Pago', 9, 'Se liquidó el pago de la factura jbi correspondiente al repartidor Juan O', 'Liquidado: No', 'Liquidado: Sí | Fecha de liquidación: 2026-10-06 | Monto: $56476.00', '2026-10-06 20:56:08'),
(71, 11, 'CREAR', 'Factura', 32, 'Se creó la factura jhvj', NULL, 'Factura: jhvj | Monto: $7655 | Estado: Pendiente | Asignado a: Pablo', '2026-10-06 22:32:19'),
(72, 11, 'REGISTRAR PAGO', 'Pago', 10, 'Se registró el pago de la factura jhvj cobrado por Pablo', 'Factura pendiente de cobro', 'Factura: jhvj | Asignada a: Pablo | Cobrado por: Pablo | Monto: $7655.00 | Forma de pago: Efectivo | Liquidado: No', '2026-10-06 22:32:37'),
(73, 11, 'LIQUIDAR', 'Pago', 10, 'Se liquidó el pago de la factura jhvj correspondiente al repartidor Pablo', 'Liquidado: No', 'Liquidado: Sí | Fecha de liquidación: 2026-10-06 | Monto: $7655.00', '2026-10-06 22:33:12'),
(74, 11, 'CERRAR SESION', 'Sesion', NULL, 'El usuario cerró sesión en el sistema', 'Sesión activa', 'Sesión cerrada', '2026-10-07 20:16:26'),
(75, 11, 'INICIAR SESION', 'Sesion', NULL, 'El usuario inició sesión en el sistema', NULL, 'Sesión iniciada', '2026-10-07 20:16:35'),
(76, 11, 'CREAR', 'Factura', 33, 'Se creó la factura hgjh', NULL, 'Factura: hgjh | Monto: $432435 | Estado: Pendiente | Asignado a: Juan Pablo', '2026-10-07 20:19:34'),
(77, 11, 'REGISTRAR PAGO', 'Pago', 11, 'Se registró el pago de la factura hgjh cobrado por Pablo', 'Factura pendiente de cobro', 'Factura: hgjh | Asignada a: Juan Pablo | Cobrado por: Pablo | Monto: $432435.00 | Forma de pago: Efectivo | Liquidado: No', '2026-10-07 20:19:54'),
(78, 11, 'LIQUIDAR', 'Pago', 11, 'Se liquidó el pago de la factura hgjh correspondiente al repartidor Pablo', 'Liquidado: No', 'Liquidado: Sí | Fecha de liquidación: 2026-10-07 | Monto: $432435.00', '2026-10-07 20:20:21'),
(79, 11, 'CREAR', 'Factura', 34, 'Se creó la factura hjdm', NULL, 'Factura: hjdm | Monto: $345 | Estado: Pendiente | Asignado a: Paolini', '2026-10-07 20:21:38'),
(80, 11, 'CREAR', 'Factura', 35, 'Se creó la factura gfg', NULL, 'Factura: gfg | Monto: $4567 | Estado: Pendiente | Asignado a: Paolini', '2026-10-07 20:21:49'),
(81, 11, 'CREAR', 'Factura', 36, 'Se creó la factura ghfjhhk', NULL, 'Factura: ghfjhhk | Monto: $67789 | Estado: Pendiente | Asignado a: Paolini', '2026-10-07 20:22:03'),
(82, 11, 'REGISTRAR PAGO', 'Pago', 12, 'Se registró el pago de la factura hjdm cobrado por Paolini', 'Factura pendiente de cobro', 'Factura: hjdm | Asignada a: Paolini | Cobrado por: Paolini | Monto: $345.00 | Forma de pago: Debito | Liquidado: No', '2026-10-07 20:23:24'),
(83, 11, 'REGISTRAR PAGO', 'Pago', 13, 'Se registró el pago de la factura ghfjhhk cobrado por Paolini', 'Factura pendiente de cobro', 'Factura: ghfjhhk | Asignada a: Paolini | Cobrado por: Paolini | Monto: $67789.00 | Forma de pago: Efectivo | Liquidado: No', '2026-10-07 20:24:12'),
(84, 11, 'REGISTRAR PAGO', 'Pago', 14, 'Se registró el pago de la factura gfg cobrado por Paolini', 'Factura pendiente de cobro', 'Factura: gfg | Asignada a: Paolini | Cobrado por: Paolini | Monto: $4567.00 | Forma de pago: Efectivo | Liquidado: No', '2026-10-07 20:24:32'),
(85, 11, 'LIQUIDAR', 'Pago', 12, 'Se liquidó el pago de la factura hjdm correspondiente al repartidor Paolini', 'Liquidado: No', 'Liquidado: Sí | Fecha de liquidación: 2026-10-07 | Monto: $345.00', '2026-10-07 20:25:05'),
(86, 11, 'LIQUIDAR', 'Pago', 13, 'Se liquidó el pago de la factura ghfjhhk correspondiente al repartidor Paolini', 'Liquidado: No', 'Liquidado: Sí | Fecha de liquidación: 2026-10-07 | Monto: $67789.00', '2026-10-07 20:25:05'),
(87, 11, 'LIQUIDAR', 'Pago', 14, 'Se liquidó el pago de la factura gfg correspondiente al repartidor Paolini', 'Liquidado: No', 'Liquidado: Sí | Fecha de liquidación: 2026-10-07 | Monto: $4567.00', '2026-10-07 20:25:05'),
(88, 11, 'CREAR', 'Factura', 37, 'Se creó la factura dfsdfg', NULL, 'Factura: dfsdfg | Monto: $4567 | Estado: Pendiente | Asignado a: Luciano', '2026-10-07 21:07:55'),
(89, 11, 'REGISTRAR PAGO', 'Pago', 15, 'Se registró el pago de la factura dfsdfg cobrado por Luciano', 'Factura pendiente de cobro', 'Factura: dfsdfg | Asignada a: Luciano | Cobrado por: Luciano | Monto: $4567.00 | Forma de pago: Efectivo | Liquidado: No', '2026-10-07 21:08:09'),
(90, 11, 'LIQUIDAR', 'Pago', 15, 'Se liquidó el pago de la factura dfsdfg correspondiente al repartidor Luciano', 'Liquidado: No', 'Liquidado: Sí | Fecha de liquidación: 2026-10-07 | Monto: $4567.00', '2026-10-07 21:08:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura`
--

CREATE TABLE `factura` (
  `id` int(10) UNSIGNED NOT NULL,
  `numero_serie` varchar(20) NOT NULL,
  `monto` decimal(8,2) NOT NULL,
  `fecha_registro` datetime NOT NULL,
  `fecha_cobro` datetime DEFAULT NULL,
  `estado` enum('Cobrado','Pendiente','Anulada') NOT NULL DEFAULT 'Pendiente',
  `id_usuario_registro` smallint(5) UNSIGNED NOT NULL,
  `id_usuario_asignado` smallint(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `factura`
--

INSERT INTO `factura` (`id`, `numero_serie`, `monto`, `fecha_registro`, `fecha_cobro`, `estado`, `id_usuario_registro`, `id_usuario_asignado`) VALUES
(1, 'A2563', 1599.00, '2026-06-24 01:59:48', '2026-10-04 23:11:40', 'Cobrado', 1, 23),
(2, 'R4I5U5', 1200.00, '2026-08-04 22:07:39', NULL, 'Pendiente', 1, 0),
(3, '809765', 1342.00, '2026-08-04 22:08:12', NULL, 'Pendiente', 1, 0),
(4, 'T75659', 89765.00, '2026-08-04 22:08:38', '2026-10-04 16:39:04', 'Cobrado', 1, 22),
(5, 'Z6575', 890.00, '2026-08-04 22:09:18', NULL, 'Pendiente', 1, 14),
(6, '44324', 500000.00, '2026-08-04 22:10:34', '2026-10-04 15:55:08', 'Cobrado', 1, 0),
(7, 'a287340', 977.00, '2026-08-04 22:16:22', NULL, 'Pendiente', 1, 0),
(8, 'A2998', 1239.77, '2026-08-04 22:19:51', NULL, 'Pendiente', 1, 0),
(11, 'A344', 345.00, '2026-08-04 22:24:02', NULL, 'Pendiente', 1, 0),
(13, 'W74567', 7351.00, '2026-08-04 22:25:10', NULL, 'Pendiente', 1, 0),
(14, 'S575757', 25000.00, '2026-08-12 20:45:59', '2026-10-03 02:09:42', 'Cobrado', 1, 1),
(15, 'F58', 500.00, '2026-08-12 20:46:47', NULL, 'Pendiente', 1, 0),
(16, 'A5678', 87656.00, '2026-08-12 21:06:38', NULL, 'Pendiente', 1, 0),
(18, 'hg6545', 45678.00, '2026-08-18 20:03:06', '2026-10-04 15:54:55', 'Cobrado', 1, 0),
(19, 'Z0987', 1234.00, '2026-08-28 21:38:34', '2026-09-08 21:06:44', 'Cobrado', 1, 0),
(20, 'dfbd', 1234.00, '2026-09-11 01:05:35', NULL, 'Pendiente', 11, 19),
(21, 'hjhduwe', 123456.00, '2026-09-17 00:00:54', '2026-09-16 19:01:37', 'Cobrado', 11, 4),
(22, 'sss', 100000.00, '2026-10-02 01:22:11', '2026-10-01 21:42:43', 'Cobrado', 11, 21),
(23, 'sssddd', 999999.99, '2026-10-02 02:33:12', '2026-10-02 02:37:34', 'Cobrado', 11, 21),
(24, 's', 12000.00, '2026-10-02 02:43:50', '2026-10-01 21:48:07', 'Cobrado', 11, 21),
(25, 'ghug', 454657.00, '2026-10-04 20:56:00', '2026-10-04 20:56:36', 'Cobrado', 11, 1),
(26, 'ghjgjhg', 45678.00, '2026-10-05 04:47:49', '2026-10-04 23:48:30', 'Cobrado', 11, 22),
(27, 'jhh', 56464.00, '2026-10-07 01:09:06', '2026-10-06 20:09:40', 'Cobrado', 11, 1),
(28, 'hgjj', 999999.99, '2026-10-07 01:22:49', '2026-10-06 20:23:10', 'Cobrado', 11, 23),
(29, 'q2343', 10.00, '2026-10-07 01:36:52', '2026-10-06 20:37:57', 'Cobrado', 11, 22),
(30, 's3456', 940.01, '2026-10-07 01:39:14', '2026-10-06 20:39:37', 'Cobrado', 11, 20),
(31, 'jbi', 56476.00, '2026-10-07 01:55:30', '2026-10-06 20:55:47', 'Cobrado', 11, 30),
(32, 'jhvj', 7655.00, '2026-10-07 03:32:19', '2026-10-06 22:32:37', 'Cobrado', 11, 14),
(33, 'hgjh', 432435.00, '2026-10-08 01:19:34', '2026-10-07 20:19:54', 'Cobrado', 11, 23),
(34, 'hjdm', 345.00, '2026-10-08 01:21:38', '2026-10-07 20:23:24', 'Cobrado', 11, 1),
(35, 'gfg', 4567.00, '2026-10-08 01:21:49', '2026-10-07 20:24:32', 'Cobrado', 11, 1),
(36, 'ghfjhhk', 67789.00, '2026-10-08 01:22:03', '2026-10-07 20:24:12', 'Cobrado', 11, 1),
(37, 'dfsdfg', 4567.00, '2026-10-08 02:07:55', '2026-10-07 21:08:09', 'Cobrado', 11, 22);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `liquidacion`
--

CREATE TABLE `liquidacion` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_repartidor` smallint(5) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `total_efectivo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_transferencia` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_general` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cantidad_pagos` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `fecha_hora_generacion` datetime NOT NULL DEFAULT current_timestamp(),
  `id_usuario_genera` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `observacion`
--

CREATE TABLE `observacion` (
  `id` int(10) UNSIGNED NOT NULL,
  `detalle` varchar(255) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `id_FACTURA_PENDIENTE` int(10) UNSIGNED NOT NULL,
  `id_USUARIO` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `observacion`
--

INSERT INTO `observacion` (`id`, `detalle`, `fecha_hora`, `id_FACTURA_PENDIENTE`, `id_USUARIO`) VALUES
(1, 'Fui a cobrarle y le largo los perros, me dijo que le vaya a cobrar a Malaquina', '2026-06-24 02:01:17', 1, 1),
(2, 'Fui a cobrarle y le largo los perros, me dijo que le vaya a cobrar a Malaquina', '2026-06-24 02:01:17', 1, 1),
(3, 'khgddkjchdlcj', '2026-09-09 03:17:47', 15, 11),
(4, 'hfghgf', '2026-09-09 03:18:44', 15, 11),
(5, 'ojeda quedo en la casa y no cobro la factura', '2026-09-17 00:02:32', 8, 11),
(6, 'svhkfvbdfkhvjb', '2026-10-02 00:40:49', 20, 11),
(7, 'el repartido se durmió', '2026-10-02 02:59:28', 22, 11),
(8, 'hey tu que pasa con esta factura??', '2026-10-02 03:03:54', 22, 11);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago`
--

CREATE TABLE `pago` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_factura` int(10) UNSIGNED NOT NULL,
  `id_repartidor` smallint(5) UNSIGNED NOT NULL,
  `monto` decimal(8,2) NOT NULL,
  `forma_pago` enum('Efectivo','Transferencia','Otro') NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `liquidado` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pago`
--

INSERT INTO `pago` (`id`, `id_factura`, `id_repartidor`, `monto`, `forma_pago`, `fecha_hora`, `liquidado`) VALUES
(1, 5, 14, 890.00, 'Efectivo', '2026-10-02 21:17:34', 0),
(2, 4, 24, 89765.00, 'Efectivo', '2026-10-04 16:39:04', 0),
(3, 1, 23, 1599.00, 'Efectivo', '2026-10-04 23:11:40', 0),
(4, 26, 22, 45678.00, 'Efectivo', '2026-10-04 23:48:30', 0),
(5, 27, 1, 56464.00, '', '2026-10-06 20:09:40', 1),
(6, 28, 23, 999999.99, '', '2026-10-06 20:23:10', 1),
(7, 29, 25, 10.00, 'Efectivo', '2026-10-06 20:37:57', 0),
(8, 30, 20, 940.01, '', '2026-10-06 20:39:37', 1),
(9, 31, 30, 56476.00, 'Efectivo', '2026-10-06 20:55:47', 1),
(10, 32, 14, 7655.00, 'Efectivo', '2026-10-06 22:32:37', 1),
(11, 33, 14, 432435.00, 'Efectivo', '2026-10-07 20:19:54', 1),
(12, 34, 1, 345.00, '', '2026-10-07 20:23:24', 1),
(13, 36, 1, 67789.00, 'Efectivo', '2026-10-07 20:24:12', 1),
(14, 35, 1, 4567.00, 'Efectivo', '2026-10-07 20:24:32', 1),
(15, 37, 22, 4567.00, 'Efectivo', '2026-10-07 21:08:09', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id`, `nombre`) VALUES
(1, 'Administrador'),
(2, 'Cajera'),
(3, 'Repartidor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `usuario` varchar(32) NOT NULL,
  `contrasenia` varchar(255) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `id_ROL` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `usuario`, `contrasenia`, `activo`, `id_ROL`) VALUES
(1, 'Paolini', '1234567890', 1, 3),
(2, 'Nicola', '$2y$10$k7HCCSowUTpq4.yAwkbe7ey9lG1X8zirOhyTuYnLffyowUtZAiJKW', 1, 1),
(4, 'Leon', '$2y$10$TBnW2azfqmmRKN97qqnfKe.9inLlDuIljJI1KYYMsypUPYAtCVwe2', 1, 3),
(10, 'root', 'root', 1, 1),
(11, 'admin', '$2y$10$tXO1eXbaaf4kR7UET85hB.J00oa1syiuaSjwMpQJ4R15gvPqDp4MK', 1, 1),
(13, 'roberto', '$2y$10$JQiss3i2Pg6uoeo1mTmtteRGbf3tGi76omBhrBN69YXfHwJXO5YqG', 0, 2),
(14, 'Pablo', '$2y$10$chPiKK/9Dlr5PvX2/qna6u1Fy8PRDKn12bJvFL2nR2ywM0ZCwAlMm', 1, 3),
(15, 'felipe', '$2y$10$sYO8Uf.h4vrOC4lyt6YaK.ZDR5bjxcgAWZciSCv70WgY0y9AwpjBG', 1, 1),
(16, 'otro', '$2y$10$o3K7DJvotjN4k8OlNyrT.O1ExZw4qMdbUYDx4q66PhcKgxFoejqkG', 1, 3),
(17, 'luciana', '$2y$10$Z0csM1v2CGh133PakJyubeoJX1GzcEjrUHO0lnqSIRxp10c.rXaL2', 1, 1),
(18, 'algo', '$2y$10$ojF8aupyyzfRm1TK9ek/ZexGDY0h7wCEv0L0lPXJxw40yymmrL7VG', 1, 1),
(19, 'Costa', '$2y$10$/ZjebvX.9FbWmRKo8SUB4uV481vJ9j6He1Gx5kV15qUHdShSVCI92', 1, 2),
(20, 'pepe2026', '$2y$10$QmU.QguAlhFOkCfTuWN8uuqfAsrkaxCkQjhGam9xiuave1CQXdQyu', 1, 3),
(21, 'poroto', '$2y$10$L82A41Vvmcl.q8m.Kn5u8eky9hPaI5XpyDdI7E4hUeH0OrR7ViWDO', 1, 3),
(22, 'Luciano', '$2y$10$LIpY4yQOH5I8KOwOr5ICS.pJYCfWwo9h/FK96XUrln1NN0cBqxBSe', 1, 3),
(23, 'Juan Pablo', '$2y$10$nT7mgpy2JyyqnneNOIhZs.n1ROJ9B3mLUExhFhFaG1Af8PZw6irjq', 1, 3),
(24, 'Flor', '$2y$10$chcFugc8H2dFecQV0Mlt1u14eA6ip.57xVPVjuT/IIrlC7vgIVV0C', 1, 2),
(25, 'Dana', '$2y$10$LRkt0U7pYQoWx6IkIclMX.QNiOEYr5jnMppXNBJC1WlcV049H15Mi', 1, 1),
(26, 'Patricia P', '$2y$10$Fa7b8D3tPIgX4AbQ51CP8uwtziBXu7u44.aVuVKQMa5fBX5UTGhW2', 1, 1),
(27, 'Hector O', '$2y$10$naUS79uB973T5fblCcNuwuwTV/sIkgXpoiAVjKb1L9edMvz.k/tWy', 1, 1),
(28, 'Gabriel', '$2y$10$7JO6hDvk3ahAjUSvc.suTOGBxfkkPyjmNyK17nYJ6Ji4TElGqkRgO', 1, 2),
(29, 'Yeilan', '$2y$10$LRk8DDlmTpY/G9a.I2/pjuzfWblfMX63dJbNf478JkioZT6uMDoia', 1, 2),
(30, 'Juan O', '$2y$10$clgtKv.V7F/6TZpHmP6w9uwImHIhy0.4c0AcyzH0C93SISp0Pkj9.', 1, 3);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_auditoria_usuario` (`id_usuario`);

--
-- Indices de la tabla `factura`
--
ALTER TABLE `factura`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_serie` (`numero_serie`),
  ADD KEY `fk_factura_usuario` (`id_usuario_registro`);

--
-- Indices de la tabla `liquidacion`
--
ALTER TABLE `liquidacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_liquidacion_repartidor` (`id_repartidor`),
  ADD KEY `fk_liquidacion_usuario` (`id_usuario_genera`);

--
-- Indices de la tabla `observacion`
--
ALTER TABLE `observacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_observacion_factura` (`id_FACTURA_PENDIENTE`),
  ADD KEY `fk_observacion_usuario` (`id_USUARIO`);

--
-- Indices de la tabla `pago`
--
ALTER TABLE `pago`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pago_factura` (`id_factura`),
  ADD KEY `fk_pago_repartidor` (`id_repartidor`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `fk_usuario_rol` (`id_ROL`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT de la tabla `factura`
--
ALTER TABLE `factura`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `liquidacion`
--
ALTER TABLE `liquidacion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `observacion`
--
ALTER TABLE `observacion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `pago`
--
ALTER TABLE `pago`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD CONSTRAINT `fk_auditoria_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `fk_factura_usuario` FOREIGN KEY (`id_usuario_registro`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `liquidacion`
--
ALTER TABLE `liquidacion`
  ADD CONSTRAINT `fk_liquidacion_repartidor` FOREIGN KEY (`id_repartidor`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `fk_liquidacion_usuario` FOREIGN KEY (`id_usuario_genera`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `observacion`
--
ALTER TABLE `observacion`
  ADD CONSTRAINT `fk_observacion_factura` FOREIGN KEY (`id_FACTURA_PENDIENTE`) REFERENCES `factura` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_observacion_usuario` FOREIGN KEY (`id_USUARIO`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `pago`
--
ALTER TABLE `pago`
  ADD CONSTRAINT `fk_pago_factura` FOREIGN KEY (`id_factura`) REFERENCES `factura` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pago_repartidor` FOREIGN KEY (`id_repartidor`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_ROL`) REFERENCES `rol` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
