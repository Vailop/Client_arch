-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-10-2025 a las 08:41:00
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
-- Base de datos: `admon_arch`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbc_perfiles`
--

CREATE TABLE `tbc_perfiles` (
  `IdPerfil` int(11) NOT NULL,
  `DesPerfil` varchar(50) DEFAULT NULL,
  `Estatus` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbc_perfiles`
--

INSERT INTO `tbc_perfiles` (`IdPerfil`, `DesPerfil`, `Estatus`) VALUES
(1, 'Administrador', 1),
(2, 'Cliente', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbp_desarrollos`
--

CREATE TABLE `tbp_desarrollos` (
  `IdDesarrollo` int(11) NOT NULL,
  `Nombre_Desarrollo` varchar(255) DEFAULT NULL,
  `Descripcion` varchar(500) DEFAULT NULL,
  `RutaLogo` varchar(500) DEFAULT NULL,
  `Estatus` int(11) DEFAULT NULL,
  `RutaImagenes` varchar(500) DEFAULT NULL,
  `UrlVideo` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbp_desarrollos`
--

INSERT INTO `tbp_desarrollos` (`IdDesarrollo`, `Nombre_Desarrollo`, `Descripcion`, `RutaLogo`, `Estatus`, `RutaImagenes`, `UrlVideo`) VALUES
(1, 'San Pedro De Los Pinos', 'Es un desarrollo exclusivo que cuenta con 137 departamentos que van desde los 43m2 hasta 257m2, distribuidos en 31 niveles de uno, dos y tres recámaras, con acabados de lujo que cuentan con los más altos estándares de calidad.', 'desarrollos/San_pedro_de_los_pinos/San_pedro_de_los_pinos.jpg', 1, '', NULL),
(3, 'Ahuehuetes 450', 'Es un desarrollo exclusivo que cuenta con 137 departamentos que van desde los 43m2 hasta 257m2, distribuidos en 31 niveles de uno, dos y tres recámaras, con acabados de lujo que cuentan con los más altos estándares de calidad.', 'desarrollos/Ahuehuetes_450/Ahuehuetes_450.jpg', 1, '', NULL),
(4, 'Casa Aida', 'Es un desarrollo exclusivo que cuenta con 137 departamentos que van desde los 43m2 hasta 257m2, distribuidos en 31 niveles de uno, dos y tres recámaras, con acabados de lujo que cuentan con los más altos estándares de calidad.', 'desarrollos/Casa_aida/Casa_aida.jpg', 1, '', NULL),
(5, 'Del Bosque', 'Es un desarrollo exclusivo que cuenta con 137 departamentos que van desde los 43m2 hasta 257m2, distribuidos en 31 niveles de uno, dos y tres recámaras, con acabados de lujo que cuentan con los más altos estándares de calidad.', 'desarrollos/Del_bosque/Del_bosque.jpg', 1, '', NULL),
(6, 'Del Valle', 'Es un desarrollo exclusivo que cuenta con 137 departamentos que van desde los 43m2 hasta 257m2, distribuidos en 31 niveles de uno, dos y tres recámaras, con acabados de lujo que cuentan con los más altos estándares de calidad.', 'desarrollos/Del_valle/Del_valle.jpg', 1, '', NULL),
(7, 'Morena', 'Es un desarrollo exclusivo que cuenta con 137 departamentos que van desde los 43m2 hasta 257m2, distribuidos en 31 niveles de uno, dos y tres recámaras, con acabados de lujo que cuentan con los más altos estándares de calidad.', 'desarrollos/Morena/Morena.jpg', 1, '', NULL),
(8, 'Patricio Sanz 37', 'Es un desarrollo exclusivo que cuenta con 137 departamentos que van desde los 43m2 hasta 257m2, distribuidos en 31 niveles de uno, dos y tres recámaras, con acabados de lujo que cuentan con los más altos estándares de calidad.', 'desarrollos/Patricio_sanz_37/Patricio_sanz_37.jpg', 1, '', NULL),
(9, 'San Borja', 'Es un desarrollo exclusivo que cuenta con 137 departamentos que van desde los 43m2 hasta 257m2, distribuidos en 31 niveles de uno, dos y tres recámaras, con acabados de lujo que cuentan con los más altos estándares de calidad.', 'desarrollos/San_borja/San_borja.jpg', 1, '', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbp_usuarios`
--

CREATE TABLE `tbp_usuarios` (
  `IdUsuario` int(11) NOT NULL,
  `Nombre` varchar(500) NOT NULL,
  `RFC` varchar(13) NOT NULL,
  `Contrasena` varchar(1000) NOT NULL,
  `Correo_electronico` varchar(255) DEFAULT NULL,
  `Telefono` varchar(25) DEFAULT NULL,
  `Estatus` int(11) DEFAULT NULL,
  `IdPerfil` int(11) DEFAULT 2,
  `Avatar` varchar(50) DEFAULT NULL,
  `FechaUltimaActualizacion` datetime DEFAULT NULL,
  `RequiereCambioPassword` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbp_usuarios`
--

INSERT INTO `tbp_usuarios` (`IdUsuario`, `Nombre`, `RFC`, `Contrasena`, `Correo_electronico`, `Telefono`, `Estatus`, `IdPerfil`, `Avatar`, `FechaUltimaActualizacion`, `RequiereCambioPassword`) VALUES
(1, 'INMOBILIARIA ALROSEDE S.A. DE C.V.', 'IAL210705MY2', '$2y$10$Ra03LAc3rSCDIfBTJZTbxu47GiC824e4sR1ZqOZ4xrwF2jzv32.FG', '', '', 1, 2, 'img/profiles/prueba.jpg', '2025-09-28 23:31:20', 0),
(2, 'JORGE ALBERTO LOPEZ VILLEDA', 'LOVJ850730F96', '$2y$10$gQj66v9A4hZlNSk24plZBuAZ2rcdjkszWMZv5fAEYkOuQoixIfhni', '', '', 1, 1, 'images/avatars/avatar_mujer.png', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbr_avance_desarrollo`
--

CREATE TABLE `tbr_avance_desarrollo` (
  `IdAvance` bigint(20) NOT NULL,
  `IdDesarrollo` int(11) NOT NULL,
  `Categoria` varchar(120) NOT NULL,
  `ValorActual` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ValorObjetivo` decimal(10,2) NOT NULL DEFAULT 1.00,
  `Peso` decimal(6,3) NOT NULL DEFAULT 0.000,
  `Unidad` varchar(30) DEFAULT NULL,
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbr_avance_desarrollo`
--

INSERT INTO `tbr_avance_desarrollo` (`IdAvance`, `IdDesarrollo`, `Categoria`, `ValorActual`, `ValorObjetivo`, `Peso`, `Unidad`, `UpdatedAt`) VALUES
(1, 1, 'Planificación', 1.00, 1.00, 0.100, 'fase', '2025-09-23 02:58:25'),
(2, 1, 'Excavación y cimentación', 0.50, 1.00, 0.150, 'fase', '2025-09-23 02:58:25'),
(3, 1, 'Pilares / estructura', 0.20, 1.00, 0.250, 'fase', '2025-09-23 02:58:25'),
(4, 1, 'Instalaciones', 0.00, 1.00, 0.200, 'fase', '2025-09-23 02:58:25'),
(5, 1, 'Acabados interiores y exteriores', 0.00, 1.00, 0.150, 'fase', '2025-09-23 02:58:25'),
(6, 1, 'Equipamiento', 0.00, 1.00, 0.100, 'fase', '2025-09-23 02:58:25'),
(7, 1, 'Inspección y pruebas', 0.00, 1.00, 0.030, 'fase', '2025-09-23 02:58:25'),
(8, 1, 'Entrega', 0.00, 1.00, 0.020, 'fase', '2025-09-23 02:58:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbr_comprobantes_pago`
--

CREATE TABLE `tbr_comprobantes_pago` (
  `IdComprobante` int(11) NOT NULL,
  `IdPago` int(11) DEFAULT NULL,
  `IdUsuario` int(11) NOT NULL,
  `IdDesarrollo` int(11) NOT NULL,
  `Dpto` varchar(50) NOT NULL,
  `NumeroComprobante` tinyint(4) DEFAULT NULL,
  `MontoComprobante` decimal(10,2) DEFAULT NULL,
  `ArchivoComprobante` varchar(255) DEFAULT NULL,
  `Referencia` varchar(100) DEFAULT NULL,
  `FechaPagoReal` date DEFAULT NULL,
  `Estatus` enum('Pendiente','Aprobado','Rechazado') DEFAULT 'Pendiente',
  `FechaSubida` datetime DEFAULT current_timestamp(),
  `ObservacionesUsuario` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Almacena múltiples comprobantes de pago por cada mensualidad. Permite que un pago de $20,000 tenga varios comprobantes (ej: $12,000 + $8,000)';

--
-- Volcado de datos para la tabla `tbr_comprobantes_pago`
--

INSERT INTO `tbr_comprobantes_pago` (`IdComprobante`, `IdPago`, `IdUsuario`, `IdDesarrollo`, `Dpto`, `NumeroComprobante`, `MontoComprobante`, `ArchivoComprobante`, `Referencia`, `FechaPagoReal`, `Estatus`, `FechaSubida`, `ObservacionesUsuario`) VALUES
(5, 46, 1, 1, '201', 1, 20000.00, 'comp_46_1758954760_8017.pdf', '1255802350', '2025-09-27', 'Pendiente', '2025-09-27 00:32:40', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbr_desarrollos_costo_mensual`
--

CREATE TABLE `tbr_desarrollos_costo_mensual` (
  `Id` int(11) NOT NULL,
  `IdDesarrollo` int(11) DEFAULT NULL,
  `M2Mensual` decimal(10,2) DEFAULT NULL,
  `Mes` int(11) DEFAULT NULL,
  `Anio` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbr_desarrollos_costo_mensual`
--

INSERT INTO `tbr_desarrollos_costo_mensual` (`Id`, `IdDesarrollo`, `M2Mensual`, `Mes`, `Anio`) VALUES
(1, 1, 46138.95, 1, 2025),
(2, 1, 47117.04, 2, 2025),
(3, 1, 48095.13, 3, 2025),
(4, 1, 49073.22, 4, 2025),
(5, 1, 50051.32, 5, 2025),
(6, 1, 51029.41, 6, 2025),
(7, 1, 52007.50, 7, 2025),
(8, 1, 52985.59, 8, 2025),
(9, 1, 53963.68, 9, 2025);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbr_pagos`
--

CREATE TABLE `tbr_pagos` (
  `IdPago` int(11) NOT NULL,
  `IdUsuario` int(11) NOT NULL,
  `IdDesarrollo` int(11) NOT NULL,
  `Dpto` varchar(20) NOT NULL,
  `IdCliente` int(11) DEFAULT NULL,
  `m2inicial` decimal(10,2) DEFAULT NULL,
  `m2actual` decimal(10,2) DEFAULT NULL,
  `Precio_Compraventa` decimal(12,2) DEFAULT NULL,
  `Precio_Actual` decimal(12,2) DEFAULT NULL,
  `FechaPago` date DEFAULT NULL,
  `Monto` decimal(12,2) DEFAULT NULL,
  `Estatus` tinyint(1) DEFAULT NULL,
  `Concepto` varchar(120) DEFAULT NULL,
  `Notas` varchar(255) DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  `UpdateAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `UpdatedAt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbr_pagos`
--

INSERT INTO `tbr_pagos` (`IdPago`, `IdUsuario`, `IdDesarrollo`, `Dpto`, `IdCliente`, `m2inicial`, `m2actual`, `Precio_Compraventa`, `Precio_Actual`, `FechaPago`, `Monto`, `Estatus`, `Concepto`, `Notas`, `CreatedAt`, `UpdateAt`, `UpdatedAt`) VALUES
(36, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2024-11-14', 20000.00, 2, 'Mensualidad 1', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:47', '2025-09-24 02:33:47'),
(37, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2024-12-14', 20000.00, 2, 'Mensualidad 2', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:50', '2025-09-24 02:33:50'),
(38, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-01-14', 20000.00, 2, 'Mensualidad 3', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:52', '2025-09-24 02:33:52'),
(39, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-02-14', 20000.00, 2, 'Mensualidad 4', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:54', '2025-09-24 02:33:54'),
(40, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-03-14', 20000.00, 2, 'Mensualidad 5', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:56', '2025-09-24 02:33:56'),
(41, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-04-14', 20000.00, 2, 'Mensualidad 6', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:59', '2025-09-24 02:33:59'),
(42, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-05-14', 20000.00, 2, 'Mensualidad 7', NULL, '2025-09-24 02:33:19', '2025-09-24 02:34:01', '2025-09-24 02:34:01'),
(43, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-06-14', 20000.00, 2, 'Mensualidad 8', NULL, '2025-09-24 02:33:19', '2025-09-24 02:34:03', '2025-09-24 02:34:03'),
(44, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-07-14', 20000.00, 2, 'Mensualidad 9', NULL, '2025-09-24 02:33:19', '2025-09-24 02:34:07', '2025-09-24 02:34:07'),
(45, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-08-14', 20000.00, 2, 'Mensualidad 10', NULL, '2025-09-24 02:33:19', '2025-09-24 02:34:09', '2025-09-24 02:34:09'),
(46, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-09-14', 20000.00, 1, 'Mensualidad 11', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(47, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-10-14', 20000.00, 1, 'Mensualidad 12', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(48, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-11-14', 20000.00, 1, 'Mensualidad 13', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(49, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2025-12-14', 20000.00, 1, 'Mensualidad 14', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(50, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-01-14', 20000.00, 1, 'Mensualidad 15', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(51, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-02-14', 20000.00, 1, 'Mensualidad 16', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(52, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-03-14', 20000.00, 1, 'Mensualidad 17', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(53, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-04-14', 20000.00, 1, 'Mensualidad 18', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(54, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-05-14', 20000.00, 1, 'Mensualidad 19', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(55, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-06-14', 20000.00, 1, 'Mensualidad 20', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(56, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-07-14', 20000.00, 1, 'Mensualidad 21', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(57, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-08-14', 20000.00, 1, 'Mensualidad 22', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(58, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-09-14', 20000.00, 1, 'Mensualidad 23', NULL, '2025-09-24 02:33:19', '2025-09-24 02:33:19', NULL),
(59, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-10-14', 20000.00, 1, 'Mensualidad 24', NULL, '2025-09-24 02:33:20', '2025-09-24 02:33:20', NULL),
(60, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-11-14', 20000.00, 1, 'Mensualidad 25', NULL, '2025-09-24 02:33:20', '2025-09-24 02:33:20', NULL),
(61, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2026-12-14', 20000.00, 1, 'Mensualidad 26', NULL, '2025-09-24 02:33:20', '2025-09-24 02:33:20', NULL),
(62, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2027-01-14', 20000.00, 1, 'Mensualidad 27', NULL, '2025-09-24 02:33:20', '2025-09-24 02:33:20', NULL),
(63, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2027-02-14', 20000.00, 1, 'Mensualidad 28', NULL, '2025-09-24 02:33:20', '2025-09-24 02:33:20', NULL),
(64, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2027-03-14', 20000.00, 1, 'Mensualidad 29', NULL, '2025-09-24 02:33:20', '2025-09-24 02:33:20', NULL),
(65, 1, 1, '201', 1, 46138.95, 53963.68, 5905785.12, 0.00, '2027-04-14', 20000.00, 1, 'Mensualidad 30', NULL, '2025-09-24 02:33:20', '2025-09-24 02:33:20', NULL),
(66, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2024-11-14', 20000.00, 2, 'Mensualidad 1', NULL, '2025-09-27 06:43:00', '2025-09-27 06:44:58', '2025-09-27 06:44:58'),
(67, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2024-12-14', 20000.00, 2, 'Mensualidad 2', NULL, '2025-09-27 06:43:00', '2025-09-27 06:45:01', '2025-09-27 06:45:01'),
(68, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2025-01-14', 20000.00, 2, 'Mensualidad 3', NULL, '2025-09-27 06:43:00', '2025-09-27 06:45:02', '2025-09-27 06:45:02'),
(69, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2025-02-14', 20000.00, 2, 'Mensualidad 4', NULL, '2025-09-27 06:43:00', '2025-09-27 06:45:04', '2025-09-27 06:45:04'),
(70, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2025-03-14', 20000.00, 2, 'Mensualidad 5', NULL, '2025-09-27 06:43:00', '2025-09-27 06:45:06', '2025-09-27 06:45:06'),
(71, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2025-04-14', 20000.00, 2, 'Mensualidad 6', NULL, '2025-09-27 06:43:00', '2025-09-27 06:45:08', '2025-09-27 06:45:08'),
(72, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2025-05-14', 20000.00, 2, 'Mensualidad 7', NULL, '2025-09-27 06:43:00', '2025-09-27 06:45:10', '2025-09-27 06:45:10'),
(73, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2025-06-14', 20000.00, 2, 'Mensualidad 8', NULL, '2025-09-27 06:43:00', '2025-09-27 06:45:11', '2025-09-27 06:45:11'),
(74, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2025-07-14', 20000.00, 2, 'Mensualidad 9', NULL, '2025-09-27 06:43:00', '2025-09-27 06:45:13', '2025-09-27 06:45:13'),
(75, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2025-08-14', 20000.00, 2, 'Mensualidad 10', NULL, '2025-09-27 06:43:00', '2025-09-27 06:45:15', '2025-09-27 06:45:15'),
(76, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2025-09-14', 20000.00, 1, 'Mensualidad 11', NULL, '2025-09-27 06:43:00', '2025-09-27 06:45:21', '2025-09-27 06:45:21'),
(77, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2025-10-14', 20000.00, 1, 'Mensualidad 12', NULL, '2025-09-27 06:43:00', '2025-09-27 06:43:00', NULL),
(78, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2025-11-14', 20000.00, 1, 'Mensualidad 13', NULL, '2025-09-27 06:43:00', '2025-09-27 06:43:00', NULL),
(79, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2025-12-14', 20000.00, 1, 'Mensualidad 14', NULL, '2025-09-27 06:43:00', '2025-09-27 06:43:00', NULL),
(80, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2026-01-14', 20000.00, 1, 'Mensualidad 15', NULL, '2025-09-27 06:43:00', '2025-09-27 06:43:00', NULL),
(81, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2026-02-14', 20000.00, 1, 'Mensualidad 16', NULL, '2025-09-27 06:43:00', '2025-09-27 06:43:00', NULL),
(82, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2026-03-14', 20000.00, 1, 'Mensualidad 17', NULL, '2025-09-27 06:43:00', '2025-09-27 06:43:00', NULL),
(83, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2026-04-14', 20000.00, 1, 'Mensualidad 18', NULL, '2025-09-27 06:43:00', '2025-09-27 06:43:00', NULL),
(84, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2026-05-14', 20000.00, 1, 'Mensualidad 19', NULL, '2025-09-27 06:43:00', '2025-09-27 06:43:00', NULL),
(85, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2026-06-14', 20000.00, 1, 'Mensualidad 20', NULL, '2025-09-27 06:43:00', '2025-09-27 06:43:00', NULL),
(86, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2026-07-14', 20000.00, 1, 'Mensualidad 21', NULL, '2025-09-27 06:43:00', '2025-09-27 06:43:00', NULL),
(87, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2026-08-14', 20000.00, 1, 'Mensualidad 22', NULL, '2025-09-27 06:43:00', '2025-09-27 06:43:00', NULL),
(88, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2026-09-14', 20000.00, 1, 'Mensualidad 23', NULL, '2025-09-27 06:43:00', '2025-09-27 06:43:00', NULL),
(89, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2026-10-14', 20000.00, 1, 'Mensualidad 24', NULL, '2025-09-27 06:43:00', '2025-09-27 06:43:00', NULL),
(90, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2026-11-14', 20000.00, 1, 'Mensualidad 25', NULL, '2025-09-27 06:43:00', '2025-09-27 06:43:00', NULL),
(91, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2026-12-14', 20000.00, 1, 'Mensualidad 26', NULL, '2025-09-27 06:43:00', '2025-09-27 06:43:00', NULL),
(92, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2027-01-14', 20000.00, 1, 'Mensualidad 27', NULL, '2025-09-27 06:43:00', '2025-09-27 06:43:00', NULL),
(93, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2027-02-14', 20000.00, 1, 'Mensualidad 28', NULL, '2025-09-27 06:43:00', '2025-09-27 06:43:00', NULL),
(94, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2027-03-14', 20000.00, 1, 'Mensualidad 29', NULL, '2025-09-27 06:43:00', '2025-09-27 06:43:00', NULL),
(95, 1, 1, '701', 1, 50095.95, 58479.24, 5460381.00, 0.00, '2027-04-14', 20000.00, 1, 'Mensualidad 30', NULL, '2025-09-27 06:43:00', '2025-09-27 06:43:00', NULL),
(96, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2024-11-14', 20000.00, 2, 'Mensualidad 1', NULL, '2025-10-02 01:01:45', '2025-10-02 01:05:00', '2025-10-02 01:05:00'),
(97, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2024-12-14', 20000.00, 2, 'Mensualidad 2', NULL, '2025-10-02 01:01:45', '2025-10-02 01:05:02', '2025-10-02 01:05:02'),
(98, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2025-01-14', 20000.00, 2, 'Mensualidad 3', NULL, '2025-10-02 01:01:45', '2025-10-02 01:05:05', '2025-10-02 01:05:05'),
(99, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2025-02-14', 20000.00, 2, 'Mensualidad 4', NULL, '2025-10-02 01:01:45', '2025-10-02 01:05:07', '2025-10-02 01:05:07'),
(100, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2025-03-14', 20000.00, 2, 'Mensualidad 5', NULL, '2025-10-02 01:01:45', '2025-10-02 01:05:09', '2025-10-02 01:05:09'),
(101, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2025-04-14', 20000.00, 2, 'Mensualidad 6', NULL, '2025-10-02 01:01:45', '2025-10-02 01:05:12', '2025-10-02 01:05:12'),
(102, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2025-05-14', 20000.00, 2, 'Mensualidad 7', NULL, '2025-10-02 01:01:45', '2025-10-02 01:05:13', '2025-10-02 01:05:13'),
(103, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2025-06-14', 20000.00, 2, 'Mensualidad 8', NULL, '2025-10-02 01:01:45', '2025-10-02 01:05:15', '2025-10-02 01:05:15'),
(104, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2025-07-14', 20000.00, 2, 'Mensualidad 9', NULL, '2025-10-02 01:01:45', '2025-10-02 01:05:17', '2025-10-02 01:05:17'),
(105, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2025-08-14', 20000.00, 2, 'Mensualidad 10', NULL, '2025-10-02 01:01:45', '2025-10-02 01:05:19', '2025-10-02 01:05:19'),
(106, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2025-09-14', 20000.00, 1, 'Mensualidad 11', NULL, '2025-10-02 01:01:45', '2025-10-02 01:01:45', NULL),
(107, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2025-10-14', 20000.00, 1, 'Mensualidad 12', NULL, '2025-10-02 01:01:45', '2025-10-02 01:01:45', NULL),
(108, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2025-11-14', 20000.00, 1, 'Mensualidad 13', NULL, '2025-10-02 01:01:45', '2025-10-02 01:01:45', NULL),
(109, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2025-12-14', 20000.00, 1, 'Mensualidad 14', NULL, '2025-10-02 01:01:45', '2025-10-02 01:01:45', NULL),
(110, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2026-01-14', 20000.00, 1, 'Mensualidad 15', NULL, '2025-10-02 01:01:45', '2025-10-02 01:01:45', NULL),
(111, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2026-02-14', 20000.00, 1, 'Mensualidad 16', NULL, '2025-10-02 01:01:45', '2025-10-02 01:01:45', NULL),
(112, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2026-03-14', 20000.00, 1, 'Mensualidad 17', NULL, '2025-10-02 01:01:45', '2025-10-02 01:01:45', NULL),
(113, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2026-04-14', 20000.00, 1, 'Mensualidad 18', NULL, '2025-10-02 01:01:45', '2025-10-02 01:01:45', NULL),
(114, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2026-05-14', 20000.00, 1, 'Mensualidad 19', NULL, '2025-10-02 01:01:45', '2025-10-02 01:01:45', NULL),
(115, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2026-06-14', 20000.00, 1, 'Mensualidad 20', NULL, '2025-10-02 01:01:45', '2025-10-02 01:01:45', NULL),
(116, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2026-07-14', 20000.00, 1, 'Mensualidad 21', NULL, '2025-10-02 01:01:45', '2025-10-02 01:01:45', NULL),
(117, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2026-08-14', 20000.00, 1, 'Mensualidad 22', NULL, '2025-10-02 01:01:45', '2025-10-02 01:01:45', NULL),
(118, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2026-09-14', 20000.00, 1, 'Mensualidad 23', NULL, '2025-10-02 01:01:45', '2025-10-02 01:01:45', NULL),
(119, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2026-10-14', 20000.00, 1, 'Mensualidad 24', NULL, '2025-10-02 01:01:45', '2025-10-02 01:01:45', NULL),
(120, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2026-11-14', 20000.00, 1, 'Mensualidad 25', NULL, '2025-10-02 01:01:45', '2025-10-02 01:01:45', NULL),
(121, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2026-12-14', 20000.00, 1, 'Mensualidad 26', NULL, '2025-10-02 01:01:45', '2025-10-02 01:01:45', NULL),
(122, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2027-01-14', 20000.00, 1, 'Mensualidad 27', NULL, '2025-10-02 01:01:45', '2025-10-02 01:01:45', NULL),
(123, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2027-02-14', 20000.00, 1, 'Mensualidad 28', NULL, '2025-10-02 01:01:45', '2025-10-02 01:01:45', NULL),
(124, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2027-03-14', 20000.00, 1, 'Mensualidad 29', NULL, '2025-10-02 01:01:45', '2025-10-02 01:01:45', NULL),
(125, 1, 1, '1001', 1, 50472.84, 58920.03, 5501539.64, 0.00, '2027-04-14', 20000.00, 1, 'Mensualidad 30', NULL, '2025-10-02 01:01:45', '2025-10-02 01:01:45', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbr_password_reset`
--

CREATE TABLE `tbr_password_reset` (
  `IdReset` int(11) NOT NULL,
  `IdUsuario` int(11) NOT NULL,
  `Token` varchar(64) NOT NULL,
  `FechaCreacion` datetime NOT NULL,
  `FechaExpiracion` datetime NOT NULL,
  `Usado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbr_password_reset`
--

INSERT INTO `tbr_password_reset` (`IdReset`, `IdUsuario`, `Token`, `FechaCreacion`, `FechaExpiracion`, `Usado`) VALUES
(1, 1, 'aa949362936ce6173ca9e470b937b64af3a7d6b37c8dd48f10b4b25b95eccccf', '2025-09-29 06:26:40', '2025-09-29 07:26:40', 0),
(2, 1, 'c370c1c8f2320f7504479fbd0be0b3a9414ef5eb9e792ddf043da8add1e5ff29', '2025-09-29 07:18:56', '2025-09-29 08:18:56', 0),
(3, 1, '7a21e7d7363cf462ae86ebfc52d8fedede04f09f2c2660ef2385893b12b90447', '2025-09-30 09:29:28', '2025-09-30 10:29:28', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbr_usuario_desarrollos`
--

CREATE TABLE `tbr_usuario_desarrollos` (
  `Id` int(11) NOT NULL,
  `IdUsuario` int(11) NOT NULL,
  `IdDesarrollo` int(11) NOT NULL,
  `Dpto` varchar(10) NOT NULL,
  `File_Comprobante` varchar(255) DEFAULT NULL,
  `File_Planos` varchar(255) DEFAULT NULL,
  `File_Avance_Obra` varchar(255) DEFAULT NULL,
  `M2Inicial` decimal(8,2) NOT NULL,
  `Fecha_Firma` date DEFAULT NULL,
  `Vigencia` date DEFAULT NULL,
  `Estatus` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbr_usuario_desarrollos`
--

INSERT INTO `tbr_usuario_desarrollos` (`Id`, `IdUsuario`, `IdDesarrollo`, `Dpto`, `File_Comprobante`, `File_Planos`, `File_Avance_Obra`, `M2Inicial`, `Fecha_Firma`, `Vigencia`, `Estatus`) VALUES
(1, 1, 1, '201', 'SP201.pdf', 'SP201P.pdf', 'SP703Estufa.pdf', 128.00, '2024-11-14', '2027-04-01', 1),
(2, 1, 1, '701', 'SP701.pdf', 'SP701P.pdf', 'SP701Estufa.pdf', 109.00, '2024-11-14', '2027-04-01', 1),
(3, 1, 1, '1001', 'SP1001.pdf', 'SP1001P.pdf', 'SP1001Estufa.pdf', 109.00, '2024-11-14', '2027-04-01', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `tbc_perfiles`
--
ALTER TABLE `tbc_perfiles`
  ADD PRIMARY KEY (`IdPerfil`);

--
-- Indices de la tabla `tbp_desarrollos`
--
ALTER TABLE `tbp_desarrollos`
  ADD PRIMARY KEY (`IdDesarrollo`);

--
-- Indices de la tabla `tbp_usuarios`
--
ALTER TABLE `tbp_usuarios`
  ADD PRIMARY KEY (`IdUsuario`);

--
-- Indices de la tabla `tbr_avance_desarrollo`
--
ALTER TABLE `tbr_avance_desarrollo`
  ADD PRIMARY KEY (`IdAvance`),
  ADD UNIQUE KEY `uk_dev_cat` (`IdDesarrollo`,`Categoria`),
  ADD KEY `idx_dev` (`IdDesarrollo`);

--
-- Indices de la tabla `tbr_comprobantes_pago`
--
ALTER TABLE `tbr_comprobantes_pago`
  ADD PRIMARY KEY (`IdComprobante`),
  ADD KEY `IdPago` (`IdPago`),
  ADD KEY `idx_comprobantes_usuario_desarrollo` (`IdUsuario`,`IdDesarrollo`),
  ADD KEY `idx_comprobantes_usuario_desarrollo_dpto` (`IdUsuario`,`IdDesarrollo`,`Dpto`);

--
-- Indices de la tabla `tbr_desarrollos_costo_mensual`
--
ALTER TABLE `tbr_desarrollos_costo_mensual`
  ADD PRIMARY KEY (`Id`);

--
-- Indices de la tabla `tbr_pagos`
--
ALTER TABLE `tbr_pagos`
  ADD PRIMARY KEY (`IdPago`),
  ADD KEY `idx_usuario_fecha` (`IdUsuario`,`FechaPago`),
  ADD KEY `idx_ud_dpto_fecha` (`IdUsuario`,`IdDesarrollo`,`Dpto`,`FechaPago`),
  ADD KEY `idx_pagos_usuario` (`IdUsuario`),
  ADD KEY `idx_pagos_desarrollo` (`IdDesarrollo`,`Dpto`),
  ADD KEY `idx_pagos_fecha` (`FechaPago`);

--
-- Indices de la tabla `tbr_password_reset`
--
ALTER TABLE `tbr_password_reset`
  ADD PRIMARY KEY (`IdReset`),
  ADD KEY `IdUsuario` (`IdUsuario`),
  ADD KEY `idx_token` (`Token`),
  ADD KEY `idx_expiracion` (`FechaExpiracion`);

--
-- Indices de la tabla `tbr_usuario_desarrollos`
--
ALTER TABLE `tbr_usuario_desarrollos`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `uk_ud_unidad` (`IdUsuario`,`IdDesarrollo`,`Dpto`),
  ADD KEY `idx_ud_usuario` (`IdUsuario`),
  ADD KEY `idx_ud_desarrollo` (`IdDesarrollo`,`Dpto`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `tbc_perfiles`
--
ALTER TABLE `tbc_perfiles`
  MODIFY `IdPerfil` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tbp_desarrollos`
--
ALTER TABLE `tbp_desarrollos`
  MODIFY `IdDesarrollo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `tbp_usuarios`
--
ALTER TABLE `tbp_usuarios`
  MODIFY `IdUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tbr_avance_desarrollo`
--
ALTER TABLE `tbr_avance_desarrollo`
  MODIFY `IdAvance` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `tbr_comprobantes_pago`
--
ALTER TABLE `tbr_comprobantes_pago`
  MODIFY `IdComprobante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tbr_desarrollos_costo_mensual`
--
ALTER TABLE `tbr_desarrollos_costo_mensual`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `tbr_pagos`
--
ALTER TABLE `tbr_pagos`
  MODIFY `IdPago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT de la tabla `tbr_password_reset`
--
ALTER TABLE `tbr_password_reset`
  MODIFY `IdReset` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tbr_usuario_desarrollos`
--
ALTER TABLE `tbr_usuario_desarrollos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `tbr_comprobantes_pago`
--
ALTER TABLE `tbr_comprobantes_pago`
  ADD CONSTRAINT `tbr_comprobantes_pago_ibfk_1` FOREIGN KEY (`IdPago`) REFERENCES `tbr_pagos` (`IdPago`);

--
-- Filtros para la tabla `tbr_password_reset`
--
ALTER TABLE `tbr_password_reset`
  ADD CONSTRAINT `tbr_password_reset_ibfk_1` FOREIGN KEY (`IdUsuario`) REFERENCES `tbp_usuarios` (`IdUsuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
