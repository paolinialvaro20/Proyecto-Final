-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-11-2026 a las 20:05:38
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
  `id_usuario` smallint(5) UNSIGNED DEFAULT NULL,
  `accion` varchar(30) NOT NULL,
  `entidad` varchar(30) NOT NULL,
  `id_entidad` int(10) UNSIGNED DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `valor_anterior` text DEFAULT NULL,
  `valor_nuevo` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `auditoria`
--

INSERT INTO `auditoria` (`id`, `id_usuario`, `accion`, `entidad`, `id_entidad`, `descripcion`, `valor_anterior`, `valor_nuevo`, `ip`, `fecha_hora`) VALUES
(365, 11, 'CERRAR SESIÓN', 'Usuario', 11, 'El usuario admin cerró su sesión correctamente', 'Sesión iniciada', 'Sesión cerrada', NULL, '2026-11-09 01:01:35'),
(366, 11, 'INICIO DE SESIÓN', 'Usuario', 11, 'El usuario admin inició sesión correctamente', '-', 'Sesión iniciada', NULL, '2026-11-09 01:01:40'),
(367, 11, 'CREAR', 'Factura', 112, 'Se creó la factura ASE', NULL, 'Factura: ASE | Monto: $123,00 | Estado: Pendiente | Asignado a: Luciano', NULL, '2026-11-09 01:05:36'),
(368, 11, 'CERRAR SESIÓN', 'Usuario', 11, 'El usuario admin cerró su sesión correctamente', 'Sesión iniciada', 'Sesión cerrada', NULL, '2026-11-18 22:36:56'),
(369, 11, 'INICIO DE SESIÓN', 'Usuario', 11, 'El usuario admin inició sesión correctamente', '-', 'Sesión iniciada', NULL, '2026-11-20 18:24:44'),
(370, 11, 'CERRAR SESIÓN', 'Usuario', 11, 'El usuario admin cerró su sesión correctamente', 'Sesión iniciada', 'Sesión cerrada', NULL, '2026-11-20 18:34:48'),
(371, 11, 'INICIO DE SESIÓN', 'Usuario', 11, 'El usuario admin inició sesión correctamente', '-', 'Sesión iniciada', NULL, '2026-11-20 18:37:43'),
(372, 11, 'CERRAR SESIÓN', 'Usuario', 11, 'El usuario admin cerró su sesión correctamente', 'Sesión iniciada', 'Sesión cerrada', NULL, '2026-11-20 19:05:35'),
(373, 11, 'INICIO DE SESIÓN', 'Usuario', 11, 'El usuario admin inició sesión correctamente', '-', 'Sesión iniciada', NULL, '2026-11-20 19:05:44'),
(374, 11, 'CERRAR SESIÓN', 'Usuario', 11, 'El usuario admin cerró su sesión correctamente', 'Sesión iniciada', 'Sesión cerrada', NULL, '2026-11-20 19:05:51'),
(375, 11, 'INICIO DE SESIÓN', 'Usuario', 11, 'El usuario admin inició sesión correctamente', '-', 'Sesión iniciada', NULL, '2026-11-20 19:06:59'),
(376, 11, 'CERRAR SESIÓN', 'Usuario', 11, 'El usuario admin cerró su sesión correctamente', 'Sesión iniciada', 'Sesión cerrada', NULL, '2026-11-20 19:07:32'),
(377, 11, 'INICIO DE SESIÓN', 'Usuario', 11, 'El usuario admin inició sesión correctamente', '-', 'Sesión iniciada', NULL, '2026-11-20 19:07:40'),
(378, 11, 'INICIO DE SESIÓN', 'Usuario', 11, 'El usuario admin inició sesión correctamente', '-', 'Sesión iniciada', NULL, '2026-11-20 19:18:01'),
(379, 11, 'CERRAR SESIÓN', 'Usuario', 11, 'El usuario admin cerró su sesión correctamente', 'Sesión iniciada', 'Sesión cerrada', NULL, '2026-11-20 19:23:44'),
(380, 11, 'INICIO DE SESIÓN', 'Usuario', 11, 'El usuario admin inició sesión correctamente', '-', 'Sesión iniciada', NULL, '2026-11-20 19:25:33'),
(381, 11, 'CERRAR SESIÓN', 'Usuario', 11, 'El usuario admin cerró su sesión correctamente', 'Sesión iniciada', 'Sesión cerrada', NULL, '2026-11-20 19:26:08'),
(382, 11, 'INICIO DE SESIÓN', 'Usuario', 11, 'El usuario admin inició sesión correctamente', '-', 'Sesión iniciada', NULL, '2026-11-20 19:38:11'),
(383, 11, 'CERRAR SESIÓN', 'Usuario', 11, 'El usuario admin cerró su sesión correctamente', 'Sesión iniciada', 'Sesión cerrada', NULL, '2026-11-20 19:45:48'),
(384, 11, 'INICIO DE SESIÓN', 'Usuario', 11, 'El usuario admin inició sesión correctamente', '-', 'Sesión iniciada', NULL, '2026-11-20 20:07:13'),
(385, 11, 'CERRAR SESIÓN', 'Usuario', 11, 'El usuario admin cerró su sesión correctamente', 'Sesión iniciada', 'Sesión cerrada', NULL, '2026-11-20 21:49:46'),
(386, 2, 'INICIO DE SESIÓN FALLIDO', 'Usuario', 2, 'Intento de inicio de sesión fallido para el usuario: Nicola', NULL, NULL, NULL, '2026-11-20 21:50:05'),
(387, 2, 'INICIO DE SESIÓN FALLIDO', 'Usuario', 2, 'Intento de inicio de sesión fallido para el usuario: Nicola', NULL, NULL, NULL, '2026-11-20 21:51:08'),
(388, 2, 'INICIO DE SESIÓN FALLIDO', 'Usuario', 2, 'Intento de inicio de sesión fallido para el usuario: Nicola', NULL, NULL, NULL, '2026-11-20 21:51:21'),
(389, 1, 'INICIO DE SESIÓN FALLIDO', 'Usuario', 1, 'Intento de inicio de sesión fallido para el usuario: Paolini', NULL, NULL, NULL, '2026-11-20 21:51:44'),
(390, 11, 'INICIO DE SESIÓN', 'Usuario', 11, 'El usuario admin inició sesión correctamente', '-', 'Sesión iniciada', NULL, '2026-11-20 21:51:59'),
(391, 11, 'EDITAR', 'Usuario', 2, 'Se modificó el usuario Nicola', 'Contraseña: anterior', 'Contraseña: modificada', NULL, '2026-11-20 21:52:15'),
(392, 11, 'EDITAR', 'Usuario', 1, 'Se modificó el usuario Paolini', 'Contraseña: anterior', 'Contraseña: modificada', NULL, '2026-11-20 21:52:27'),
(393, 11, 'CERRAR SESIÓN', 'Usuario', 11, 'El usuario admin cerró su sesión correctamente', 'Sesión iniciada', 'Sesión cerrada', NULL, '2026-11-20 21:52:30'),
(394, 2, 'INICIO DE SESIÓN', 'Usuario', 2, 'El usuario Nicola inició sesión correctamente', '-', 'Sesión iniciada', NULL, '2026-11-20 21:52:40'),
(395, 2, 'CREAR', 'Factura', 113, 'Se creó la factura GHF', NULL, 'Factura: GHF | Monto: $123,00 | Estado: Pendiente | Asignado a: Nicola', NULL, '2026-11-20 21:53:11'),
(396, 2, 'REGISTRAR', 'Pago', 92, 'Se registró el pago de la factura GHF cobrado por Paolini', 'Factura pendiente de cobro', 'Factura: GHF | Asignada a: Nicola | Cobrado por: Paolini | Monto: $123.00 | Forma de pago: Efectivo | Liquidado: No', NULL, '2026-11-20 21:53:25'),
(397, 2, 'LIQUIDACION REPARTIDOR', 'Liquidacion', 8, 'Liquidación del repartidor Paolini', 'Sin liquidar', 'ID liquidación: 8 | Fecha de pagos: 2026-11-20 | Cantidad: 1 | Total: $123,00 | IDs de pagos: 92', NULL, '2026-11-20 21:53:49'),
(398, 2, 'CERRAR SESIÓN', 'Usuario', 2, 'El usuario Nicola cerró su sesión correctamente', 'Sesión iniciada', 'Sesión cerrada', NULL, '2026-11-20 23:10:16'),
(399, 11, 'INICIO DE SESIÓN', 'Usuario', 11, 'El usuario admin inició sesión correctamente', '-', 'Sesión iniciada', '::1', '2026-11-25 11:29:55'),
(400, 11, 'CREAR', 'Rol', 13, 'Se creó el rol que', NULL, 'Nombre: que | Permisos: facturas.listar, observaciones.listar, pagos.listar, liquidaciones.listar, usuarios.listar, roles.listar, auditoria.ver', '::1', '2026-11-25 11:32:50'),
(401, 11, 'CREAR', 'Usuario', 36, 'Se creó el usuario algo', NULL, 'Usuario: algo | Rol: que | Estado: Activo', '::1', '2026-11-25 11:33:29'),
(402, 11, 'CERRAR SESIÓN', 'Usuario', 11, 'El usuario admin cerró su sesión correctamente', 'Sesión iniciada', 'Sesión cerrada', '::1', '2026-11-25 11:33:33'),
(403, 36, 'INICIO DE SESIÓN', 'Usuario', 36, 'El usuario algo inició sesión correctamente', '-', 'Sesión iniciada', '::1', '2026-11-25 11:33:42'),
(404, 36, 'CERRAR SESIÓN', 'Usuario', 36, 'El usuario algo cerró su sesión correctamente', 'Sesión iniciada', 'Sesión cerrada', '::1', '2026-11-25 11:33:53'),
(405, 11, 'INICIO DE SESIÓN', 'Usuario', 11, 'El usuario admin inició sesión correctamente', '-', 'Sesión iniciada', '::1', '2026-11-25 11:34:01'),
(406, 11, 'INICIO DE SESIÓN', 'Usuario', 11, 'El usuario admin inició sesión correctamente', '-', 'Sesión iniciada', '::1', '2026-11-25 12:06:38'),
(407, 11, 'CERRAR SESIÓN', 'Usuario', 11, 'El usuario admin cerró su sesión correctamente', 'Sesión iniciada', 'Sesión cerrada', '::1', '2026-11-25 12:11:40'),
(408, 11, 'INICIO DE SESIÓN', 'Usuario', 11, 'El usuario admin inició sesión correctamente', '-', 'Sesión iniciada', '::1', '2026-11-25 12:28:18'),
(409, 11, 'EDITAR', 'Rol', 13, 'Se modificó el rol que', 'Nombre: que | Permisos: facturas.listar, observaciones.listar, pagos.listar, liquidaciones.listar, usuarios.listar, roles.listar, auditoria.ver', 'Nombre: que | Permisos: facturas.listar, observaciones.listar, pagos.listar, usuarios.listar, roles.listar, auditoria.ver', '::1', '2026-11-25 12:34:57'),
(410, 11, 'INICIO DE SESIÓN', 'Usuario', 11, 'El usuario admin inició sesión correctamente', '-', 'Sesión iniciada', '::1', '2026-11-25 13:37:10'),
(411, 11, 'DESACTIVAR', 'Usuario', 36, 'Se desactivó el usuario algo', 'Estado: Activo', 'Estado: Inactivo', '::1', '2026-11-25 13:46:47'),
(412, 11, 'CREAR', 'Factura', 114, 'Se creó la factura ASDFGH', NULL, 'Factura: ASDFGH | Monto: $123,00 | Estado: Pendiente | Asignado a: Pablo', '::1', '2026-11-25 13:53:12'),
(413, 11, 'REGISTRAR', 'Pago', 93, 'Se registró el pago de la factura ASDFGH cobrado por Pablo', 'Factura pendiente de cobro', 'Factura: ASDFGH | Asignada a: Pablo | Cobrado por: Pablo | Monto: $123.00 | Forma de pago: Efectivo | Liquidado: No', '::1', '2026-11-25 13:56:57'),
(414, 11, 'LIQUIDACION REPARTIDOR', 'Liquidacion', 9, 'Liquidación del repartidor Pablo', 'Sin liquidar', 'ID liquidación: 9 | Fecha de pagos: 2026-11-25 | Cantidad: 1 | Total: $123,00 | IDs de pagos: 93', '::1', '2026-11-25 13:57:54'),
(415, 11, 'CREAR', 'Factura', 115, 'Se creó la factura GHJKL', NULL, 'Factura: GHJKL | Monto: $150,00 | Estado: Pendiente | Asignado a: Paolini', '::1', '2026-11-25 13:58:49'),
(416, 11, 'REGISTRAR', 'Pago', 94, 'Se registró el pago de la factura GHJKL cobrado por Paolini', 'Factura pendiente de cobro', 'Factura: GHJKL | Asignada a: Paolini | Cobrado por: Paolini | Monto: $150.00 | Forma de pago: Efectivo | Liquidado: No', '::1', '2026-11-25 13:59:00'),
(417, 11, 'CREAR', 'Factura', 116, 'Se creó la factura ÑLKJ', NULL, 'Factura: ÑLKJ | Monto: $150,00 | Estado: Pendiente | Asignado a: Flor', '::1', '2026-11-25 13:59:27'),
(418, 11, 'REGISTRAR', 'Pago', 95, 'Se registró el pago de la factura ÑLKJ cobrado por Flor', 'Factura pendiente de cobro', 'Factura: ÑLKJ | Asignada a: Flor | Cobrado por: Flor | Monto: $150.00 | Forma de pago: Efectivo | Liquidado: No', '::1', '2026-11-25 13:59:38'),
(419, 11, 'LIQUIDACION CAJERO', 'Liquidacion', 10, 'Liquidación del cajero Flor', 'Sin liquidar', 'ID liquidación: 10 | Fecha de pagos: 2026-11-25 | Cantidad: 1 | Total: $150,00 | IDs de pagos: 95', '::1', '2026-11-25 14:00:04');

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
  `id_usuario_asignado` smallint(5) UNSIGNED DEFAULT NULL
) ;

