-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-10-2026 a las 00:24:36
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
(104, 11, 'CREAR', 'Factura', 45, 'Se creó la factura hgj', NULL, 'Factura: hgj | Monto: $6.757,00 | Estado: Pendiente | Asignado a: Paolini', '2026-10-27 19:13:10'),
(105, 11, 'REGISTRAR', 'Pago', 22, 'Se registró el pago de la factura hgj cobrado por Flor', 'Factura pendiente de cobro', 'Factura: hgj | Asignada a: Paolini | Cobrado por: Flor | Monto: $6757.00 | Forma de pago: Transferencia | Liquidado: No', '2026-10-27 19:13:33'),
(106, 11, 'LIQUIDACION CAJERO', 'Pago', 22, 'Liquidación del cajero Flor', 'Pagos pendientes de liquidación', 'Fecha: 2026-10-27 | Cantidad: 1 | Total: $6.757,00 | IDs: 22', '2026-10-27 19:14:14'),
(107, 11, 'CREAR', 'Factura', 46, 'Se creó la factura SAD', NULL, 'Factura: SAD | Monto: $123,00 | Estado: Pendiente | Asignado a: Pablo', '2026-10-27 19:15:27'),
(108, 11, 'REGISTRAR', 'Pago', 23, 'Se registró el pago de la factura SAD cobrado por Pablo', 'Factura pendiente de cobro', 'Factura: SAD | Asignada a: Pablo | Cobrado por: Pablo | Monto: $123.00 | Forma de pago: Cheque | Liquidado: No', '2026-10-27 19:16:45'),
(109, 11, 'LIQUIDACION REPARTIDOR', 'Pago', 23, 'Liquidación del repartidor Pablo', 'Pagos pendientes de liquidación', 'Fecha: 2026-10-27 | Cantidad: 1 | Total: $123,00 | IDs: 23', '2026-10-27 19:17:24'),
(110, 11, 'CAMBIAR ESTADO', 'Factura', 38, 'Se cambió el estado de la factura jgm de Cobrado a Anulada', 'Factura: jgm | Monto: $6.756,00 | Estado: Cobrado | Asignado a: Paolini', 'Factura: jgm | Monto: $6.756,00 | Estado: Anulada | Asignado a: Paolini', '2026-10-27 19:21:42'),
(111, 11, 'CREAR', 'Rol', 11, 'Se creó el rol ayudate', NULL, 'Nombre: ayudate', '2026-10-27 19:22:07'),
(112, 11, 'ELIMINAR', 'Rol', 11, 'Se eliminó el rol ayudate', 'Nombre: ayudate', NULL, '2026-10-27 19:22:18'),
(113, 11, 'EDITAR', 'Factura', 40, 'Se modificó la factura jkh', 'Factura: jkh | Monto: $8.686,00 | Estado: Pendiente | Asignado a: Luciano', 'Factura: jkh | Monto: $8.686,00 | Estado: Pendiente | Asignado a: Pablo', '2026-10-27 19:38:06'),
(114, 11, 'CERRAR SESIÓN', 'Usuario', 11, 'El usuario admin cerró su sesión correctamente', 'Sesión iniciada', 'Sesión cerrada', '2026-10-27 19:48:24'),
(115, 11, 'INICIO DE SESIÓN', 'Usuario', 11, 'El usuario admin inició sesión correctamente', '-', 'Sesión iniciada', '2026-10-27 19:55:50'),
(116, 11, 'CREAR', 'Factura', 47, 'Se creó la factura jkhl', NULL, 'Factura: jkhl | Monto: $78,00 | Estado: Pendiente | Asignado a: Paolini', '2026-10-27 19:57:30'),
(117, 11, 'REGISTRAR', 'Pago', 24, 'Se registró el pago de la factura jkhl cobrado por Flor', 'Factura pendiente de cobro', 'Factura: jkhl | Asignada a: Paolini | Cobrado por: Flor | Monto: $78.00 | Forma de pago: Efectivo | Liquidado: No', '2026-10-27 19:58:01'),
(118, 11, 'LIQUIDACION CAJERO', 'Pago', 24, 'Liquidación del cajero Flor', 'Pagos pendientes de liquidación', 'Fecha: 2026-10-27 | Cantidad: 1 | Total: $78,00 | IDs: 24', '2026-10-27 19:58:37');

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
(37, 'dfsdfg', 4567.00, '2026-10-08 02:07:55', '2026-10-07 21:08:09', 'Cobrado', 11, 22),
(38, 'jgm', 6756.00, '2026-10-27 00:34:30', NULL, 'Anulada', 11, 1),
(39, 'kjg', 8768.00, '2026-10-27 00:36:59', '2026-10-26 20:38:13', 'Cobrado', 11, 14),
(40, 'jkh', 8686.00, '2026-10-27 00:39:54', NULL, 'Pendiente', 11, 14),
(41, 'lkh', 98767.00, '2026-10-27 00:53:30', '2026-10-26 20:53:55', 'Cobrado', 11, 14),
(42, 'POP', 500.00, '2026-10-27 01:31:33', '2026-10-26 21:32:21', 'Cobrado', 11, 14),
(43, 'df', 234.00, '2026-10-27 01:38:05', '2026-10-26 21:38:24', 'Cobrado', 11, 1),
(44, 'mn', 34465.00, '2026-10-27 01:42:14', '2026-10-26 21:42:31', 'Cobrado', 11, 22),
(45, 'hgj', 6757.00, '2026-10-27 23:13:10', '2026-10-27 19:13:33', 'Cobrado', 11, 1),
(46, 'SAD', 123.00, '2026-10-27 23:15:27', '2026-10-27 19:16:45', 'Cobrado', 11, 14),
(47, 'jkhl', 78.00, '2026-10-27 23:57:30', '2026-10-27 19:58:01', 'Cobrado', 11, 1);

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
  `forma_pago` enum('Efectivo','Transferencia','Debito','Credito','Cheque') NOT NULL,
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
(15, 37, 22, 4567.00, 'Efectivo', '2026-10-07 21:08:09', 1),
(16, 38, 24, 6756.00, 'Transferencia', '2026-10-26 20:34:48', 1),
(17, 39, 14, 8768.00, 'Efectivo', '2026-10-26 20:38:13', 1),
(18, 41, 24, 98767.00, 'Debito', '2026-10-26 20:53:55', 1),
(19, 42, 14, 500.00, 'Cheque', '2026-10-26 21:32:21', 0),
(20, 43, 1, 234.00, 'Credito', '2026-10-26 21:38:24', 0),
(21, 44, 24, 34465.00, 'Cheque', '2026-10-26 21:42:31', 0),
(22, 45, 24, 6757.00, 'Transferencia', '2026-10-27 19:13:33', 1),
(23, 46, 14, 123.00, 'Cheque', '2026-10-27 19:16:45', 1),
(24, 47, 24, 78.00, 'Efectivo', '2026-10-27 19:58:01', 1);

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
(2, 'Nicola', '$2y$10$k7HCCSowUTpq4.yAwkbe7ey9lG1X8zirOhyTuYnLffyowUtZAiJKW', 0, 1),
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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT de la tabla `factura`
--
ALTER TABLE `factura`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
