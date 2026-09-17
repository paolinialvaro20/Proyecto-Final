-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-09-2026 a las 02:08:32
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
(1, 'A2563', 1599.00, '2026-06-24 01:59:48', NULL, 'Pendiente', 1, 0),
(2, 'R4I5U5', 1200.00, '2026-08-04 22:07:39', NULL, 'Pendiente', 1, 0),
(3, '809765', 1342.00, '2026-08-04 22:08:12', NULL, 'Pendiente', 1, 0),
(4, 'T75659', 89765.00, '2026-08-04 22:08:38', NULL, 'Pendiente', 1, 0),
(5, 'Z6575', 890.00, '2026-08-04 22:09:18', NULL, 'Pendiente', 1, 0),
(6, '44324', 500000.00, '2026-08-04 22:10:34', NULL, 'Pendiente', 1, 0),
(7, 'a287340', 977.00, '2026-08-04 22:16:22', NULL, 'Pendiente', 1, 0),
(8, 'A2998', 1239.77, '2026-08-04 22:19:51', NULL, 'Pendiente', 1, 0),
(11, 'A344', 345.00, '2026-08-04 22:24:02', NULL, 'Pendiente', 1, 0),
(13, 'W74567', 7351.00, '2026-08-04 22:25:10', NULL, 'Pendiente', 1, 0),
(14, 'S575757', 25000.00, '2026-08-12 20:45:59', NULL, 'Pendiente', 1, 0),
(15, 'F58', 500.00, '2026-08-12 20:46:47', NULL, 'Pendiente', 1, 0),
(16, 'A5678', 87656.00, '2026-08-12 21:06:38', '2026-09-08 19:49:30', 'Cobrado', 1, 0),
(18, 'hg6545', 45678.00, '2026-08-18 20:03:06', NULL, 'Anulada', 1, 4),
(19, 'Z0987', 1234.00, '2026-08-28 21:38:34', NULL, 'Pendiente', 1, 0),
(20, 'A9988', 455.99, '2026-09-03 00:04:41', '2026-09-16 19:38:28', 'Cobrado', 11, 17),
(21, 'H5659', 9876.00, '2026-09-03 00:05:30', NULL, 'Pendiente', 11, 3),
(22, 'EE0987', 87654.00, '2026-09-03 00:09:26', '2026-09-04 21:41:48', 'Cobrado', 11, 13),
(23, 'uj7430', 654567.00, '2026-09-03 00:12:56', NULL, 'Pendiente', 18, NULL),
(24, 'E56789', 34565.00, '2026-09-09 01:02:10', '2026-09-16 19:38:42', 'Cobrado', 11, 4),
(25, 'G8765', 5673.00, '2026-09-17 01:18:52', NULL, 'Pendiente', 10, 11),
(27, 'OP865', 7857.00, '2026-09-17 01:20:08', NULL, 'Pendiente', 10, 19);

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
(3, 'El hdp me largo los perros y me mordieron el tobillo derecho, después de eso me aprete un huevo y voy a estar de licencia un mes por estres perruno', '2026-09-05 02:26:59', 3, 16),
(4, 'Imposible de cobrar, me dio unu beso en la boca, nose que hacer, me siento acosado sexualmente y voy a hacer una denuncia', '2026-09-05 02:30:55', 15, 16),
(5, 'Imposible de cobrar, me dio unu beso en la boca, nose que hacer, me siento acosado sexualmente y voy a hacer una denuncia', '2026-09-05 02:32:33', 15, 16),
(6, 'Hay muchos pozos, no puedo ir rapido asi que no voy. AlvaVeloz', '2026-09-09 01:03:14', 24, 11),
(7, 'Peperepepepepepepepe', '2026-09-09 01:28:25', 21, 18),
(8, 'Peperepepepepepepepe', '2026-09-09 01:32:06', 21, 18),
(9, 'Holaaaaaaaaaaa', '2026-09-09 01:37:46', 20, 18);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id`, `nombre`) VALUES
(2, 'Cajeras'),
(23, 'HacenSombra'),
(3, 'Repartidore'),
(1, 'SuperAdministrador');

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
(1, 'Paolini', '1234567890', 0, 3),
(2, 'Nicola', '1234567890', 1, 3),
(3, 'Costa', '1234567890', 0, 1),
(4, 'Leon', '1234567890', 1, 3),
(10, 'root', '$2y$10$MIQ6kW8RThT9yq079GMo7OH4kp.vf4ouaSIRLtAGgMmyMkwDFNgl6', 1, 1),
(11, 'admin', '$2y$10$tXO1eXbaaf4kR7UET85hB.J00oa1syiuaSjwMpQJ4R15gvPqDp4MK', 1, 1),
(13, 'roberto', '$2y$10$JQiss3i2Pg6uoeo1mTmtteRGbf3tGi76omBhrBN69YXfHwJXO5YqG', 1, 2),
(14, 'Pablo', '$2y$10$KtTshjXuIIuiOrO.ovIVEeC1qGrpbeCvPYng9tZBIJxqPHWvTwePe', 1, 2),
(15, 'felipe', '$2y$10$sYO8Uf.h4vrOC4lyt6YaK.ZDR5bjxcgAWZciSCv70WgY0y9AwpjBG', 0, 1),
(16, 'otro', '$2y$10$o3K7DJvotjN4k8OlNyrT.O1ExZw4qMdbUYDx4q66PhcKgxFoejqkG', 0, 3),
(17, 'luciana', '$2y$10$Z0csM1v2CGh133PakJyubeoJX1GzcEjrUHO0lnqSIRxp10c.rXaL2', 0, 1),
(18, 'algo', '$2y$10$Yyvd72Eta42mcWxXjbGFfe5JbKzHZpTF/6X6sywiAirbPOGbRWLCe', 1, 1),
(19, 'alvafunes', '$2y$10$PJMppKTRGzjYyJOO72mwKugCkEOEUly3/BE45JYbAEigXHsikBtiu', 1, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `factura`
--
ALTER TABLE `factura`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_serie` (`numero_serie`),
  ADD KEY `fk_factura_usuario` (`id_usuario_registro`);

--
-- Indices de la tabla `observacion`
--
ALTER TABLE `observacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_observacion_factura` (`id_FACTURA_PENDIENTE`),
  ADD KEY `fk_observacion_usuario` (`id_USUARIO`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

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
-- AUTO_INCREMENT de la tabla `factura`
--
ALTER TABLE `factura`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `observacion`
--
ALTER TABLE `observacion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `fk_factura_usuario` FOREIGN KEY (`id_usuario_registro`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `observacion`
--
ALTER TABLE `observacion`
  ADD CONSTRAINT `fk_observacion_factura` FOREIGN KEY (`id_FACTURA_PENDIENTE`) REFERENCES `factura` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_observacion_usuario` FOREIGN KEY (`id_USUARIO`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_ROL`) REFERENCES `rol` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