--
-- Volcado de datos para la tabla `factura`
--

INSERT INTO `factura` (`id`, `numero_serie`, `monto`, `fecha_registro`, `fecha_cobro`, `estado`, `id_usuario_registro`, `id_usuario_asignado`) VALUES
(100, 'SDF', 123.00, '2026-11-09 01:32:34', '2026-11-08 21:32:47', 'Cobrado', 11, 24),
(101, 'AAS', 233.99, '2026-11-09 01:48:01', '2026-11-08 21:48:12', 'Cobrado', 11, 24),
(102, 'ADD', 234.00, '2026-11-09 01:52:44', '2026-11-08 21:52:56', 'Cobrado', 11, 14),
(103, 'ASDA', 12.00, '2026-11-09 02:37:34', '2026-11-08 22:37:47', 'Cobrado', 11, 14),
(104, 'AFG', 123.00, '2026-11-09 02:40:00', '2026-11-08 22:40:14', 'Cobrado', 11, 24),
(105, 'DFGH', 123.00, '2026-11-09 02:43:49', '2026-11-08 22:44:01', 'Cobrado', 11, 24),
(106, 'ASDF', 123.00, '2026-11-09 02:50:50', '2026-11-08 22:50:59', 'Cobrado', 11, 24),
(107, 'ASDFS', 123.00, '2026-11-09 02:56:40', '2026-11-08 22:56:48', 'Cobrado', 11, 24),
(109, 'SADF', 123.00, '2026-11-09 02:58:40', '2026-11-08 22:58:51', 'Cobrado', 11, 24),
(110, 'ASZ', 12.00, '2026-11-09 04:32:41', '2026-11-09 00:32:53', 'Cobrado', 11, 2),
(111, 'ASXC', 12.00, '2026-11-09 04:33:13', '2026-11-09 00:33:24', 'Cobrado', 11, NULL),
(112, 'ASE', 123.00, '2026-11-09 05:05:36', NULL, 'Pendiente', 11, 22),
(113, 'GHF', 123.00, '2026-11-21 01:53:11', '2026-11-20 21:53:25', 'Cobrado', 2, 2),
(114, 'ASDFGH', 123.00, '2026-11-25 17:53:12', '2026-11-25 13:56:57', 'Cobrado', 11, 14),
(115, 'GHJKL', 150.00, '2026-11-25 17:58:49', '2026-11-25 13:59:00', 'Cobrado', 11, 1),
(116, 'ÑLKJ', 150.00, '2026-11-25 17:59:27', '2026-11-25 13:59:38', 'Cobrado', 11, 24);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intento_login`
--

CREATE TABLE `intento_login` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario` varchar(32) NOT NULL,
  `ip` varchar(45) NOT NULL DEFAULT '',
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `liquidacion`
--

