-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-11-2026 a las 05:09:41
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
(365, 11, 'CERRAR SESIÓN', 'Usuario', 11, 'El usuario admin cerró su sesión correctamente', 'Sesión iniciada', 'Sesión cerrada', '2026-11-09 01:01:35'),
(366, 11, 'INICIO DE SESIÓN', 'Usuario', 11, 'El usuario admin inició sesión correctamente', '-', 'Sesión iniciada', '2026-11-09 01:01:40'),
(367, 11, 'CREAR', 'Factura', 112, 'Se creó la factura ASE', NULL, 'Factura: ASE | Monto: $123,00 | Estado: Pendiente | Asignado a: Luciano', '2026-11-09 01:05:36');

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
(111, 'ASXC', 12.00, '2026-11-09 04:33:13', '2026-11-09 00:33:24', 'Cobrado', 11, 16),
(112, 'ASE', 123.00, '2026-11-09 05:05:36', NULL, 'Pendiente', 11, 22);

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
(7, 24, 11, '2026-11-08 22:59:19', 1, 0.00, 123.00, 0.00, 0.00, 0.00, 123.00);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(91, 111, 2, 12.00, 'Cheque', '2026-11-09 00:33:24', 0, NULL);

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
(11, 'admin', '$2y$10$6N3PqHpmAFQ5u.WmDbxV4.PUItN187xRKC22xWBiaBgYlZhPcen8q', 1, 1),
(14, 'Pablo', '$2y$10$chPiKK/9Dlr5PvX2/qna6u1Fy8PRDKn12bJvFL2nR2ywM0ZCwAlMm', 1, 3),
(19, 'Costa', '$2y$10$/ZjebvX.9FbWmRKo8SUB4uV481vJ9j6He1Gx5kV15qUHdShSVCI92', 0, 2),
(22, 'Luciano', '$2y$10$LIpY4yQOH5I8KOwOr5ICS.pJYCfWwo9h/FK96XUrln1NN0cBqxBSe', 1, 3),
(23, 'Juan Pablo', '$2y$10$nT7mgpy2JyyqnneNOIhZs.n1ROJ9B3mLUExhFhFaG1Af8PZw6irjq', 1, 3),
(24, 'Flor', '$2y$10$chcFugc8H2dFecQV0Mlt1u14eA6ip.57xVPVjuT/IIrlC7vgIVV0C', 1, 2),
(26, 'Patricia P', '$2y$10$Fa7b8D3tPIgX4AbQ51CP8uwtziBXu7u44.aVuVKQMa5fBX5UTGhW2', 1, 1),
(27, 'Hector O', '$2y$10$naUS79uB973T5fblCcNuwuwTV/sIkgXpoiAVjKb1L9edMvz.k/tWy', 1, 1),
(28, 'Gabriel', '$2y$10$7JO6hDvk3ahAjUSvc.suTOGBxfkkPyjmNyK17nYJ6Ji4TElGqkRgO', 1, 2),
(29, 'Yeilan', '$2y$10$x6eqgzUya02KLOSyKE6ctuHE355ZtZuRmAdi.U3yjeXasvGqtE7/O', 1, 2);

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
  ADD KEY `fk_pago_factura` (`id_factura`),
  ADD KEY `fk_pago_repartidor` (`id_repartidor`),
  ADD KEY `idx_pago_liquidacion` (`id_liquidacion`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=368;

--
-- AUTO_INCREMENT de la tabla `factura`
--
ALTER TABLE `factura`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT de la tabla `liquidacion`
--
ALTER TABLE `liquidacion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `observacion`
--
ALTER TABLE `observacion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `pago`
--
ALTER TABLE `pago`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

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
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_ROL`) REFERENCES `rol` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