CREATE TABLE `liquidacion` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_usuario_liquidado` smallint(5) UNSIGNED NOT NULL,
  `id_usuario_registro` smallint(5) UNSIGNED NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `cantidad_pagos` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_efectivo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_transferencia` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_debito` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_credito` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_cheque` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_general` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `liquidacion`
--

INSERT INTO `liquidacion` (`id`, `id_usuario_liquidado`, `id_usuario_registro`, `fecha_hora`, `cantidad_pagos`, `total_efectivo`, `total_transferencia`, `total_debito`, `total_credito`, `total_cheque`, `total_general`) VALUES
(1, 14, 11, '2026-11-08 21:33:36', 1, 0.00, 0.00, 0.00, 0.00, 123.00, 123.00),
(2, 24, 11, '2026-11-08 21:49:45', 1, 0.00, 0.00, 0.00, 233.99, 0.00, 233.99),
(3, 14, 11, '2026-11-08 21:53:10', 1, 0.00, 0.00, 0.00, 234.00, 0.00, 234.00),
(4, 14, 11, '2026-11-08 22:37:57', 1, 0.00, 0.00, 0.00, 12.00, 0.00, 12.00),
(5, 24, 11, '2026-11-08 22:40:41', 1, 0.00, 123.00, 0.00, 0.00, 0.00, 123.00),
(6, 24, 11, '2026-11-08 22:51:11', 1, 0.00, 123.00, 0.00, 0.00, 0.00, 123.00),
(7, 24, 11, '2026-11-08 22:59:19', 1, 0.00, 123.00, 0.00, 0.00, 0.00, 123.00),
(8, 1, 2, '2026-11-20 21:53:48', 1, 123.00, 0.00, 0.00, 0.00, 0.00, 123.00),
(9, 14, 11, '2026-11-25 13:57:54', 1, 123.00, 0.00, 0.00, 0.00, 0.00, 123.00),
(10, 24, 11, '2026-11-25 14:00:04', 1, 150.00, 0.00, 0.00, 0.00, 0.00, 150.00);

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago`
--

CREATE TABLE `pago` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_factura` int(10) UNSIGNED NOT NULL,
  `id_repartidor` smallint(5) UNSIGNED NOT NULL,
  `monto` decimal(8,2) NOT NULL,
  `forma_pago` enum('Efectivo','Transferencia','Debito','Credito','Cheque') NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `liquidado` tinyint(1) NOT NULL DEFAULT 0,
  `id_liquidacion` int(10) UNSIGNED DEFAULT NULL
) ;

--
-- Volcado de datos para la tabla `pago`
--

INSERT INTO `pago` (`id`, `id_factura`, `id_repartidor`, `monto`, `forma_pago`, `fecha_hora`, `liquidado`, `id_liquidacion`) VALUES
(83, 102, 14, 234.00, 'Credito', '2026-11-08 21:52:56', 1, 3),
(84, 103, 14, 12.00, 'Credito', '2026-11-08 22:37:47', 1, 4),
(85, 104, 24, 123.00, 'Transferencia', '2026-11-08 22:40:14', 1, 5),
(86, 105, 24, 123.00, 'Transferencia', '2026-11-08 22:44:01', 1, NULL),
(87, 106, 24, 123.00, 'Transferencia', '2026-11-08 22:50:59', 1, 6),
(88, 107, 24, 123.00, 'Transferencia', '2026-11-08 22:56:48', 1, NULL),
(89, 109, 24, 123.00, 'Transferencia', '2026-11-08 22:58:51', 1, 7),
(90, 110, 2, 12.00, 'Debito', '2026-11-09 00:32:53', 0, NULL),
(91, 111, 2, 12.00, 'Cheque', '2026-11-09 00:33:24', 0, NULL),
(92, 113, 1, 123.00, 'Efectivo', '2026-11-20 21:53:25', 1, 8),
(93, 114, 14, 123.00, 'Efectivo', '2026-11-25 13:56:57', 1, 9),
(94, 115, 1, 150.00, 'Efectivo', '2026-11-25 13:59:00', 0, NULL),
(95, 116, 24, 150.00, 'Efectivo', '2026-11-25 13:59:38', 1, 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permiso`
--

CREATE TABLE `permiso` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `clave` varchar(50) NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `descripcion` varchar(160) NOT NULL,
  `grupo` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permiso`
--

INSERT INTO `permiso` (`id`, `clave`, `nombre`, `descripcion`, `grupo`) VALUES
(1, 'facturas.listar', 'Listar facturas', 'Ver el listado de facturas', 'Facturas'),
(2, 'facturas.ver_todas', 'Ver facturas de todos', 'Si no se marca, solo ve las facturas que tiene asignadas', 'Facturas'),
(3, 'facturas.crear', 'Ingresar factura', 'Registrar facturas nuevas', 'Facturas'),
(4, 'facturas.editar', 'Editar factura', 'Modificar los datos de una factura', 'Facturas'),
(5, 'facturas.cambiar_estado', 'Cambiar estado de factura', 'Pasar una factura a Pendiente, Cobrado o Anulada', 'Facturas'),
(6, 'observaciones.listar', 'Listar observaciones', 'Ver las observaciones de las facturas', 'Observaciones'),
(7, 'observaciones.agregar', 'Agregar observación', 'Escribir observaciones en una factura', 'Observaciones'),
(8, 'pagos.listar', 'Listar pagos', 'Ver el listado de pagos', 'Pagos'),
(9, 'pagos.ver_todos', 'Ver pagos de todos', 'Si no se marca, solo ve los pagos que cobró él mismo', 'Pagos'),
(10, 'pagos.registrar', 'Registrar pago', 'Registrar el cobro de una factura', 'Pagos'),
(11, 'liquidaciones.listar', 'Listar liquidaciones', 'Ver el listado de liquidaciones realizadas', 'Liquidaciones'),
(12, 'liquidaciones.repartidor', 'Liquidar repartidores', 'Confirmar la liquidación diaria de un repartidor', 'Liquidaciones'),
(13, 'liquidaciones.cajero', 'Liquidar cajeros', 'Confirmar la liquidación diaria de un cajero', 'Liquidaciones'),
(14, 'usuarios.listar', 'Listar usuarios', 'Ver el listado de usuarios', 'Usuarios'),
(15, 'usuarios.crear', 'Ingresar usuario', 'Crear usuarios nuevos (nunca administradores)', 'Usuarios'),
(16, 'usuarios.editar', 'Editar usuario', 'Modificar usuarios (nunca administradores)', 'Usuarios'),
(17, 'usuarios.eliminar', 'Eliminar usuario', 'Eliminar (desactivar) usuarios', 'Usuarios'),
(18, 'usuarios.cambiar_estado', 'Cambiar estado de usuario', 'Activar o desactivar usuarios', 'Usuarios'),
(19, 'roles.listar', 'Listar roles', 'Ver el listado de roles y sus permisos', 'Roles'),
(20, 'roles.crear', 'Ingresar rol', 'Crear roles nuevos y elegir sus permisos', 'Roles'),
(21, 'roles.editar', 'Editar rol', 'Cambiar el nombre y los permisos de un rol (solo los que uno mismo tiene)', 'Roles'),
(22, 'roles.eliminar', 'Eliminar rol', 'Eliminar roles que no tengan usuarios', 'Roles'),
(23, 'auditoria.ver', 'Ver auditoría', 'Consultar el historial de acciones del sistema', 'Auditoría');

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
(13, 'que'),
(3, 'Repartidor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_permiso`
--

CREATE TABLE `rol_permiso` (
  `id_rol` tinyint(3) UNSIGNED NOT NULL,
  `id_permiso` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol_permiso`
--

INSERT INTO `rol_permiso` (`id_rol`, `id_permiso`) VALUES
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 10),
(3, 1),
(3, 5),
(3, 6),
(3, 7),
(3, 8),
(3, 10),
(13, 1),
(13, 6),
(13, 8),
(13, 14),
(13, 19),
(13, 23);

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
(1, 'Paolini', '$2y$10$rPI6/oC93EMFz0/D.UDRkeF05gkMijGHN58pJQ80WBoYS4QukGM4q', 1, 3),
(2, 'Nicola', '$2y$10$XcEFvVfft7TOWKGgqZZSE.JhSocDh9RLD8VjtDlqFfTUxEojP7lau', 1, 1),
(4, 'Leon', '$2y$10$TBnW2azfqmmRKN97qqnfKe.9inLlDuIljJI1KYYMsypUPYAtCVwe2', 1, 3),
(10, 'root', 'root', 0, 1),
(11, 'admin', '$2y$10$6N3PqHpmAFQ5u.WmDbxV4.PUItN187xRKC22xWBiaBgYlZhPcen8q', 1, 1),
(14, 'Pablo', '$2y$10$chPiKK/9Dlr5PvX2/qna6u1Fy8PRDKn12bJvFL2nR2ywM0ZCwAlMm', 1, 3),
(19, 'Costa', '$2y$10$/ZjebvX.9FbWmRKo8SUB4uV481vJ9j6He1Gx5kV15qUHdShSVCI92', 0, 2),
(22, 'Luciano', '$2y$10$LIpY4yQOH5I8KOwOr5ICS.pJYCfWwo9h/FK96XUrln1NN0cBqxBSe', 1, 3),
(23, 'Juan Pablo', '$2y$10$nT7mgpy2JyyqnneNOIhZs.n1ROJ9B3mLUExhFhFaG1Af8PZw6irjq', 1, 3),
(24, 'Flor', '$2y$10$chcFugc8H2dFecQV0Mlt1u14eA6ip.57xVPVjuT/IIrlC7vgIVV0C', 1, 2),
(26, 'Patricia P', '$2y$10$Fa7b8D3tPIgX4AbQ51CP8uwtziBXu7u44.aVuVKQMa5fBX5UTGhW2', 1, 1),
(27, 'Hector O', '$2y$10$naUS79uB973T5fblCcNuwuwTV/sIkgXpoiAVjKb1L9edMvz.k/tWy', 1, 1),
(28, 'Gabriel', '$2y$10$7JO6hDvk3ahAjUSvc.suTOGBxfkkPyjmNyK17nYJ6Ji4TElGqkRgO', 1, 2),
(29, 'Yeilan', '$2y$10$x6eqgzUya02KLOSyKE6ctuHE355ZtZuRmAdi.U3yjeXasvGqtE7/O', 1, 2),
(36, 'algo', '$2y$10$r6FOQXuGV4qhhiV4l0GYV.zaKyTTACiFXybMMIqTQh7jAZP3bWssa', 0, 13);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_auditoria_usuario` (`id_usuario`),
  ADD KEY `idx_auditoria_fecha` (`fecha_hora`);

--
-- Indices de la tabla `factura`
--
ALTER TABLE `factura`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_serie` (`numero_serie`),
  ADD KEY `fk_factura_usuario` (`id_usuario_registro`),
  ADD KEY `fk_factura_usuario_asignado` (`id_usuario_asignado`),
  ADD KEY `idx_factura_estado` (`estado`),
  ADD KEY `idx_factura_fecha` (`fecha_registro`);

--
-- Indices de la tabla `intento_login`
--
ALTER TABLE `intento_login`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_intento_usuario` (`usuario`,`fecha_hora`),
  ADD KEY `idx_intento_ip` (`ip`,`fecha_hora`);

--
-- Indices de la tabla `liquidacion`
--
ALTER TABLE `liquidacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_liquidacion_usuario` (`id_usuario_liquidado`),
  ADD KEY `idx_liquidacion_registro` (`id_usuario_registro`);

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
  ADD UNIQUE KEY `uq_pago_factura` (`id_factura`),
  ADD KEY `fk_pago_factura` (`id_factura`),
  ADD KEY `fk_pago_repartidor` (`id_repartidor`),
  ADD KEY `idx_pago_liquidacion` (`id_liquidacion`),
  ADD KEY `idx_pago_fecha` (`fecha_hora`);

--
-- Indices de la tabla `permiso`
--
ALTER TABLE `permiso`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_permiso_clave` (`clave`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_rol_nombre` (`nombre`);

--
-- Indices de la tabla `rol_permiso`
--
ALTER TABLE `rol_permiso`
  ADD PRIMARY KEY (`id_rol`,`id_permiso`),
  ADD KEY `idx_rol_permiso_permiso` (`id_permiso`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=420;

--
-- AUTO_INCREMENT de la tabla `factura`
--
ALTER TABLE `factura`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `intento_login`
--
ALTER TABLE `intento_login`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `liquidacion`
--
ALTER TABLE `liquidacion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `observacion`
--
ALTER TABLE `observacion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `pago`
--
ALTER TABLE `pago`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permiso`
--
ALTER TABLE `permiso`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

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
  ADD CONSTRAINT `fk_factura_usuario` FOREIGN KEY (`id_usuario_registro`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_usuario_asignado` FOREIGN KEY (`id_usuario_asignado`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `liquidacion`
--
ALTER TABLE `liquidacion`
  ADD CONSTRAINT `fk_liquidacion_usuario_liquidado` FOREIGN KEY (`id_usuario_liquidado`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_liquidacion_usuario_registro` FOREIGN KEY (`id_usuario_registro`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE;

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
  ADD CONSTRAINT `fk_pago_liquidacion` FOREIGN KEY (`id_liquidacion`) REFERENCES `liquidacion` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pago_repartidor` FOREIGN KEY (`id_repartidor`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `rol_permiso`
--
ALTER TABLE `rol_permiso`
  ADD CONSTRAINT `fk_rol_permiso_permiso` FOREIGN KEY (`id_permiso`) REFERENCES `permiso` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rol_permiso_rol` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_ROL`) REFERENCES `rol` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
